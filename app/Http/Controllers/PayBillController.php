<?php

namespace App\Http\Controllers;

use App\Models\PayBill;
use App\Models\PayBillItem;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Invoice;
use App\Models\Grn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayBillController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'Supplier');
        $payments = PayBill::with(['vendor', 'customer'])
            ->where('type', $type)
            ->latest()
            ->paginate(10);
        
        return view('pay_bills.index', compact('payments', 'type'));
    }

    public function createSupplier(Request $request)
    {
        return $this->createInternal($request, 'Supplier');
    }

    public function createCustomer(Request $request)
    {
        return $this->createInternal($request, 'Customer');
    }

    public function create(Request $request)
    {
        $type = $request->get('type', 'Supplier');
        return $this->createInternal($request, $type);
    }

    private function createInternal(Request $request, $type)
    {
        $vendors = Vendor::orderBy('company_name')->get();
        $customers = Customer::orderBy('company_name')->get();
        $locations = Location::where('is_active', 1)->orderBy('name')->get();
        
        // Generate next Voucher Number
        $lastPayment = PayBill::where('type', $type)->orderBy('id', 'desc')->first();
        $prefix = $type === 'Supplier' ? 'RV/' : 'CRV/';
        if (!$lastPayment) {
            $nextVoucherNo = $prefix . '00001';
        } else {
            // Extract the numeric part after /
            $lastNoStr = $lastPayment->voucher_no;
            if (str_contains($lastNoStr, '/')) {
                $parts = explode('/', $lastNoStr);
                $lastNo = (int) end($parts);
            } else {
                $lastNo = (int) preg_replace('/[^0-9]/', '', $lastNoStr);
            }
            $nextVoucherNo = $prefix . str_pad($lastNo + 1, 5, '0', STR_PAD_LEFT);
        }

        return view('pay_bills.create', compact('vendors', 'customers', 'locations', 'nextVoucherNo', 'type'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:Supplier,Customer',
            'vendor_id' => 'required_if:type,Supplier|exists:vendors,id|nullable',
            'customer_id' => 'required_if:type,Customer|exists:customers,id|nullable',
            'location_id' => 'required|exists:locations,id',
            'date' => 'required|date',
            'voucher_no' => 'required|unique:pay_bills,voucher_no',
            'payment_method' => 'required|string',
            'cheque_no' => 'nullable|string',
            'pd_cheque_date' => 'nullable|date',
            'memo' => 'nullable|string',
            'total_amount' => 'required|numeric|min:0',
            'items' => 'required|array',
            'items.*.grn_id' => 'required_if:type,Supplier|exists:grns,id|nullable',
            'items.*.invoice_id' => 'required_if:type,Customer|exists:invoices,id|nullable',
            'items.*.amount_to_pay' => 'required|numeric|min:0',
        ]);

        $payBill = DB::transaction(function () use ($validated, $request) {
            $payBill = PayBill::create([
                'type' => $validated['type'],
                'vendor_id' => $validated['vendor_id'] ?? null,
                'customer_id' => $validated['customer_id'] ?? null,
                'location_id' => $validated['location_id'],
                'voucher_no' => $validated['voucher_no'],
                'date' => $validated['date'],
                'total_amount' => $validated['total_amount'],
                'payment_method' => $validated['payment_method'],
                'cheque_no' => $request->cheque_no,
                'pd_cheque_date' => $request->pd_cheque_date,
                'memo' => $request->memo,
                'status' => 'Paid',
            ]);

            foreach ($request->items as $item) {
                if (isset($item['amount_to_pay']) && $item['amount_to_pay'] > 0) {
                    $billNo = '';
                    $billDate = null;
                    $dueDate = null;
                    $billAmount = 0;

                    if ($validated['type'] === 'Supplier') {
                        $grn = Grn::find($item['grn_id']);
                        $billNo = $grn->grn_no;
                        $billDate = $grn->date;
                        $dueDate = $grn->due_date;
                        $billAmount = $grn->total_amount;
                    } else {
                        $invoice = Invoice::find($item['invoice_id']);
                        $billNo = $invoice->invoice_no;
                        $billDate = $invoice->date;
                        $dueDate = $invoice->due_date; // Assuming invoice has due_date, check model
                        $billAmount = $invoice->total_amount;
                    }

                    PayBillItem::create([
                        'pay_bill_id' => $payBill->id,
                        'grn_id' => $item['grn_id'] ?? null,
                        'invoice_id' => $item['invoice_id'] ?? null,
                        'bill_no' => $billNo,
                        'bill_date' => $billDate,
                        'due_date' => $dueDate,
                        'bill_amount' => $billAmount,
                        'amount_to_pay' => $item['amount_to_pay'],
                    ]);
                }
            }
            return $payBill;
        });

        if ($request->action === 'pay_and_new') {
            $routeName = $validated['type'] === 'Supplier' ? 'pay-bills.supplier.create' : 'pay-bills.customer.create';
            return redirect()->route($routeName)->with('success', 'Payment recorded successfully.');
        }

        if ($request->action === 'save_and_print') {
            return redirect()->route('pay-bills.print', $payBill->id);
        }

        return redirect()->route('pay-bills.index', ['type' => $validated['type']])->with('success', 'Payment recorded successfully.');
    }

    public function print($id)
    {
        $payment = PayBill::with(['vendor', 'customer', 'items.grn', 'items.invoice'])->findOrFail($id);
        return view('pay_bills.print', compact('payment'));
    }

    public function show($id)
    {
        $payment = PayBill::with(['vendor', 'customer', 'items.grn', 'items.invoice'])->findOrFail($id);
        return view('pay_bills.show', compact('payment'));
    }

    public function destroy($id)
    {
        $payment = PayBill::findOrFail($id);
        $payment->delete(); // Cascade delete will handle items
        return redirect()->route('pay-bills.index')->with('success', 'Payment deleted successfully.');
    }
}
