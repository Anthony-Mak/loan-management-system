<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Change Password</title>
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
      background-color: #f8f9fa;
      margin: 0;
      padding: 0;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    
    .container {
      width: 100%;
      max-width: 400px;
      background-color: white;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
      padding: 32px;
      animation: fadeIn 0.4s ease-out;
      margin: 20px;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-15px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .form-header {
      text-align: center;
      margin-bottom: 28px;
    }
    
    .form-header h2 {
      color: #333;
      font-size: 20px;
      font-weight: 600;
      margin: 0;
    }
    
    .form-header p {
      color: #777;
      margin-top: 8px;
      font-size: 14px;
    }
    
    form {
      display: flex;
      flex-direction: column;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    label {
      display: block;
      margin-bottom: 6px;
      font-weight: 500;
      color: #555;
      font-size: 14px;
    }
    
    input {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #e0e0e0;
      border-radius: 6px;
      transition: all 0.2s;
      font-size: 14px;
      background-color: #f9f9f9;
    }
    
    input:focus {
      border-color: #007bff;
      outline: none;
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
      background-color: #fff;
    }
    
    button {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 12px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.2s;
      box-shadow: 0 2px 4px rgba(0, 123, 255, 0.2);
    }
    
    button:hover {
      background-color: #0069d9;
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
    }
    
    button:active {
      transform: translateY(0);
      box-shadow: 0 2px 4px rgba(0, 123, 255, 0.2);
    }
    
    .message {
      padding: 10px;
      border-radius: 6px;
      margin-bottom: 20px;
      text-align: center;
      display: none;
      font-size: 14px;
      animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .error-message {
      background-color: #fff2f2;
      color: #e74c3c;
      border: 1px solid #ffcdd2;
    }
    
    .success-message {
      background-color: #f0f9f0;
      color: #2ecc71;
      border: 1px solid #c8e6c9;
    }

    .password-strength {
      height: 4px;
      margin-top: 8px;
      border-radius: 2px;
      transition: all 0.3s;
      background-color: #eee;
    }

    .password-requirements {
      font-size: 12px;
      color: #888;
      margin-top: 6px;
    }
    
    .form-logo {
      text-align: center;
      margin-bottom: 24px;
    }
    
    .form-logo svg {
      width: 40px;
      height: 40px;
      color: #007bff;
    }
    
    .divider {
      border-top: 1px solid #eee;
      margin: 15px 0;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="form-logo">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
      </svg>
    </div>
    
    <div class="form-header">
      <h2>Change Your Password</h2>
      <p>Please create a new password to continue</p>
    </div>
    
    <div id="password-change-error" class="message error-message"></div>
    <div id="password-change-success" class="message success-message"></div>
    
    <form id="password-change-form">
      @csrf
      <input type="hidden" id="username" name="username" value="{{ $username ?? '' }}">
      <input type="hidden" id="token" name="token" value="{{ $token ?? '' }}">
      <input type="hidden" id="role" name="role" value="{{ $role ?? 'employee' }}">
      
      <div class="form-group">
        <label for="current-password">Current Password</label>
        <input type="password" id="current-password" name="current_password" placeholder="Enter your current password" required autocomplete="current-password">
      </div>
      
      <div class="divider"></div>
      
      <div class="form-group">
        <label for="new-password">New Password</label>
        <input type="password" id="new-password" name="new_password" placeholder="Enter new password" required autocomplete="new-password">
        <div class="password-strength" id="password-strength"></div>
        <div class="password-requirements">Password must be at least 8 characters long</div>
      </div>
      
      <div class="form-group">
        <label for="new-password-confirmation">Confirm New Password</label>
        <input type="password" id="new-password-confirmation" name="new_password_confirmation" placeholder="Confirm new password" required autocomplete="new-password">
      </div>
      
      <button type="submit">Update Password</button>
    </form>
  </div>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const passwordChangeForm = document.getElementById('password-change-form');
      const passwordChangeError = document.getElementById('password-change-error');
      const passwordChangeSuccess = document.getElementById('password-change-success');
      const passwordStrength = document.getElementById('password-strength');
      const newPasswordInput = document.getElementById('new-password');
      
      // Get the CSRF token from meta tag
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
      console.log('CSRF Token:', csrfToken);
      
      // Get user data from URL parameters
      const username = document.getElementById('username').value;
      const token = document.getElementById('token').value;
      const role = document.getElementById('role').value;
      
      console.log(`User data: username=${username}, role=${role}`);
      
      if (!username || !token) {
        passwordChangeError.textContent = "Missing required authentication data. Please log in again.";
        passwordChangeError.style.display = 'block';
        
        // Redirect back to login after a short delay
        setTimeout(() => {
          window.location.href = '/login';
        }, 3000);
      }
      
      // Password strength indicator
      if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
          const password = this.value;
          let strength = 0;
          
          if (password.length >= 8) strength += 25;
          if (password.match(/[A-Z]/)) strength += 25;
          if (password.match(/[0-9]/)) strength += 25;
          if (password.match(/[^A-Za-z0-9]/)) strength += 25;
          
          passwordStrength.style.width = strength + '%';
          
          if (strength < 50) {
            passwordStrength.style.backgroundColor = '#ff5252';
          } else if (strength < 75) {
            passwordStrength.style.backgroundColor = '#ffb700';
          } else {
            passwordStrength.style.backgroundColor = '#4CAF50';
          }
        });
      }
      
      if (passwordChangeForm) {
        passwordChangeForm.addEventListener('submit', (e) => {
          e.preventDefault();
          
          const currentPassword = document.getElementById('current-password').value;
          const newPassword = document.getElementById('new-password').value;
          const newPasswordConfirmation = document.getElementById('new-password-confirmation').value;
          
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
              'Authorization': `Bearer ${decodeURIComponent(token)}` 
            },
            credentials: 'include',
            body: JSON.stringify({
              username: username,
              token: token,  
              current_password: currentPassword,
              new_password: newPassword,
              new_password_confirmation: newPasswordConfirmation,
              first_time_login: "true"
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
              
              // Store user information in localStorage
              try {
                localStorage.setItem('currentUser', JSON.stringify({
                  username: username,
                  role: role
                }));
              } catch (error) {
                console.error('Error storing user data:', error);
              }
              
              // Show success message
              passwordChangeSuccess.textContent = data.message || "Password changed successfully!";
              passwordChangeSuccess.style.display = 'block';
              
              // Redirect after a short delay
              setTimeout(() => {
                const redirectPath = (role === 'admin' ? '/admin/dashboard' : 
                                      role === 'hr' ? '/hr/dashboard' : 
                                      '/employee/dashboard');
                window.location.href = redirectPath;
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
    });
  </script>
</body>
</html>