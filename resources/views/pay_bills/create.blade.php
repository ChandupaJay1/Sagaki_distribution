@extends('layouts.admin')

@section('title', $type . ' Bills - Create')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">{{ $type === 'Customer' ? 'Customer Payment' : 'Supplier Payment' }}</h4>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-danger-subtle text-danger"><i class="ri-error-warning-line me-1"></i>Date Control is Inactive.</span>
                <span class="text-muted small fw-bold"><i class="ri-money-dollar-circle-line me-1"></i>Rs: <span id="headerTotalAmount">0.00</span></span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <h5 class="card-title mb-0"><i class="ri-money-dollar-circle-line me-1"></i>{{ $type }} Bills</h5>
                <div class="float-end">
                    <button type="submit" form="createPayBillForm" name="action" value="pay_and_new" class="btn btn-info btn-sm me-1"><i class="ri-add-circle-fill me-1"></i>Pay And New</button>
                    <button type="submit" form="createPayBillForm" name="action" value="pay_selected" class="btn btn-success btn-sm me-1"><i class="ri-check-fill me-1"></i>Pay Selected Bill</button>
                    <button type="submit" form="createPayBillForm" name="action" value="save_and_print" class="btn btn-light border btn-sm text-dark me-1"><i class="ri-printer-fill me-1 text-muted"></i>Save And Print</button>
                    <button type="reset" form="createPayBillForm" class="btn btn-warning btn-sm"><i class="ri-refresh-line me-1"></i>Reset</button>
                </div>
            </div>
            <div class="card-body p-3">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form id="createPayBillForm" action="{{ route('pay-bills.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" id="entityBalance">
                    <span id="creditCount" class="d-none"></span>

                    <!-- Header Row 1 -->
                    <div class="row g-2 mb-2">
                        <div class="col-md-3">
                            @if($type === 'Supplier')
                                <label class="form-label small fw-bold mb-1">Paid To <span class="text-danger">*</span></label>
                                <select name="vendor_id" id="vendorSelect" class="form-select form-select-sm" required>
                                    <option value="">-- Select Vendor --</option>
                                    @foreach($vendors as $v)
                                        <option value="{{ $v->id }}">{{ $v->company_name ?? $v->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <label class="form-label small fw-bold mb-1">Received From <span class="text-danger">*</span></label>
                                <select name="customer_id" id="customerSelect" class="form-select form-select-sm" required>
                                    <option value="">-- Select Customer --</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->company_name ?? $c->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Class</label>
                            <select class="form-select form-select-sm bg-light" disabled>
                                <option value="">-- Select Class --</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Site <span class="text-danger">*</span></label>
                            <select name="location_id" class="form-select form-select-sm" required>
                                <option value="">-- Select Site --</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ $loc->name == 'Main' ? 'selected' : '' }}>{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Account <span class="text-danger">*</span></label>
                            <select name="account_id" class="form-select form-select-sm" required>
                                <option value="">-- Select Account --</option>
                                @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Header Row 2 -->
                    <div class="row g-2 mb-2">
                        <div class="col-md-2">
                            <label class="form-label small fw-bold mb-1">Deposit To <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm bg-light" disabled>
                                <option value="">LKR</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold mb-1">Amount <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0 small">LKR</span>
                                <input type="text" id="displayAmount" class="form-control form-control-sm border-start-0 text-end" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-bold mb-1">Ex.Rate</label>
                            <input type="text" class="form-control form-control-sm text-center bg-light" value="1.00" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold mb-1">Pmt.Method <span class="text-danger">*</span></label>
                            <select name="payment_method" id="paymentMethod" class="form-select form-select-sm" required>
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold mb-1">Cheque No</label>
                            <input type="text" name="cheque_no" id="chequeNo" class="form-control form-control-sm" placeholder="Cheque No" disabled>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Cus Pay No</label>
                            <input type="text" name="voucher_no" class="form-control form-control-sm bg-light" value="{{ $nextVoucherNo }}" readonly>
                        </div>
                    </div>

                    <!-- Header Row 3 -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Memo</label>
                            <textarea name="memo" class="form-control form-control-sm" rows="1" placeholder="Memo"></textarea>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold mb-1">Receipt Number</label>
                            <input type="text" class="form-control form-control-sm" placeholder="Receipt Number">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold mb-1">LKR Total Amount</label>
                            <input type="text" id="lkrTotalAmount" class="form-control form-control-sm bg-light text-end" readonly placeholder="0.00">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-bold mb-1">Rep <span class="text-danger">*</span></label>
                            <select name="rep_id" class="form-select form-select-sm">
                                <option value="">-- Select Rep --</option>
                                @foreach($reps ?? [] as $rep)
                                    <option value="{{ $rep->id }}">{{ $rep->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-bold mb-1">Pmt.Type</label>
                            <select class="form-select form-select-sm bg-light" disabled>
                                <option value=""></option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-bold mb-1">Cheque Date</label>
                            <input type="date" name="pd_cheque_date" id="pdChequeDate" class="form-control form-control-sm" disabled>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small fw-bold mb-1">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Job No</label>
                            <select class="form-select form-select-sm bg-light" disabled>
                                <option value="">-- Select Job No --</option>
                            </select>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive mb-3 border rounded">
                        <table class="table table-sm mb-0 align-middle text-center" id="billsTable">
                            <thead>
                                <tr>
                                    <th class="fw-bold py-2">Date</th>
                                    <th class="fw-bold py-2">Type</th>
                                    <th class="fw-bold py-2">Number</th>
                                    <th class="fw-bold py-2">Orig.Amt.</th>
                                    <th class="fw-bold py-2">Amt.Due</th>
                                    <th class="fw-bold py-2">Cr in use</th>
                                    <th class="fw-bold py-2">Discount</th>
                                    <th class="fw-bold py-2" style="width: 150px;">New Payment</th>
                                </tr>
                            </thead>
                            <tbody id="billsTableBody">
                                <tr class="empty-row">
                                    <td colspan="8" class="py-4 text-muted small italic bg-light">Select a {{ $type === 'Supplier' ? 'vendor' : 'customer' }} to load outstanding bills.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-3">
                        <!-- Left Panel: Tabs -->
                        <div class="col-md-7">
                            <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#credits-tab" role="tab" aria-selected="true">Available Credits</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#discount-tab" role="tab" aria-selected="false">Discount</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#credit-card-tab" role="tab" aria-selected="false">Credit Card</a>
                                </li>
                            </ul>
                            <div class="tab-content text-muted p-0">
                                <div class="tab-pane active" id="credits-tab" role="tabpanel">
<<<<<<< HEAD
                                    <div class="bg-light p-3 rounded d-flex justify-content-between align-items-center mb-3 border">
                                        <div class="flex-grow-1">
                                            <p class="mb-0 text-dark small fw-medium">This {{ $type === 'Supplier' ? 'vendor' : 'customer' }} has credit available <span class="fw-bold ms-5 fs-15 text-primary" id="availableCreditSpan">0.00</span></p>
                                        </div>
                                        <button type="button" id="viewCreditsBtn" class="btn btn-primary btn-sm"><i class="ri-eye-line me-1"></i>View</button>
=======
                                    <div class="credit-alert-box d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <p class="mb-2 text-dark small fw-medium">Number of credit available: <span id="creditCount">0</span></p>
                                            <p class="mb-0 text-dark small fw-medium">This {{ $type === 'Supplier' ? 'vendor' : 'customer' }} has credit available <span class="fw-bold ms-5 fs-15" id="availableCreditSpan">0.00</span></p>
                                        </div>
                                        <button type="button" id="viewCreditsBtn" class="btn btn-primary btn-sm"><i class="ri-bank-card-line me-1"></i>View Credits</button>
>>>>>>> 913b2f98292aa583ed4456f295deaf6aa9fdf31f
                                    </div>
                                </div>
                                <div class="tab-pane" id="discount-tab" role="tabpanel">
                                    <div class="bg-light p-3 rounded mb-3 border">
                                        <p class="text-muted small mb-0">No discounts applied.</p>
                                    </div>
                                </div>
                                <div class="tab-pane" id="credit-card-tab" role="tabpanel">
                                    <div class="bg-light p-3 rounded mb-3 border">
                                        <p class="text-muted small mb-0">No credit card info.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Panel: Amount for Select Invoice -->
                        <div class="col-md-5">
                            <div class="bg-light p-3 rounded mb-3 border">
                                <h6 class="fw-bold mb-3 text-dark fs-14">Amount for Select Invoice</h6>
                                <div class="row g-2 align-items-center mb-2">
                                    <div class="col-4">
                                        <label class="form-label small mb-0">Amount Due</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" id="summaryAmountDue" class="form-control form-control-sm text-end bg-light fw-bold" readonly value="0.00">
                                    </div>
                                </div>
                                <div class="row g-2 align-items-center mb-2">
                                    <div class="col-4">
                                        <label class="form-label small mb-0">Applied</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" id="summaryPayment" class="form-control form-control-sm text-end bg-light fw-bold text-success" readonly value="0.00">
                                    </div>
                                </div>
                                <div class="row g-2 align-items-center mb-2">
                                    <div class="col-4">
                                        <label class="form-label small mb-0">Over payment amount</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" id="summaryCredit" class="form-control form-control-sm text-end bg-light fw-bold text-info" readonly value="0.00">
                                    </div>
                                </div>
                                <div class="row g-2 align-items-center mb-0 border-top pt-2 mt-2">
                                    <div class="col-4">
                                        <label class="form-label small mb-0 fw-bold">Total Payment</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" id="summaryTotalPayment" class="form-control form-control-sm text-end bg-light fw-bold text-primary" readonly value="0.00">
                                    </div>
                                </div>
                                <div class="row g-2 align-items-center mb-0 d-none">
                                    <div class="col-4">
                                        <label class="form-label small mb-0">Discount</label>
                                    </div>
                                    <div class="col-8">
                                        <input type="text" id="summaryDiscount" class="form-control form-control-sm text-end bg-light" readonly value="0.00">
                                    </div>
                                </div>
                                <input type="hidden" name="total_amount" id="totalToPayInput" value="0">
                            </div>
                        </div>
                    </div>

                    <!-- Full Width Credits Table (Blue Circled in Image) -->
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="table-responsive mb-3 border rounded shadow-sm">
                                <table class="table table-sm table-bordered mb-0 align-middle text-center" style="border-top:2px solid #3577f1;">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th class="fw-bold py-2 small">Date</th>
                                            <th class="fw-bold py-2 small">Transaction No</th>
                                            <th class="fw-bold py-2 small">Type</th>
                                            <th class="fw-bold py-2 small">Credit Amount</th>
                                            <th class="fw-bold py-2 small">Credit Balance</th>
                                            <th class="fw-bold py-2 small">Amount To Use</th>
                                        </tr>
                                    </thead>
                                    <tbody id="creditsTableBody">
                                        <tr>
                                            <td colspan="6" class="py-3 bg-light text-muted small">No credits available</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="text-end p-2 border-top bg-white">
                                    <button type="button" class="btn btn-primary btn-sm"><i class="ri-settings-3-line me-1"></i>Set Credit</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Action Buttons -->
                    <div class="d-flex justify-content-end align-items-center gap-2 mt-2 border-top pt-3">
                        <button type="submit" name="action" value="pay_and_new" class="btn btn-info btn-sm"><i class="ri-add-circle-fill me-1"></i>Save & New</button>
                        <button type="submit" name="action" value="pay_selected" class="btn btn-success btn-sm"><i class="ri-save-line me-1"></i>Save & Close</button>
                        <button type="submit" name="action" value="save_and_print" class="btn btn-light border btn-sm text-dark"><i class="ri-printer-fill me-1 text-muted"></i>Save & Print</button>
                        <button type="reset" class="btn btn-warning btn-sm text-white"><i class="ri-refresh-line me-1"></i>Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
<<<<<<< HEAD
    /* Structure & Layout */
    .page-title-box h4 { font-weight: 700; }
    .card { border-radius: 0.25rem; }
    
    /* Table Styling */
    .table thead th { background-color: #3577f1 !important; color: #fff !important; border-color: #3577f1; font-weight: 500; text-transform: none; font-size: 0.8rem; vertical-align: middle; }
    .table tbody td { vertical-align: middle; font-size: 0.8rem; }
    
    .nav-tabs-custom .nav-link { font-weight: 500; padding: 0.5rem 1rem; }
    .nav-tabs-custom .nav-link.active { color: #3577f1; border-bottom: 2px solid #3577f1; background: transparent; }
    
    .form-label { font-size: 0.75rem; }
    .form-control-sm, .form-select-sm { font-size: 0.75rem; }

    /* Fix for dark theme visibility in inputs */
    .form-control:disabled, .form-control[readonly] { opacity: 0.7; }
=======
    /* Premium UI Enhancements */
    :root {
        --sg-primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        --sg-surface-light: #ffffff;
        --sg-border-light: #e2e8f0;
        --sg-text-muted: #64748b;
    }

    /* Card & Header Styling */
    .card {
        border: none !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05) !important;
        border-radius: 16px !important;
        overflow: hidden;
    }

    .card-header {
        background: #f8fafc !important;
        border-bottom: 1px solid var(--sg-border-light) !important;
        padding: 1rem 1.25rem !important;
    }

    .card-title {
        font-weight: 700 !important;
        color: #1e293b !important;
        letter-spacing: -0.02em;
    }

    /* Form Elements */
    .form-label {
        font-weight: 600 !important;
        color: #475569 !important;
        font-size: 0.75rem !important;
        margin-bottom: 0.4rem !important;
    }

    .form-control, .form-select {
        border-radius: 10px !important;
        border: 1px solid #cbd5e1 !important;
        padding: 0.5rem 0.75rem !important;
        font-size: 0.85rem !important;
        transition: all 0.2s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
    }

    .form-control:read-only, .form-control:disabled {
        background-color: #f1f5f9 !important;
        border-color: #e2e8f0 !important;
        color: #64748b !important;
    }

    /* Table Styling - Professional Look */
    .table-responsive {
        border-radius: 12px !important;
        overflow: hidden;
        border: 1px solid #e2e8f0 !important;
    }

    .table thead th {
        background-color: #f8fafc !important;
        color: #475569 !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.7rem !important;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 0.75rem !important;
    }

    .table tbody td {
        padding: 0.75rem !important;
        vertical-align: middle !important;
        color: #334155 !important;
        font-size: 0.85rem !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }

    .bill-row:hover td {
        background-color: #f8fafc !important;
    }

    /* Summary Panel */
    .summary-box {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 1.25rem;
    }

    .total-row {
        background: white;
        border-radius: 10px;
        padding: 0.75rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        margin-top: 1rem;
    }

    /* Buttons Modernization */
    .btn-sm {
        border-radius: 8px !important;
        padding: 0.4rem 0.8rem !important;
        font-weight: 600 !important;
        transition: all 0.2s ease !important;
    }

    .btn-info { background-color: #0ea5e9 !important; border: none !important; }
    .btn-success { background-color: #10b981 !important; border: none !important; }
    .btn-warning { background-color: #f59e0b !important; border: none !important; color: white !important; }
    
    .btn:hover {
        transform: translateY(-1px);
        filter: brightness(1.1);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    /* Dark Mode Support (using Layout's theme attribute) */
    [data-bs-theme="dark"] .card { background-color: #1e293b !important; border: 1px solid #334155 !important; }
    [data-bs-theme="dark"] .card-header { background-color: #0f172a !important; border-bottom: 1px solid #334155 !important; }
    [data-bs-theme="dark"] .card-title { color: #f1f5f9 !important; }
    [data-bs-theme="dark"] .form-control, [data-bs-theme="dark"] .form-select {
        background-color: #0f172a !important;
        border-color: #334155 !important;
        color: #f1f5f9 !important;
    }
    [data-bs-theme="dark"] .form-control:read-only { background-color: #1e293b !important; color: #94a3b8 !important; }
    [data-bs-theme="dark"] .table thead th { background-color: #0f172a !important; color: #94a3b8 !important; border-bottom-color: #334155 !important; }
    [data-bs-theme="dark"] .table tbody td { color: #cbd5e1 !important; border-bottom-color: #334155 !important; }
    [data-bs-theme="dark"] .summary-box { background: #0f172a !important; border-color: #334155 !important; }
    [data-bs-theme="dark"] .total-row { background: #1e293b !important; color: white !important; }
    [data-bs-theme="dark"] .text-dark { color: #f1f5f9 !important; }
    [data-bs-theme="dark"] .bg-light { background-color: #0f172a !important; color: #94a3b8 !important; }

    /* Additional Refinements */
    .credit-alert-box {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 12px;
        padding: 1rem;
        transition: all 0.3s ease;
    }

    [data-bs-theme="dark"] .credit-alert-box {
        background: rgba(20, 83, 45, 0.2);
        border-color: rgba(34, 197, 94, 0.3);
    }

    .nav-tabs-custom .nav-link {
        font-weight: 600;
        border-radius: 8px 8px 0 0;
        padding: 0.6rem 1.2rem;
        border: none;
        color: var(--sg-text-muted);
    }

    .nav-tabs-custom .nav-link.active {
        background: white;
        color: #4f46e5 !important;
        box-shadow: 0 -4px 10px rgba(0,0,0,0.03);
    }

    [data-bs-theme="dark"] .nav-tabs-custom .nav-link.active {
        background: #1e293b;
        color: #818cf8 !important;
    }
>>>>>>> 913b2f98292aa583ed4456f295deaf6aa9fdf31f
</style>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const type = "{{ $type }}";
        const entitySelectWrapper = document.getElementById(type === 'Supplier' ? 'vendorSelect' : 'customerSelect');
        const billsTableBody = document.getElementById('billsTableBody');
        const entityBalanceInput = document.getElementById('entityBalance');
        const lkrTotalAmountInput = document.getElementById('lkrTotalAmount');
        const paymentMethodSelect = document.getElementById('paymentMethod');
        const chequeNoInput = document.getElementById('chequeNo');
        const pdChequeDateInput = document.getElementById('pdChequeDate');
        const availableCreditSpan = document.getElementById('availableCreditSpan');
        const creditCountSpan = document.getElementById('creditCount');
        const creditsTableBody = document.getElementById('creditsTableBody');
        const viewCreditsBtn = document.getElementById('viewCreditsBtn');

        let cachedCredits = [];

        // Handle Entity selection
        const initEntitySelect = () => {
            if (entitySelectWrapper.tomselect) {
                entitySelectWrapper.tomselect.on('change', function(value) {
                    fetchOutstandingBills(value);
                });
            } else {
                setTimeout(initEntitySelect, 100);
            }
        };
        initEntitySelect();

        // Handle View Credits button
        viewCreditsBtn.addEventListener('click', function() {
            renderCredits(cachedCredits);
            // Scroll to credits table
            creditsTableBody.closest('.table-responsive').scrollIntoView({ behavior: 'smooth' });
        });

        // Handle Payment Method change
        paymentMethodSelect.addEventListener('change', function() {
            if (this.value === 'Cheque') {
                chequeNoInput.disabled = false;
                pdChequeDateInput.disabled = false;
            } else {
                chequeNoInput.disabled = true;
                pdChequeDateInput.disabled = true;
                chequeNoInput.value = '';
                pdChequeDateInput.value = '';
            }
        });

        // Form Validation before submission
        document.getElementById('createPayBillForm').addEventListener('submit', function(e) {
            const entityId = entitySelectWrapper.value;
            const totalPay = parseFloat(document.getElementById('totalToPayInput').value) || 0;

            if (!entityId) {
                e.preventDefault();
                alert(`Please select a ${type === 'Supplier' ? 'Vendor' : 'Customer'} first.`);
                return false;
            }

            if (totalPay <= 0) {
                e.preventDefault();
                alert('Total payment amount must be greater than zero.');
                return false;
            }
        });

        function fetchOutstandingBills(entityId) {
            if (!entityId) {
                clearTable();
                return;
            }

            billsTableBody.innerHTML = '<tr><td colspan="8" class="py-4 text-center bg-light"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading...</td></tr>';

            const endpoint = type === 'Supplier' 
                ? `/api/vendors/${entityId}/outstanding-bills` 
                : `/api/customers/${entityId}/outstanding-invoices`;

            fetch(endpoint)
                .then(response => response.json())
                .then(data => {
                    const entity = type === 'Supplier' ? data.vendor : data.customer;
                    const items = type === 'Supplier' ? data.bills : data.invoices;

                    if (entity) {
                        entityBalanceInput.value = parseFloat(entity.credit_limit || 0).toLocaleString(undefined, {minimumFractionDigits: 2});
                    }
                    
                    cachedCredits = data.credits || [];
                    let totalCredit = cachedCredits.reduce((sum, c) => sum + parseFloat(c.total_amount), 0);
                    availableCreditSpan.textContent = totalCredit.toLocaleString(undefined, {minimumFractionDigits: 2});
                    creditCountSpan.textContent = cachedCredits.length;

                    if (items && items.length > 0) {
                        renderBills(items);
                    } else {
                        billsTableBody.innerHTML = `<tr><td colspan="8" class="py-4 text-muted bg-light">No outstanding ${type === 'Supplier' ? 'bills' : 'invoices'} found.</td></tr>`;
                        updateTotals();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    billsTableBody.innerHTML = '<tr><td colspan="8" class="py-4 text-danger bg-light">Error loading data. Please try again.</td></tr>';
                });
        }

        function renderCredits(credits) {
            if (!credits || credits.length === 0) {
                creditsTableBody.innerHTML = '<tr><td colspan="6" class="py-3 bg-light text-muted small">No credits available</td></tr>';
                return;
            }

            let html = '';
            credits.forEach((credit, index) => {
                const amount = parseFloat(credit.total_amount) || 0;
                html += `
                <tr>
                    <td class="small">${credit.date || '—'}</td>
                    <td class="small text-primary fw-medium">${credit.return_no || '—'}</td>
                    <td class="small">Return</td>
                    <td class="text-end small">${amount.toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                    <td class="text-end small">${amount.toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-end credit-use-input" 
                               step="any" min="0" max="${amount}" placeholder="0.00">
                    </td>
                </tr>`;
            });
            creditsTableBody.innerHTML = html;
        }

        function renderBills(items) {
            let html = '';
            items.forEach((item, index) => {
                const totalAmount = parseFloat(item.total_amount) || 0;
                const billNo = type === 'Supplier' ? item.grn_no : item.invoice_no;
                const idField = type === 'Supplier' ? 'grn_id' : 'invoice_id';

                html += `
                <tr class="bill-row">
                    <td>${item.date || '—'}</td>
                    <td class="text-muted">${type === 'Supplier' ? 'Bill' : 'Invoice'}</td>
                    <td>${billNo || '—'}</td>
                    <td class="text-end">${totalAmount.toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                    <td class="text-end">${totalAmount.toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                    <td><input type="text" class="form-control form-control-sm text-end bg-light" readonly value="0.00"></td>
                    <td><input type="text" class="form-control form-control-sm text-end bg-light" readonly value="0.00"></td>
                    <td>
                        <input type="hidden" name="items[${index}][${idField}]" value="${item.id}">
                        <input type="number" name="items[${index}][amount_to_pay]" class="form-control form-control-sm text-end pay-input" 
                               step="any" min="0" max="${totalAmount}" data-due="${totalAmount}" placeholder="0.00">
                    </td>
                </tr>`;
            });
            billsTableBody.innerHTML = html;
            initTableEvents();
            updateTotals();
        }

        function clearTable() {
            billsTableBody.innerHTML = `<tr class="empty-row"><td colspan="8" class="py-4 text-muted small italic bg-light">Select a ${type === 'Supplier' ? 'vendor' : 'customer'} to load outstanding bills.</td></tr>`;
            creditsTableBody.innerHTML = '<tr><td colspan="6" class="py-3 bg-light text-muted small">No credits available</td></tr>';
            availableCreditSpan.textContent = '0.00';
            cachedCredits = [];
            updateTotals();
        }

        function initTableEvents() {
            document.querySelectorAll('.pay-input').forEach(input => {
                input.addEventListener('input', updateTotals);
                
                // Allow double click to set full amount
                input.addEventListener('dblclick', function() {
                    this.value = this.dataset.due;
                    updateTotals();
                });
            });
        }

        function updateTotals() {
            let totalDue = 0;
            let totalPay = 0;

            document.querySelectorAll('.bill-row').forEach(row => {
                const payInput = row.querySelector('.pay-input');
                totalDue += parseFloat(payInput.dataset.due) || 0;
                totalPay += parseFloat(payInput.value) || 0;
            });

            document.getElementById('summaryAmountDue').value = totalDue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('summaryPayment').value = totalPay.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('summaryTotalPayment').value = totalPay.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('headerTotalAmount').textContent = totalPay.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('totalToPayInput').value = totalPay.toFixed(2);
            lkrTotalAmountInput.value = totalPay.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
    });
</script>
@endpush
