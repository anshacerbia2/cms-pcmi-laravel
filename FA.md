## 💰 Financial & Accounting (FA) Module Concepts

The application includes a comprehensive Finance & Accounting module to track the full business lifecycle from initial proposal to final payment reconciliation.

### 1. Business Flows
The system distinguishes between two main project types:

- **Regular Projects**: 
  - **Flow**: Proposal (BoQ) ➔ Project ➔ Invoice.
  - Invoices are generated based on specific items (Sales Items) selected from a winning Proposal.
- **FIT Projects**: 
  - **Flow**: Project ➔ Expense Collection ➔ Invoice.
  - Used for smaller/variable costs (e.g., flight tickets). Expenses are gathered and summarized into a single Sales Item during Invoice creation.

### 2. Vouchers vs. Discounts
In this system, a **Voucher** is an **Accounting Document** (a proof of record), not a discount code.

- **Receive Voucher (RV)**: A record of incoming funds (e.g., a bank transfer from a client). It proves that money has entered the company's account.
- **Payment Voucher (PV)**: A record of outgoing funds (e.g., paying a supplier or an employee's expense).

### 3. Payment Reconciliation (Matching)
The "Magic" happens during reconciliation, managed by the **FA Officer**:

1. **Invoice Issued**: The system tracks the expected revenue. Status: `UNPAID`.
2. **RV Created**: When money arrives, an FA Officer records a **Receive Voucher**.
3. **Linking & Deductions**: The RV is linked to one or more Invoices. At this stage, **Deductions** (e.g., **PPh-23 2%**, Bank Charges) are recorded.
4. **Auto-Status**: The system verifies if `RV Amount + Deductions == Invoice Amount`. If they match, the Invoice status automatically updates to `FULLY PAID`.

### 4. Taxation Types
- **No Tax**: No VAT/PPh logic applied.
- **Tax - Non WAPU**: Standard VAT (Ppn) collected by the company.
- **Tax - WAPU**: VAT is withheld by the client (Wajib Pungut), requiring specific reconciliation logic during payment.

---
