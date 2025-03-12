<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STAFF LOAN APPLICATION FORM</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        .container {
            width: 100%;
            margin: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        h2, h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        .status {
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending {
            color: #ff9800;
        }
        .status-approved {
            color: #4caf50;
        }
        .status-rejected {
            color: #f44336;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>STAFF LOAN APPLICATION FORM</h2>
            <p>Application ID: {{ $loan->loan_id }} | Date: {{ $loan->application_date }}</p>
            <p>Status: <span class="status status-{{ strtolower($loan->status) }}">{{ $loan->status }}</span></p>
        </div>

        <h3>Applicant's Information</h3>
        <table>
            <tr>
                <td>Title</td>
                <td>{{ $employee->title }}</td>
            </tr>
            <tr>
                <td>Name in Full</td>
                <td>{{ $employee->full_name }}</td>
            </tr>
            <tr>
                <td>ID/Passport/Driver's License No.</td>
                <td>{{ $employee->national_id }}</td>
            </tr>
            <tr>
                <td>Date of Birth</td>
                <td>{{ $employee->date_of_birth }}</td>
            </tr>
            <tr>
                <td>Gender</td>
                <td>{{ $employee->gender }}</td>
            </tr>
            <tr>
                <td>Marital Status</td>
                <td>{{ $employee->marital_status }}</td>
            </tr>
            <tr>
                <td>Number of Dependents</td>
                <td>{{ $employee->dependents }}</td>
            </tr>
            <tr>
                <td>Physical Home Address</td>
                <td>{{ $employee->physical_address }}</td>
            </tr>
            <tr>
                <td>Accommodation Type</td>
                <td>{{ $employee->accommodation_type }}</td>
            </tr>
            <tr>
                <td>Postal Address</td>
                <td>{{ $employee->postal_address }}</td>
            </tr>
            <tr>
                <td>Telephone Numbers</td>
                <td>Tel: {{ $employee->telephone }} | Cell: {{ $employee->cell_phone }}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>{{ $employee->email }}</td>
            </tr>
            <tr>
                <td>Next of Kin</td>
                <td>{{ $employee->next_of_kin }}</td>
            </tr>
            <tr>
                <td>Next of Kin Address</td>
                <td>{{ $employee->next_of_kin_address }}</td>
            </tr>
            <tr>
                <td>Next of Kin Cell</td>
                <td>{{ $employee->next_of_kin_cell }}</td>
            </tr>
        </table>

        <h3>Employment Details</h3>
        <table>
            <tr>
                <td>Designation</td>
                <td>{{ $employee->position }}</td>
            </tr>
            <tr>
                <td>Department</td>
                <td>{{ $employee->department }}</td>
            </tr>
            <tr>
                <td>Monthly Salary</td>
                <td>Gross: ${{ number_format($employee->salary_gross, 2) }} | Net: ${{ number_format($employee->salary_net, 2) }}</td>
            </tr>
        </table>

        <h3>Banking Details</h3>
        <table>
            <tr>
                <td>Account with ZWMB Bank?</td>
                <td>{{ $banking_details->has_zwmb_account ? 'Yes' : 'No' }}</td>
            </tr>
            @if($banking_details->has_zwmb_account)
            <tr>
                <td>Years with ZWMB</td>
                <td>{{ $banking_details->years_with_zwmb }}</td>
            </tr>
            <tr>
                <td>Branch</td>
                <td>{{ $banking_details->branch }}</td>
            </tr>
            <tr>
                <td>Account Number</td>
                <td>{{ $banking_details->account_number }}</td>
            </tr>
            @endif
            <tr>
                <td>Previous Loan with ZWMB?</td>
                <td>{{ $banking_details->had_previous_loan ? 'Yes' : 'No' }}</td>
            </tr>
            @if($banking_details->had_previous_loan)
            <tr>
                <td>Previous Loan Amount</td>
                <td>${{ number_format($banking_details->previous_loan_amount, 2) }}</td>
            </tr>
            <tr>
                <td>Current Balance</td>
                <td>${{ number_format($banking_details->current_balance, 2) }}</td>
            </tr>
            <tr>
                <td>Loan Date</td>
                <td>{{ $banking_details->loan_date }}</td>
            </tr>
            <tr>
                <td>Maturity Date</td>
                <td>{{ $banking_details->maturity_date }}</td>
            </tr>
            <tr>
                <td>Monthly Repayment</td>
                <td>${{ number_format($banking_details->monthly_repayment, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td>Other Bank</td>
                <td>{{ $banking_details->other_bank }}</td>
            </tr>
            <tr>
                <td>Account Type</td>
                <td>{{ $banking_details->other_account_type }}</td>
            </tr>
        </table>

        <h3>Loan Details</h3>
        <table>
            <tr>
                <td>Loan Type</td>
                <td>{{ $loan_type->name }}</td>
            </tr>
            <tr>
                <td>Amount Requested</td>
                <td>${{ number_format($loan->amount, 2) }}</td>
            </tr>
            <tr>
                <td>Term (Months)</td>
                <td>{{ $loan->term_months }}</td>
            </tr>
            <tr>
                <td>Interest Rate</td>
                <td>{{ $loan_type->interest_rate }}%</td>
            </tr>
            <tr>
                <td>Purpose</td>
                <td>{{ $loan->purpose }}</td>
            </tr>
        </table>

        @if($loan->status != 'Pending' && $loan->review_notes)
        <h3>Review Notes</h3>
        <p>{{ $loan->review_notes }}</p>
        @endif

        <div class="footer">
            <p>This document was generated on {{ $generated_at }} and is for official use only.</p>
        </div>
    </div>
</body>
</html>