<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Loan Management System - {{ ucfirst(Auth::user()->role) }} Dashboard</title>
    @vite(['resources/css/admin-hr.css'])
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div id="collapse-toggle" onclick="toggleSidebar()"></div>
        <ul>
            <li onclick="loadSection('loans')"><span>Loans</span></li>
            <li onclick="loadSection('employees')"><span>Employees</span></li>
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
        // Store CSRF token for API requests
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var userRole = '{{ Auth::user()->role }}';
        var apiBase = '/api/hr';
        
        // Toggle loading indicator
        function toggleLoading(show) {
            var loadingIndicator = document.getElementById('loading-indicator');
            if (loadingIndicator) {
                loadingIndicator.style.display = show ? 'flex' : 'none';
            }
        }
        
        // Show notification
        function showNotification(message, type) {
            // Remove existing notifications
            var existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(function(notification) {
                notification.remove();
            });
            
            var notification = document.createElement('div');
            notification.className = 'notification ' + (type || 'success');
            notification.innerHTML = message;
            
            document.body.appendChild(notification);
            
            // Auto hide after 3 seconds
            setTimeout(function() {
                notification.style.opacity = '0';
                setTimeout(function() {
                    notification.remove();
                }, 500);
            }, 3000);
        }
        
        // Load section content
        function loadSection(section) {
            var params = new URLSearchParams();
            
            // Add filters if they exist
            var statusFilter = document.getElementById('statusFilter');
            var fromDateInput = document.getElementById('fromDate');
            var toDateInput = document.getElementById('toDate');
            
            if (statusFilter && statusFilter.value) {
                params.append('status', statusFilter.value);
            }
    
            if (fromDateInput && fromDateInput.value) {
                params.append('date_from', fromDateInput.value);
            }
            
            if (toDateInput && toDateInput.value) {
                params.append('date_to', toDateInput.value);
            }

            toggleLoading(true);
            
            var endpoint = '';
            switch(section) {
                case 'loans':
                    endpoint = '/loans';
                    break;
                case 'employees':
                    endpoint = '/employees';
                    break;
                case 'reports':
                    endpoint = '/reports';
                    break;
                default:
                    endpoint = '/' + section;
            }
            
            fetch(apiBase + endpoint + '?' + params.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'include'
            })
            .then(function(response) {
                if (!response.ok) throw new Error('Failed to load data');
                return response.json();
            })
            .then(function(data) {
                // Update UI based on section
                renderSection(section, data);
            })
            .catch(function(error) {
                showNotification(error.message, 'error');
                console.error('Loading error:', error);
            })
            .finally(function() {
                toggleLoading(false);
            });
        }
        
        // Render section content
        function renderSection(section, data) {
            var container = document.getElementById('content-container');
            
            switch(section) {
                case 'loans':
                    container.innerHTML = renderLoans(data);
                    break;
                case 'employees':
                    container.innerHTML = renderEmployees(data);
                    initEmployeeEventListeners();
                    break;
                case 'reports':
                    container.innerHTML = renderReports(data);
                    break;
                default:
                    container.innerHTML = '<div>Unknown section: ' + section + '</div>';
            }
        }
        
        // Render loans section
        function renderLoans(loans) {
    if (!loans || !loans.data || !Array.isArray(loans.data)) {
        return '<div class="error-message">No loan data available</div>';
    }
    
    var html = '<div class="section-header">'
        + '<h2>Loan Applications</h2>'
        + '</div>'
        + '<div class="filters">'
        + '<div class="filter-group">'
        + '<label for="statusFilter">Status:</label>'
        + '<select id="statusFilter" onchange="loadSection(\'loans\')">'
        + '<option value="">All Statuses</option>'
        + '<option value="Pending">Pending</option>'
        + '<option value="Approved">Approved</option>'
        + '<option value="Rejected">Rejected</option>'
        + '</select>'
        + '</div>'
        + '<div class="filter-group">'
        + '<label for="fromDate">From Date:</label>'
        + '<input type="date" id="fromDate" onchange="loadSection(\'loans\')">'
        + '</div>'
        + '<div class="filter-group">'
        + '<label for="toDate">To Date:</label>'
        + '<input type="date" id="toDate" onchange="loadSection(\'loans\')">'
        + '</div>'
        + '</div>'
        + '<div class="loan-list">';
    
    loans.data.forEach(function(loan) {
        var employeeName = loan.employee && loan.employee.full_name ? loan.employee.full_name : 'Unknown';
        var loanTypeName = loan.loan_type && loan.loan_type.name ? loan.loan_type.name : 'Unknown';
        var loanStatus = loan.status ? loan.status.toLowerCase() : '';
        var loanAmount = loan.amount || 0;
        var loanTermMonths = loan.term_months || 0;
        var applicationDate = loan.application_date ? new Date(loan.application_date).toLocaleDateString() : 'Unknown';
        var processedDate = loan.processed_date ? new Date(loan.processed_date).toLocaleDateString() : '';

        var processedByName = 'System';
        if (loan.processed_by) {
            if (loan.processed_by_user && loan.processed_by_user.username) {
                processedByName = loan.processed_by_user.username;
            } else if (typeof loan.processed_by === 'string') {
                processedByName = loan.processed_by;
                }
        }
        
        html += '<div class="loan-item">'
            + '<div class="loan-header">'
            + '<h3>' + employeeName + ' - ' + loanTypeName + '</h3>'
            + '<span class="status ' + loanStatus + '">' + (loan.status || 'Unknown') + '</span>'
            + '</div>'
            + '<div class="loan-details">'
            + '<p>Amount: $' + loanAmount + ' | Term: ' + loanTermMonths + ' months</p>'
            + '<p>Applied: ' + applicationDate + '</p>';
        
        if (processedDate) {
            html += '<p>Processed: ' + processedDate + ' by ' + processedByName + '</p>';
        }
        
        html += '</div>';
        
        html += '<div class="hr-actions">';
        if (loan.status === 'Pending') {
            html += '<button class="action-btn" onclick="showReviewModal(' + loan.loan_id + ')">Review</button>';
        } else {
            html += '<button class="action-btn" onclick="viewLoanDetails(' + loan.loan_id + ')">View Details</button>';
        }
        html += '<button class="action-btn" onclick="downloadLoanPDF(' + loan.loan_id + ')">Download PDF</button>';
        html += '</div>';
        
        if (loan.review_notes) {
            html += '<div class="review-notes">'
                + '<h4>Review Notes:</h4>'
                + '<p>' + loan.review_notes + '</p>'
                + '</div>';
        }
        
        html += '</div>';
    });
    
    html += '</div>';
    html += renderPagination(loans);
    
    return html;
}


        
        // Render employees section
        function renderEmployees(employees) {
            var html = '<div class="employees-container">'
                + '<div class="section-header">'
                + '<h2>Employee Management</h2>'
                + '</div>'
                + '<div class="filters">'
                + '<div class="filter-group">'
                + '<label for="department-filter">Department:</label>'
                + '<input type="text" id="department-filter" placeholder="Filter by department">'
                + '</div>'
                + '<div class="filter-group">'
                + '<label for="emp-search">Search:</label>'
                + '<input type="text" id="emp-search" placeholder="Search employees...">'
                + '</div>'
                + '<div class="filter-group">'
                + '<button class="action-btn primary" onclick="applyEmployeeFilters()">Apply Filters</button>'
                + '<button class="action-btn secondary" onclick="clearEmployeeFilters()">Clear</button>'
                + '</div>'
                + '</div>'
                + '<div class="employees-list">';
            
            if (employees.data && employees.data.length) {
                employees.data.forEach(function(employee) {
                    html += '<div class="employee-card" data-employee-id="' + employee.employee_id + '">'
                        + '<div class="employee-info">'
                        + '<h3>' + employee.full_name + '</h3>'
                        + '<p>Employee ID: ' + employee.employee_id + '</p>'
                        + '<p>Department: ' + employee.department + '</p>'
                        + '<p>Position: ' + employee.position + '</p>'
                        + '<p>Branch: ' + (employee.branch ? employee.branch.branch_name : 'Unknown') + '</p>'
                        + '</div>'
                        + '<div class="employee-actions">'
                        + '<button class="action-btn" onclick="viewEmployeeDetails(' + employee.employee_id + ')">View Details</button>'
                        + '</div>'
                        + '</div>';
                });
            } else {
                html += '<p class="no-data">No employees found</p>';
            }
            
            html += '</div>';
            html += renderPagination(employees);
            html += '</div>';
            
            return html;
        }
        
        // Initialize employee event listeners
        function initEmployeeEventListeners() {
            var searchInput = document.getElementById('emp-search');
            var departmentFilter = document.getElementById('department-filter');
            
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        applyEmployeeFilters();
                    }
                });
            }
            
            if (departmentFilter) {
                departmentFilter.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        applyEmployeeFilters();
                    }
                });
            }
        }
        
        // Apply employee filters
        function applyEmployeeFilters() {
            var departmentFilter = document.getElementById('department-filter');
            var searchTerm = document.getElementById('emp-search');
            
            var params = new URLSearchParams();
            if (departmentFilter && departmentFilter.value) {
                params.append('department', departmentFilter.value);
            }
            
            if (searchTerm && searchTerm.value) {
                params.append('search', searchTerm.value);
            }
            
            fetch(apiBase + '/employees?' + params.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'include'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                var container = document.getElementById('content-container');
                container.innerHTML = renderEmployees(data);
                initEmployeeEventListeners();
            })
            .catch(function(error) {
                showNotification('Error loading employees: ' + error.message, 'error');
            });
        }
        
        // Clear employee filters
        function clearEmployeeFilters() {
            var departmentFilter = document.getElementById('department-filter');
            var searchTerm = document.getElementById('emp-search');
            
            if (departmentFilter) departmentFilter.value = '';
            if (searchTerm) searchTerm.value = '';
            
            loadSection('employees');
        }
        
        // View employee details
        function viewEmployeeDetails(employeeId) {
            fetch(apiBase + '/employees/' + employeeId, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'include'
            })
            .then(function(response) {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('Employee not found or server error: ' + response.status);
                    }
                return response.json();
            })
            .then(function(employee) {
                 console.log('Employee data:', employee);
                var modalHTML = '<div class="modal-overlay" id="employee-details-modal">'
                    + '<div class="modal-content wide-modal">'
                    + '<h2>Employee Details</h2>'
                    + '<div class="detail-sections">'
                    
                    // Personal Information
                    + '<div class="detail-section">'
                    + '<h3>Personal Information</h3>'
                    + '<div class="detail-row"><div class="detail-label">Employee ID:</div><div class="detail-value">' + employee.employee_id + '</div></div>'
                    + '<div class="detail-row"><div class="detail-label">Name:</div><div class="detail-value">' + (employee.title || '') + ' ' + employee.full_name + '</div></div>'
                    + '<div class="detail-row"><div class="detail-label">National ID:</div><div class="detail-value">' + employee.national_id + '</div></div>'
                    + '<div class="detail-row"><div class="detail-label">Date of Birth:</div><div class="detail-value">' + employee.date_of_birth + '</div></div>'
                    + '<div class="detail-row"><div class="detail-label">Gender:</div><div class="detail-value">' + employee.gender + '</div></div>'
                    + '<div class="detail-row"><div class="detail-label">Marital Status:</div><div class="detail-value">' + employee.marital_status + '</div></div>'
                    + '<div class="detail-row"><div class="detail-label">Dependents:</div><div class="detail-value">' + employee.dependents + '</div></div>'
                    + '</div>'
                    
                    // Contact Information
                    + '<div class="detail-section">'
                    + '<h3>Contact Information</h3>'
                    + '<div class="detail-row"><div class="detail-label">Physical Address:</div><div class="detail-value">' + employee.physical_address + '</div></div>'
                    + '<div class="detail-row"><div class="detail-label">Accommodation:</div><div class="detail-value">' + (employee.accommodation_type || 'Not specified') + '</div></div>'
                    + '<div class="detail-row"><div class="detail-label">Postal Address:</div><div class="detail-value">' + (employee.postal_address || 'Not specified') + '</div></div>'
                    + '<div class="detail-row"><div class="detail-label">Cell Phone:</div><div class="detail-value">' + employee.cell_phone + '</div></div>'
                    + '<div class="detail-row"><div class="detail-label">Email:</div><div class="detail-value">' + employee.email + '</div></div>'
                    + '</div>'
                    
                    // Employment Information
                    + '<div class="detail-section">'
                    + '<h3>Employment Information</h3>'
                    + '<div class="detail-row"><div class="detail-label">Branch:</div><div class="detail-value">' + (employee.branch ? employee.branch.branch_name : 'Unknown') + '</div></div>'
                    + '<div class="detail-row"><div class="detail-label">Department:</div><div class="detail-value">' + employee.department + '</div></div>'
                    + '<div class="detail-row"><div class="detail-label">Position:</div><div class="detail-value">' + employee.position + '</div></div>'
                    + '<div class="detail-row"><div class="detail-label">Hire Date:</div><div class="detail-value">' + employee.hire_date + '</div></div>'
                    + '<div class="detail-row"><div class="detail-label">Gross Salary:</div><div class="detail-value">$' + parseFloat(employee.salary_gross).toFixed(2) + '</div></div>'
                    + '<div class="detail-row"><div class="detail-label">Net Salary:</div><div class="detail-value">$' + parseFloat(employee.salary_net).toFixed(2) + '</div></div>'
                    + '</div>'
                    
                    // Loan Information
                    + '<div class="detail-section">'
                    + '<h3>Loan History</h3>'
                    + '<div class="loan-history">';
                
                if (employee.loan_applications && employee.loan_applications.length > 0) {
                    modalHTML += '<table class="loan-history-table">'
                        + '<thead><tr><th>Date</th><th>Type</th><th>Amount</th><th>Status</th></tr></thead>'
                        + '<tbody>';
                    
                    employee.loan_applications.forEach(function(loan) {
                        modalHTML += '<tr>'
                            + '<td>' + new Date(loan.application_date).toLocaleDateString() + '</td>'
                            + '<td>' + (loan.loan_type ? loan.loan_type.name : 'Unknown') + '</td>'
                            + '<td>$' + parseFloat(loan.amount).toFixed(2) + '</td>'
                            + '<td><span class="status-pill status-' + loan.status.toLowerCase() + '">' + loan.status + '</span></td>'
                            + '</tr>';
                    });
                    
                    modalHTML += '</tbody></table>';
                } else {
                    modalHTML += '<p>No loan history found.</p>';
                }
                
                modalHTML += '</div></div></div>'
                    + '<div class="modal-actions">'
                    + '<button class="cancel-btn" onclick="closeModal(\'employee-details-modal\')">Close</button>'
                    + '</div>'
                    + '</div>'
                    + '</div>';
                
                document.body.insertAdjacentHTML('beforeend', modalHTML);
            })
            .catch(function(error) {
                 console.error('Error details:', error);
                showNotification('Error loading employee details: ' + error.message, 'error');
            });
        }
        
        // Close modal
        function closeModal(modalId) {
            var modal = document.getElementById(modalId);
            if (modal) modal.remove();
        }
        function viewLoanDetails(loanId) {
    fetch(apiBase + '/loans/' + loanId, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include'
    })
    .then(function(response) {
        if (!response.ok) {
            throw new Error('Failed to load loan details');
        }
        return response.json();
    })
    .then(function(loan) {
        var processedByName = 'System';
        if (loan.processed_by) {
            if (typeof loan.processed_by === 'object' && loan.processed_by.employee && loan.processed_by.employee.full_name) {
                processedByName = loan.processed_by.employee.full_name;
            } else if (typeof loan.processed_by === 'string') {
                processedByName = loan.processed_by;
            }
        }
        
        var modalHTML = '<div class="modal-overlay" id="loan-details-modal">'
            + '<div class="modal-content">'
            + '<h2>Loan Application Details</h2>'
            + '<p>Employee: ' + (loan.employee ? loan.employee.full_name : 'Unknown') + '</p>'
            + '<p>Loan Type: ' + (loan.loan_type ? loan.loan_type.name : 'Unknown') + '</p>'
            + '<p>Amount: $' + (loan.amount || 0) + '</p>'
            + '<p>Term: ' + (loan.term_months || 0) + ' months</p>'
            + '<p>Purpose: ' + (loan.purpose || 'Not specified') + '</p>'
            + '<p>Status: <span class="status-pill status-' + loan.status.toLowerCase() + '">' + loan.status + '</span></p>'
            + '<p>Applied: ' + (loan.application_date ? new Date(loan.application_date).toLocaleDateString() : 'Unknown') + '</p>';
            
        if (loan.processed_date) {
            modalHTML += '<p>Processed: ' + new Date(loan.processed_date).toLocaleDateString() + ' by ' + processedByName + '</p>';
        }
        
        if (loan.review_notes) {
            modalHTML += '<div class="review-notes">'
                + '<h4>Review Notes:</h4>'
                + '<p>' + loan.review_notes + '</p>'
                + '</div>';
        }
        
        modalHTML += '<div class="modal-actions">';
        
        // Allow loan status to be changed even after it's been processed
        if (loan.status !== 'Pending') {
            modalHTML += '<button class="action-btn primary" onclick="editLoan(' + loan.loan_id + ')">Edit Status</button>';
        }
        
        modalHTML += '<button class="action-btn" onclick="downloadLoanPDF(' + loan.loan_id + ')">Download PDF</button>'
            + '<button class="cancel-btn" onclick="closeModal(\'loan-details-modal\')">Close</button>'
            + '</div>'
            + '</div>'
            + '</div>';
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    })
    .catch(function(error) {
        showNotification('Error loading loan details: ' + error.message, 'error');
    });
}



        
// Edit loan status function
function editLoan(loanId) {
    closeModal('loan-details-modal');
    showReviewModal(loanId);
}
function downloadLoanPDF(loanId) {
    showNotification('PDF download functionality will be implemented soon.', 'info');
    // This is just a placeholder - you'll implement the actual PDF download functionality
}

        
        // Show loan review modal
        function showReviewModal(loanId) {
            fetch(apiBase + '/loans/' + loanId, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'include'
            })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Failed to load loan details');
                }
                return response.json();
            })
            .then(function(loan) {
                var modalHTML = '<div class="modal-overlay" id="review-modal">'
                    + '<div class="modal-content">'
                    + '<h2>Review Loan Application</h2>'
                    + '<p>Employee: ' + (loan.employee ? loan.employee.full_name : 'Unknown') + '</p>'
                    + '<p>Loan Type: ' + (loan.loan_type ? loan.loan_type.name : 'Unknown') + '</p>'
                    + '<p>Amount: $' + (loan.amount || 0) + '</p>'
                    + '<p>Term: ' + (loan.term_months || 0) + ' months</p>'
                    + '<p>Purpose: ' + (loan.purpose || 'Not specified') + '</p>'
                    + '<div class="form-group">'
                    + '<label for="review-notes">Review Notes:</label>'
                    + '<textarea id="review-notes" rows="4"></textarea>'
                    + '</div>'
                    + '<div class="modal-actions">'
                    + '<button class="approve-btn" onclick="processLoan(' + loan.loan_id + ', \'Approved\')">Approve</button>'
                    + '<button class="reject-btn" onclick="processLoan(' + loan.loan_id + ', \'Rejected\')">Reject</button>'
                    + '<button class="cancel-btn" onclick="closeReviewModal()">Cancel</button>'
                    + '</div>'
                    + '</div>'
                    + '</div>';
                
                document.body.insertAdjacentHTML('beforeend', modalHTML);
            })
            .catch(function(error) {
                showNotification('Error loading loan details: ' + error.message, 'error');
            });
        }
        
        // Close review modal
        function closeReviewModal() {
            var modal = document.getElementById('review-modal');
            if (modal) modal.remove();
        }
        
        // Process loan application
        function processLoan(loanId, status) {
            var notes = document.getElementById('review-notes').value;
            
            fetch(apiBase + '/loan-applications/' + loanId, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    status: status,
                    review_notes: notes
                }),
                credentials: 'include'
            })
            .then(function(response) {
                if (!response.ok) throw new Error('Failed to update loan');
                return response.json();
            })
            .then(function(data) {
                showNotification('Loan application ' + status.toLowerCase() + ' successfully');
                closeReviewModal();
                loadSection('loans');
            })
            .catch(function(error) {
                showNotification(error.message, 'error');
            });
        }
        
        // Render reports section
        function renderReports(data) {
            return '<div class="hr-reports-container">'
                + '<div class="reports-header">'
                + '<h2 class="reports-title">HR Loan Reports</h2>'
                + '<div class="reports-actions">'
                + '<button class="btn-export" onclick="exportReportCSV()">Export CSV</button>'
                + '<button class="btn-print" onclick="window.print()">Print Report</button>'
                + '</div>'
                + '</div>'
                + '<div class="report-filters">'
                + '<div class="filter-group">'
                + '<label for="period">Report Period</label>'
                + '<select id="period" onchange="periodChanged()">'
                + '<option value="monthly">Current Month</option>'
                + '<option value="quarterly">Current Quarter</option>'
                + '<option value="yearly">Current Year</option>'
                + '<option value="custom">Custom Range</option>'
                + '</select>'
                + '</div>'
                + '<div class="filter-group date-range" style="display: none;">'
                + '<label for="start-date">From</label>'
                + '<input type="date" id="start-date">'
                + '</div>'
                + '<div class="filter-group date-range" style="display: none;">'
                + '<label for="end-date">To</label>'
                + '<input type="date" id="end-date">'
                + '</div>'
                + '<div class="filter-group">'
                + '<label for="department">Department</label>'
                + '<select id="department">'
                + '<option value="">All Departments</option>'
                + '</select>'
                + '</div>'
                + '<div class="filter-buttons">'
                + '<button class="action-btn primary" onclick="refreshReports()">Generate Report</button>'
                + '<button class="action-btn secondary" onclick="resetFilters()">Reset</button>'
                + '</div>'
                + '</div>'
                + '<div class="reports-grid">'
                + '<div class="report-card loan-summary">'
                + '<h3>Loan Applications Summary</h3>'
                + '<div class="report-stats">'
                + '<div class="stat-item">'
                + '<div class="stat-value total" id="total-loans">' + (data.total_applications || 0) + '</div>'
                + '<div class="stat-label">Total</div>'
                + '</div>'
                + '<div class="stat-item">'
                + '<div class="stat-value approved" id="approved-loans">' + (data.approved_applications || 0) + '</div>'
                + '<div class="stat-label">Approved</div>'
                + '</div>'
                + '<div class="stat-item">'
                + '<div class="stat-value pending" id="pending-loans">' + (data.pending_applications || 0) + '</div>'
                + '<div class="stat-label">Pending</div>'
                + '</div>'
                + '<div class="stat-item">'
                + '<div class="stat-value rejected" id="rejected-loans">' + (data.rejected_applications || 0) + '</div>'
                + '<div class="stat-label">Rejected</div>'
                + '</div>'
                + '</div>'
                + '</div>'
                + '<div class="report-card">'
                + '<h3>Department Loan Distribution</h3>'
                + '<div class="department-list" id="department-distribution">'
                + renderDepartmentDistribution(data.department_breakdown)
                + '</div>'
                + '</div>'
                + '<div class="report-card">'
                + '<h3>Loan Metrics</h3>'
                + '<div class="report-stats">'
                + '<div class="stat-item">'
                + '<div class="stat-value" id="avg-amount">$' + (data.average_loan_amount ? parseFloat(data.average_loan_amount).toFixed(2) : '0') + '</div>'
                + '<div class="stat-label">Avg. Loan Amount</div>'
                + '</div>'
                + '<div class="stat-item">'
                + '<div class="stat-value" id="avg-processing">' + (data.avg_processing_days ? data.avg_processing_days.toFixed(1) : '0') + '</div>'
                + '<div class="stat-label">Avg. Days to Process</div>'
                + '</div>'
                + '</div>'
                + '</div>'
                + '</div>'
                + '</div>';
        }
        
        // Render department distribution
        function renderDepartmentDistribution(departmentData) {
            if (!departmentData || Object.keys(departmentData).length === 0) {
                return '<p>No data available</p>';
            }
            
            var html = '';
            Object.keys(departmentData).forEach(function(dept) {
                html += '<div class="dept-item">'
                    + '<span class="dept-name">' + dept + '</span>'
                    + '<span class="dept-count">' + departmentData[dept] + '</span>'
                    + '</div>';
            });
            
            return html;
        }
        
        // Period changed event
        function periodChanged() {
            var period = document.getElementById('period').value;
            var dateRangeElements = document.querySelectorAll('.date-range');
            
            dateRangeElements.forEach(function(el) {
                el.style.display = period === 'custom' ? 'block' : 'none';
            });
        }
        
        // Reset filters
        function resetFilters() {
            var periodSelect = document.getElementById('period');
            if (periodSelect) periodSelect.value = 'monthly';
            
            var deptSelect = document.getElementById('department');
            if (deptSelect) deptSelect.value = '';
            
            // Initialize date range
            var today = new Date();
            var startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            
            var startDateInput = document.getElementById('start-date');
            var endDateInput = document.getElementById('end-date');
            
            if (startDateInput && endDateInput) {
                startDateInput.valueAsDate = startOfMonth;
                endDateInput.valueAsDate = today;
            }
            
            periodChanged();
            loadSection('reports');
        }
        
        // Refresh reports
        function refreshReports() {
            var period = document.getElementById('period').value;
            var department = document.getElementById('department').value;
            var params = new URLSearchParams();
            
            params.append('period', period);
            
            if (period === 'custom') {
                params.append('start_date', document.getElementById('start-date').value);
                params.append('end_date', document.getElementById('end-date').value);
            }
            
            if (department) {
                params.append('department', department);
            }
            
            fetch(apiBase + '/reports?' + params.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'include'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                renderSection('reports', data);
            })
            .catch(function(error) {
                showNotification('Error refreshing reports: ' + error.message, 'error');
            });
        }
        
        // Export report to CSV
        function exportReportCSV() {
            // Get table data
            var departments = document.querySelectorAll('.dept-item');
            if (departments.length === 0) {
                showNotification('No data to export', 'error');
                return;
            }
            
            var csvContent = "data:text/csv;charset=utf-8,Department,Loan Count\r\n";
            
            departments.forEach(function(dept) {
                var deptName = dept.querySelector('.dept-name').textContent;
                var deptCount = dept.querySelector('.dept-count').textContent;
                csvContent += deptName + "," + deptCount + "\r\n";
            });
            
            var encodedUri = encodeURI(csvContent);
            var link = document.createElement('a');
            link.setAttribute('href', encodedUri);
            link.setAttribute('download', 'department_report_' + new Date().toISOString().slice(0, 10) + '.csv');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // Render pagination
        function renderPagination(data) {
            if (!data.links || data.links.length <= 3) return '';
            
            var html = '<div class="pagination">';
            
            data.links.forEach(function(link) {
                var active = link.active ? 'active' : '';
                var disabled = link.url ? '' : 'disabled';
                var onclick = link.url ? 'loadPage(\'' + link.url + '\')' : 'return false';
                
                html += '<a href="#" class="' + active + ' ' + disabled + '" onclick="' + onclick + '">' + link.label + '</a>';
            });
            
            html += '</div>';
            return html;
        }
        
        // Load page from pagination
        function loadPage(url) {
            toggleLoading(true);
            
            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'include'
            })
            .then(function(response) {
                if (!response.ok) throw new Error('Failed to load data');
                return response.json();
            })
            .then(function(data) {
                // Extract the section from the URL
                var urlParts = url.split('/');
                var section = urlParts[urlParts.length - 1].split('?')[0];
                
                // Map API endpoints to sections
                if (section === 'loan-applications') section = 'loans';
                
                renderSection(section, data);
            })
            .catch(function(error) {
                showNotification(error.message, 'error');
            })
            .finally(function() {
                toggleLoading(false);
            });
        }
        
        // Handle logout
        function handleLogout() {
            fetch('/logout', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'include'
            })
            .then(function(response) {
                if (response.ok) {
                    window.location.href = '/login';
                }
            })
            .catch(function(error) {
                showNotification('Logout failed: ' + error.message, 'error');
            });
        }
        
        // Toggle sidebar
        function toggleSidebar() {
            var sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
        }
        
        // Load departments for reports
        function loadDepartments() {
            fetch(apiBase + '/departments', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                credentials: 'include'
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(departments) {
                var select = document.getElementById('department');
                if (!select) return;
                
                // Clear existing options except the first one
                while (select.options.length > 1) {
                    select.remove(1);
                }
                
                // Add department options
                departments.forEach(function(dept) {
                    var option = document.createElement('option');
                    option.value = dept;
                    option.textContent = dept;
                    select.appendChild(option);
                });
            })
            .catch(function(error) {
                console.error('Error loading departments:', error);
            });
        }
        
        // Initialize the dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Load default section
            loadSection('loans');
            
            // Check session every 5 minutes
            setInterval(function() {
                fetch('/api/check-session', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                })
                .then(function(response) {
                    if (!response.ok) {
                        handleLogout();
                    }
                })
                .catch(function() {
                    handleLogout();
                });
            }, 300000); // 5 minutes
        });
    </script>
</body>
</html>