<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Loan Management System - Login</title>
  <link rel="stylesheet" href="style.css">

</head>
<body>

  <header>
    <h1>Loan Management System</h1>
  </header>

  <div class="container">
    <!-- Login Section -->
    <div id="login-section">
      <h2>Login</h2>
      <form id="login-form">
        <label for="username">Username:</label>
        <input type="text" id="username" placeholder="Enter your username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" placeholder="Enter your password" required>

        <label for="role">Role:</label>
        <select id="role">
          <option value="employee">Employee</option>
          <option value="admin">Admin</option>
          <option value="manager">Manager</option>
        </select>

        <button type="submit">Login</button>
      </form>
      <p><strong>Demo credentials:</strong><br>
      Admin: username = "admin", password = "admin123"<br>
      Employee: username = "employee1", password = "employee123"</p>
    </div>
  </div>

  <script>
    // Sample data storage
    const validUsers = [
      { username: 'admin', password: 'admin123', role: 'admin' },
      { username: 'employee1', password: 'employee123', role: 'employee' }
    ];

    // Login Elements
    const loginForm = document.getElementById('login-form');

    // Login Form Submission
    loginForm.addEventListener('submit', (e) => {
      e.preventDefault();
      
      const username = document.getElementById('username').value;
      const password = document.getElementById('password').value;
      const role = document.getElementById('role').value;
  
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
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Store the token
          localStorage.setItem('auth_token', data.token);
          localStorage.setItem('currentUser', JSON.stringify({
            username: data.user.username,
            role: data.user.role
          }));
          // Redirect based on role
          if (data.user.role === 'employee') {
            window.location.href = '/employee/dashboard';
          } else if (data.user.role === 'admin' || data.user.role === 'manager') {
            window.location.href = '/admin/dashboard';
          }
        } else {
          alert(data.message || 'Login failed. Please check your credentials.');
        }
      })
      .catch(error => {
        console.error('Login error:', error);
        alert('Login failed. Please try again later.');
      });
    });

    async function loadLoanHistory() {
    try {
        const response = await fetch('/api/employee/loans', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
                'Accept': 'application/json'
            }
        });
        
        const loans = await response.json();
        renderLoans(loans);
    } catch (error) {
        console.error('Error loading loan history:', error);
    }
}

function renderLoans(loans) {
    const container = document.getElementById('loan-history');
    container.innerHTML = loans.map(loan => `
        <div class="loan-card">
            <h3>Loan #${loan.loan_id}</h3>
            <p>Amount: $${loan.amount}</p>
            <p>Status: <span class="status ${loan.status.toLowerCase()}">${loan.status}</span></p>
            <p>Applied: ${new Date(loan.application_date).toLocaleDateString()}</p>
        </div>
    `).join('');
}
  </script>
</body>
</html>