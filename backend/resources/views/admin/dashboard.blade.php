{{-- backend/resources/views/admin/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Loan Management System - Admin Dashboard</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
        }

        header {
            background-color: #007bff;
            color: white;
            text-align: center;
            padding: 1rem 0;
        }

        .container {
            width: 80%;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            flex-grow: 1;
        }

        .user-info {
            text-align: right;
            margin-bottom: 20px;
        }

        #admin-loans {
            margin-top: 20px;
        }

        .loan-item {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        .admin-actions {
            margin-top: 10px;
        }

        .admin-actions button {
            padding: 5px 10px;
            margin-right: 5px;
            cursor: pointer;
        }

        .status.approved {
            color: green;
        }

        .status.rejected {
            color: red;
        }

        .status.pending {
            color: orange;
        }

        .logout-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }

        .sidebar {
            width: 200px;
            background-color: #333;
            color: white;
            padding: 20px 0;
            height: 100vh;
            transition: width 0.3s ease;
            overflow: hidden;
        }

        .sidebar.collapsed {
            width: 60px;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar li {
            padding: 10px 20px;
            cursor: pointer;
            white-space: nowrap;
            overflow: hidden;
            transition: padding 0.3s ease;
        }

        .sidebar.collapsed li {
            padding: 10px 10px;
        }

        .sidebar li:hover {
            background-color: #555;
        }

        .content {
            flex-grow: 1;
        }

        #collapse-toggle {
            cursor: pointer;
            padding: 10px;
            background-color: #444;
            color: white;
            text-align: center;
        }

        .sidebar.collapsed #collapse-toggle::before {
            content: '▶';
        }

        #collapse-toggle::before {
            content: '◀';
        }

        .sidebar li span {
            display: inline-block;
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed li span {
            opacity: 0;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 5px;
            color: white;
            z-index: 1000;
        }
        .notification.success { background-color: #4CAF50; }
        .notification.error { background-color: #ff5252; }

        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        #loading-indicator {
            text-align: center;
            margin: 20px 0;
        }

        .loan-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div id="collapse-toggle" onclick="toggleSidebar()"></div>
        <ul>
            <li onclick="loadSection('loans')"><span>Loans</span></li>
            <li onclick="loadSection('users')"><span>Users</span></li>
            <li onclick="loadSection('reports')"><span>Reports</span></li>
            <li onclick="handleLogout()"><span>Logout</span></li>
        </ul>
    </div>

    <div class="content">
        <header>
            <h1>Loan Management System</h1>
            <h2>Admin Dashboard</h2>
        </header>

        <div class="container">
            <div class="user-info">
                <p>Welcome, <span id="username-display">{{ Auth::user()->username }}</span>!</p>
                <button class="logout-btn" onclick="handleLogout()">Logout</button>
            </div>

            <div id="admin-section">
                <!-- Dynamic content will be loaded here -->
                <div id="loading-indicator" style="display: none;">
                    <div class="loader"></div>
                    <p>Loading...</p>
                </div>
                <div id="content-container"></div>
            </div>
        </div>
    </div>

    <script>
        // CSRF Token handling
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // API Client
        const apiClient = {
            get: async (url) => {
                return fetch(`/api/admin${url}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
            },
            post: async (url, data) => {
                return fetch(`/api/admin${url}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include',
                    body: JSON.stringify(data)
                });
            },
            put: async (url, data) => {
                return fetch(`/api/admin${url}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include',
                    body: JSON.stringify(data)
                });
            }
        };

        // UI Functions
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 5000);
        }

        function toggleLoading(show) {
            document.getElementById('loading-indicator').style.display = show ? 'block' : 'none';
        }

        // Data Loading
        async function loadSection(section) {
            try {
                toggleLoading(true);
                const response = await apiClient.get(`/${section}`);
                
                if (!response.ok) throw new Error('Failed to load data');
                
                const data = await response.json();
                renderSection(section, data);
            } catch (error) {
                showNotification(error.message, 'error');
                if (error.response && error.response.status === 401) {
                    setTimeout(() => window.location.href = '/login', 2000);
                }
            } finally {
                toggleLoading(false);
            }
        }

        function renderSection(section, data) {
            const container = document.getElementById('content-container');
            switch(section) {
                case 'loans':
                    container.innerHTML = renderLoans(data);
                    break;
                case 'users':
                    container.innerHTML = renderUsers(data);
                    break;
                case 'reports':
                    container.innerHTML = renderReports(data);
                    break;
            }
        }

        function renderLoans(loans) {
            return `
                <h2>Loan Applications</h2>
                <div class="loan-list">
                    ${loans.data.map(loan => `
                        <div class="loan-item">
                            <h3>Loan #${loan.loan_id}</h3>
                            <p>Amount: $${loan.amount}</p>
                            <p>Status: <span class="status ${loan.status.toLowerCase()}">${loan.status}</span></p>
                            <div class="admin-actions">
                                ${loan.status === 'Pending' ? `
                                    <button onclick="updateLoan(${loan.loan_id}, 'Approved')">Approve</button>
                                    <button onclick="updateLoan(${loan.loan_id}, 'Rejected')">Reject</button>
                                ` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
                ${renderPagination(loans)}
            `;
        }

        function renderUsers(users) {
            return `
                <h2>System Users</h2>
                <div class="users-list">
                    ${users.data.map(user => `
                        <div class="user-item">
                            <h3>${user.username}</h3>
                            <p>Email: ${user.email}</p>
                            <p>Role: ${user.role}</p>
                            <p>Status: <span class="status ${user.status.toLowerCase()}">${user.status}</span></p>
                        </div>
                    `).join('')}
                </div>
                ${renderPagination(users)}
            `;
        }

        function renderReports(reports) {
            return `
                <h2>System Reports</h2>
                <div class="reports-list">
                    <div class="report-item">
                        <h3>Loan Summary</h3>
                        <p>Total Loans: ${reports.totalLoans}</p>
                        <p>Approved Loans: ${reports.approvedLoans}</p>
                        <p>Pending Loans: ${reports.pendingLoans}</p>
                        <p>Rejected Loans: ${reports.rejectedLoans}</p>
                    </div>
                    <div class="report-item">
                        <h3>User Summary</h3>
                        <p>Total Users: ${reports.totalUsers}</p>
                        <p>Active Users: ${reports.activeUsers}</p>
                    </div>
                </div>
            `;
        }

        function renderPagination(data) {
            if (!data.links || data.links.length <= 3) return '';
            
            return `
                <div class="pagination">
                    ${data.links.map(link => `
                        <a href="#" 
                           class="${link.active ? 'active' : ''} ${link.url ? '' : 'disabled'}"
                           onclick="${link.url ? `loadPage('${link.url}')` : 'return false'}"
                        >
                            ${link.label}
                        </a>
                    `).join('')}
                </div>
            `;
        }

        async function loadPage(url) {
            try {
                toggleLoading(true);
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
                
                if (!response.ok) throw new Error('Failed to load data');
                
                const data = await response.json();
                const currentSection = url.split('/').pop().split('?')[0];
                renderSection(currentSection, data);
            } catch (error) {
                showNotification(error.message, 'error');
            } finally {
                toggleLoading(false);
            }
        }

        // Loan Actions
        async function updateLoan(loanId, status) {
            try {
                const response = await apiClient.put(`/loan-applications/${loanId}`, { status });
                if (!response.ok) throw new Error('Update failed');
                
                const updatedLoan = await response.json();
                showNotification('Loan updated successfully');
                loadSection('loans');
            } catch (error) {
                showNotification(error.message, 'error');
            }
        }

        // Authentication
        async function handleLogout() {
            try {
                const response = await fetch('/logout', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
                
                if (response.ok) {
                    window.location.href = '/login';
                }
            } catch (error) {
                showNotification('Logout failed', 'error');
            }
        }

        // Initialization
        document.addEventListener('DOMContentLoaded', () => {
            // Load default section
            loadSection('loans');
            
            // Verify session periodically
            setInterval(async () => {
                try {
                    await apiClient.get('/user');
                } catch (error) {
                    handleLogout();
                }
            }, 300000); // 5 minutes
        });

        // Sidebar toggle function
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
        }
    </script>
</body>
</html>