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
    /* Modal Styles */
    .modal {
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .modal-content {
      background-color: white;
      padding: 20px;
      border-radius: 5px;
      width: 90%;
      max-width: 500px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .close-button {
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }

    .close-button:hover {
      color: #555;
    }

    #password-change-form {
      margin-top: 20px;
    }

    #password-change-form input {
      width: 100%;
      padding: 10px;
      margin: 8px 0;
      display: inline-block;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
    }

    #password-change-form button {
      width: 100%;
      background-color: #4CAF50;
      color: white;
      padding: 12px;
      margin: 10px 0;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }

    #password-change-form button:hover {
      background-color: #45a049;
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

    <!-- Password Change Modal -->
    <div id="password-change-modal" class="modal" style="display:none;">
      <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Change Your Password</h2>
        <p>You must change your default password before continuing.</p>
        
        <div id="password-change-error" class="error-message" style="display: none;"></div>
        <div id="password-change-success" class="success-message" style="display: none;"></div>
        
        <form id="password-change-form">
          <input type="hidden" id="modal-username" name="username">
          <input type="hidden" id="modal-token" name="token">
          
          <label for="current-password">Current Password:</label>
          <input type="password" id="current-password" name="current_password" placeholder="Enter your current password" required>
          
          <label for="new-password">New Password:</label>
          <input type="password" id="new-password" name="new_password" placeholder="Enter new password" required>
          
          <label for="new-password-confirmation">Confirm New Password:</label>
          <input type="password" id="new-password-confirmation" name="new_password_confirmation" placeholder="Confirm new password" required>
          
          <button type="submit">Change Password</button>
        </form>
      </div>
    </div>
  </div>
  
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    console.log(`[${new Date().toISOString()}] CSRF Token: ${csrfToken || 'Not found'}`);
    
    // Login Elements
    const loginForm = document.getElementById('login-form');
    const loginError = document.getElementById('login-error');
    const loginSection = document.getElementById('login-section');
    
    // Password Change Elements
    const passwordChangeModal = document.getElementById('password-change-modal');
    const passwordChangeForm = document.getElementById('password-change-form');
    const passwordChangeError = document.getElementById('password-change-error');
    const passwordChangeSuccess = document.getElementById('password-change-success');
    const closeButton = document.querySelector('.close-button');
    
    function debugCsrfToken() {
      const metaToken = document.querySelector('meta[name="csrf-token"]')?.content;
      const cookieToken = document.cookie.split('; ')
        .find(row => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1];
      
      console.log('Meta CSRF Token:', metaToken);
      console.log('Cookie CSRF Token:', cookieToken ? decodeURIComponent(cookieToken) : 'Not found');
    }
    
    debugCsrfToken();
    
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
      
      // Determine if we're using API or web login
      const isApiLogin = window.location.pathname.includes('/api/');
      const loginUrl = isApiLogin ? '/api/login' : '/login';
      
      // Send login request to Laravel backend
      fetch(loginUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        credentials: 'include', 
        body: JSON.stringify({
          username: username,
          password: password,
          _token: csrfToken  // Include the token in the request body as well
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
            // If we can't parse the JSON, return the original error
            if (jsonError instanceof SyntaxError) {
              console.error(`[${timestamp}] Network error: Status ${statusCode}, Message: ${response.statusText}`);
              throw new Error(`Network error: ${statusCode} - ${response.statusText}`);
            }
            throw jsonError;
          });
        }
        return response.json();
      })
      .then(data => {
        console.log(`[${timestamp}] Login response:`, data);
        if (data.success) {
          console.log(`[${timestamp}] Login successful for user: ${username}, Role: ${data.user?.role}`);
          
          // Store the auth token
          localStorage.setItem('auth_token', data.token);
          
          // Store the user info
          localStorage.setItem('currentUser', JSON.stringify({
            username: data.user.username,
            role: data.user.role
          }));
          
          // Check if password change is required
          if (data.password_change_required) {
            console.log(`[${timestamp}] Password change required. Redirecting...`);
            
            // For API requests with JSON response, show the password change modal
            if (isApiLogin) {
              showPasswordChangeModal(username, data.token);
            } else {
              // For web requests, redirect to the password change form
              window.location.href = `/change-password?username=${encodeURIComponent(username)}&token=${encodeURIComponent(data.token)}&role=${encodeURIComponent(data.user.role)}`;
            }
          } else {
            // No password change needed, redirect based on role
            console.log(`[${timestamp}] About to redirect user to dashboard`);
            redirectBasedOnRole(data.user.role);
          }
        } else {
          console.error(`[${timestamp}] Login failed for user: ${username}, Reason: ${data.message || 'Unknown reason'}`);
          // Show error message
          showError(data.message || 'Login failed. Please check your credentials.');
        }
      })
      .catch(error => {
        console.error(`[${timestamp}] Login error:`, error);
        showError('Login failed. Please try again later.');
        // Create a more detailed error message for developers
        const errorDetails = {
          timestamp: timestamp,
          username: username,
          error: error.toString(),
          userAgent: navigator.userAgent,
          url: window.location.href,
        };
        
        // Log the error to the server
        const errorLogUrl = isApiLogin ? '/api/log-error' : '/api/log-error';
        fetch(errorLogUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify(errorDetails)
        }).catch(logError => {
          console.error(`[${timestamp}] Failed to log error to server:`, logError);
        });
      });
    });
    
    // Show password change modal
    function showPasswordChangeModal(username, token) {
      // Set values in the hidden fields
      document.getElementById('modal-username').value = username;
      document.getElementById('modal-token').value = token;
      
      // Clear any previous form data
      document.getElementById('current-password').value = '';
      document.getElementById('new-password').value = '';
      document.getElementById('new-password-confirmation').value = '';
      
      // Hide any error/success messages
      passwordChangeError.style.display = 'none';
      passwordChangeSuccess.style.display = 'none';
      
      // Show the modal
      passwordChangeModal.style.display = 'flex';
    }
    
    // Close modal when clicking the X button
    if (closeButton) {
      closeButton.addEventListener('click', () => {
        passwordChangeModal.style.display = 'none';
      });
    }
    
    // Close modal when clicking outside the modal content
    window.addEventListener('click', (e) => {
      if (e.target === passwordChangeModal) {
        passwordChangeModal.style.display = 'none';
      }
    });
    
    // Password Change Form Submission
    if (passwordChangeForm) {
      passwordChangeForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const currentPassword = document.getElementById('current-password').value;
        const newPassword = document.getElementById('new-password').value;
        const newPasswordConfirmation = document.getElementById('new-password-confirmation').value;
        const username = document.getElementById('modal-username').value;
        const token = document.getElementById('modal-token').value;
        
        // Clear previous messages
        passwordChangeError.style.display = 'none';
        passwordChangeSuccess.style.display = 'none';
        
        // Validate passwords
        if (newPassword !== newPasswordConfirmation) {
          passwordChangeError.textContent = "New passwords don't match";
          passwordChangeError.style.display = 'block';
          return;
        }
        
        if (newPassword.length < 8) {
          passwordChangeError.textContent = "New password must be at least 8 characters long";
          passwordChangeError.style.display = 'block';
          return;
        }
        
        const timestamp = new Date().toISOString();
        console.log(`[${timestamp}] Attempting to change password for ${username}`);
        
        // Send password change request
        fetch('/api/change-password', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
          },
          credentials: 'include',
          body: JSON.stringify({
            current_password: currentPassword,
            new_password: newPassword,
            new_password_confirmation: newPasswordConfirmation,
            first_time_login: true
          })
        })
        .then(response => {
          console.log(`[${timestamp}] Password change response status: ${response.status}`);
          return response.json();
        })
        .then(data => {
          console.log(`[${timestamp}] Password change response:`, data);
          
          if (data.success) {
            // Store new token if provided
            if (data.token) {
              localStorage.setItem('auth_token', data.token);
            }
            
            // Show success message
            passwordChangeSuccess.textContent = data.message || "Password changed successfully!";
            passwordChangeSuccess.style.display = 'block';
            
            // Redirect after a short delay
            setTimeout(() => {
              const currentUser = JSON.parse(localStorage.getItem('currentUser') || '{}');
              redirectBasedOnRole(currentUser.role || 'employee');
            }, 1500);
          } else {
            // Show error message
            passwordChangeError.textContent = data.message || "Failed to change password";
            passwordChangeError.style.display = 'block';
          }
        })
        .catch(error => {
          console.error(`[${timestamp}] Password change error:`, error);
          passwordChangeError.textContent = "An error occurred. Please try again.";
          passwordChangeError.style.display = 'block';
        });
      });
    }
    
    // Function to get XSRF token from cookies
    function getXsrfToken() {
      const tokenCookie = document.cookie
        .split('; ')
        .find(row => row.startsWith('XSRF-TOKEN='));
      
      if (tokenCookie) {
        return decodeURIComponent(tokenCookie.split('=')[1]);
      }
      return null;
    }
    
    // Function to show error messages
    function showError(message) {
      loginError.textContent = message;
      loginError.style.display = 'block';
    }
    
    // Function to redirect based on role
    function redirectBasedOnRole(role) {
      const redirectPath = (role === 'admin' ? '/admin/dashboard' : 
                          role === 'hr' ? '/hr/dashboard' : 
                          '/employee/dashboard');
      console.log(`Redirecting to: ${redirectPath}`);
      window.location.href = redirectPath;
    }
  });
  </script>
</body>
</html>