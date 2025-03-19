{{-- resources/views/employee/loan/create.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Staff Loan Application Form</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 20px;
            background-color: #f4f4f4;
        }
        .container {
            width: 90%;
            max-width: 800px;
            margin: auto;
            overflow: hidden;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
        .checkbox-group label {
            margin-right: 20px;
        }
        input[type="text"], input[type="number"], input[type="date"], input[type="tel"], input[type="email"], textarea, select {
            width: calc(100% - 16px);
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 1em;
        }
        textarea {
            resize: vertical;
        }
        label {
            display: inline-block;
            margin-right: 10px;
            color: #555;
        }
        input[type="radio"], input[type="checkbox"] {
            margin-right: 5px;
        }
        p {
            margin-bottom: 10px;
        }
        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
            color: #333;
        }
        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: calc(100% - 12px);
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 1em;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        h2{
            color: #333;
            margin-bottom: 1em;
        }
        .text-danger {
            color: #dc3545;
            font-size: 0.85em;
            margin-top: -15px;
            margin-bottom: 10px;
        }
        .navbar {
            background-color: #4361ee;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .user-profile {
            background-color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #4361ee;
        }
        .logout-btn {
            background-color: transparent;
            border: 1px solid white;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            background-color: white;
            color: #4361ee;
        }
        @media (max-width: 600px) {
            .container {
                padding: 10px;
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">Loan Management System</div>
        <div class="user-info">
            <div class="user-profile" id="user-initial">{{ auth()->user()->username[0] }}</div>
            <span id="username-display">{{ auth()->user()->username }}</span>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </nav>

    <div class="container">
        <form id="loanApplicationForm" action="{{ route('employee.loan.store') }}" method="POST">
            @csrf
            <h2>STAFF LOAN APPLICATION FORM</h2>

            @if(session('success'))
                <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <h3>Applicant's Information</h3>
            <table>
                <tr>
                    <td>Title (Prof/Dr/Mr/Mrs/Ms)</td>
                    <td>
                        <select name="title" required>
                            <option value="" {{ old('title') == '' ? 'selected' : '' }}>(Select a title)</option>
                            <option value="Prof" {{ old('title', $employee->title ?? '') == 'Prof' ? 'selected' : '' }}>Prof</option>
                            <option value="Dr" {{ old('title', $employee->title ?? '') == 'Dr' ? 'selected' : '' }}>Dr</option>
                            <option value="Mr" {{ old('title', $employee->title ?? '') == 'Mr' ? 'selected' : '' }}>Mr</option>
                            <option value="Mrs" {{ old('title', $employee->title ?? '') == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                            <option value="Ms" {{ old('title', $employee->title ?? '') == 'Ms' ? 'selected' : '' }}>Ms</option>
                        </select>
                        @error('title')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Name in Full</td>
                    <td>
                        <input type="text" name="full_name" value="{{ old('full_name', $employee->full_name ?? '') }}" required>
                        @error('full_name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>ID/Passport/Driver's License No.</td>
                    <td>
                        <input type="text" name="national_id" value="{{ old('national_id', $employee->national_id ?? '') }}" required>
                        @error('national_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Date of Birth</td>
                    <td>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $employee->date_of_birth ?? '') }}" required>
                        @error('date_of_birth')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Gender</td>
                    <td>
                        <select name="gender" required>
                            <option value="" {{ old('gender') == '' ? 'selected' : '' }}>Select</option>
                            <option value="Male" {{ old('gender', $employee->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender', $employee->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender', $employee->gender ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Marital Status</td>
                    <td>
                        <select name="marital_status" required>
                            <option value="" {{ old('marital_status') == '' ? 'selected' : '' }}>Select</option>
                            <option value="Married" {{ old('marital_status', $employee->marital_status ?? '') == 'Married' ? 'selected' : '' }}>Married</option>
                            <option value="Single" {{ old('marital_status', $employee->marital_status ?? '') == 'Single' ? 'selected' : '' }}>Single</option>
                            <option value="Divorced" {{ old('marital_status', $employee->marital_status ?? '') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                            <option value="Widowed" {{ old('marital_status', $employee->marital_status ?? '') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                        </select>
                        @error('marital_status')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Number of Dependents</td>
                    <td>
                        <input type="number" name="dependents" value="{{ old('dependents', $employee->dependents ?? 0) }}" min="0" required>
                        @error('dependents')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Physical Home Address</td>
                    <td>
                        <input type="text" name="physical_address" value="{{ old('physical_address', $employee->physical_address ?? '') }}" required>
                        @error('physical_address')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Accommodation Type</td>
                    <td>
                        <select name="accommodation_type" required>
                            <option value="" {{ old('accommodation_type') == '' ? 'selected' : '' }}>Select</option>
                            <option value="Owned" {{ old('accommodation_type', $employee->accommodation_type ?? '') == 'Owned' ? 'selected' : '' }}>Owned</option>
                            <option value="Rented" {{ old('accommodation_type', $employee->accommodation_type ?? '') == 'Rented' ? 'selected' : '' }}>Rented</option>
                            <option value="Employer Provided" {{ old('accommodation_type', $employee->accommodation_type ?? '') == 'Employer Provided' ? 'selected' : '' }}>Employer Provided</option>
                            <option value="Staying with Parents" {{ old('accommodation_type', $employee->accommodation_type ?? '') == 'Staying with Parents' ? 'selected' : '' }}>Staying with Parents</option>
                        </select>
                        @error('accommodation_type')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Postal Address (different from above)</td>
                    <td>
                        <input type="text" name="postal_address" value="{{ old('postal_address', $employee->postal_address ?? '') }}">
                        @error('postal_address')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Telephone Numbers</td>
                    <td>
                        Tel: <input type="tel" name="telephone" value="{{ old('telephone', $employee->telephone ?? '') }}">
                        Cell: <input type="tel" name="cell_phone" value="{{ old('cell_phone', $employee->cell_phone ?? '') }}" required>
                        @error('telephone')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                        @error('cell_phone')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>
                        <input type="email" name="email" value="{{ old('email', $employee->email ?? '') }}" required>
                        @error('email')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Next of Kin</td>
                    <td>
                        <input type="text" name="next_of_kin" value="{{ old('next_of_kin', $employee->next_of_kin ?? '') }}" required>
                        @error('next_of_kin')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Next of Kin Address</td>
                    <td>
                        <input type="text" name="next_of_kin_address" value="{{ old('next_of_kin_address', $employee->next_of_kin_address ?? '') }}" required>
                        @error('next_of_kin_address')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                    </td>
                </tr>
                <tr>
                    <td>Next of Kin Telephone Numbers</td>
                    <td>
                        Cell: <input type="tel" name="next_of_kin_cell" value="{{ old('next_of_kin_cell', $employee->next_of_kin_cell ?? '') }}" required>
                        @error('next_of_kin_cell')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
            </table>

            <h3>Employment/Business Details (Applicant)</h3>
            <table>
                <tr>
                    <td>Designation</td>
                    <td>
                        Position: <input type="text" name="position" value="{{ old('position', $employee->position ?? '') }}" required>
                        Department: <input type="text" name="department" value="{{ old('department', $employee->department ?? '') }}" required>
                        @error('position')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                        @error('department')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Monthly Salary</td>
                    <td>
                        Gross($): <input type="number" name="salary_gross" value="{{ old('salary_gross', $employee->salary_gross ?? '') }}" step="0.01" required>
                        Net($): <input type="number" name="salary_net" value="{{ old('salary_net', $employee->salary_net ?? '') }}" step="0.01" required>
                        @error('salary_gross')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                        @error('salary_net')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
            </table>

            <h3>Applicant's Banking Details</h3>
            <table>
                <tr>
                    <td>Any Account with ZWMB Bank?</td>
                    <td>
                        <label>
                            <input type="radio" name="has_zwmb_account" value="1" {{ old('has_zwmb_account', $bankingDetails->has_zwmb_account ?? '') == '1' ? 'checked' : '' }} onclick="toggleYearsInput('show')"> Yes
                        </label>
                        <label>
                            <input type="radio" name="has_zwmb_account" value="0" {{ old('has_zwmb_account', $bankingDetails->has_zwmb_account ?? '') == '0' ? 'checked' : '' }} onclick="toggleYearsInput('hide')"> No
                        </label>
                        <br>
                        <span id="years_input_container" style="{{ old('has_zwmb_account', $bankingDetails->has_zwmb_account ?? '') == '1' ? 'display: inline' : 'display: none' }}">
                            If Yes, number of years with ZWMB: <input type="number" name="years_with_zwmb" id="years_input" value="{{ old('years_with_zwmb', $bankingDetails->years_with_zwmb ?? '') }}">
                        </span>
                        @error('has_zwmb_account')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                        @error('years_with_zwmb')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Branch</td>
                    <td>
                        <input type="text" name="bank_branch" value="{{ old('bank_branch', $bankingDetails->branch ?? '') }}">
                        @error('bank_branch')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Account Number</td>
                    <td>
                        <input type="text" name="account_number" value="{{ old('account_number', $bankingDetails->account_number ?? '') }}">
                        @error('account_number')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Have you previously taken any loan with ZWMB?</td>
                    <td>
                        <label>
                            <input type="radio" name="had_previous_loan" value="1" {{ old('had_previous_loan', $bankingDetails->had_previous_loan ?? '') == '1' ? 'checked' : '' }} onclick="toggleLoanAmount('show')"> Yes
                        </label>
                        <label>
                            <input type="radio" name="had_previous_loan" value="0" {{ old('had_previous_loan', $bankingDetails->had_previous_loan ?? '') == '0' ? 'checked' : '' }} onclick="toggleLoanAmount('hide')"> No
                        </label>
                        <br>
                        <span id="loan_amount_container" style="{{ old('had_previous_loan', $bankingDetails->had_previous_loan ?? '') == '1' ? 'display: inline' : 'display: none' }}">
                            If Yes, Loan Amount ($): <input type="number" name="previous_loan_amount" id="loan_amount_input" value="{{ old('previous_loan_amount', $bankingDetails->previous_loan_amount ?? '') }}" step="0.01">
                        </span>
                        @error('had_previous_loan')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                        @error('previous_loan_amount')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Current balance</td>
                    <td>
                        <input type="number" name="current_balance" value="{{ old('current_balance', $bankingDetails->current_balance ?? '') }}" step="0.01">
                        @error('current_balance')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Taken on</td>
                    <td>
                        <input type="date" name="loan_date" value="{{ old('loan_date', $bankingDetails->loan_date ?? '') }}">
                        @error('loan_date')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Maturity Date</td>
                    <td>
                        <input type="date" name="maturity_date" value="{{ old('maturity_date', $bankingDetails->maturity_date ?? '') }}">
                        @error('maturity_date')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Currently being repaid at ($) per month</td>
                    <td>
                        <input type="number" name="monthly_repayment" value="{{ old('monthly_repayment', $bankingDetails->monthly_repayment ?? '') }}" step="0.01">
                        @error('monthly_repayment')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
            </table>

            <h3>My Other Banking Details</h3>
            <table>
                <tr>
                    <td>Bank & Branch</td>
                    <td>
                        <input type="text" name="other_bank" value="{{ old('other_bank', $bankingDetails->other_bank ?? '') }}">
                        @error('other_bank')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Type of Account(s)</td>
                    <td>
                        <input type="text" name="other_account_type" value="{{ old('other_account_type', $bankingDetails->other_account_type ?? '') }}">
                        @error('other_account_type')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
            </table>

            <h3>Applicant's Loan Request</h3>
            <table>
                <tr>
                    <td>Loan Type</td>
                    <td>
                        <select name="loan_type_id" required>
                            <option value="">Select Loan Type</option>
                            @foreach($loanTypes as $type)
                                <option value="{{ $type->id }}" {{ old('loan_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} (Max: ${{ number_format($type->max_amount, 2) }}, Interest: {{ $type->interest_rate }}%)
                                </option>
                            @endforeach
                        </select>
                        @error('loan_type_id')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Loan Amount ($)</td>
                    <td>
                        <input type="number" name="loan_amount" value="{{ old('loan_amount') }}" step="0.01" required>
                        @error('loan_amount')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Loan Period (Months)</td>
                    <td>
                        <input type="number" name="term_months" value="{{ old('term_months') }}" min="1" required>
                        @error('term_months')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td>Purpose of Loan</td>
                    <td>
                        <textarea name="purpose" rows="4" required>{{ old('purpose') }}</textarea>
                        @error('purpose')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
            </table>

            <h3>Declaration</h3>
            <p>
                I hereby declare that the information provided above is true and accurate to the best of my knowledge. 
                I understand that providing false information may result in rejection of my loan application and/or disciplinary action.
            </p>
            <div>
                <label>
                    <input type="checkbox" name="declaration" required {{ old('declaration') ? 'checked' : '' }}>
                    I agree to the terms and conditions
                </label>
                @error('declaration')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-top: 20px; text-align: center;">
                <button type="submit" class="btn btn-primary">Submit Application</button>
            </div>
        </form>
    </div>

    <script>
        function toggleYearsInput(action) {
            var container = document.getElementById('years_input_container');
            if (action === 'show') {
                container.style.display = 'inline';
            } else {
                container.style.display = 'none';
                document.getElementById('years_input').value = '';
            }
        }

        function toggleLoanAmount(action) {
            var container = document.getElementById('loan_amount_container');
            if (action === 'show') {
                container.style.display = 'inline';
            } else {
                container.style.display = 'none';
                document.getElementById('loan_amount_input').value = '';
            }
        }
    </script>
</body>
</html>