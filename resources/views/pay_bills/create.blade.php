@extends('layouts.admin')

@section('title', $type . ' Bills - Create')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">{{ $type }} Bills</h4>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-danger-subtle text-danger"><i class="ri-error-warning-line me-1"></i>Date Control is Inactive.</span>
                <span class="text-muted small fw-bold">Rs: <span id="headerTotalAmount">0.00</span></span>
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

                    <!-- Header Row 1 -->
                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            @if($type === 'Supplier')
                                <label class="form-label small fw-bold mb-1">Vendor Name <span class="text-danger">*</span></label>
                                <select name="vendor_id" id="vendorSelect" class="form-select form-select-sm" required>
                                    <option value="">-- Select Vendor --</option>
                                    @foreach($vendors as $v)
                                        <option value="{{ $v->id }}">{{ $v->company_name ?? $v->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <label class="form-label small fw-bold mb-1">Customer Name <span class="text-danger">*</span></label>
                                <select name="customer_id" id="customerSelect" class="form-select form-select-sm" required>
                                    <option value="">-- Select Customer --</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}">{{ $c->company_name ?? $c->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold mb-1">Site <span class="text-danger">*</span></label>
                            <select name="location_id" class="form-select form-select-sm" required>
                                <option value="">-- Select Site --</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ $loc->name == 'Main' ? 'selected' : '' }}>{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold mb-1">Voucher No</label>
                            <input type="text" name="voucher_no" class="form-control form-control-sm bg-light" value="{{ $nextVoucherNo }}" readonly>
                        </div>
                    </div>

                    <!-- Header Row 2 -->
                    <div class="row g-2 mb-2">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Balance</label>
                            <input type="text" id="entityBalance" class="form-control form-control-sm bg-light" readonly placeholder="0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Method <span class="text-danger">*</span></label>
                            <select name="payment_method" id="paymentMethod" class="form-select form-select-sm" required>
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Cheque No</label>
                            <input type="text" name="cheque_no" id="chequeNo" class="form-control form-control-sm" placeholder="Cheque No" disabled>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <!-- Header Row 3 -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold mb-1">Memo</label>
                            <textarea name="memo" class="form-control form-control-sm" rows="1" placeholder="Memo"></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">LKR Total Amount</label>
                            <input type="text" id="lkrTotalAmount" class="form-control form-control-sm bg-light" readonly placeholder="0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">PD Cheque Date</label>
                            <input type="date" name="pd_cheque_date" id="pdChequeDate" class="form-control form-control-sm" disabled>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive mb-3 border rounded">
                        <table class="table table-sm mb-0 align-middle text-center" id="billsTable">
                            <thead>
                                <tr>
                                    <th class="fw-bold py-2">Date</th>
                                    <th class="fw-bold py-2">Ref No / JO No</th>
                                    <th class="fw-bold py-2">Bill No / Inv No</th>
                                    <th class="fw-bold py-2">Type</th>
                                    <th class="fw-bold py-2">Amt.Due</th>
                                    <th class="fw-bold py-2">Discount</th>
                                    <th class="fw-bold py-2">Credit Used</th>
                                    <th class="fw-bold py-2" style="width: 150px;">Amt.To Pay</th>
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
                        <!-- Left Panel -->
                        <div class="col-md-7">
                            <ul class="nav nav-tabs nav-tabs-custom nav-success mb-3" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#credits-tab" role="tab" aria-selected="true">Available Credits</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#discount-tab" role="tab" aria-selected="false">Discount</a>
                                </li>
                            </ul>
                            <div class="tab-content text-muted p-0">
                                <div class="tab-pane active" id="credits-tab" role="tabpanel">
                                    <div class="credit-alert-box d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <p class="mb-2 text-dark small fw-medium">Number of credit available: <span id="creditCount">0</span></p>
                                            <p class="mb-0 text-dark small fw-medium">This {{ $type === 'Supplier' ? 'vendor' : 'customer' }} has credit available <span class="fw-bold ms-5 fs-15" id="availableCreditSpan">0.00</span></p>
                                        </div>
                                        <button type="button" id="viewCreditsBtn" class="btn btn-primary btn-sm"><i class="ri-bank-card-line me-1"></i>View Credits</button>
                                    </div>
                                </div>
                                <div class="tab-pane" id="discount-tab" role="tabpanel">
                                    <div class="bg-light p-3 rounded mb-3 border">
                                        <p class="text-muted small mb-0">No discounts applied.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Credits Table -->
                            <div class="table-responsive mb-3 border rounded">
                                <table class="table table-sm mb-0 align-middle text-center">
                                    <thead>
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
                                    <button type="button" class="btn btn-primary btn-sm"><i class="ri-add-circle-fill me-1"></i>Set Credit</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Panel (Totals) -->
                        <div class="col-md-5">
                            <div class="summary-box mb-3">
                                <h6 class="fw-bold mb-3 text-dark fs-14">Total Payment</h6>
                                <div class="d-flex justify-content-between align-items-center mb-2 small text-dark">
                                    <span style="width: 120px;">Amount Due</span>
                                    <span>:</span>
                                    <span id="summaryAmountDue" class="text-end flex-grow-1 fw-medium">0.00</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2 small text-dark">
                                    <span style="width: 120px;">Payment</span>
                                    <span>:</span>
                                    <span id="summaryPayment" class="text-end flex-grow-1 fw-medium">0.00</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2 small text-dark">
                                    <span style="width: 120px;">Credit</span>
                                    <span>:</span>
                                    <span id="summaryCredit" class="text-end flex-grow-1 fw-medium">0.00</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2 small text-dark">
                                    <span style="width: 120px;">Discount</span>
                                    <span>:</span>
                                    <span id="summaryDiscount" class="text-end flex-grow-1 fw-medium">0.00</span>
                                </div>
                                <div class="total-row d-flex justify-content-between align-items-center mt-3 small text-dark fw-bold fs-13">
                                    <span style="width: 120px;">Total Payment</span>
                                    <span>:</span>
                                    <span id="summaryTotalPayment" class="text-end flex-grow-1">0.00</span>
                                    <input type="hidden" name="total_amount" id="totalToPayInput" value="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Action Buttons -->
                    <div class="d-flex justify-content-end align-items-center gap-2 mt-4 border-top pt-3">
                        <button type="submit" name="action" value="pay_and_new" class="btn btn-info btn-sm"><i class="ri-add-circle-fill me-1"></i>Pay And New</button>
                        <button type="submit" name="action" value="pay_selected" class="btn btn-success btn-sm"><i class="ri-check-fill me-1"></i>Pay Selected Bill</button>
                        <button type="submit" name="action" value="save_and_print" class="btn btn-light border btn-sm text-dark"><i class="ri-printer-fill me-1 text-muted"></i>Save And Print</button>
                        <button type="reset" class="btn btn-warning btn-sm"><i class="ri-refresh-line me-1"></i>Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
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
                const refNo = type === 'Supplier' ? (item.reference_no || '—') : '—';
                const idField = type === 'Supplier' ? 'grn_id' : 'invoice_id';

                html += `
                <tr class="bill-row">
                    <td>${item.date || '—'}</td>
                    <td>${refNo}</td>
                    <td>${billNo || '—'}</td>
                    <td class="text-muted">${type === 'Supplier' ? 'Bill' : 'Invoice'}</td>
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
            entityBalanceInput.value = '0.00';
            availableCreditSpan.textContent = '0.00';
            creditCountSpan.textContent = '0';
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

            document.getElementById('summaryAmountDue').textContent = totalDue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('summaryPayment').textContent = totalPay.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('summaryTotalPayment').textContent = totalPay.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('headerTotalAmount').textContent = totalPay.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('totalToPayInput').value = totalPay.toFixed(2);
            lkrTotalAmountInput.value = totalPay.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
    });
</script>
@endpush
