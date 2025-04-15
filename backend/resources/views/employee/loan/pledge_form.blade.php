<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pledge Agreement</title>
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
            z-index: 1000;
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
            margin-right: 1rem;
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
            margin-top: .3rem;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap; 
        }

        .logout-btn:hover {
            background-color: white;
            color: #4361ee;
        }

        /* Notification Styles */
        #notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #4CAF50;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 2000;
            display: none;
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

        /* Rest of the existing styles */
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
        .input-field {
            width: 70%;
            padding: 8px;
            margin: 10px auto;
            box-sizing: border-box;
            display: block;
        }
        .error {
            color: red;
            display: block;
            margin-top: 5px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }
        .signature-section {
            margin-top: 30px;
        }
        #currentDateDisplay {
            display: inline-block;
            min-width: 150px;
            border-bottom: 1px solid #333;
            padding: 5px;
        }
        .container {
            margin-top: 80px; /* To prevent content from being hidden behind fixed navbar */
        }
        /* Signature upload styles */
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
    </style>
</head>
<body>
    <!-- Notification Element -->
    <div id="notification"></div>

    <nav class="navbar">
        <div class="navbar-brand">Loan Management System</div>
        <div class="user-info">
            <div class="user-profile" id="user-initial">{{ strtoupper(substr(Auth::user()->username, 0, 1)) }}</div>
            <span id="username-display">{{ Auth::user()->username }}</span>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </nav>

    <div class="container">
        <h1>Pledge Agreement</h1>

        <form method="POST" action="{{ route('employee.loan.pledge.store', ['loan' => $loan->loan_id]) }}" id="pledgeForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="loan_id" value="{{ $loan->loan_id }}">

            <p>
                Name <input type="text" class="input-field" id="name" name="name" placeholder="Name" value="{{ old('name', $loan->employee->full_name ?? '') }}" required>
                @error('name')
                    <span class="error" id="nameError">{{ $message }}</span>
                @enderror
                
                The holder of National ID number <input type="text" class="input-field" id="nationalId" name="national_id" placeholder="National ID Number" value="{{ old('national_id', $loan->employee->national_id ?? '') }}" required>
                @error('national_id')
                    <span class="error" id="nationalIdError">{{ $message }}</span>
                @enderror
                
                Residing at <input type="text" class="input-field" id="address" name="address" placeholder="Address" value="{{ old('address', $loan->employee->physical_address ?? '') }}" required>
                @error('address')
                    <span class="error" id="addressError">{{ $message }}</span>
                @enderror
                
                hereby pledge the following assets to secure the loan that is granted to me by the Lender, Zimbabwe Women's Microfinance Bank (ZWMB), in full accordance with the provisions in the loan agreement:
            </p>

            <table>
                <thead>
                    <tr>
                        <th>Description of Asset</th>
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
                    Signed at <input type="text" class="input-field" id="location" name="location" placeholder="Location" value="{{ old('location') }}" required>
                    @error('location')
                        <span class="error" id="locationError">{{ $message }}</span>
                    @enderror
                    on this <span id="currentDateDisplay"></span>
                </p>
                
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
                
                <a href="{{ route('employee.loan.policy', ['loan' => $loan->loan_id]) }}" class="btn btn-secondary">Back</a>
                <button type="submit" id="finishButton">Submit Pledge</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentDate = new Date();
            const day = currentDate.getDate();
            const monthNames = ["January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ];
            const month = monthNames[currentDate.getMonth()];
            const year = currentDate.getFullYear();

            document.getElementById('currentDateDisplay').textContent = `${day} day of ${month} of ${year}`;
            
            // Function to show notification
            function showNotification(message, type = 'success') {
                const notification = document.getElementById('notification');
                notification.textContent = message;
                notification.style.backgroundColor = type === 'success' ? '#4CAF50' : '#f44336';
                notification.style.display = 'block';
                
                // Auto-hide notification after 5 seconds
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 5000);
            }

            // Check if there's a success message from previous redirect
            const successMessage = "{{ session('success') }}";
            if (successMessage) {
                showNotification(successMessage);
            }
            
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
            
            document.getElementById('pledgeForm').addEventListener('submit', function(event) {
                let isValid = true;
                let hasAtLeastOneAsset = false;
                
                // Basic Validation
                const requiredFields = ['name', 'nationalId', 'address', 'location', 'signature'];
                requiredFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (fieldId === 'signature') {
                        // Skip text validation for file input
                        return;
                    }
                    
                    const errorElement = document.getElementById(fieldId + 'Error');
                    if (!field.value.trim()) {
                        if (errorElement) {
                            errorElement.textContent = 'This field is required';
                            errorElement.style.display = 'block';
                        }
                        isValid = false;
                    } else if (errorElement) {
                        errorElement.style.display = 'none';
                    }
                });
                
                // Validate signature file is selected
                if (!signatureInput.files || signatureInput.files.length === 0) {
                    const errorElement = document.querySelector('[id$="signatureError"]');
                    if (errorElement) {
                        errorElement.textContent = 'Please upload your signature';
                        errorElement.style.display = 'block';
                    }
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
                } else {
                    // Show loading notification
                    showNotification('Submitting pledge information...', 'loading');
                }
            });
        });
    </script>
</body>
</html>