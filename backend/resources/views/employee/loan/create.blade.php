<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>STAFF LOAN APPLICATION FORM</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 20px;
            background-color: #f4f4f4;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
        }

        .navbar-brand {
            font-size: 1.5em;
            font-weight: bold;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-profile {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #ffffff;
            color: #007bff;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            margin-right: 10px;
        }

        #username-display {
            margin-right: 10px;
        }

        .logout-btn {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

        /* Existing CSS */
        .container {
            width: 90%;
            max-width: 800px;
            margin: auto;
            overflow: hidden;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            
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
        h2 {
            color: #333;
            margin-bottom: 1em;
        }
        /* Step progress bar styles */
        .step-progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        .step-progress::before {
            content: "";
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #ddd;
            z-index: 1;
        }
        .step-progress-bar {
            position: absolute;
            top: 15px;
            left: 0;
            height: 3px;
            background-color: #007bff;
            z-index: 2;
            transition: width 0.3s ease;
        }
        .step-item {
            z-index: 3;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #ddd;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .step-item.active .step-number {
            background-color: #007bff;
            color: white;
        }
        .step-item.completed .step-number {
            background-color: #28a745;
            color: white;
        }
        .step-label {
            font-size: 12px;
            text-align: center;
        }
        .form-step {
            display: none;
        }
        .form-step.active {
            display: block;
        }
        .form-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .required-field::after {
            content: "*";
            color: red;
            margin-left: 5px;
        }
        .form-info {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 5px solid #007bff;
        }
        .form-step-title {
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .hint {
            font-size: 12px;
            color: #666;
            margin-top: -15px;
            margin-bottom: 10px;
        }
        .percentage-complete {
            text-align: right;
            font-size: 14px;
            color: #007bff;
            margin-bottom: 10px;
        }
        .text-danger {
            color: #dc3545;
            font-size: 0.85em;
            margin-top: -15px;
            margin-bottom: 10px;
        }
        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        @media (max-width: 600px) {
            .container {
                padding: 10px;
                width: 95%;
            }
            .step-label {
                display: none;
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
        <h2>STAFF LOAN APPLICATION FORM</h2>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="percentage-complete">
            <span id="percentage">0%</span> complete
        </div>

        <div class="step-progress">
            <div class="step-progress-bar" id="progress-bar"></div>
            <div class="step-item active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-label">Personal Details</div>
            </div>
            <div class="step-item" data-step="2">
                <div class="step-number">2</div>
                <div class="step-label">Employment Info</div>
            </div>
            <div class="step-item" data-step="3">
                <div class="step-number">3</div>
                <div class="step-label">Banking Details</div>
            </div>
            <div class="step-item" data-step="4">
                <div class="step-number">4</div>
                <div class="step-label">Loan Request</div>
            </div>
            <div class="step-item" data-step="5">
                <div class="step-number">5</div>
                <div class="step-label">Collateral</div>
            </div>
            <div class="step-item" data-step="6">
                <div class="step-number">6</div>
                <div class="step-label">Review & Submit</div>
            </div>
        </div>

        <form id="loanApplicationForm" action="{{ route('employee.loan.store') }}" method="POST">
            @csrf
            <!-- Step 1: Personal Information -->
            <div class="form-step active" id="step-1">
                <h3 class="form-step-title">Step 1: Applicant's Personal Information</h3>
                <div class="form-info">
                    <p>Please provide your personal details. Fields marked with an asterisk (*) are required.</p>
                </div>

                <table>
                    <tr>
                        <td class="required-field">Title</td>
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
                        <td class="required-field">Name in Full</td>
                        <td>
                            <input type="text" name="full_name" value="{{ old('full_name', $employee->full_name ?? '') }}" required>
                            @error('full_name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td class="required-field">ID/Passport/Driver's License No.</td>
                        <td>
                            <input type="text" name="national_id" value="{{ old('national_id', $employee->national_id ?? '') }}" required>
                            @error('national_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td class="required-field">Date of Birth</td>
                        <td>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $employee->date_of_birth ?? '') }}" required>
                            @error('date_of_birth')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td class="required-field">Gender</td>
                        <td>
                            <select name="gender" required>
                                <option value="" {{ old('gender') == '' ? 'selected' : '' }}>Select</option>
                                <option value="Male" {{ old('gender', $employee->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $employee->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td class="required-field">Marital Status</td>
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
                            <input type="number" name="dependents" value="{{ old('dependents', $employee->dependents ?? 0) }}" min="0">
                            @error('dependents')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td class="required-field">Physical Home Address</td>
                        <td>
                            <input type="text" name="physical_address" value="{{ old('physical_address', $employee->physical_address ?? '') }}" required>
                            @error('physical_address')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td>Length of Stay at Home Address</td>
                        <td>
                            <input type="text" name="length_of_stay" placeholder="Years and Months (e.g., 2 years, 6 months)" value="{{ old('length_of_stay', $employee->length_of_stay ?? '') }}">
                            @error('length_of_stay')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td class="required-field">Accommodation Type</td>
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
                        <td>Postal Address (if different from above)</td>
                        <td>
                            <input type="text" name="postal_address" value="{{ old('postal_address', $employee->postal_address ?? '') }}">
                            @error('postal_address')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td class="required-field">Telephone Numbers</td>
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
                        <td class="required-field">Email</td>
                        <td>
                            <input type="email" name="email" value="{{ old('email', $employee->email ?? '') }}" required>
                            @error('email')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td class="required-field">Next of Kin</td>
                        <td>
                            <input type="text" name="next_of_kin" value="{{ old('next_of_kin', $employee->next_of_kin ?? '') }}" required>
                            @error('next_of_kin')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td class="required-field">Next of Kin Address</td>
                        <td>
                            <input type="text" name="next_of_kin_address" value="{{ old('next_of_kin_address', $employee->next_of_kin_address ?? '') }}" required>
                            @error('next_of_kin_address')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td class="required-field">Next of Kin Telephone Numbers</td>
                        <td>
                            Tel: <input type="tel" name="next_of_kin_tel" value="{{ old('next_of_kin_tel', $employee->next_of_kin_tel ?? '') }}">
                            Cell: <input type="tel" name="next_of_kin_cell" value="{{ old('next_of_kin_cell', $employee->next_of_kin_cell ?? '') }}" required>
                            @error('next_of_kin_tel')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            @error('next_of_kin_cell')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                </table>

                <div class="form-navigation">
                    <button type="button" disabled style="visibility: hidden;">Previous</button>
                    <button type="button" onclick="nextStep(1)">Next</button>
                </div>
            </div>

            <!-- Step 2: Employment Details -->
            <div class="form-step" id="step-2">
                <h3 class="form-step-title">Step 2: Employment/Business Details</h3>
                <div class="form-info">
                    <p>Please provide information about your current employment or business.</p>
                </div>

                <table>
                    <tr>
                        <td class="required-field">Period with Employer</td>
                        <td>
                            <input type="text" name="employment_period" value="{{ old('employment_period', $employee->employment_period ?? '') }}" required placeholder="e.g., 5 years, 3 months">
                            @error('employment_period')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td class="required-field">Designation</td>
                        <td>
                            Post: <input type="text" name="position" value="{{ old('position', $employee->position ?? '') }}" required>
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
                        <td class="required-field">Monthly Salary</td>
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

                <div class="form-navigation">
                    <button type="button" onclick="prevStep(2)">Previous</button>
                    <button type="button" onclick="nextStep(2)">Next</button>
                </div>
            </div>

            <!-- Step 3: Banking Details -->
            <div class="form-step" id="step-3">
                <h3 class="form-step-title">Step 3: Banking Details</h3>
                <div class="form-info">
                    <p>Please provide your banking information and history with ZWMB Bank.</p>
                </div>

                <h4>APPLICANT'S BANKING DETAILS</h4>
                <table>
                    <tr>
                        <td class="required-field">Any Account with ZWMB Bank?</td>
                        <td>
                            <label>
                                <input type="radio" name="has_zwmb_account" value="1" {{ old('has_zwmb_account', $bankingDetails->has_zwmb_account ?? '') == '1' ? 'checked' : '' }} onclick="toggleYearsInput('show')" required> Yes
                            </label>
                            <label>
                                <input type="radio" name="has_zwmb_account" value="0" {{ old('has_zwmb_account', $bankingDetails->has_zwmb_account ?? '') == '0' ? 'checked' : '' }} onclick="toggleYearsInput('hide')" required> No
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
                        <td class="required-field">Have you previously taken any loan with ZWMB?</td>
                        <td>
                            <label>
                                <input type="radio" name="had_previous_loan" value="1" {{ old('had_previous_loan', $bankingDetails->had_previous_loan ?? '') == '1' ? 'checked' : '' }} onclick="toggleLoanAmount('show')" required> Yes
                            </label>
                            <label>
                                <input type="radio" name="had_previous_loan" value="0" {{ old('had_previous_loan', $bankingDetails->had_previous_loan ?? '') == '0' ? 'checked' : '' }} onclick="toggleLoanAmount('hide')" required> No
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
                    <tr class="previous-loan-field" style="{{ old('had_previous_loan', $bankingDetails->had_previous_loan ?? '') == '1' ? 'display: table-row' : 'display: none' }}">
                        <td>Current balance</td>
                        <td>
                            <input type="number" name="current_balance" value="{{ old('current_balance', $bankingDetails->current_balance ?? '') }}" step="0.01">
                            @error('current_balance')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr class="previous-loan-field" style="{{ old('had_previous_loan', $bankingDetails->had_previous_loan ?? '') == '1' ? 'display: table-row' : 'display: none' }}">
                        <td>Taken on</td>
                        <td>
                            <input type="date" name="loan_date" value="{{ old('loan_date', $bankingDetails->loan_date ?? '') }}">
                            @error('loan_date')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr class="previous-loan-field" style="{{ old('had_previous_loan', $bankingDetails->had_previous_loan ?? '') == '1' ? 'display: table-row' : 'display: none' }}">
                        <td>Maturity Date</td>
                        <td>
                            <input type="date" name="maturity_date" value="{{ old('maturity_date', $bankingDetails->maturity_date ?? '') }}">
                            @error('maturity_date')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr class="previous-loan-field" style="{{ old('had_previous_loan', $bankingDetails->had_previous_loan ?? '') == '1' ? 'display: table-row' : 'display: none' }}">
                        <td>Currently being repaid at ($) per month</td>
                        <td>
                            <input type="number" name="monthly_repayment" value="{{ old('monthly_repayment', $bankingDetails->monthly_repayment ?? '') }}" step="0.01">
                            @error('monthly_repayment')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                </table>

                <h4>My other banking details</h4>
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

                <div class="form-navigation">
                    <button type="button" onclick="prevStep(3)">Previous</button>
                    <button type="button" onclick="nextStep(3)">Next</button>
                </div>
            </div>

            <!-- Step 4: Loan Request -->
            <div class="form-step" id="step-4">
                <h3 class="form-step-title">Step 4: Loan Request</h3>
                <div class="form-info">
                    <p>Please specify the loan amount and purpose you are applying for.</p>
                </div>

                <table>
                    <tr>
                        <th class="required-field">Loan Type</th>
                        <td>
                            <select name="loan_type_id" required>
                                <option value="">Select Loan Type</option>
                                @foreach($loanTypes as $type)
                                    <option value="{{ $type->loan_type_id }}" {{ old('loan_type_id') == $type->loan_type_id ? 'selected' : '' }}>
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
                        <th class="required-field">Loan Requested ($)</th>
                        <td>
                            <input type="number" name="loan_amount" id="loanAmountFigures" value="{{ old('loan_amount') }}" step="0.01" placeholder="In figures" required>
                            <div class="hint">Enter the loan amount in numbers</div>
                            <input type="text" name="loan_amount_words" id="loanAmountWords" value="{{ old('loan_amount_words') }}" placeholder="In words" required>
                            <div class="hint">Enter the loan amount in words (e.g., Ten Thousand Dollars)</div>
                            @error('loan_amount')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            @error('loan_amount_words')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <th class="required-field">Loan Period</th>
                        <td>
                            Year(s): <input type="number" name="loan_years" id="loanYears" min="0" max="30" value="{{ old('loan_years', 0) }}">
                            Month(s): <input type="number" name="term_months" id="loanMonths" min="0" max="11" value="{{ old('term_months') }}" required>
                            <div class="hint">The loan period must be at least 1 month and not exceed 30 years</div>
                            @error('loan_years')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            @error('term_months')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <th class="required-field">Brief Purpose of Loan:</th>
                        <td>
                            <textarea name="purpose" id="briefPurpose" rows="4" required>{{ old('purpose') }}</textarea>
                            @error('purpose')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <th class="required-field">Specific Loan Purpose</th>
                        <td>
                            <select name="specific_purpose" required>
                                <option value="">Select Loan Purpose</option>
                                <option value="Personal Loan" {{ old('specific_purpose') == 'Personal Loan' ? 'selected' : '' }}>Personal Loan</option>
                                <option value="Home Improvement Loan" {{ old('specific_purpose') == 'Home Improvement Loan' ? 'selected' : '' }}>Home Improvement Loan</option>
                                <option value="Car Loan" {{ old('specific_purpose') == 'Car Loan' ? 'selected' : '' }}>Car Loan</option>
                                <option value="Educational Loan" {{ old('specific_purpose') == 'Educational Loan' ? 'selected' : '' }}>Educational Loan</option>
                                <option value="Other" {{ old('specific_purpose') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select><br>
                            <div id="otherLoanType" style="{{ old('specific_purpose') == 'Other' ? 'display: block' : 'display: none' }}">
                                <label>If Other, state Type of Loan Required: <input type="text" name="other_loan_type" value="{{ old('other_loan_type') }}"></label>
                            </div>
                            @error('specific_purpose')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                            @error('other_loan_type')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                </table>

                <div class="form-navigation">
                    <button type="button" onclick="prevStep(4)">Previous</button>
                    <button type="button" onclick="nextStep(4)">Next</button>
                </div>
            </div>

            <!-- Step 5: Collateral Security -->
            <div class="form-step" id="step-5">
                <h3 class="form-step-title">Step 5: Collateral Security</h3>
                <div class="form-info">
                    <p>Please provide details of any collateral security you are offering for this loan.</p>
                </div>

                <table>
                    <tr>
                        <th>SECURITY TYPE</th>
                        <th>DESCRIPTION</th>
                        <th>Value ($)</th>
                    </tr>
                    <tr>
                        <td>
                            <input type="text" name="security_type1" value="{{ old('security_type1') }}" placeholder="e.g., Vehicle, Property">
                            @error('security_type1')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>
                            <input type="text" name="security_description1" value="{{ old('security_description1') }}" placeholder="e.g., 2020 Toyota Camry">
                            @error('security_description1')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>
                            <input type="number" name="security_value1" value="{{ old('security_value1') }}" step="0.01" placeholder="Estimated value">
                            @error('security_value1')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="text" name="security_type2" value="{{ old('security_type2') }}">
                            @error('security_type2')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>
                            <input type="text" name="security_description2" value="{{ old('security_description2') }}">
                            @error('security_description2')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>
                            <input type="number" name="security_value2" value="{{ old('security_value2') }}" step="0.01">
                            @error('security_value2')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="text" name="security_type3" value="{{ old('security_type3') }}">
                            @error('security_type3')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>
                            <input type="text" name="security_description3" value="{{ old('security_description3') }}">
                            @error('security_description3')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>
                            <input type="number" name="security_value3" value="{{ old('security_value3') }}" step="0.01">
                            @error('security_value3')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                </table>

                <div class="form-navigation">
                    <button type="button" onclick="prevStep(5)">Previous</button>
                    <button type="button" onclick="nextStep(5)">Next</button>
                </div>
            </div>

            <!-- Step 6: Review and Submit -->
            <div class="form-step" id="step-6">
                <h3 class="form-step-title">Step 6: Review and Submit</h3>
                <div class="form-info">
                    <p>Please review all the information you've provided before submitting your application.</p>
                </div>

                <div id="review-summary">
                    <!-- This will be populated with a summary of the form data -->
                </div>

                <h4>DECLARATION BY APPLICANT</h4>
                <p>I hereby declare that the information provided is true and accurate. I understand that providing false information may lead to rejection of my application and/or legal consequences.</p>
                
                <div class="form-field">
                    <label>
                        <input type="checkbox" name="agree_terms" required {{ old('agree_terms') ? 'checked' : '' }}> I agree to the terms and conditions of the loan application
                    </label>
                    @error('agree_terms')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-navigation">
                    <button type="button" onclick="prevStep(6)">Previous</button>
                    <button type="submit">Submit Application</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Current step
        let currentStep = 1;
        const totalSteps = 6;

        // Check if there are validation errors and move to the relevant step
        document.addEventListener('DOMContentLoaded', function() {
            const hasErrors = {{ $errors->any() ? 'true' : 'false' }};
            if (hasErrors) {
                // Find which step has errors
                let errorStep = 1; // Default to first step
                
                // Check personal details errors (step 1)
                const personalErrors = {{ ($errors->hasAny([
                    'title', 'full_name', 'national_id', 'date_of_birth', 'gender', 
                    'marital_status', 'dependents', 'physical_address', 'length_of_stay',
                    'accommodation_type', 'postal_address', 'telephone', 'cell_phone',
                    'email', 'next_of_kin', 'next_of_kin_address', 'next_of_kin_tel', 'next_of_kin_cell'
                ])) ? 'true' : 'false' }};
                
                // Check employment errors (step 2)
                const employmentErrors = {{ ($errors->hasAny([
                    'employment_period', 'position', 'department', 'salary_gross', 'salary_net'
                ])) ? 'true' : 'false' }};
                
                // Check banking details errors (step 3)
                const bankingErrors = {{ ($errors->hasAny([
                    'has_zwmb_account', 'years_with_zwmb', 'bank_branch', 'account_number',
                    'had_previous_loan', 'previous_loan_amount', 'current_balance', 
                    'loan_date', 'maturity_date', 'monthly_repayment', 'other_bank', 'other_account_type'
                ])) ? 'true' : 'false' }};
                
                // Check loan request errors (step 4)
                const loanRequestErrors = {{ ($errors->hasAny([
                    'loan_type_id', 'loan_amount', 'loan_amount_words', 'loan_years', 
                    'term_months', 'purpose', 'specific_purpose', 'other_loan_type'
                ])) ? 'true' : 'false' }};
                
                // Check collateral errors (step 5)
                const collateralErrors = {{ ($errors->hasAny([
                    'security_type1', 'security_description1', 'security_value1',
                    'security_type2', 'security_description2', 'security_value2',
                    'security_type3', 'security_description3', 'security_value3'
                ])) ? 'true' : 'false' }};
                
                // Check submission errors (step 6)
                const submissionErrors = {{ ($errors->hasAny(['agree_terms'])) ? 'true' : 'false' }};
                
                // Determine which step to show
                if (personalErrors) errorStep = 1;
                else if (employmentErrors) errorStep = 2;
                else if (bankingErrors) errorStep = 3;
                else if (loanRequestErrors) errorStep = 4;
                else if (collateralErrors) errorStep = 5;
                else if (submissionErrors) errorStep = 6;
                
                // Show the step with errors
                currentStep = errorStep;
                showStep(currentStep);
            }
            
            // Initialize form display
            showStep(currentStep);
            
            // Show/hide Other loan type field based on selection
            const specificPurposeSelect = document.querySelector('select[name="specific_purpose"]');
            const otherLoanTypeDiv = document.getElementById('otherLoanType');
            if (specificPurposeSelect && specificPurposeSelect.value === 'Other') {
                otherLoanTypeDiv.style.display = 'block';
            }
        });

        // Function to show a specific step
        function showStep(step) {
            // Hide all steps
            document.querySelectorAll('.form-step').forEach(el => {
                el.classList.remove('active');
            });
            
            // Show the specified step
            document.getElementById('step-' + step).classList.add('active');
            
            // Update progress bar
            updateProgress(step);
            
            // Update step indicators
            updateStepIndicators(step);
        }

        // Function to move to the next step
        function nextStep(step) {
            if (validateStep(step)) {
                currentStep = step + 1;
                showStep(currentStep);
                
                // If we're on the last step, populate the review summary
                if (currentStep === totalSteps) {
                    populateReviewSummary();
                }
            }
        }

        // Function to move to the previous step
        function prevStep(step) {
            currentStep = step - 1;
            showStep(currentStep);
        }

        // Update progress bar
        function updateProgress(step) {
            const progressPercentage = ((step - 1) / (totalSteps - 1)) * 100;
            document.getElementById('progress-bar').style.width = progressPercentage + '%';
            document.getElementById('percentage').textContent = Math.round(progressPercentage) + '%';
        }

        // Update step indicators
        function updateStepIndicators(currentStep) {
            document.querySelectorAll('.step-item').forEach(item => {
                const step = parseInt(item.getAttribute('data-step'));
                item.classList.remove('active', 'completed');
                
                if (step === currentStep) {
                    item.classList.add('active');
                } else if (step < currentStep) {
                    item.classList.add('completed');
                }
            });
        }

        // Validate each step
        function validateStep(step) {
            // Get all required fields in the current step
            const stepElement = document.getElementById('step-' + step);
            const requiredFields = stepElement.querySelectorAll('[required]');
            
            // Check if all required fields are filled
            let isValid = true;
            requiredFields.forEach(field => {
                if (field.type === 'checkbox' && !field.checked) {
                    isValid = false;
                    field.style.outline = '2px solid red';
                } else if (field.type !== 'checkbox' && !field.value) {
                    isValid = false;
                    field.style.borderColor = 'red';
                } else {
                    if (field.type === 'checkbox') {
                        field.style.outline = 'none';
                    } else {
                        field.style.borderColor = '#ccc';
                    }
                }
            });
            
            // Validate specific fields if needed
            if (step === 4) {
                // Validate loan period
                const years = parseInt(document.getElementById('loanYears').value) || 0;
                const months = parseInt(document.getElementById('loanMonths').value) || 0;
                
                if (years === 0 && months === 0) {
                    isValid = false;
                    document.getElementById('loanYears').style.borderColor = 'red';
                    document.getElementById('loanMonths').style.borderColor = 'red';
                    alert('Please specify a loan period');
                }
                
                // Validate specific purpose "Other" field
                const specificPurpose = document.querySelector('select[name="specific_purpose"]');
                if (specificPurpose.value === 'Other') {
                    const otherLoanType = document.querySelector('input[name="other_loan_type"]');
                    if (!otherLoanType.value) {
                        isValid = false;
                        otherLoanType.style.borderColor = 'red';
                    } else {
                        otherLoanType.style.borderColor = '#ccc';
                    }
                }
            }
            
            if (!isValid) {
                alert('Please fill in all required fields');
            }
            
            return isValid;
        }

        // Show/hide additional fields based on ZWMB account selection
        function toggleYearsInput(action) {
            var container = document.getElementById('years_input_container');
            if (action === 'show') {
                container.style.display = 'inline';
            } else {
                container.style.display = 'none';
                document.getElementById('years_input').value = '';
            }
        }

        // Show/hide additional fields based on previous loan selection
        function toggleLoanAmount(action) {
            var container = document.getElementById('loan_amount_container');
            var previousLoanFields = document.querySelectorAll('.previous-loan-field');
            
            if (action === 'show') {
                container.style.display = 'inline';
                previousLoanFields.forEach(field => {
                    field.style.display = 'table-row';
                });
            } else {
                container.style.display = 'none';
                document.getElementById('loan_amount_input').value = '';
                previousLoanFields.forEach(field => {
                    field.style.display = 'none';
                });
            }
        }

        // Show/hide "Other" loan type field
        document.querySelector('select[name="specific_purpose"]').addEventListener('change', function() {
            if (this.value === 'Other') {
                document.getElementById('otherLoanType').style.display = 'block';
            } else {
                document.getElementById('otherLoanType').style.display = 'none';
            }
        });

        // Populate review summary
        function populateReviewSummary() {
            const form = document.getElementById('loanApplicationForm');
            const formData = new FormData(form);
            let summaryHTML = '<h4>Personal Information</h4>';
            
            // Personal Information
            summaryHTML += `<p><strong>Name:</strong> ${formData.get('title')} ${formData.get('full_name')}</p>`;
            summaryHTML += `<p><strong>ID/Passport:</strong> ${formData.get('national_id')}</p>`;
            summaryHTML += `<p><strong>Date of Birth:</strong> ${formData.get('date_of_birth')}</p>`;
            summaryHTML += `<p><strong>Contact:</strong> ${formData.get('email')} / ${formData.get('cell_phone')}</p>`;
            
            // Employment Details
            summaryHTML += '<h4>Employment Details</h4>';
            summaryHTML += `<p><strong>Position:</strong> ${formData.get('position')}, ${formData.get('department')}</p>`;
            summaryHTML += `<p><strong>Employment Period:</strong> ${formData.get('employment_period')}</p>`;
            summaryHTML += `<p><strong>Salary:</strong> $${formData.get('salary_gross')} (Gross), $${formData.get('salary_net')} (Net)</p>`;
            
            // Loan Request
            const loanTypeSelect = document.querySelector('select[name="loan_type_id"]');
            const loanTypeText = loanTypeSelect.options[loanTypeSelect.selectedIndex].text;
            
            summaryHTML += '<h4>Loan Request</h4>';
            summaryHTML += `<p><strong>Loan Type:</strong> ${loanTypeText}</p>`;
            summaryHTML += `<p><strong>Loan Amount:</strong> $${formData.get('loan_amount')} (${formData.get('loan_amount_words')})</p>`;
            const years = parseInt(formData.get('loan_years')) || 0;
            const months = parseInt(formData.get('term_months')) || 0;
            summaryHTML += `<p><strong>Loan Period:</strong> ${years} year(s), ${months} month(s)</p>`;
            summaryHTML += `<p><strong>Purpose:</strong> ${formData.get('purpose')}</p>`;
            
            // Banking Details
            summaryHTML += '<h4>Banking Details</h4>';
            const hasZwmbAccount = formData.get('has_zwmb_account') === '1';
            summaryHTML += `<p><strong>ZWMB Account:</strong> ${hasZwmbAccount ? 'Yes' : 'No'}`;
            if (hasZwmbAccount) {
                summaryHTML += `, ${formData.get('years_with_zwmb')} years with ZWMB`;
            }
            summaryHTML += `</p>`;
            
            if (formData.get('bank_branch')) {
                summaryHTML += `<p><strong>Branch:</strong> ${formData.get('bank_branch')}</p>`;
            }
            
            if (formData.get('account_number')) {
                summaryHTML += `<p><strong>Account Number:</strong> ${formData.get('account_number')}</p>`;
            }
            
            const hasPreviousLoan = formData.get('had_previous_loan') === '1';
            summaryHTML += `<p><strong>Previous ZWMB Loan:</strong> ${hasPreviousLoan ? 'Yes' : 'No'}`;
            if (hasPreviousLoan) {
                summaryHTML += `, Amount: $${formData.get('previous_loan_amount')}`;
                summaryHTML += `, Current Balance: $${formData.get('current_balance')}`;
                summaryHTML += `, Monthly Repayment: $${formData.get('monthly_repayment')}`;
            }
            summaryHTML += `</p>`;
            
            // Collateral Security
            summaryHTML += '<h4>Collateral Security</h4>';
            let hasCollateral = false;
            
            for (let i = 1; i <= 3; i++) {
                const securityType = formData.get(`security_type${i}`);
                const securityDesc = formData.get(`security_description${i}`);
                const securityValue = formData.get(`security_value${i}`);
                
                if (securityType && securityDesc && securityValue) {
                    hasCollateral = true;
                    summaryHTML += `<p><strong>${securityType}:</strong> ${securityDesc} - $${securityValue}</p>`;
                }
            }
            
            if (!hasCollateral) {
                summaryHTML += '<p>No collateral security provided.</p>';
            }
            
            // Update the review summary section
            document.getElementById('review-summary').innerHTML = summaryHTML;
        }

        // Convert loan amount to words when entered
        document.getElementById('loanAmountFigures').addEventListener('input', function() {
            const amount = parseFloat(this.value);
            if (!isNaN(amount)) {
                document.getElementById('loanAmountWords').value = numberToWords(amount);
            }
        });

        // Simple number to words converter (simplified version)
        function numberToWords(num) {
            const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
                        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
                        'Seventeen', 'Eighteen', 'Nineteen'];
            const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
            const scales = ['', 'Thousand', 'Million', 'Billion', 'Trillion'];
            
            if (num === 0) return 'Zero Dollars';
            
            // Handle decimal part
            const decimalPart = Math.round((num % 1) * 100);
            const dollarPart = Math.floor(num);
            
            let result = '';
            
            // Convert dollar part (simplified for demo)
            if (dollarPart > 0) {
                if (dollarPart < 20) {
                    result = ones[dollarPart];
                } else if (dollarPart < 100) {
                    result = tens[Math.floor(dollarPart / 10)] + (dollarPart % 10 !== 0 ? '-' + ones[dollarPart % 10] : '');
                } else {
                    result = 'Amount too large for simple converter';
                }
                
                result += ' Dollar' + (dollarPart === 1 ? '' : 's');
            }
            
            // Add cents part
            if (decimalPart > 0) {
                if (result !== '') {
                    result += ' and ';
                }
                
                if (decimalPart < 20) {
                    result += ones[decimalPart];
                } else {
                    result += tens[Math.floor(decimalPart / 10)] + (decimalPart % 10 !== 0 ? '-' + ones[decimalPart % 10] : '');
                }
                
                result += ' Cent' + (decimalPart === 1 ? '' : 's');
            }
            
            return result;
        }
    </script>
</body>
</html>