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
            position: sticky;
            top: 0;
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

/* For reports  */
.hr-reports-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .reports-title {
        text-align: center;
        color: #007bff;
        margin-bottom: 30px;
    }

    .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .report-card {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 20px;
        text-align: center;
    }

    .report-card h3 {
        color: #333;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }

    .report-stats, .credit-stats {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-around;
    }

    .stat-item {
        display: flex;
        flex-direction: column;
        margin: 10px;
    }

    .stat-label {
        color: #666;
        font-size: 0.9em;
        margin-bottom: 5px;
    }

    .stat-value {
        font-weight: bold;
        font-size: 1.2em;
    }

    .stat-value.total { color: #007bff; }
    .stat-value.approved { color: #28a745; }
    .stat-value.pending { color: #ffc107; }
    .stat-value.rejected { color: #dc3545; }

    .department-list {
        max-height: 200px;
        overflow-y: auto;
    }

    .dept-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }

    .dept-name {
        font-weight: 500;
    }

    .dept-count {
        color: #007bff;
    }

    .reports-container {
    max-width: 100%;
    margin: 0 auto;
    padding: 0;
}

.reports-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.reports-title {
    color: #2c3e50;
    margin: 0;
}

.reports-actions {
    display: flex;
    gap: 10px;
}

.report-filters {
    background-color: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    display: block;
    margin-bottom: 5px;
    color: #7f8c8d;
    font-size: 14px;
}

.filter-group select, 
.filter-group input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.filter-buttons {
    display: flex;
    gap: 10px;
}

.detailed-title {
    margin-top: 30px;
    color: #2c3e50;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
}

.status-pill {
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    display: inline-block;
}

.status-approved {
    background-color: #e3fcef;
    color: #1f9d55;
}

.status-pending {
    background-color: #fff8e6;
    color: #cb8600;
}

.status-rejected {
    background-color: #fee2e2;
    color: #dc2626;
}

.summary-boxes {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.summary-box {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    padding: 20px;
    text-align: center;
}

.summary-box h3 {
    margin-top: 0;
    color: #2c3e50;
}

.summary-value {
    font-size: 28px;
    font-weight: bold;
    margin: 10px 0;
}

.summary-change {
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.change-up {
    color: #2ecc71;
}

.change-down {
    color: #e74c3c;
}

.btn-export, .btn-print {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
}

.btn-export {
    background-color: #2ecc71;
    color: white;
}

.btn-print {
    background-color: #9b59b6;
    color: white;
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
                { label: 'Reports', action: 'loadReports', icon: 'BarChart2' },
                { label: 'Logout', action: 'handleLogout', icon: 'LogOut' }
            ],
            'hr': [
                { label: 'Loan Applications', section: 'loans', icon: 'FileText' },
                { label: 'Employees', section: 'employees', icon: 'User' },
                { label: 'Reports', action: 'loadReports', icon: 'BarChart' },
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

        function loadReports() {
    toggleLoading(true);
    
    const url = userRole === 'hr' ? '/api/hr/reports' : '/api/admin/reports';
    
    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        // Render the reports directly in the content container
        const container = document.getElementById('content-container');
        
        if (userRole === 'hr') {
            container.innerHTML = renderHrReports(data);
        } else {
            container.innerHTML = renderAdminReports(data);
        }
        
        // Initialize any report-specific functionality
        initializeReportFunctions();
    })
    .catch(error => {
        showNotification('Failed to load reports', 'error');
        console.error('Error loading reports:', error);
    })
    .finally(() => {
        toggleLoading(false);
    });
}
function initializeReportFunctions() {
    // Set up date ranges
    initializeDateRange();
    
    // Load departments or branches based on role
    if (userRole === 'hr') {
        fetchDepartments();
        fetchDetailedLoanData();
    } else {
        fetchBranches();
        fetchDetailedBranchData();
    }
    
    // Set up event handlers
    document.getElementById('period').addEventListener('change', periodChanged);
}

function initializeDateRange() {
    const today = new Date();
    const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
    
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    
    if (startDateInput && endDateInput) {
        startDateInput.valueAsDate = startOfMonth;
        endDateInput.valueAsDate = today;
    }
}

function periodChanged() {
    const period = document.getElementById('period').value;
    const dateRangeElements = document.querySelectorAll('.date-range');
    
    if (period === 'custom') {
        dateRangeElements.forEach(el => el.style.display = 'block');
    } else {
        dateRangeElements.forEach(el => el.style.display = 'none');
    }
}

function resetFilters() {
    const periodSelect = document.getElementById('period');
    if (periodSelect) periodSelect.value = 'monthly';
    
    if (userRole === 'hr') {
        const deptSelect = document.getElementById('department');
        if (deptSelect) deptSelect.value = '';
    } else {
        const branchSelect = document.getElementById('branch');
        if (branchSelect) branchSelect.value = '';
    }
    
    initializeDateRange();
    periodChanged();
    refreshReports();
}

function refreshReports() {
    loadReports();
}

async function fetchDepartments() {
    try {
        const response = await fetch('/api/hr/departments', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include'
        });
        
        if (!response.ok) throw new Error('Failed to fetch departments');
        
        const data = await response.json();
        
        const departmentSelect = document.getElementById('department');
        if (!departmentSelect) return;
        
        departmentSelect.innerHTML = '<option value="">All Departments</option>';
        
        data.forEach(dept => {
            const option = document.createElement('option');
            option.value = dept;
            option.textContent = dept;
            departmentSelect.appendChild(option);
        });
    } catch (error) {
        showNotification('Could not load departments', 'error');
    }
}

async function fetchBranches() {
    try {
        const response = await fetch('/api/admin/branches', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include'
        });
        
        if (!response.ok) throw new Error('Failed to fetch branches');
        
        const data = await response.json();
        
        const branchSelect = document.getElementById('branch');
        if (!branchSelect) return;
        
        branchSelect.innerHTML = '<option value="">All Branches</option>';
        
        data.forEach(branch => {
            const option = document.createElement('option');
            option.value = branch.id;
            option.textContent = branch.name;
            branchSelect.appendChild(option);
        });
    } catch (error) {
        showNotification('Could not load branches', 'error');
    }
}

async function fetchDetailedLoanData() {
    try {
        const params = new URLSearchParams();
        const periodSelect = document.getElementById('period');
        
        if (periodSelect) {
            const period = periodSelect.value;
            params.append('period', period);
            
            if (period === 'custom') {
                const startDate = document.getElementById('start-date').value;
                const endDate = document.getElementById('end-date').value;
                params.append('start_date', startDate);
                params.append('end_date', endDate);
            }
            
            const department = document.getElementById('department');
            if (department && department.value) {
                params.append('department', department.value);
            }
        }
        
        const response = await fetch(`/api/hr/loan-applications?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include'
        });
        
        if (!response.ok) throw new Error('Failed to fetch detailed loan data');
        
        const loansData = await response.json();
        updateDetailedLoanTable(loansData.data);
    } catch (error) {
        console.error('Error fetching detailed loan data:', error);
    }
}

async function fetchDetailedBranchData() {
    try {
        const params = new URLSearchParams();
        const periodSelect = document.getElementById('period');
        
        if (periodSelect) {
            const period = periodSelect.value;
            params.append('period', period);
            
            if (period === 'custom') {
                const startDate = document.getElementById('start-date').value;
                const endDate = document.getElementById('end-date').value;
                params.append('start_date', startDate);
                params.append('end_date', endDate);
            }
            
            const branch = document.getElementById('branch');
            if (branch && branch.value) {
                params.append('branch_id', branch.value);
            }
        }
        
        const response = await fetch(`/api/admin/branch-statistics?${params.toString()}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include'
        });
        
        if (!response.ok) throw new Error('Failed to fetch detailed branch data');
        
        const branchData = await response.json();
        updateDetailedBranchTable(branchData);
    } catch (error) {
        console.error('Error fetching detailed branch data:', error);
    }
}

function updateDetailedLoanTable(loans) {
    const tableBody = document.getElementById('loan-details');
    if (!tableBody) return;
    
    if (!loans || !Array.isArray(loans) || loans.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No loan data found for the selected criteria</td></tr>';
        return;
    }
    
    let html = '';
    loans.forEach(loan => {
        const employeeName = loan.employee && loan.employee.full_name ? loan.employee.full_name : 'Unknown';
        const department = loan.employee && loan.employee.department ? loan.employee.department : 'Unknown';
        const loanType = loan.loan_type && loan.loan_type.name ? loan.loan_type.name : 'Unknown';
        const amount = loan.amount ? `$${parseFloat(loan.amount).toFixed(2)}` : '$0';
        const appliedDate = loan.application_date ? new Date(loan.application_date).toLocaleDateString() : 'Unknown';
        const processedBy = loan.processed_by && loan.processed_by.full_name ? loan.processed_by.full_name : 'N/A';
        
        let statusClass = '';
        switch(loan.status) {
            case 'Approved': statusClass = 'status-approved'; break;
            case 'Pending': statusClass = 'status-pending'; break;
            case 'Rejected': statusClass = 'status-rejected'; break;
        }
        
        html += `
            <tr data-loan-id="${loan.loan_id}" class="clickable" onclick="viewLoanDetails(${loan.loan_id})">
                <td>${employeeName}</td>
                <td>${department}</td>
                <td>${loanType}</td>
                <td>${amount}</td>
                <td>${appliedDate}</td>
                <td><span class="status-pill ${statusClass}">${loan.status || 'Unknown'}</span></td>
                <td>${processedBy}</td>
            </tr>
        `;
    });
    
    tableBody.innerHTML = html;
}

function updateDetailedBranchTable(branchData) {
    const tableBody = document.getElementById('branch-details');
    if (!tableBody) return;
    
    if (!branchData || !Array.isArray(branchData) || branchData.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No branch data found for the selected criteria</td></tr>';
        return;
    }
    
    let html = '';
    branchData.forEach(branch => {
        const branchName = branch.name || 'Unknown';
        const userCount = branch.user_count || 0;
        const totalLoans = branch.total_loans || 0;
        const approvedLoans = branch.approved_loans || 0;
        const rejectedLoans = branch.rejected_loans || 0;
        const amountDisbursed = formatCurrency(branch.amount_disbursed || 0);
        
        // Calculate performance score (0-100)
        const performance = calculatePerformance(branch);
        const performanceClass = getPerformanceClass(performance);
        
        html += `
            <tr data-branch-id="${branch.id}" class="clickable" onclick="viewBranchDetails(${branch.id})">
                <td>${branchName}</td>
                <td>${userCount}</td>
                <td>${totalLoans}</td>
                <td>${approvedLoans}</td>
                <td>${rejectedLoans}</td>
                <td>${amountDisbursed}</td>
                <td><span class="status-pill ${performanceClass}">${performance}/100</span></td>
            </tr>
        `;
    });
    
    tableBody.innerHTML = html;
}

function calculatePerformance(branch) {
    // Sample performance calculation
    if (!branch.total_loans) return 0;
    
    const approvalRate = branch.total_loans ? (branch.approved_loans / branch.total_loans) * 100 : 0;
    const processingSpeed = 100 - Math.min(100, (branch.avg_processing_days || 0) * 5);
    
    // Weight different factors
    return Math.round((approvalRate * 0.6) + (processingSpeed * 0.4));
}

function getPerformanceClass(score) {
    if (score >= 80) return 'status-approved';
    if (score >= 50) return 'status-pending';
    return 'status-rejected';
}

function formatCurrency(amount) {
    return '$' + formatNumber(parseFloat(amount).toFixed(2));
}

function exportReportCSV() {
    // Get the table data
    const table = document.querySelector('table');
    if (!table) return;
    
    const rows = table.querySelectorAll('tr');
    
    let csvContent = "data:text/csv;charset=utf-8,";
    
    // Add headers
    const headers = [];
    table.querySelectorAll('th').forEach(th => {
        headers.push(th.textContent);
    });
    csvContent += headers.join(',') + '\r\n';
    
    // Skip header row, add data rows
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const rowData = [];
        
        row.querySelectorAll('td').forEach(cell => {
            // Remove HTML and clean the content
            let cellText = cell.textContent.trim().replace(/"/g, '""');
            rowData.push(`"${cellText}"`);
        });
        
        csvContent += rowData.join(',') + '\r\n';
    }
    
    // Create download link
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', `report_${new Date().toISOString().slice(0,10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function filterByDepartment(department) {
    const departmentSelect = document.getElementById('department');
    if (departmentSelect) {
        departmentSelect.value = department;
        refreshReports();
    }
}

function filterByBranch(branchName) {
    const branches = document.getElementById('branch');
    if (!branches) return;
    
    for (let i = 0; i < branches.options.length; i++) {
        if (branches.options[i].text === branchName) {
            branches.value = branches.options[i].value;
            refreshReports();
            break;
        }
    }
}

function viewLoanDetails(loanId) {
    // Instead of navigating away, we can show a modal or load details inline
    showNotification(`Viewing details for loan #${loanId}`);
}

function viewBranchDetails(branchId) {
    // Instead of navigating away, we can show a modal or load details inline
    showNotification(`Viewing details for branch #${branchId}`);
}

function renderHrReports(data) {
    return `
        <div class="hr-reports-container">
            <div class="reports-header">
                <h2 class="reports-title">HR Loan Reports</h2>
                <div class="reports-actions">
                    <button class="btn-export" onclick="exportReportCSV()">Export CSV</button>
                    <button class="btn-print" onclick="window.print()">Print Report</button>
                </div>
            </div>
            
            <div class="report-filters">
                <div class="filter-group">
                    <label for="period">Report Period</label>
                    <select id="period" onchange="periodChanged()">
                        <option value="monthly">Current Month</option>
                        <option value="quarterly">Current Quarter</option>
                        <option value="yearly">Current Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                
                <div class="filter-group date-range" style="display: none;">
                    <label for="start-date">From</label>
                    <input type="date" id="start-date">
                </div>
                
                <div class="filter-group date-range" style="display: none;">
                    <label for="end-date">To</label>
                    <input type="date" id="end-date">
                </div>
                
                <div class="filter-group">
                    <label for="department">Department</label>
                    <select id="department">
                        <option value="">All Departments</option>
                    </select>
                </div>
                
                <div class="filter-buttons">
                    <button class="btn-primary" onclick="refreshReports()">Generate Report</button>
                    <button class="btn-outline" onclick="resetFilters()">Reset</button>
                </div>
            </div>
            
            <div id="error-container"></div>
            
            <div class="reports-grid">
                <div class="report-card loan-summary">
                    <h3>Loan Applications Summary</h3>
                    <div class="report-stats">
                        <div class="stat-item">
                            <div class="stat-value total" id="total-loans">${data.total_applications || 0}</div>
                            <div class="stat-label">Total</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value approved" id="approved-loans">${data.approved_applications || 0}</div>
                            <div class="stat-label">Approved</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value pending" id="pending-loans">${data.pending_applications || 0}</div>
                            <div class="stat-label">Pending</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value rejected" id="rejected-loans">${data.rejected_applications || 0}</div>
                            <div class="stat-label">Rejected</div>
                        </div>
                    </div>
                </div>
                
                <div class="report-card">
                    <h3>Department Loan Distribution</h3>
                    <div class="department-list" id="department-distribution">
                        ${renderDepartmentDistribution(data.department_breakdown)}
                    </div>
                </div>
                
                <div class="report-card">
                    <h3>Loan Metrics</h3>
                    <div class="report-stats">
                        <div class="stat-item">
                            <div class="stat-value" id="avg-amount">$${data.average_loan_amount ? parseFloat(data.average_loan_amount).toFixed(2) : '0'}</div>
                            <div class="stat-label">Avg. Loan Amount</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value" id="most-common-type">${data.most_common_loan_type && data.most_common_loan_type.name ? data.most_common_loan_type.name : 'N/A'}</div>
                            <div class="stat-label">Most Common Loan Type</div>
                        </div>
                    </div>
                </div>
                
                <div class="report-card">
                    <h3>Loan Processing Times</h3>
                    <div class="report-stats">
                        <div class="stat-item">
                            <div class="stat-value" id="avg-processing">${data.avg_processing_days ? data.avg_processing_days.toFixed(1) : '0'}</div>
                            <div class="stat-label">Avg. Days to Process</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <h2 class="detailed-title">Detailed Loan Report</h2>
            <div id="detailed-report">
                <table>
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Loan Type</th>
                            <th>Amount</th>
                            <th>Applied Date</th>
                            <th>Status</th>
                            <th>Processed By</th>
                        </tr>
                    </thead>
                    <tbody id="loan-details">
                        <tr>
                            <td colspan="7" style="text-align: center;">Loading detailed loan data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function renderAdminReports(data) {
    return `
        <div class="reports-container">
            <div class="reports-header">
                <h2 class="reports-title">System-Wide Reports</h2>
                <div class="reports-actions">
                    <button class="btn-export" onclick="exportReportCSV()">Export CSV</button>
                    <button class="btn-print" onclick="window.print()">Print Report</button>
                </div>
            </div>
            
            <div class="report-filters">
                <div class="filter-group">
                    <label for="period">Report Period</label>
                    <select id="period" onchange="periodChanged()">
                        <option value="monthly">Current Month</option>
                        <option value="quarterly">Current Quarter</option>
                        <option value="yearly">Current Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                
                <div class="filter-group date-range" style="display: none;">
                    <label for="start-date">From</label>
                    <input type="date" id="start-date">
                </div>
                
                <div class="filter-group date-range" style="display: none;">
                    <label for="end-date">To</label>
                    <input type="date" id="end-date">
                </div>
                
                <div class="filter-group">
                    <label for="branch">Branch</label>
                    <select id="branch">
                        <option value="">All Branches</option>
                    </select>
                </div>
                
                <div class="filter-buttons">
                    <button class="btn-primary" onclick="refreshReports()">Generate Report</button>
                    <button class="btn-outline" onclick="resetFilters()">Reset</button>
                </div>
            </div>
            
            <div id="error-container"></div>
            
            <div class="summary-boxes">
                <div class="summary-box">
                    <h3>Total System Users</h3>
                    <div class="summary-value" id="total-users">${data.total_users || 0}</div>
                </div>
                
                <div class="summary-box">
                    <h3>Active Branches</h3>
                    <div class="summary-value" id="active-branches">${data.active_branches || 0}</div>
                </div>
                
                <div class="summary-box">
                    <h3>Total Employees</h3>
                    <div class="summary-value" id="total-employees">${data.total_employees || 0}</div>
                </div>
            </div>
            
            <div class="reports-grid">
                <div class="report-card">
                    <h3>Branch Performance</h3>
                    <div class="department-list" id="branch-distribution">
                        ${renderBranchDistribution(data.branch_breakdown)}
                    </div>
                </div>
                
                <div class="report-card">
                    <h3>User Activity</h3>
                    <div class="department-list" id="user-activity">
                        ${renderUserActivity(data.user_activity)}
                    </div>
                </div>
                
                <div class="report-card">
                    <h3>System Performance</h3>
                    <div class="report-stats">
                        <div class="stat-item">
                            <div class="stat-value" id="avg-processing">${data.avg_processing_days ? data.avg_processing_days.toFixed(1) : '0'}</div>
                            <div class="stat-label">Avg. System Processing Time</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <h2 class="detailed-title">Detailed System Report</h2>
            <div id="detailed-report">
                <table>
                    <thead>
                        <tr>
                            <th>Branch</th>
                            <th>Users</th>
                            <th>System Load</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody id="branch-details">
                        <tr>
                            <td colspan="4" style="text-align: center;">Loading detailed branch data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function renderDepartmentDistribution(departmentData) {
    if (!departmentData || Object.keys(departmentData).length === 0) {
        return '<p>No data available</p>';
    }
    
    let html = '';
    Object.entries(departmentData).forEach(([dept, count]) => {
        html += `
            <div class="dept-item clickable" onclick="filterByDepartment('${dept}')">
                <span class="dept-name">${dept}</span>
                <span class="dept-count">${count}</span>
            </div>
        `;
    });
    
    return html;
}

function renderBranchDistribution(branchData) {
    if (!branchData || Object.keys(branchData).length === 0) {
        return '<p>No data available</p>';
    }
    
    let html = '';
    Object.entries(branchData).forEach(([branchName, count]) => {
        html += `
            <div class="dept-item clickable" onclick="filterByBranch('${branchName}')">
                <span class="dept-name">${branchName}</span>
                <span class="dept-count">${count}</span>
            </div>
        `;
    });
    
    return html;
}

function renderUserActivity(userData) {
    if (!userData || Object.keys(userData).length === 0) {
        return '<p>No data available</p>';
    }
    
    let html = '';
    Object.entries(userData).forEach(([username, count]) => {
        html += `
            <div class="dept-item">
                <span class="dept-name">${username}</span>
                <span class="dept-count">${count} actions</span>
            </div>
        `;
    });
    
    return html;
}

function renderChangeIndicator(changePercent) {
    if (changePercent === undefined || changePercent === null) {
        return 'No change data';
    }
    
    const isPositive = changePercent >= 0;
    const changeClass = isPositive ? 'change-up' : 'change-down';
    const changeIcon = isPositive ? '↑' : '↓';
    
    return `
        <span class="${changeClass}">
            ${changeIcon} ${Math.abs(changePercent).toFixed(1)}% from previous period
        </span>
    `;
}

function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
        function renderReports(reports) {
    // If we're on a specialized reports page, don't render anything
    if (window.location.pathname.endsWith('/reports')) {
        return '';
    }
    
    
    // Default report rendering for the dashboard
    return `
        <div class="hr-reports-container">
            <h2 class="reports-title">Summary Reports</h2>
            
            <div class="reports-grid">
                <div class="report-card loan-summary">
                    <h3>Loan Overview</h3>
                    <div class="report-stats">
                        <div class="stat-item">
                            <span class="stat-label">Total Loans</span>
                            <span class="stat-value total">${reports.total_applications || 0}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Approved</span>
                            <span class="stat-value approved">${reports.approved_applications || 0}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Pending</span>
                            <span class="stat-value pending">${reports.pending_applications || 0}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Rejected</span>
                            <span class="stat-value rejected">${reports.rejected_applications || 0}</span>
                        </div>
                    </div>
                </div>

                <div class="report-card">
                    <h3>Department Loan Distribution</h3>
                    <p>Click <a href="${userRole === 'hr' ? '/hr/reports' : '/admin/reports'}" class="reports-link">here</a> to view detailed reports</p>
                </div>
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