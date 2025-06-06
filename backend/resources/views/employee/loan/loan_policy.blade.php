{{-- resources/views/employee/loan/loan_policy.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Staff Loan Policy</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 20px;
            background-color: #f4f4f4;
            color: #333;
        }
        /* ========== Navbar Styles ========== */
        .navbar {
            background-color: #4361ee;
            color: white;
            width: 100%;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem; 
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
            flex-shrink: 0; 
        }

        #username-display {
            margin-right: 0.5rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis; 
            max-width: 150px; 
        }

        .logout-btn {
            background-color: transparent;
            border: 1px solid white;
            color: white;
            padding: 0.8rem 1.2rem;
            margin-right: 3rem;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap; 
        }

        .logout-btn:hover {
            background-color: white;
            color: #4361ee;
        }

        /* Responsive adjustments for smaller screens */
        @media (max-width: 600px) {
            .navbar {
                padding: 1rem;
            }

            .user-info {
                gap: 0.5rem;
            }

            #username-display {
                display: none; /* Hide username on very small screens */
            }

            .user-profile {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
            }

            .logout-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.9rem;
                
            }
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
        h1, h2, h3 {
            color: #0056b3;
            margin-bottom: 10px;
            text-align: left;
        }
        ol {
            padding-left: 20px;
        }
        ol ol {
            list-style-type: lower-roman;
            padding-left: 30px;
        }
        li {
            margin-bottom: 10px;
        }
        .signature-section {
            margin-top: 50px;
            border-top: 1px solid #ddd;
            padding-top: 30px;
        }
        .signature-input {
            width: 250px;
            border: none;
            border-bottom: 1px solid #333;
            padding: 5px;
        }
        .date-display {
            display: inline-block;
            min-width: 150px;
            border-bottom: 1px solid #333;
            padding: 5px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-right: 10px;
        }
        button:hover {
            background-color: #0056b3;
        }
        #acknowledgementMessage {
            margin-top: 20px;
            color: green;
            display: none;
        }
        .signature-upload-container {
            margin-bottom: 15px;
        }
        .preview-container {
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: inline-block;
        }
        #signature-preview {
            max-width: 300px;
            max-height: 150px;
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
        <h1>Staff Loan Policy</h1>

        <h2>1. ELIGIBILITY</h2>
        <ol>
            <li>
                1.1 In an effort to motivate and assist staff to be effective in executing their duties, the Bank may, subject to availability of funding and in line with employee contracts, provide personal loans to eligible employees. The Qualifying criteria is as follows:
                <ol>
                    <li>He/she is on a long-term contract of at least 1 year with the Bank.</li>
                    <li>He/she agrees to fulfil all loan requirements and execute all supporting documents to the satisfaction of the Bank.</li>
                    <li>He/she acknowledges that any such loans will be granted on the basis of contract obligations.</li>
                </ol>
            </li>
            <li>1.1. Employees on probation shall not be eligible for staff loans.</li>
            <li>1.2. Employees on fixed term contracts will only be eligible for personal loans if their contracts are for a period of one year or more.</li>
        </ol>

        <h2>2. LOAN LIMITS</h2>
        <ol>
            <li>2.1. The Bank may grant personal loans as shall be set by the Board of the Bank.</li>
            <li>2.2. The loans limits and interest rates shall be subject to review by the Board of the Bank from time to time.</li>
            <li>2.3. The personal loan entitlement shall be determined by the size of the staff loan limit, one's basic salary, approved loan tenure and maximum individual deductions as prescribed by the law (Currently 25% of one's basic salary).</li>
            <li>2.4. Application for loans shall be on a first-come-first-served basis, and the funding available for such loans at any given time.</li>
        </ol>

        <h2>3. REPAYMENTS OF LOAN</h2>
        <ol>
            <li>3.1. Loans shall be repayable up to a maximum of one year at a subsidised interest rate, as approved by the Board of the Bank.</li>
            <li>3.2. Repayment of the principal amount and interest on the loan shall be made in equal monthly instalments through deductions from the employee's salary by the Bank until full repayment of the amount due.</li>
            <li>3.3. The repayment terms shall be subject to review by the Bank.</li>
            <li>3.4. The employee may, at any time, liquidate the outstanding loan balance in full without incurring any further interest.</li>
        </ol>

        <h2>4. SECURITY</h2>
        <ol>
            <li>4.1. The employee shall cede his/her terminal benefits as security for the loan.</li>
            <li>4.2. The employee shall pledge a security guarantee for the loan prior to accessing it.</li>
            <li>4.3. The Employee shall provide a guarantor for all loans that are above one's basic salary.</li>
            <li>4.4. In the event of default by the employee, the Bank shall have the right to dispose of the pledged asset and the proceeds therefore shall be applied to liquidate the outstanding loan balances.</li>
        </ol>

        <h2>5. TERMINATION OF EMPLOYMENT CONTRACT</h2>
        <ol>
            <li>5.1. In the event of the termination of the employee's employment contract, either by resignation, discharge, death or due to any other cause, the full loan balance shall become immediately due and payable and, in any event, must be repaid in full within thirty (30) days from the date of termination.</li>
            <li>5.2. In the event of default, the Bank shall credit the proportional amount of the ceded terminal benefits into the loan account to liquidate the loan balance.</li>
            <li>5.3. In the event that the proceeds in terms of paragraph 5.2 above are not able to fully liquidate the loan balance, the Bank shall repossess the pledged Asset and dispose of it to liquidate the loan balance.</li>
            <li>5.4. In the event that a loan balance remains after the application of paragraphs 5.2 and 5.3, respectively, Guarantor shall be obligated to liquidate the loan balance.</li>
            <li>5.5. The Bank may convert the loan to a commercial loan upon written request by the employee and, in any such event, the standard bank credit worthiness assessment will be undertaken and a new loan agreement will be signed.</li>
        </ol>

        <h2>6. LOAN APPLICATION</h2>
        <ol>
            <li>6.1. Employees will be required to complete a loan application form, Asset pledge form and Guarantor form (where applicable) with the Bank.</li>
            <li>6.2. If the loan is granted, the loan will be funded into the employee's bank Account.</li>
        </ol>

        <div class="signature-section">
            <h2>ACKNOWLEDGEMENT BY EMPLOYEE</h2>
            <p>By clicking the Next Button, I hereby duly acknowledge that I have read and understood the Staff Loan Policy attached hereto and agree to the terms and conditions set out therein.</p>
            
            <form action="{{ route('employee.loan.policy.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="loan_id" value="{{ $loan->loan_id }}">
                
                <div class="form-group">
                    <label for="signature">Your Signature:</label>
                    <div class="signature-upload-container">
                        <input type="file" id="signature" name="signature" accept="image/*" required class="signature-upload">
                        <div class="preview-container" id="signature-preview-container" style="display: none;">
                            <img id="signature-preview" src="#" alt="Signature Preview">
                            <button type="button" id="remove-signature" style="padding: 5px 10px; font-size: 12px; margin-top: 5px;">Remove</button>
                        </div>
                    </div>
                    <small style="color: #666; display: block; margin-top: 5px;">Please upload an image of your signature. Acceptable formats: JPG, PNG, GIF.</small>
                    @error('signature')
                        <div style="color: red; margin-top: 5px;">{{ $message }}</div>
                    @enderror
                </div>
                
                <p>
                    Date: <span class="date-display" id="currentDate"></span>
                </p>
                
                <button type="submit">Next</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Display current date
            const currentDate = new Date();
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('currentDate').textContent = currentDate.toLocaleDateString(undefined, options);
            
            // Signature upload preview functionality
            const signatureInput = document.getElementById('signature');
            const previewContainer = document.getElementById('signature-preview-container');
            const previewImage = document.getElementById('signature-preview');
            const removeButton = document.getElementById('remove-signature');
            
            signatureInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        previewContainer.style.display = 'block';
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                }
            });
            
            removeButton.addEventListener('click', function() {
                signatureInput.value = '';
                previewContainer.style.display = 'none';
                previewImage.src = '#';
            });
        });
    </script>
</body>
</html>