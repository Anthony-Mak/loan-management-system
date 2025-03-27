{{-- backend/resources/views/admin/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Loan Management System - {{ ucfirst(Auth::user()->role) }} Dashboard</title>
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
        .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    width: 80%;
    max-width: 500px;
    max-height: 80vh;
    overflow-y: auto;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
}

.form-group textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.modal-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

.modal-actions button {
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    border: none;
}

.approve-btn {
    background-color: #4CAF50;
    color: white;
}

.reject-btn {
    background-color: #f44336;
    color: white;
}

.cancel-btn {
    background-color: #ccc;
}

.error-message {
    color: #f44336;
    padding: 10px;
    background-color: #ffebee;
    border-radius: 4px;
    margin-bottom: 15px;
}
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div id="collapse-toggle" onclick="toggleSidebar()"></div>
        <ul>
            <li onclick="loadSection('loan-applications')"><span>Loans</span></li>
            <li onclick="loadSection('employees')"><span>Users</span></li>
            <li onclick="loadSection('reports')"><span>Reports</span></li>
            <li onclick="handleLogout()"><span>Logout</span></li>
        </ul>
    </div>

    <div class="content">
        <header>
            <h1>Loan Management System</h1>
            <h2>{{ ucfirst(Auth::user()->role) }} Dashboard</h2>
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
        const userRole = '{{ Auth::user()->role }}';
        const apiBase = userRole === 'hr' ? '/api/hr' : '/api/admin';

        // Sidebar Configuration
        const sidebarConfig = {
            'admin': [
                { label: 'Users', section: 'users', icon: 'Users' },
                { label: 'Reports', section: 'reports', icon: 'BarChart2' },
                { label: 'Logout', action: 'handleLogout', icon: 'LogOut' }
            ],
            'hr': [
                { label: 'Loan Applications', section: 'loans', icon: 'FileText' },
                { label: 'Employees', section: 'employees', icon: 'User' },
                { label: 'HR Reports', section: 'reports', icon: 'BarChart' },
                { label: 'Logout', action: 'handleLogout', icon: 'LogOut' }
            ]
        };

        // Render Sidebar
        function renderSidebar() {
            const sidebar = document.getElementById('sidebar');
            const role = userRole.toLowerCase(); // Ensure lowercase for matching
            // Clear existing sidebar content
            sidebar.innerHTML = `
            <div id="collapse-toggle" onclick="toggleSidebar()"></div>
            <ul id="sidebar-menu">
             ${sidebarConfig[role].map(item => `
                <li onclick="${item.action ? item.action + '()' : 'loadSection(\'' + item.section + '\')'}" 
                    class="sidebar-item" 
                    data-section="${item.section || item.action}">
                    <span class="sidebar-icon">${getSidebarIcon(item.icon)}</span>
                    <span class="sidebar-label">${item.label}</span>
                </li>
            `).join('')}
            </ul>
            `;
        
        }
        function getSidebarIcon(iconName) {
    const icons = {
        'FileText': '📄',
        'Users': '👥',
        'User': '👤',
        'BarChart': '📊',
        'BarChart2': '📈',
        'LogOut': '🚪'
    };
    return icons[iconName] || '•';
}

        // Section Rendering
        function renderSection(section, data) {
            const container = document.getElementById('content-container');
            const renderMap = {
                'admin': {
                    'loans': () => renderLoans(data),
                    'users': () => renderUsers(data),
                    'reports': () => renderAdminReports(data)
                },
                'hr': {
                    'loans': () => renderHrLoans(data),
                    'employees': () => renderEmployees(data),
                    'reports': () => renderHrReports(data)
                }
            };

            container.innerHTML = renderMap[userRole][section]();
        }

         // HR-specific Render Functions
        function renderHrLoans(loans) {
            return `
                <div class="filters">
                    <select id="statusFilter" onchange="loadSection('loan-applications')">
                        <option value="">All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Approved</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                    <input type="date" id="fromDate" onchange="loadSection('loan-applications')">
                    <input type="date" id="toDate" onchange="loadSection('loan-applications')">
                </div>
                <h2>HR Loan Applications</h2>
                <div class="loan-list">
                    ${loans.data.map(loan => `
                        <div class="loan-item">
                            <div class="loan-header">
                                <h3>${loan.employee.full_name} - ${loan.loan_type.name}</h3>
                                <span class="status ${loan.status.toLowerCase()}">${loan.status}</span>
                            </div>
                            <div class="loan-details">
                                <p>Amount: $${loan.amount} | Term: ${loan.term_months} months</p>
                                <p>Applied: ${new Date(loan.application_date).toLocaleDateString()}</p>
                            </div>
                            ${loan.status === 'Pending' ? `
                                <div class="hr-actions">
                                    <button onclick="showReviewModal(${loan.loan_id})">Review</button>
                                </div>
                            ` : ''}
                        </div>
                    `).join('')}
                </div>
                ${renderPagination(loans)}
            `;
        }

        function renderHrReports(reports) {
            return `
                <h2>HR Department Reports</h2>
                <div class="reports-section">
                    <div class="report-item">
                        <h3>Loan Processing</h3>
                        <p>Total Applications: ${reports.total_applications}</p>
                        <p>Approved Applications: ${reports.approved_applications}</p>
                        <p>Pending Applications: ${reports.pending_applications}</p>
                        <p>Rejected Applications: ${reports.rejected_applications}</p>
                    </div>
                    <div class="report-item">
                        <h3>Department Breakdown</h3>
                        ${Object.entries(reports.department_breakdown).map(([dept, count]) => `
                            <p>${dept}: ${count} loans</p>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        function renderEmployees(employees) {
            if (!employees || !employees.data || !Array.isArray(employees.data)) {
        return `<div class="error-message">No employee data available</div>`;
    }
            return `
        <h2>Employees</h2>
        <div class="employees-list">
            ${employees.data.map(employee => `
                <div class="employee-item">
                    <h3>${employee.full_name || 'Unknown Employee'}</h3>
                    <p>Department: ${employee.department || 'Unknown'}</p>
                    <p>Pending Loans: ${employee.pending_loans || 0}</p>
                </div>
            `).join('')}
        </div>
        ${renderPagination(employees)}
    `;
}

        // API Client
        const apiClient = {
            get: async (url) => {
                
                return fetch(`${apiBase}${url}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
            },
            post: async (url, data) => {
                return fetch(`${apiBase}${url}`, {
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
                return fetch(`${apiBase}${url}`, {
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
        
        async function loadSection(section) {
            const params = new URLSearchParams();
            const sectionMap = {
        'loan-applications': 'loans',
        'loans': 'loans',
        'users': 'users',
        'employees': 'employees',
        'reports': 'reports'
    };

                const mappedSection = sectionMap[section] || section;
    
    
    // Optional null checks
    const statusFilter = document.getElementById('statusFilter');
    const fromDateInput = document.getElementById('fromDate');
    const toDateInput = document.getElementById('toDate');
    
    if (statusFilter && statusFilter.value) {
        params.append('status', statusFilter.value);
    }
    
    if (fromDateInput && fromDateInput.value) {
        params.append('date_from', fromDateInput.value);
    }
    
    if (toDateInput && toDateInput.value) {
        params.append('date_to', toDateInput.value);
    }
    
    try {
        toggleLoading(true);
        const endpoint = {
            'loans': '/loan-applications', 
            'users': '/users',
            'employees': '/employees',
            'reports': '/reports'
        }[mappedSection] || `/${mappedSection}`;

        fetch(`${apiBase}${endpoint}?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include'
        }).then(response => {
            if (!response.ok) throw new Error('Failed to load data');
            return response.json();
        })
        .then(data => {
             renderSection(mappedSection, data);
        })
        .catch(error => {
            showNotification(error.message, 'error');
            console.error('Loading error:', error);
        })
        .finally(() => {
            toggleLoading(false);
        });
    } catch (error) {
        showNotification(error.message, 'error');
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
        case 'employees':
            container.innerHTML = renderEmployees(data);
            break;
        case 'reports':
            container.innerHTML = renderReports(data);
            break;
        default:
            container.innerHTML = `<div>Unknown section: ${section}</div>`;
    }
}

        function renderLoans(loans) {
    if (!loans || !loans.data || !Array.isArray(loans.data)) {
        return `<div class="error-message">No loan data available</div>`;
    }
    
    return `
        <div class="filters">
            <select id="statusFilter" onchange="loadSection('loans')">
                <option value="">All Statuses</option>
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
            </select>
            <input type="date" id="fromDate" onchange="loadSection('loans')">
            <input type="date" id="toDate" onchange="loadSection('loans')">
        </div>
        <h2>Loan Applications</h2>
        <div class="loan-list">
            ${loans.data.map(loan => `
                <div class="loan-item">
                    <div class="loan-header">
                        <h3>${loan.employee && loan.employee.full_name ? loan.employee.full_name : 'Unknown Employee'} - 
                            ${loan.loan_type && loan.loan_type.name ? loan.loan_type.name : 'Unknown Loan Type'}</h3>
                        <span class="status ${loan.status ? loan.status.toLowerCase() : ''}">${loan.status || 'Unknown'}</span>
                    </div>
                    <div class="loan-details">
                        <p>Amount: $${loan.amount || 0} | Term: ${loan.term_months || 0} months</p>
                        <p>Applied: ${loan.application_date ? new Date(loan.application_date).toLocaleDateString() : 'Unknown'}</p>
                        ${loan.processed_date ? 
                            `<p>Processed: ${new Date(loan.processed_date).toLocaleDateString()} by 
                            ${loan.processed_by && loan.processed_by.full_name ? loan.processed_by.full_name : 'System'}</p>` : ''}
                    </div>
                    ${loan.status === 'Pending' ? `
                        <div class="hr-actions">
                            <button onclick="showReviewModal(${loan.loan_id})">Review</button>
                        </div>
                    ` : ''}
                    ${loan.review_notes ? `
                        <div class="review-notes">
                            <h4>Review Notes:</h4>
                            <p>${loan.review_notes}</p>
                        </div>
                    ` : ''}
                </div>
            `).join('')}
        </div>
        ${renderPagination(loans)}
    `;
}
function showReviewModal(loanId) {
    // Use a more flexible API endpoint
    const endpoints = [
        `/api/hr/loan-applications/${loanId}`,
        `/api/hr/loans/${loanId}`
    ];

    function tryNextEndpoint(index) {
        if (index >= endpoints.length) {
            showNotification('Failed to load loan details', 'error');
            return;
        }

        fetch(endpoints[index], {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(loan => {
            // Create modal HTML (your existing code)
            const modalHTML = `
                <div class="modal-overlay" id="review-modal">
                    <div class="modal-content">
                        <h2>Review Loan Application</h2>
                        <p>Employee: ${loan.employee ? loan.employee.full_name : 'Unknown'}</p>
                        <p>Loan Type: ${loan.loan_type ? loan.loan_type.name : 'Unknown'}</p>
                        <p>Amount: $${loan.amount || 0}</p>
                        <p>Term: ${loan.term_months || 0} months</p>
                        <p>Purpose: ${loan.purpose || 'Not specified'}</p>
                        
                        <div class="form-group">
                            <label for="review-notes">Review Notes:</label>
                            <textarea id="review-notes" rows="4"></textarea>
                        </div>
                        
                        <div class="modal-actions">
                            <button class="approve-btn" onclick="processLoan(${loan.loan_id}, 'Approved')">Approve</button>
                            <button class="reject-btn" onclick="processLoan(${loan.loan_id}, 'Rejected')">Reject</button>
                            <button class="cancel-btn" onclick="closeReviewModal()">Cancel</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        })
        .catch(error => {
            console.error('Error fetching loan details:', error);
            tryNextEndpoint(index + 1);
        });
    }

    tryNextEndpoint(0);
}

// Modify initialization to handle multiple section names
document.addEventListener('DOMContentLoaded', () => {
    renderSidebar();

    const sectionByRole = {
        'hr': 'loans',
        'admin': 'users',
    };
    
    // More flexible section loading
    const initialSection = sectionByRole[userRole.toLowerCase()] || 'loans';
    
    loadSection(initialSection);
    
    // Session verification
    setInterval(async () => {
        try {
            await apiClient.get('/user');
        } catch (error) {
            handleLogout();
        }
    }, 300000); // 5 minutes
});
function closeReviewModal() {
    const modal = document.getElementById('review-modal');
    if (modal) modal.remove();
}
function processLoan(loanId, status) {
    const notes = document.getElementById('review-notes').value;
    
    apiClient.put(`/loan-applications/${loanId}`, {
        status: status,
        review_notes: notes
    })
    .then(response => {
        if (!response.ok) throw new Error('Failed to update loan');
        return response.json();
    })
    .then(data => {
        showNotification(`Loan application ${status.toLowerCase()} successfully`);
        closeReviewModal();
        loadSection('loans');
    })
    .catch(error => {
        showNotification(error.message, 'error');
    });
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
                loadSection('loan-applications');
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
             renderSidebar();
            // Load default section
            loadSection('loan-applications');
            
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