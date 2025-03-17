<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Loan Management System - Login</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .error-message {
      background-color: #ff5252;
      color: white;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 15px;
      text-align: center;
      display: none;
    }
    
    .success-message {
      background-color: #4CAF50;
      color: white;
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 15px;
      text-align: center;
      display: none;
    }
  </style>
</head>
<body>
  <header>
    <h1>Loan Management System</h1>
  </header>
  
  <div class="container">
    <!-- Login Section -->
    <div id="login-section">
      <h2>Login</h2>
      <div id="login-error" class="error-message" style="display: none;"></div>
      <form id="login-form">
        @csrf
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" placeholder="Enter your username" required>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>
        
        <button type="submit">Login</button>
      </form>
    </div>
  </div>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Login Elements
      const loginForm = document.getElementById('login-form');
      const loginError = document.getElementById('login-error');
      const loginSection = document.getElementById('login-section');
      
      // Login Form Submission
      loginForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        
        // Clear previous error messages
        loginError.style.display = 'none';

        // Create a timestamp for logging
        const timestamp = new Date().toISOString();

        // Log the login attempt (username only, not password)
        console.log(`[${timestamp}] Login attempt: ${username}`);
        
        // Send login request to Laravel backend
        fetch('/api/login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            username: username,
            password: password
          })
        })
        .then(response => {
          // Log the response status
          console.log(`[${timestamp}] Server response status: ${response.status}`);
          const statusCode = response.status;
          if (!response.ok) {
            return response.json().then(errorData => {
              // Log the detailed error message from the server
              console.error(`[${timestamp}] Server error: Status ${statusCode}, Message:`, errorData);
              throw new Error(`Server error: ${statusCode} - ${errorData.message || 'Unknown error'}`);
            }).catch(jsonError => {
              console.error(`[${timestamp}] Network error: Status ${statusCode}, Message: ${response.statusText}`);
              throw new Error(`Network error: ${statusCode} - ${response.statusText}`);
            });
          }
          return response.json();
        })
        .then(data => {
          console.log(`[${timestamp}] Login response:`, data);
          if (data.success) {
            console.log(`[${timestamp}] Login successful for user: ${username}, Role: ${data.user.role}`);
            console.log(`[${timestamp}] About to redirect user to: ${data.user.role === 'employee' ? '/employee/dashboard' : '/admin/dashboard'}`);
            // Store the token
            localStorage.setItem('auth_token', data.token);
            localStorage.setItem('currentUser', JSON.stringify({
              username: data.user.username,
              role: data.user.role
            }));
            
            // Check if password change is required for first-time login
            if (data.user.password_change_required) {
              console.log(`[${timestamp}] Password change required for first-time login`);
              // Show password change form
              showPasswordChangeForm();
            } else {
              // Redirect based on role
              if (data.user.role === 'employee') {
                console.log(`[${timestamp}] Redirecting to employee dashboard`);
                window.location.href = '/employee/dashboard';
              } else if (data.user.role === 'admin' || data.user.role === 'manager') {
                console.log(`[${timestamp}] Redirecting to admin dashboard`);
                window.location.href = '/admin/dashboard';
              }
            }
          } else {
            console.error(`[${timestamp}] Login failed for user: ${username}, Reason: ${data.message || 'Unknown reason'}`);
            // Show error message
            loginError.textContent = data.message || 'Login failed. Please check your credentials.';
            loginError.style.display = 'block';
          }
        })
        .catch(error => {
          console.error(`[${timestamp}] Login error:`, error);
          loginError.textContent = 'Login failed. Please try again later.';
          loginError.style.display = 'block';
          // Create a more detailed error message for developers
          const errorDetails = {
            timestamp: timestamp,
            username: username,
            error: error.toString(),
            userAgent: navigator.userAgent,
            url: window.location.href,
          };
          fetch('/api/log-error', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify(errorDetails)
          }).catch(logError => {
            console.error(`[${timestamp}] Failed to log error to server:`, logError);
          });
        });
      });
      
      // Function to show password change form
      function showPasswordChangeForm() {
        // Create and append the password change form to the container
        const container = document.querySelector('.container');
        
        // Store the login section HTML
        const loginSectionHTML = container.innerHTML;
        container.innerHTML = '';
        
        // Create the password change section
        const passwordChangeSection = document.createElement('div');
        passwordChangeSection.className = 'change-password-section';
        passwordChangeSection.innerHTML = `
          <h2>First-time Login</h2>
          <p>Welcome to the Loan Management System. For security reasons, you must change your password before continuing.</p>
          <h3>Change Password</h3>
          <div id="password-change-error" class="error-message" style="display: none;"></div>
          <div id="password-change-success" class="success-message" style="display: none;"></div>
          <form id="change-password-form">
            <label for="current_password">Current Password:</label>
            <input type="password" id="current_password" name="current_password" required>
            
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>
            
            <label for="new_password_confirmation">Confirm New Password:</label>
            <input type="password" id="new_password_confirmation" name="new_password_confirmation" required>
            
            <button type="submit">Change Password</button>
          </form>
        `;
        
        container.appendChild(passwordChangeSection);
        
        // Add event listener to the new form
        const changePasswordForm = document.getElementById('change-password-form');
        const passwordChangeError = document.getElementById('password-change-error');
        const passwordChangeSuccess = document.getElementById('password-change-success');
        
        changePasswordForm.addEventListener('submit', (e) => {
          e.preventDefault();
          
          // Clear previous messages
          passwordChangeError.style.display = 'none';
          passwordChangeSuccess.style.display = 'none';
          
          const currentPassword = document.getElementById('current_password').value;
          const newPassword = document.getElementById('new_password').value;
          const newPasswordConfirmation = document.getElementById('new_password_confirmation').value;
          
          // Validate passwords match
          if (newPassword !== newPasswordConfirmation) {
            passwordChangeError.textContent = "New passwords don't match";
            passwordChangeError.style.display = 'block';
            return;
          }
          
          // Send password change request to server
          fetch('/api/change-password', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
            },
            body: JSON.stringify({
              current_password: currentPassword,
              new_password: newPassword,
              new_password_confirmation: newPasswordConfirmation,
              first_time_login: true
            })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              passwordChangeSuccess.textContent = data.message || "Password changed successfully!";
              passwordChangeSuccess.style.display = 'block';
              changePasswordForm.reset();
              
              // After 2 seconds, redirect to the appropriate dashboard
              setTimeout(() => {
                const currentUser = JSON.parse(localStorage.getItem('currentUser'));
                if (currentUser.role === 'employee') {
                  window.location.href = '/employee/dashboard';
                } else if (currentUser.role === 'admin' || currentUser.role === 'manager') {
                  window.location.href = '/admin/dashboard';
                }
              }, 2000);
            } else {
              passwordChangeError.textContent = data.message || "Password change failed";
              passwordChangeError.style.display = 'block';
            }
          })
          .catch(error => {
            console.error('Error:', error);
            passwordChangeError.textContent = 'An error occurred. Please try again.';
            passwordChangeError.style.display = 'block';
          });
        });
      }
    });
  </script>
</body>
</html>