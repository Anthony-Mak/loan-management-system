{{-- resources/views/employee/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Loan Management System - Employee Dashboard</title>
  <style>
    :root {
      --primary: #4361ee;
      --secondary: #3f37c9;
      --success: #4cc9f0;
      --danger: #f72585;
      --warning: #f8961e;
      --info: #4895ef;
      --light: #f8f9fa;
      --dark: #212529;
      --pending: #f8961e;
      --approved: #4cc9f0;
      --rejected: #f72585;
      --background: #f6f8fa;
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

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background-color: var(--background);
      color: var(--dark);
    }

    .navbar {
      background-color: var(--primary);
      color: white;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
      color: var(--primary);
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
      color: var(--primary);
    }

    .dashboard {
      padding: 2rem;
      max-width: 1200px;
      margin: 0 auto;
    }

    .summary-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1rem;
      margin-bottom: 2rem;
    }

    .card {
      background: white;
      border-radius: 8px;
      padding: 1.5rem;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .card-title {
      font-size: 0.9rem;
      color: #777;
      margin-bottom: 0.5rem;
    }

    .card-value {
      font-size: 1.8rem;
      font-weight: bold;
      color: var(--dark);
    }

    .actions {
      display: flex;
      gap: 1rem;
      margin-bottom: 2rem;
    }

    .btn {
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-primary {
      background-color: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      background-color: var(--secondary);
    }

    .section-title {
      font-size: 1.5rem;
      margin-bottom: 1rem;
      color: var(--dark);
      border-bottom: 2px solid var(--primary);
      padding-bottom: 0.5rem;
    }

    .loan-list {
      margin-top: 1rem;
    }

    .loan-item {
      background: white;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1rem;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      display: grid;
      grid-template-columns: 1fr 1fr 1fr 1fr;
      gap: 1rem;
      align-items: center;
    }

    .loan-id {
      color: #777;
      font-size: 0.9rem;
    }

    .loan-name {
      font-weight: bold;
    }

    .loan-amount {
      font-size: 1.2rem;
      font-weight: bold;
    }

    .status {
      padding: 0.25rem 0.75rem;
      border-radius: 50px;
      font-size: 0.8rem;
      font-weight: bold;
      text-align: center;
      display: inline-block;
    }

    .status.pending {
      background-color: rgba(248, 150, 30, 0.2);
      color: var(--pending);
    }

    .status.approved {
      background-color: rgba(76, 201, 240, 0.2);
      color: var(--approved);
    }

    .status.rejected {
      background-color: rgba(247, 37, 133, 0.2);
      color: var(--rejected);
    }

    .loan-detail {
      background: white;
      border-radius: 8px;
      padding: 1.5rem;
      margin-top: 1rem;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      display: none;
    }

    .loan-detail.active {
      display: block;
    }

    .loan-detail-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    .loan-detail-item {
      margin-bottom: 1rem;
    }

    .loan-detail-label {
      font-size: 0.9rem;
      color: #777;
      margin-bottom: 0.25rem;
    }

    .loan-detail-value {
      font-weight: bold;
    }

    .review-block {
      background-color: var(--light);
      border-left: 3px solid var(--primary);
      padding: 1rem;
      margin-top: 1rem;
      border-radius: 4px;
    }

    .empty-state {
      text-align: center;
      padding: 2rem;
      color: #777;
    }

    @media (max-width: 768px) {
      .loan-item {
        grid-template-columns: 1fr;
      }
      
      .loan-detail-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <nav class="navbar">
    <div class="navbar-brand">Loan Management System</div>
    <div class="user-info">
      <div class="user-profile" id="user-initial">{{ auth()->user()->name[0] }}</div>
      <span id="username-display">{{ auth()->user()->name }}</span>
      <form method="POST" action="{{ route('logout') }}" class="inline">
        @csrf
        <button type="submit" class="logout-btn">Logout</button>
      </form>
    </div>
  </nav>

  <div class="dashboard">
    <div class="summary-cards">
      <div class="card">
        <div class="card-title">Total Applications</div>
        <div class="card-value" id="total-applications">0</div>
      </div>
      <div class="card">
        <div class="card-title">Pending</div>
        <div class="card-value" id="pending-applications">0</div>
      </div>
      <div class="card">
        <div class="card-title">Approved</div>
        <div class="card-value" id="approved-applications">0</div>
      </div>
      <div class="card">
        <div class="card-title">Rejected</div>
        <div class="card-value" id="rejected-applications">0</div>
      </div>
    </div>

    <div class="actions">
      <a href="{{ route('employee.loan.create') }}" class="btn btn-primary">Apply for New Loan</a>
    </div>
    
    <h2 class="section-title">Your Loan Applications</h2>
    
    <div id="employee-loans" class="loan-list">
      <!-- Loan items will be added here dynamically -->
    </div>
    
    <div id="loan-detail" class="loan-detail">
      <!-- Selected loan details will be shown here -->
    </div>
  </div>

  <script>
    // API URL for the backend
    const apiUrl = '/api';

    // Fetch dashboard data
    async function fetchDashboardData() {
      try {
        const response = await fetch(`${apiUrl}/employee/dashboard`, {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          credentials: 'include'
        });
        
        if (!response.ok) {
          throw new Error('Failed to fetch dashboard data');
        }
        
        const data = await response.json();
        
        // Update dashboard stats
        document.getElementById('total-applications').textContent = data.total_applications;
        document.getElementById('pending-applications').textContent = data.pending_applications;
        document.getElementById('approved-applications').textContent = data.approved_applications;
        document.getElementById('rejected-applications').textContent = data.rejected_applications;
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
      }
    }

    // Fetch loan applications
    async function fetchLoanApplications() {
      try {
        const response = await fetch(`${apiUrl}/employee/loans`, {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          credentials: 'include'
        });
        
        if (!response.ok) {
          throw new Error('Failed to fetch loan applications');
        }
        
        const loans = await response.json();
        renderEmployeeLoans(loans);
      } catch (error) {
        console.error('Error fetching loan applications:', error);
      }
    }

    // Render Employee Loans
    function renderEmployeeLoans(loans) {
      const employeeLoans = document.getElementById('employee-loans');
      employeeLoans.innerHTML = '';

      if (loans.length === 0) {
        employeeLoans.innerHTML = `
          <div class="empty-state">
            <p>No loan applications found.</p>
            <p>Apply for a loan to get started.</p>
          </div>
        `;
        return;
      }

      loans.forEach((loan) => {
        const div = document.createElement('div');
        div.classList.add('loan-item');
        div.innerHTML = `
          <div>
            <div class="loan-id">#${loan.id}</div>
            <div class="loan-name">${loan.employee ? loan.employee.full_name : 'Employee'}</div>
          </div>
          <div class="loan-amount">$${loan.amount}</div>
          <div>
            <span class="status ${loan.status.toLowerCase()}">${loan.status}</span>
          </div>
          <div>
            <button onclick="showLoanDetail('${loan.id}')" class="btn btn-primary">View Details</button>
          </div>
        `;
        employeeLoans.appendChild(div);
      });
    }

    // Show Loan Detail
    async function showLoanDetail(loanId) {
      try {
        const response = await fetch(`${apiUrl}/employee/loans/${loanId}`, {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          credentials: 'include'
        });
        
        if (!response.ok) {
          throw new Error('Failed to fetch loan details');
        }
        
        const loan = await response.json();
        const loanDetail = document.getElementById('loan-detail');
        
        loanDetail.innerHTML = `
          <h3>Loan Details</h3>
          <div class="loan-detail-grid">
            <div class="loan-detail-item">
              <div class="loan-detail-label">Loan ID</div>
              <div class="loan-detail-value">${loan.id}</div>
            </div>
            <div class="loan-detail-item">
              <div class="loan-detail-label">Applicant</div>
              <div class="loan-detail-value">${loan.employee ? loan.employee.full_name : 'Employee'}</div>
            </div>
            <div class="loan-detail-item">
              <div class="loan-detail-label">Amount Requested</div>
              <div class="loan-detail-value">$${loan.amount}</div>
            </div>
            <div class="loan-detail-item">
              <div class="loan-detail-label">Status</div>
              <div class="loan-detail-value">
                <span class="status ${loan.status.toLowerCase()}">${loan.status}</span>
              </div>
            </div>
            <div class="loan-detail-item">
              <div class="loan-detail-label">Application Date</div>
              <div class="loan-detail-value">${new Date(loan.application_date).toLocaleDateString()}</div>
            </div>
            <div class="loan-detail-item">
              <div class="loan-detail-label">Purpose</div>
              <div class="loan-detail-value">${loan.purpose || 'Not specified'}</div>
            </div>
          </div>
          ${loan.review_notes ? `
            <div class="review-block">
              <div class="loan-detail-label">Review Comments</div>
              <div class="loan-detail-value">${loan.review_notes}</div>
            </div>
          ` : ''}
        `;
        loanDetail.classList.add('active');
      } catch (error) {
        console.error('Error fetching loan details:', error);
      }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', async function() {
      await fetchDashboardData();
      await fetchLoanApplications();
    });
  </script>
</body>
</html>