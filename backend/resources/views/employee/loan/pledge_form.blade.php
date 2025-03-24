@extends('employee.dashboard')

@section('content')
<div class="container">
    <h1>Pledge Agreement</h1>

    <form method="POST" action="{{ route('employee.loan.store_pledge') }}" id="pledgeForm">
        @csrf
        <input type="hidden" name="loan_id" value="{{ $loan->loan_id }}">

        <p>
            Name <input type="text" class="input-field" id="name" name="name" placeholder="Name" value="{{ old('name', $loan->employee->full_name ?? '') }}">
            @error('name')
                <span class="error" style="display: block;">{{ $message }}</span>
            @enderror
            
            The holder of National ID number <input type="text" class="input-field" id="nationalId" name="national_id" placeholder="National ID Number" value="{{ old('national_id', $loan->employee->national_id ?? '') }}">
            @error('national_id')
                <span class="error" style="display: block;">{{ $message }}</span>
            @enderror
            
            Residing at <input type="text" class="input-field" id="address" name="address" placeholder="Address" value="{{ old('address', $loan->employee->physical_address ?? '') }}">
            @error('address')
                <span class="error" style="display: block;">{{ $message }}</span>
            @enderror
            
            hereby pledge the following assets to secure the loan that is granted to me by the Lender, Zimbabwe Women's Microfinance Bank (ZWMB), in full accordance with the provisions in the loan agreement:
        </p>

        <table>
            <thead>
                <tr>
                    <th>Description of asset</th>
                    <th>Estimated Value</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 0; $i < 6; $i++)
                <tr>
                    <td><input type="text" class="input-field" name="assets[{{ $i }}][description]" id="asset{{ $i+1 }}" value="{{ old('assets.' . $i . '.description') }}"></td>
                    <td><input type="text" class="input-field" name="assets[{{ $i }}][value]" id="value{{ $i+1 }}" value="{{ old('assets.' . $i . '.value') }}"></td>
                </tr>
                @endfor
            </tbody>
        </table>

        @error('assets')
            <span class="error" style="display: block; color: red; text-align: center;">{{ $message }}</span>
        @enderror

        <p>
            I declare that I am the legal owner of the aforementioned assets and that no other person has any interest in or right to them. I agree that at any time during the term of this loan until I have fully repaid the loan, the Lender has the right to take possession of the aforementioned assets and to hold them in a safe place without using them. I understand that if I default from the loan, the Lender has the right to sell, dispose of, and realise value from the aforementioned assets. I confirm that I have handed over to the Lender the original motor vehicle registration book (registration number <input type="text" class="input-field" id="registrationNumber" name="registration_number" placeholder="Registration Number" value="{{ old('registration_number') }}">). I understand that I will not have access to this document until after I have fully repaid my loan.
        </p>

        <div class="signature-section">
            <p>
                Signed at <input type="text" class="input-field" id="location" name="location" placeholder="Location" value="{{ old('location') }}">
                @error('location')
                    <span class="error" style="display: block;">{{ $message }}</span>
                @enderror
                on this <span id="currentDateDisplay"></span>
            </p>
            
            <p>
                Signature: <input type="text" class="input-field" id="signature" name="signature" placeholder="Your Full Name as Signature" value="{{ old('signature') }}">
                @error('signature')
                    <span class="error" style="display: block;">{{ $message }}</span>
                @enderror
            </p>
            
            <a href="{{ route('employee.loan.policy', ['loan' => $loan->loan_id]) }}" class="btn btn-secondary">Back</a>
            <button type="submit" id="finishButton" class="btn btn-primary">Submit Pledge</button>
            
            <div id="sessionErrorMessage" class="error-message" style="display: none;">Session expired. Please log in again.</div>
            @if(session('success'))
                <p id="finishMessage" style="display: block;">{{ session('success') }}</p>
            @endif
        </div>
    </form>
</div>

@endsection

@section('styles')
<style>
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        margin: 40px auto;
        max-width: 800px;
        color: #333;
        text-align: center;
    }
    h1 {
        margin-bottom: 30px;
        color: #0056b3;
    }
    table {
        width: 80%;
        border-collapse: collapse;
        margin: 20px auto;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    th {
        background-color: #f2f2f2;
    }
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin: 0 5px;
    }
    .btn-primary {
        background-color: #007bff;
        color: white;
    }
    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }
    .input-field {
        width: 70%;
        padding: 8px;
        margin: 10px auto;
        box-sizing: border-box;
        display: block;
    }
    .signature-section {
        margin-top: 30px;
    }
    .signature-line {
        display: inline-block;
        min-width: 200px;
        border-bottom: 1px solid #333;
        margin-right: 10px;
    }
    #currentDateDisplay {
        display: inline-block;
        min-width: 150px;
        border-bottom: 1px solid #333;
        padding: 5px;
    }
    #finishMessage {
        margin-top: 20px;
        color: green;
        display: none;
    }
    .error {
        color: red;
        display: none;
    }
    .error-message {
        background-color: #ff5252;
        color: white;
        padding: 10px;
        border-radius: 4px;
        margin-top: 15px;
        text-align: center;
    }
    label {
        display: block;
        margin: 10px auto;
        width: 70%;
        text-align: left;
    }
    .signature-section p {
        text-align: center;
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if user is authenticated
        checkAuthenticationStatus();
        
        const currentDate = new Date();
        const day = currentDate.getDate();
        const monthNames = ["January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];
        const month = monthNames[currentDate.getMonth()];
        const year = currentDate.getFullYear();

        document.getElementById('currentDateDisplay').textContent = `${day} day of ${month} of ${year}`;
        
        // Function to check authentication status
        function checkAuthenticationStatus() {
            const authToken = localStorage.getItem('auth_token');
            const currentUser = JSON.parse(localStorage.getItem('currentUser'));
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            
            console.log(`[${new Date().toISOString()}] Checking authentication status`);
            
            // If no auth token or user info is found, redirect to login
            if (!authToken || !currentUser) {
                console.error(`[${new Date().toISOString()}] No auth token or user info found`);
                showSessionError();
                return false;
            }
            
            // If CSRF token is missing, session might be invalid
            if (!csrfToken) {
                console.error(`[${new Date().toISOString()}] CSRF token is missing`);
                showSessionError();
                return false;
            }
            
            // Set the token in the form header
            const form = document.getElementById('pledgeForm');
            const csrfInput = form.querySelector('input[name="_token"]');
            if (csrfInput) {
                csrfInput.value = csrfToken;
            }
            
            console.log(`[${new Date().toISOString()}] User authenticated as ${currentUser.role}`);
            return true;
        }
        
        // Show session error message and provide login redirect
        function showSessionError() {
            const errorMessage = document.getElementById('sessionErrorMessage');
            errorMessage.style.display = 'block';
            
            // Disable form submission
            const submitButton = document.getElementById('finishButton');
            submitButton.disabled = true;
            
            // Redirect to login after 3 seconds
            setTimeout(function() {
                window.location.href = '/login';
            }, 3000);
        }
        
        document.getElementById('pledgeForm').addEventListener('submit', function(event) {
            // Check authentication first
            if (!checkAuthenticationStatus()) {
                event.preventDefault();
                return;
            }
            
            let isValid = true;
            let hasAtLeastOneAsset = false;
            
            // Basic Validation
            if (!document.getElementById('name').value) {
                document.getElementById('name').nextElementSibling.style.display = 'block';
                isValid = false;
            }
            if (!document.getElementById('nationalId').value) {
                document.getElementById('nationalId').nextElementSibling.style.display = 'block';
                isValid = false;
            }
            if (!document.getElementById('address').value) {
                document.getElementById('address').nextElementSibling.style.display = 'block';
                isValid = false;
            }
            if (!document.getElementById('location').value) {
                document.getElementById('location').nextElementSibling.style.display = 'block';
                isValid = false;
            }
            if (!document.getElementById('signature').value) {
                document.getElementById('signature').nextElementSibling.style.display = 'block';
                isValid = false;
            }
            
            // Check if at least one asset is entered
            for (let i = 1; i <= 6; i++) {
                const assetDesc = document.getElementById('asset' + i).value;
                const assetValue = document.getElementById('value' + i).value;
                if (assetDesc && assetValue) {
                    hasAtLeastOneAsset = true;
                    break;
                }
            }
            
            if (!hasAtLeastOneAsset) {
                alert('Please enter at least one asset with description and value.');
                isValid = false;
            }
            
            if (!isValid) {
                event.preventDefault();
                return;
            }
            
            // Add auth token to the form submission
            const authToken = localStorage.getItem('auth_token');
            if (authToken) {
                // Add authorization header by creating a new hidden input
                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = 'auth_token';
                tokenInput.value = authToken;
                this.appendChild(tokenInput);
            }
        });
    });
</script>
@endsection