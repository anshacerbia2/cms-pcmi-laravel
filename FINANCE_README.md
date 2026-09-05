# 💰 Finance & Accounting (FA) Module Documentation

The FA module handles the business lifecycle from invoicing to payment reconciliation using **Receive Vouchers (RV)** and **Payment Vouchers (PV)**.

## 📥 Receive Voucher (RV) Flow
**User Story**: Tracking incoming money from clients or other sources.

### Components Involved:
- **Models**: `ReceiveVoucher`, `Invoice`, `PcmiBank`, `Customer`, `Supplier`, `User`.
- **Database**: `receive_vouchers`, `invoice_receive_voucher` (pivot).

### Scenarios:
1.  **Invoice Payment (Full/Partial)**:
    - **FA Officer** records an RV.
    - **FA Officer** links the RV to one or more Invoices and **manually enters** the `amount_applied` and deductions (**PPh23**, **Bank Charges**, **WAPU**).
    - System calculates the `balance_due` and updates the status: `UNPAID` ➔ `PARTLY PAID` ➔ `FULLY PAID`.
2.  **Refund/Retur**:
    - Recording money returned to the company from various sources.
3.  **Returning Deposit / Staff Loan**:
    - Recording repayment of internal loans or return of corporate deposits.

---

## 📤 Payment Voucher (PV) Flow
**User Story**: Tracking outgoing money for business operations or expenses.

### Components Involved:
- **Models**: `PaymentVoucher`, `PurchaseOrder`, `PcmiBank`, `Supplier`, `User`.
- **Database**: `payment_vouchers`.

### Scenarios:
1.  **COGS / AP Trade (Direct Business Cost)**:
    - Linked to a **Purchase Order (PO)**.
    - Tracks payments for inventory or project-specific services.
    - Helps maintain the PO-Invoice matching loop.
2.  **Operational Expense**:
    - Payments for `Personal Expense` (BPJS, Meals, Honorarium, Bonus).
    - Payments for `Office/General Expense` (Utility, Marketing, etc.).
3.  **Disbursing Staff Loan**:
    - Recording money lent to employees from company funds.

---

## ⚙️ Reconciliation Logic (Automated Calculation)
The `recalculateReconciliation()` function ensures data integrity based on the values **input by the FA Officer**:

- **Formula**: `Balance Due = Total Invoice Amount - (Applied RV + PPh23 + Bank Charge + Wapu + Adjustment)`.
- **Automatic Status**:
    - If `Balance Due <= 0`: **FULLY PAID**
    - If `Applied Amount > 0`: **PARTLY PAID**
    - Otherwise: **UNPAID**

## 🏦 Bank Integration
All vouchers must specify a `PCMI Bank`, ensuring every penny is accounted for in a specific company account.
- **Rule**: "No Tax" invoices MUST use the specific BCA account (ending in 666).
