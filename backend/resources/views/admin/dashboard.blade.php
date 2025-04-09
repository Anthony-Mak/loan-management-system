{{-- backend/resources/views/admin/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Loan Management System - {{ ucfirst(Auth::user()->role) }} Dashboard</title>
    @vite(['resources/css/admin-hr.css'])
    @vite(['resources/css/admin.css'])
    
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div id="collapse-toggle" onclick="toggleSidebar()"></div>
        <ul>
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
                { label: 'Dashboard', section: 'dashboard', icon: 'Grid' },
                { label: 'Users', section: 'users', icon: 'Users' },
                { label: 'Employees', section: 'employees', icon: 'User' },
                { label: 'Loan Types', section: 'loan-types', icon: 'FileText' },
                { label: 'Branches', section: 'branches', icon: 'MapPin' },
                { label: 'System Config', section: 'system-config', icon: 'Settings' },
                { label: 'Audit Logs', section: 'audit-logs', icon: 'FileText' },
                { label: 'Reports', action: 'loadReports', icon: 'BarChart2' },
                { label: 'Logout', action: 'handleLogout', icon: 'LogOut' }
            ],
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
        'LogOut': '🚪',
        'Settings': '⚙️',
        'Grid': '📱',
        'MapPin': '📍'
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
                }
            };

            container.innerHTML = renderMap[userRole][section]();
        }

function renderEmployees(employees) {
    return `
        <div class="employees-container">
            <div class="section-header">
                <h2>Employee Management</h2>
                <button class="action-btn" onclick="showCreateEmployeeModal()">Create Employee</button>
            </div>
            
            <div class="filters">
                <div class="filter-group">
                    <label for="department-filter">Department:</label>
                    <input type="text" id="department-filter" placeholder="Filter by department">
                </div>
                
                <div class="filter-group">
                    <label for="emp-search">Search:</label>
                    <input type="text" id="emp-search" placeholder="Search employees..." onkeyup="handleEmployeeSearch(event)">
                </div>
                
                <div class="filter-group">
                    <button class="action-btn" onclick="applyEmployeeFilters()">Apply Filters</button>
                    <button class="action-btn secondary" onclick="clearEmployeeFilters()">Clear</button>
                </div>
            </div>
            
            <div class="employees-list">
                ${employees.data && employees.data.length ? 
                    employees.data.map(employee => `
                        <div class="employee-card" data-employee-id="${employee.employee_id}">
                            <div class="employee-info">
                                <h3>${employee.full_name}</h3>
                                <p>Employee ID: ${employee.employee_id}</p>
                                <p>Department: ${employee.department}</p>
                                <p>Position: ${employee.position}</p>
                                <p>Branch: ${employee.branch ? employee.branch.branch_name : 'Unknown'}</p>
                            </div>
                            <div class="employee-actions">
                                <button class="action-btn" onclick="viewEmployeeDetails(${employee.employee_id})">View Details</button>
                                <button class="action-btn" onclick="showEditEmployeeModal(${employee.employee_id})">Edit</button>
                            </div>
                        </div>
                    `).join('') : 
                    '<p class="no-data">No employees found</p>'
                }
            </div>
            
            ${renderPagination(employees)}
        </div>
    `;
}

function initEmployeeEventListeners() {
    document.getElementById('emp-search')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyEmployeeFilters();
        }
    });
    
    document.getElementById('department-filter')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyEmployeeFilters();
        }
    });
}

function applyEmployeeFilters() {
    const departmentFilter = document.getElementById('department-filter')?.value;
    const searchTerm = document.getElementById('emp-search')?.value;
    
    const params = new URLSearchParams();
    if (departmentFilter) params.append('department', departmentFilter);
    if (searchTerm) params.append('search', searchTerm);
    
    fetch(`${apiBase}/employees?${params.toString()}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('content-container');
        container.innerHTML = renderEmployees(data);
        initEmployeeEventListeners();
    })
    .catch(error => {
        showNotification('Error loading employees: ' + error.message, 'error');
    });
}

function handleEmployeeSearch(event) {
    if (event.key === 'Enter') {
        applyEmployeeFilters();
    }
}

function clearEmployeeFilters() {
    document.getElementById('department-filter').value = '';
    document.getElementById('emp-search').value = '';
    loadSection('employees');
}

function showCreateEmployeeModal() {
    // First, load branches for the dropdown
    fetch(`${apiBase}/branches`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include'
    })
    .then(response => response.json())
    .then(branches => {
        const modalHTML = `
            <div class="modal-overlay" id="create-employee-modal">
                <div class="modal-content wide-modal">
                    <h2>Create New Employee</h2>
                    <div id="create-employee-error" class="error-message" style="display: none;"></div>
                    
                    <form id="create-employee-form" class="employee-form">
                        <div class="form-section">
                            <h3>Personal Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="employee-id">Employee ID*:</label>
                                    <input type="text" id="employee-id" required>
                                </div>
                                <div class="form-group">
                                    <label for="title">Title:</label>
                                    <select id="title">
                                        <option value="Mr">Mr</option>
                                        <option value="Mrs">Mrs</option>
                                        <option value="Ms">Ms</option>
                                        <option value="Dr">Dr</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="full-name">Full Name*:</label>
                                    <input type="text" id="full-name" required>
                                </div>
                                <div class="form-group">
                                    <label for="national-id">National ID*:</label>
                                    <input type="text" id="national-id" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="date-of-birth">Date of Birth*:</label>
                                    <input type="date" id="date-of-birth" required>
                                </div>
                                <div class="form-group">
                                    <label for="gender">Gender*:</label>
                                    <select id="gender" required>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="marital-status">Marital Status*:</label>
                                    <select id="marital-status" required>
                                        <option value="Single">Single</option>
                                        <option value="Married">Married</option>
                                        <option value="Divorced">Divorced</option>
                                        <option value="Widowed">Widowed</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="dependents">Dependents:</label>
                                    <input type="number" id="dependents" min="0" value="0">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Contact Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="physical-address">Physical Address*:</label>
                                    <input type="text" id="physical-address" required>
                                </div>
                                <div class="form-group">
                                    <label for="accommodation-type">Accommodation Type:</label>
                                    <input type="text" id="accommodation-type">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="postal-address">Postal Address:</label>
                                    <input type="text" id="postal-address">
                                </div>
                                <div class="form-group">
                                    <label for="cell-phone">Cell Phone*:</label>
                                    <input type="text" id="cell-phone" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">Email*:</label>
                                    <input type="email" id="email" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Next of Kin</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="next-of-kin">Name*:</label>
                                    <input type="text" id="next-of-kin" required>
                                </div>
                                <div class="form-group">
                                    <label for="next-of-kin-cell">Cell Phone*:</label>
                                    <input type="text" id="next-of-kin-cell" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="next-of-kin-address">Address:</label>
                                    <input type="text" id="next-of-kin-address">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Employment Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="branch-id">Branch*:</label>
                                    <select id="branch-id" required>
                                        <option value="">Select Branch</option>
                                        ${branches.map(branch => `
                                            <option value="${branch.branch_id}">${branch.branch_name}</option>
                                        `).join('')}
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="department">Department*:</label>
                                    <input type="text" id="department" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="position">Position*:</label>
                                    <input type="text" id="position" required>
                                </div>
                                <div class="form-group">
                                    <label for="hire-date">Hire Date*:</label>
                                    <input type="date" id="hire-date" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="salary-gross">Gross Salary*:</label>
                                    <input type="number" id="salary-gross" step="0.01" min="0" required>
                                </div>
                                <div class="form-group">
                                    <label for="salary-net">Net Salary*:</label>
                                    <input type="number" id="salary-net" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <div class="modal-actions">
                        <button class="action-btn primary" onclick="createEmployee()">Create Employee</button>
                        <button class="cancel-btn" onclick="closeModal('create-employee-modal')">Cancel</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    })
    .catch(error => {
        showNotification('Error loading branches: ' + error.message, 'error');
    });
}

function createEmployee() {
    const employeeData = {
        employee_id: document.getElementById('employee-id').value,
        title: document.getElementById('title').value,
        full_name: document.getElementById('full-name').value,
        national_id: document.getElementById('national-id').value,
        date_of_birth: document.getElementById('date-of-birth').value,
        gender: document.getElementById('gender').value,
        marital_status: document.getElementById('marital-status').value,
        dependents: document.getElementById('dependents').value,
        physical_address: document.getElementById('physical-address').value,
        accommodation_type: document.getElementById('accommodation-type').value,
        postal_address: document.getElementById('postal-address').value,
        cell_phone: document.getElementById('cell-phone').value,
        email: document.getElementById('email').value,
        next_of_kin: document.getElementById('next-of-kin').value,
        next_of_kin_address: document.getElementById('next-of-kin-address').value,
        next_of_kin_cell: document.getElementById('next-of-kin-cell').value,
        branch_id: document.getElementById('branch-id').value,
        department: document.getElementById('department').value,
        position: document.getElementById('position').value,
        hire_date: document.getElementById('hire-date').value,
        salary_gross: document.getElementById('salary-gross').value,
        salary_net: document.getElementById('salary-net').value
    };
    
    fetch(`${apiBase}/employees`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include',
        body: JSON.stringify(employeeData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Failed to create employee');
            });
        }
        return response.json();
    })
    .then(data => {
        showNotification('Employee created successfully');
        closeModal('create-employee-modal');
        loadSection('employees');
    })
    .catch(error => {
        document.getElementById('create-employee-error').textContent = error.message;
        document.getElementById('create-employee-error').style.display = 'block';
    });
}

function viewEmployeeDetails(employeeId) {
    fetch(`${apiBase}/employees/${employeeId}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include'
    })
    .then(response => response.json())
    .then(employee => {
        const modalHTML = `
            <div class="modal-overlay" id="employee-details-modal">
                <div class="modal-content wide-modal">
                    <h2>Employee Details</h2>
                    
                    <div class="detail-sections">
                        <div class="detail-section">
                            <h3>Personal Information</h3>
                            <div class="detail-row">
                                <div class="detail-label">Employee ID:</div>
                                <div class="detail-value">${employee.employee_id}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Name:</div>
                                <div class="detail-value">${employee.title || ''} ${employee.full_name}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">National ID:</div>
                                <div class="detail-value">${employee.national_id}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Date of Birth:</div>
                                <div class="detail-value">${employee.date_of_birth}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Gender:</div>
                                <div class="detail-value">${employee.gender}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Marital Status:</div>
                                <div class="detail-value">${employee.marital_status}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Dependents:</div>
                                <div class="detail-value">${employee.dependents}</div>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h3>Contact Information</h3>
                            <div class="detail-row">
                                <div class="detail-label">Physical Address:</div>
                                <div class="detail-value">${employee.physical_address}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Accommodation:</div>
                                <div class="detail-value">${employee.accommodation_type || 'Not specified'}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Postal Address:</div>
                                <div class="detail-value">${employee.postal_address || 'Not specified'}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Cell Phone:</div>
                                <div class="detail-value">${employee.cell_phone}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Email:</div>
                                <div class="detail-value">${employee.email}</div>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h3>Next of Kin</h3>
                            <div class="detail-row">
                                <div class="detail-label">Name:</div>
                                <div class="detail-value">${employee.next_of_kin}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Address:</div>
                                <div class="detail-value">${employee.next_of_kin_address || 'Not specified'}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Contact:</div>
                                <div class="detail-value">${employee.next_of_kin_cell}</div>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h3>Employment Information</h3>
                            <div class="detail-row">
                                <div class="detail-label">Branch:</div>
                                <div class="detail-value">${employee.branch ? employee.branch.branch_name : 'Unknown'}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Department:</div>
                                <div class="detail-value">${employee.department}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Position:</div>
                                <div class="detail-value">${employee.position}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Hire Date:</div>
                                <div class="detail-value">${employee.hire_date}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Gross Salary:</div>
                                <div class="detail-value">$${parseFloat(employee.salary_gross).toFixed(2)}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Net Salary:</div>
                                <div class="detail-value">$${parseFloat(employee.salary_net).toFixed(2)}</div>
                            </div>
                        </div>
                        
                        <div class="detail-section">
                            <h3>System User</h3>
                            <div class="detail-row">
                                <div class="detail-label">Username:</div>
                                <div class="detail-value">${employee.user ? employee.user.username : 'No user account'}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Role:</div>
                                <div class="detail-value">${employee.user ? employee.user.role : 'N/A'}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Status:</div>
                                <div class="detail-value">${employee.user ? (employee.user.is_active ? 'Active' : 'Inactive') : 'N/A'}</div>
                            </div>
                            <div class="detail-row">
                                <div class="detail-label">Last Login:</div>
                                <div class="detail-value">${employee.user && employee.user.last_login ? new Date(employee.user.last_login).toLocaleString() : 'Never'}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-actions">
                        <button class="action-btn" onclick="showEditEmployeeModal(${employee.employee_id})">Edit Employee</button>
                        ${!employee.user ? `<button class="action-btn primary" onclick="showCreateUserModal('${employee.employee_id}')">Create User Account</button>` : ''}
                        <button class="cancel-btn" onclick="closeModal('employee-details-modal')">Close</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    })
    .catch(error => {
        showNotification('Error loading employee details: ' + error.message, 'error');
    });
}

function showEditEmployeeModal(employeeId) {
    // First, load branches
    fetch(`${apiBase}/branches`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include'
    })
    .then(response => response.json())
    .then(branches => {
        // Then load employee details
        return fetch(`${apiBase}/employees/${employeeId}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include'
        })
        .then(response => response.json())
        .then(employee => {
            return { branches, employee };
        });
    })
    .then(({ branches, employee }) => {
        const modalHTML = `
            <div class="modal-overlay" id="edit-employee-modal">
                <div class="modal-content wide-modal">
                    <h2>Edit Employee</h2>
                    <div id="edit-employee-error" class="error-message" style="display: none;"></div>
                    
                    <form id="edit-employee-form" class="employee-form">
                        <div class="form-section">
                            <h3>Personal Information</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit-employee-id">Employee ID*:</label>
                                    <input type="text" id="edit-employee-id" value="${employee.employee_id}" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="edit-title">Title:</label>
                                    <select id="edit-title">
                                        <option value="Mr" ${employee.title === 'Mr' ? 'selected' : ''}>Mr</option>
                                        <option value="Mrs" ${employee.title === 'Mrs' ? 'selected' : ''}>Mrs</option>
                                        <option value="Ms" ${employee.title === 'Ms' ? 'selected' : ''}>Ms</option>
                                        <option value="Dr" ${employee.title === 'Dr' ? 'selected' : ''}>Dr</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- More form fields similar to create employee but prefilled with employee data -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit-full-name">Full Name*:</label>
                                    <input type="text" id="edit-full-name" value="${employee.full_name}" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit-national-id">National ID*:</label>
                                    <input type="text" id="edit-national-id" value="${employee.national_id}" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit-date-of-birth">Date of Birth*:</label>
                                    <input type="date" id="edit-date-of-birth" value="${employee.date_of_birth}" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit-gender">Gender*:</label>
                                    <select id="edit-gender" required>
                                        <option value="Male" ${employee.gender === 'Male' ? 'selected' : ''}>Male</option>
                                        <option value="Female" ${employee.gender === 'Female' ? 'selected' : ''}>Female</option>
                                        <option value="Other" ${employee.gender === 'Other' ? 'selected' : ''}>Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Continue with all other form fields -->
                            <!-- ... -->
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit-branch-id">Branch*:</label>
                                    <select id="edit-branch-id" required>
                                        <option value="">Select Branch</option>
                                        ${branches.map(branch => `
                                            <option value="${branch.branch_id}" ${employee.branch_id == branch.branch_id ? 'selected' : ''}>${branch.branch_name}</option>
                                        `).join('')}
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="edit-department">Department*:</label>
                                    <input type="text" id="edit-department" value="${employee.department}" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit-position">Position*:</label>
                                    <input type="text" id="edit-position" value="${employee.position}" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit-hire-date">Hire Date*:</label>
                                    <input type="date" id="edit-hire-date" value="${employee.hire_date}" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit-salary-gross">Gross Salary*:</label>
                                    <input type="number" id="edit-salary-gross" step="0.01" min="0" value="${employee.salary_gross}" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit-salary-net">Net Salary*:</label>
                                    <input type="number" id="edit-salary-net" step="0.01" min="0" value="${employee.salary_net}" required>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <div class="modal-actions">
                        <button class="action-btn primary" onclick="updateEmployee(${employee.employee_id})">Update Employee</button>
                        <button class="cancel-btn" onclick="closeModal('edit-employee-modal')">Cancel</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    })
    .catch(error => {
        showNotification('Error loading employee details: ' + error.message, 'error');
    });
}

function updateEmployee(employeeId) {
    const employeeData = {
        title: document.getElementById('edit-title').value,
        full_name: document.getElementById('edit-full-name').value,
        national_id: document.getElementById('edit-national-id').value,
        date_of_birth: document.getElementById('edit-date-of-birth').value,
        gender: document.getElementById('edit-gender').value,
        // Include all other fields...
        branch_id: document.getElementById('edit-branch-id').value,
        department: document.getElementById('edit-department').value,
        position: document.getElementById('edit-position').value,
        hire_date: document.getElementById('edit-hire-date').value,
        salary_gross: document.getElementById('edit-salary-gross').value,
        salary_net: document.getElementById('edit-salary-net').value
    };
    
    fetch(`${apiBase}/employees/${employeeId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include',
        body: JSON.stringify(employeeData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Failed to update employee');
            });
        }
        return response.json();
    })
    .then(data => {
        showNotification('Employee updated successfully');
        closeModal('edit-employee-modal');
        loadSection('employees');
    })
    .catch(error => {
        document.getElementById('edit-employee-error').textContent = error.message;
        document.getElementById('edit-employee-error').style.display = 'block';
    });
}

// Loan Type Management
function renderLoanTypes(loanTypes) {
    return `
        <div class="loan-types-container">
            <div class="section-header">
                <h2>Loan Type Management</h2>
                <button class="action-btn" onclick="showCreateLoanTypeModal()">Create Loan Type</button>
            </div>
            
            <div class="loan-types-list">
                ${loanTypes && loanTypes.length ? 
                    loanTypes.map(loanType => `
                        <div class="loan-type-card" data-loan-type-id="${loanType.loan_type_id}">
                            <div class="loan-type-info">
                                <h3>${loanType.name}</h3>
                                <p class="description">${loanType.description || 'No description'}</p>
                                <div class="loan-type-details">
                                    <p>Interest Rate: <strong>${loanType.interest_rate}%</strong></p>
                                    <p>Maximum Amount: <strong>$${parseFloat(loanType.max_amount).toFixed(2)}</strong></p>
                                    <p>Maximum Term: <strong>${loanType.max_term} months</strong></p>
                                </div>
                            </div>
                            <div class="loan-type-actions">
                                <button class="action-btn" onclick="showEditLoanTypeModal(${loanType.loan_type_id})">Edit</button>
                            </div>
                        </div>
                    `).join('') : 
                    '<p class="no-data">No loan types found</p>'
                }
            </div>
        </div>
    `;
}

function initLoanTypeEventListeners() {
    // Add any event listeners for loan type section
}

function showCreateLoanTypeModal() {
    const modalHTML = `
        <div class="modal-overlay" id="create-loan-type-modal">
            <div class="modal-content">
                <h2>Create New Loan Type</h2>
                <div id="create-loan-type-error" class="error-message" style="display: none;"></div>
                
                <div class="form-group">
                    <label for="loan-type-name">Name*:</label>
                    <input type="text" id="loan-type-name" required>
                </div>
                
                <div class="form-group">
                    <label for="loan-type-description">Description:</label>
                    <textarea id="loan-type-description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="loan-type-interest">Interest Rate (%)*:</label>
                    <input type="number" id="loan-type-interest" step="0.01" min="0" max="100" required>
                </div>
                
                <div class="form-group">
                    <label for="loan-type-max-amount">Maximum Amount ($)*:</label>
                    <input type="number" id="loan-type-max-amount" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="loan-type-max-term">Maximum Term (months)*:</label>
                    <input type="number" id="loan-type-max-term" min="1" required>
                </div>
                
                <div class="modal-actions">
                    <button class="action-btn primary" onclick="createLoanType()">Create Loan Type</button>
                    <button class="cancel-btn" onclick="closeModal('create-loan-type-modal')">Cancel</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

function createLoanType() {
    const loanTypeData = {
        name: document.getElementById('loan-type-name').value,
        description: document.getElementById('loan-type-description').value,
        interest_rate: document.getElementById('loan-type-interest').value,
        max_amount: document.getElementById('loan-type-max-amount').value,
        max_term: document.getElementById('loan-type-max-term').value
    };
    
    fetch(`${apiBase}/loan-types`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include',
        body: JSON.stringify(loanTypeData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Failed to create loan type');
            });
        }
        return response.json();
    })
    .then(data => {
        showNotification('Loan type created successfully');
        closeModal('create-loan-type-modal');
        loadSection('loan-types');
    })
    .catch(error => {
        document.getElementById('create-loan-type-error').textContent = error.message;
        document.getElementById('create-loan-type-error').style.display = 'block';
    });
}

function showEditLoanTypeModal(loanTypeId) {
    fetch(`${apiBase}/loan-types/${loanTypeId}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include'
    })
    .then(response => response.json())
    .then(loanType => {
        const modalHTML = `
            <div class="modal-overlay" id="edit-loan-type-modal">
                <div class="modal-content">
                    <h2>Edit Loan Type</h2>
                    <div id="edit-loan-type-error" class="error-message" style="display: none;"></div>
                    
                    <div class="form-group">
                        <label for="edit-loan-type-name">Name*:</label>
                        <input type="text" id="edit-loan-type-name" value="${loanType.name}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-loan-type-description">Description:</label>
                        <textarea id="edit-loan-type-description" rows="3">${loanType.description || ''}</textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-loan-type-interest">Interest Rate (%)*:</label>
                        <input type="number" id="edit-loan-type-interest" step="0.01" min="0" max="100" value="${loanType.interest_rate}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-loan-type-max-amount">Maximum Amount ($)*:</label>
                        <input type="number" id="edit-loan-type-max-amount" step="0.01" min="0" value="${loanType.max_amount}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-loan-type-max-term">Maximum Term (months)*:</label>
                        <input type="number" id="edit-loan-type-max-term" min="1" value="${loanType.max_term}" required>
                    </div>
                    
                    <div class="modal-actions">
                        <button class="action-btn primary" onclick="updateLoanType(${loanType.loan_type_id})">Update Loan Type</button>
                        <button class="cancel-btn" onclick="closeModal('edit-loan-type-modal')">Cancel</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    })
    .catch(error => {
        showNotification('Error loading loan type details: ' + error.message, 'error');
    });
}

function updateLoanType(loanTypeId) {
    const loanTypeData = {
        name: document.getElementById('edit-loan-type-name').value,
        description: document.getElementById('edit-loan-type-description').value,
        interest_rate: document.getElementById('edit-loan-type-interest').value,
        max_amount: document.getElementById('edit-loan-type-max-amount').value,
        max_term: document.getElementById('edit-loan-type-max-term').value
    };
    
    fetch(`${apiBase}/loan-types/${loanTypeId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include',
        body: JSON.stringify(loanTypeData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Failed to update loan type');
            });
        }
        return response.json();
    })
    .then(data => {
        showNotification('Loan type updated successfully');
        closeModal('edit-loan-type-modal');
        loadSection('loan-types');
    })
    .catch(error => {
        document.getElementById('edit-loan-type-error').textContent = error.message;
        document.getElementById('edit-loan-type-error').style.display = 'block';
    });
}// Branch Management
function renderBranches(branches) {
    return `
        <div class="branches-container">
            <div class="section-header">
                <h2>Branch Management</h2>
                <button class="action-btn" onclick="showCreateBranchModal()">Create Branch</button>
            </div>
            
            <div class="branches-list">
                ${branches && branches.length ? 
                    branches.map(branch => `
                        <div class="branch-card" data-branch-id="${branch.branch_id}">
                            <div class="branch-info">
                                <h3>${branch.branch_name}</h3>
                                <p>Location: ${branch.location}</p>
                                <p>Branch Code: ${branch.branch_code || 'N/A'}</p>
                            </div>
                            <div class="branch-actions">
                                <button class="action-btn" onclick="showEditBranchModal(${branch.branch_id})">Edit</button>
                            </div>
                        </div>
                    `).join('') : 
                    '<p class="no-data">No branches found</p>'
                }
            </div>
        </div>
    `;
}

function initBranchEventListeners() {
    // Add any event listeners for branch section
}

function showCreateBranchModal() {
    const modalHTML = `
        <div class="modal-overlay" id="create-branch-modal">
            <div class="modal-content">
                <h2>Create New Branch</h2>
                <div id="create-branch-error" class="error-message" style="display: none;"></div>
                
                <div class="form-group">
                    <label for="branch-name">Branch Name*:</label>
                    <input type="text" id="branch-name" required>
                </div>
                
                <div class="form-group">
                    <label for="branch-location">Location*:</label>
                    <input type="text" id="branch-location" required>
                </div>
                
                <div class="form-group">
                    <label for="branch-code">Branch Code:</label>
                    <input type="text" id="branch-code">
                </div>
                
                <div class="modal-actions">
                    <button class="action-btn primary" onclick="createBranch()">Create Branch</button>
                    <button class="cancel-btn" onclick="closeModal('create-branch-modal')">Cancel</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

function createBranch() {
    const branchData = {
        branch_name: document.getElementById('branch-name').value,
        location: document.getElementById('branch-location').value,
        branch_code: document.getElementById('branch-code').value
    };
    
    fetch(`${apiBase}/branches`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include',
        body: JSON.stringify(branchData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Failed to create branch');
            });
        }
        return response.json();
    })
    .then(data => {
        showNotification('Branch created successfully');
        closeModal('create-branch-modal');
        loadSection('branches');
    })
    .catch(error => {
        document.getElementById('create-branch-error').textContent = error.message;
        document.getElementById('create-branch-error').style.display = 'block';
    });
}

function showEditBranchModal(branchId) {
    fetch(`${apiBase}/branches/${branchId}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include'
    })
    .then(response => response.json())
    .then(branch => {
        const modalHTML = `
            <div class="modal-overlay" id="edit-branch-modal">
                <div class="modal-content">
                    <h2>Edit Branch</h2>
                    <div id="edit-branch-error" class="error-message" style="display: none;"></div>
                    
                    <div class="form-group">
                        <label for="edit-branch-name">Branch Name*:</label>
                        <input type="text" id="edit-branch-name" value="${branch.branch_name}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-branch-location">Location*:</label>
                        <input type="text" id="edit-branch-location" value="${branch.location}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-branch-code">Branch Code:</label>
                        <input type="text" id="edit-branch-code" value="${branch.branch_code || ''}">
                    </div>
                    
                    <div class="modal-actions">
                        <button class="action-btn primary" onclick="updateBranch(${branch.branch_id})">Update Branch</button>
                        <button class="cancel-btn" onclick="closeModal('edit-branch-modal')">Cancel</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    })
    .catch(error => {
        showNotification('Error loading branch details: ' + error.message, 'error');
    });
}

function updateBranch(branchId) {
    const branchData = {
        branch_name: document.getElementById('edit-branch-name').value,
        location: document.getElementById('edit-branch-location').value,
        branch_code: document.getElementById('edit-branch-code').value
    };
    
    fetch(`${apiBase}/branches/${branchId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include',
        body: JSON.stringify(branchData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Failed to update branch');
            });
        }
        return response.json();
    })
    .then(data => {
        showNotification('Branch updated successfully');
        closeModal('edit-branch-modal');
        loadSection('branches');
    })
    .catch(error => {
        document.getElementById('edit-branch-error').textContent = error.message;
        document.getElementById('edit-branch-error').style.display = 'block';
    });
}

// System Configuration
function renderSystemConfig(data) {
    return `
        <div class="system-config-container">
            <h2>System Configuration</h2>
            
            <div class="config-sections">
                <div class="config-section">
                    <h3>Loan Types</h3>
                    <p>Total Loan Types: ${data.loan_types.length}</p>
                    <button class="action-btn" onclick="loadSection('loan-types')">Manage Loan Types</button>
                </div>
                
                <div class="config-section">
                    <h3>Branches</h3>
                    <p>Total Branches: ${data.branches.length}</p>
                    <button class="action-btn" onclick="loadSection('branches')">Manage Branches</button>
                </div>
                
                <div class="config-section">
                    <h3>User Roles</h3>
                    <ul class="role-list">
                        ${data.user_roles.map(role => `<li>${role}</li>`).join('')}
                    </ul>
                    <button class="action-btn" onclick="loadSection('users')">Manage Users</button>
                </div>
            </div>
        </div>
    `;
}

// Audit Logs
function renderAuditLogs(logs) {
    return `
        <div class="audit-logs-container">
            <h2>System Audit Logs</h2>
            
            <div class="audit-filters">
                <div class="filter-group">
                    <label for="action-type-filter">Action Type:</label>
                    <select id="action-type-filter">
                        <option value="">All Actions</option>
                        <!-- Will be populated by AJAX -->
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="entity-type-filter">Entity Type:</label>
                    <select id="entity-type-filter">
                        <option value="">All Entities</option>
                        <!-- Will be populated by AJAX -->
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="start-date-filter">Start Date:</label>
                    <input type="date" id="start-date-filter">
                </div>
                
                <div class="filter-group">
                    <label for="end-date-filter">End Date:</label>
                    <input type="date" id="end-date-filter">
                </div>
                
                <div class="filter-group">
                    <button class="action-btn" onclick="applyAuditLogFilters()">Apply Filters</button>
                    <button class="action-btn secondary" onclick="clearAuditLogFilters()">Clear</button>
                </div>
            </div>
            
            <div class="audit-logs-list">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Entity</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${logs.data && logs.data.length ? 
                            logs.data.map(log => `
                                <tr>
                                    <td>${new Date(log.created_at).toLocaleString()}</td>
                                    <td>${log.user_id || 'System'}</td>
                                    <td>${log.action_type}</td>
                                    <td>${log.entity_type || 'N/A'}</td>
                                    <td>${log.description}</td>
                                </tr>
                            `).join('') : 
                            '<tr><td colspan="5" class="no-data">No audit logs found</td></tr>'
                        }
                    </tbody>
                </table>
            </div>
            
            ${renderPagination(logs)}
        </div>
    `;
}

function initAuditLogFilters() {
    // Fetch action types
    fetch(`${apiBase}/audit-logs/action-types`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include'
    })
    .then(response => response.json())
    .then(actionTypes => {
        const actionTypeSelect = document.getElementById('action-type-filter');
        if (actionTypeSelect) {
            actionTypes.forEach(type => {
                const option = document.createElement('option');
                option.value = type;
                option.textContent = type;
                actionTypeSelect.appendChild(option);
            });
        }
    })
    .catch(error => {
        console.error('Error loading action types:', error);
    });
    
    // Fetch entity types
    fetch(`${apiBase}/audit-logs/entity-types`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include'
    })
    .then(response => response.json())
    .then(entityTypes => {
        const entityTypeSelect = document.getElementById('entity-type-filter');
        if (entityTypeSelect) {
            entityTypes.forEach(type => {
                const option = document.createElement('option');
                option.value = type;
                option.textContent = type;
                entityTypeSelect.appendChild(option);
            });
        }
    })
    .catch(error => {
        console.error('Error loading entity types:', error);
    });
}

function applyAuditLogFilters() {
    const actionType = document.getElementById('action-type-filter')?.value;
    const entityType = document.getElementById('entity-type-filter')?.value;
    const startDate = document.getElementById('start-date-filter')?.value;
    const endDate = document.getElementById('end-date-filter')?.value;
    
    const params = new URLSearchParams();
    if (actionType) params.append('action_type', actionType);
    if (entityType) params.append('entity_type', entityType);
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);
    
    fetch(`${apiBase}/audit-logs?${params.toString()}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('content-container');
        container.innerHTML = renderAuditLogs(data);
        initAuditLogFilters();
    })
    .catch(error => {
        showNotification('Error loading audit logs: ' + error.message, 'error');
    });
}

function clearAuditLogFilters() {
    document.getElementById('action-type-filter').value = '';
    document.getElementById('entity-type-filter').value = '';
    document.getElementById('start-date-filter').value = '';
    document.getElementById('end-date-filter').value = '';
    loadSection('audit-logs');
}

// Utility functions
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.remove();
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

        // UI Functions consider adding to the centralised admin-hr.js
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
        'users': 'users',
        'employees': 'employees',
        'reports': 'reports',
         'loan-types': 'loan-types',
        'branches': 'branches',
        'system-config': 'system-config',
        'audit-logs': 'audit-logs',
        'dashboard': 'dashboard'
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
            'users': '/users',
            'employees': '/employees',
            'reports': '/reports',
            'loan-types': '/loan-types',
            'branches': '/branches',
            'system-config': '/system-config',
            'audit-logs': '/audit-logs',
            'dashboard': ''
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
        case 'dashboard':
            container.innerHTML = renderAdminDashboard(data);
            break;
        case 'loans':
            container.innerHTML = renderLoans(data);
            break;
        case 'users':
            container.innerHTML = renderUsers(data);
            initUserEventListeners();
            break;
        case 'employees':
            container.innerHTML = renderEmployees(data);
            initEmployeeEventListeners();
            break;
        case 'reports':
            container.innerHTML = renderReports(data);
            break;
        case 'loan-types':
            container.innerHTML = renderLoanTypes(data);
            initLoanTypeEventListeners();
            break;
        case 'branches':
            container.innerHTML = renderBranches(data);
            initBranchEventListeners();
            break;
        case 'system-config':
            container.innerHTML = renderSystemConfig(data);
            break;
        case 'audit-logs':
            container.innerHTML = renderAuditLogs(data);
            initAuditLogFilters();
            break;
        default:
            container.innerHTML = `<div>Unknown section: ${section}</div>`;
    }
}

      
// Modify initialization to handle multiple section names
document.addEventListener('DOMContentLoaded', () => {
    renderSidebar();

    const sectionByRole = {
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


function renderUsers(users) {
    return `
        <div class="users-container">
            <div class="section-header">
                <h2>User Management</h2>
                <button class="action-btn" onclick="showCreateUserModal()">Create User</button>
            </div>
            
            <div class="filters">
                <div class="filter-group">
                    <label for="role-filter">Role:</label>
                    <select id="role-filter" onchange="applyUserFilters()">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="hr">HR</option>
                        <option value="employee">Employee</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="status-filter">Status:</label>
                    <select id="status-filter" onchange="applyUserFilters()">
                        <option value="">All Statuses</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="user-search">Search:</label>
                    <input type="text" id="user-search" placeholder="Search users..." onkeyup="handleUserSearch(event)">
                </div>
            </div>
            
            <div class="users-list">
                ${users.data && users.data.length ? 
                    users.data.map(user => `
                        <div class="user-card" data-user-id="${user.user_id}">
                            <div class="user-info">
                                <h3>${user.username}</h3>
                                <p class="employee-name">${user.employee && user.employee.full_name ? user.employee.full_name : 'No Employee'}</p>
                                <p>Role: <span class="user-role">${user.role}</span></p>
                                <p>Status: <span class="status ${user.is_active ? 'approved' : 'rejected'}">${user.is_active ? 'Active' : 'Inactive'}</span></p>
                                <p>Last Login: ${user.last_login ? new Date(user.last_login).toLocaleString() : 'Never'}</p>
                            </div>
                            <div class="user-actions">
                                <button class="action-btn" onclick="showEditUserModal(${user.user_id})">Edit Role</button>
                                <button class="action-btn" onclick="toggleUserStatus(${user.user_id}, ${!user.is_active})">${user.is_active ? 'Disable' : 'Enable'}</button>
                                <button class="action-btn" onclick="resetUserPassword(${user.user_id})">Reset Password</button>
                            </div>
                        </div>
                    `).join('') : 
                    '<p class="no-data">No users found</p>'
                }
            </div>
            
            ${renderPagination(users)}
        </div>
    `;
}

function initUserEventListeners() {
    document.getElementById('user-search')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyUserFilters();
        }
    });
}

function applyUserFilters() {
    const roleFilter = document.getElementById('role-filter')?.value;
    const statusFilter = document.getElementById('status-filter')?.value;
    const searchTerm = document.getElementById('user-search')?.value;
    
    const params = new URLSearchParams();
    if (roleFilter) params.append('role', roleFilter);
    if (statusFilter !== '') params.append('is_active', statusFilter);
    if (searchTerm) params.append('search', searchTerm);
    
    fetch(`${apiBase}/users?${params.toString()}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('content-container');
        container.innerHTML = renderUsers(data);
        initUserEventListeners();
    })
    .catch(error => {
        showNotification('Error loading users: ' + error.message, 'error');
    });
}

function handleUserSearch(event) {
    if (event.key === 'Enter') {
        applyUserFilters();
    }
}

function showCreateUserModal() {
    // First, fetch employees to populate the dropdown
    fetch(`${apiBase}/employees`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        const employees = data.data || [];
        
        const modalHTML = `
            <div class="modal-overlay" id="create-user-modal">
                <div class="modal-content">
                    <h2>Create New User</h2>
                    <div id="create-user-error" class="error-message" style="display: none;"></div>
                    
                    <div class="form-group">
                        <label for="employee-select">Employee:</label>
                        <select id="employee-select" required>
                            <option value="">Select Employee</option>
                            ${employees.map(emp => `
                                <option value="${emp.employee_id}">${emp.full_name} (${emp.employee_id})</option>
                            `).join('')}
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="username-input">Username:</label>
                        <input type="text" id="username-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role-select">Role:</label>
                        <select id="role-select" required>
                            <option value="employee">Employee</option>
                            <option value="hr">HR</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="password-input">Password:</label>
                        <input type="password" id="password-input" placeholder="Leave blank for default password">
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="active-checkbox" checked>
                            Active
                        </label>
                    </div>
                    
                    <div class="modal-actions">
                        <button class="action-btn primary" onclick="createUser()">Create User</button>
                        <button class="cancel-btn" onclick="closeModal('create-user-modal')">Cancel</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    })
    .catch(error => {
        showNotification('Error loading employees: ' + error.message, 'error');
    });
}

function createUser() {
    const employeeId = document.getElementById('employee-select').value.toString();

    const username = document.getElementById('username-input').value;
    const role = document.getElementById('role-select').value;
    const password = document.getElementById('password-input').value;
    const isActive = document.getElementById('active-checkbox').checked;
    
    if (!employeeId || !username || !role) {
        const errorDiv = document.getElementById('create-user-error');
        errorDiv.textContent = 'Please fill all required fields.';
        errorDiv.style.display = 'block';
        return;
    }
    
    const userData = {
        employee_id: employeeId,
        username: username,
        role: role,
        is_active: isActive
    };
    
    if (password) {
        userData.password = password;
    }
    
    fetch(`${apiBase}/users`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include',
        body: JSON.stringify(userData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Failed to create user');
            });
        }
        return response.json();
    })
    .then(data => {
        showNotification('User created successfully');
        closeModal('create-user-modal');
        loadSection('users');
    })
    .catch(error => {
        document.getElementById('create-user-error').textContent = error.message;
        document.getElementById('create-user-error').style.display = 'block';
    });
}

function showEditUserModal(userId) {
    // Fetch user details first
    fetch(`${apiBase}/users/${userId}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include'
    })
    .then(response => response.json())
    .then(user => {
        const modalHTML = `
            <div class="modal-overlay" id="edit-user-modal">
                <div class="modal-content">
                    <h2>Edit User Role</h2>
                    <div id="edit-user-error" class="error-message" style="display: none;"></div>
                    
                    <p>Username: ${user.username}</p>
                    <p>Employee: ${user.employee ? user.employee.full_name : 'N/A'}</p>
                    
                    <div class="form-group">
                        <label for="edit-role-select">Role:</label>
                        <select id="edit-role-select">
                            <option value="employee" ${user.role === 'employee' ? 'selected' : ''}>Employee</option>
                            <option value="hr" ${user.role === 'hr' ? 'selected' : ''}>HR</option>
                            <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                        </select>
                    </div>
                    
                    <div class="modal-actions">
                        <button class="action-btn primary" onclick="updateUserRole(${userId})">Update Role</button>
                        <button class="cancel-btn" onclick="closeModal('edit-user-modal')">Cancel</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    })
    .catch(error => {
        showNotification('Error loading user details: ' + error.message, 'error');
    });
}

function updateUserRole(userId) {
    const role = document.getElementById('edit-role-select').value;
    
    fetch(`${apiBase}/users/${userId}/role`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include',
        body: JSON.stringify({ role })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Failed to update user role');
            });
        }
        return response.json();
    })
    .then(data => {
        showNotification('User role updated successfully');
        closeModal('edit-user-modal');
        loadSection('users');
    })
    .catch(error => {
        document.getElementById('edit-user-error').textContent = error.message;
        document.getElementById('edit-user-error').style.display = 'block';
    });
}

function toggleUserStatus(userId, newStatus) {
    fetch(`${apiBase}/users/${userId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'include',
        body: JSON.stringify({ is_active: newStatus })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Failed to update user status');
            });
        }
        return response.json();
    })
    .then(data => {
        showNotification(`User ${newStatus ? 'enabled' : 'disabled'} successfully`);
        loadSection('users');
    })
    .catch(error => {
        showNotification(error.message, 'error');
    });
}

function resetUserPassword(userId) {
    if (confirm('Are you sure you want to reset this user\'s password?')) {
        fetch(`${apiBase}/users/${userId}/reset-password`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'include'
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || 'Failed to reset password');
                });
            }
            return response.json();
        })
        .then(data => {
            showNotification('Password reset successful. Temporary password: ' + data.temporary_password);
        })
        .catch(error => {
            showNotification(error.message, 'error');
        });
    }
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
                    <h3>Total Users</h3>
                    <div class="summary-value" id="total-users">${data.total_users || 0}</div>
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
            loadSection('dashboard');
            
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
        //Admin side

        function renderAdminDashboard(metrics) {
    return `
        <div class="dashboard-container">
            <h2>Admin Dashboard</h2>
            
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-value">${metrics.total_employees || 0}</div>
                    <div class="metric-label">Employees</div>
                </div>
                <div class="metric-card">
                    <div class="metric-value">${metrics.total_users || 0}</div>
                    <div class="metric-label">Users</div>
                </div>
                <div class="metric-card">
                    <div class="metric-value">${metrics.total_branches || 0}</div>
                    <div class="metric-label">Branches</div>
                </div>
                <div class="metric-card">
                    <div class="metric-value">${metrics.total_loan_types || 0}</div>
                    <div class="metric-label">Loan Types</div>
                </div>
            </div>
            
            <div class="dashboard-sections">
                <div class="dashboard-section">
                    <h3>Recent Users</h3>
                    <div class="recent-items">
                        ${metrics.recent_users && metrics.recent_users.length > 0 ? 
                            metrics.recent_users.map(user => `
                                <div class="recent-item">
                                    <div>${user.username}</div>
                                    <div>${user.role}</div>
                                    <div>${new Date(user.created_at).toLocaleDateString()}</div>
                                </div>
                            `).join('') : 
                            '<p>No recent users</p>'
                        }
                    </div>
                </div>
                
                <div class="dashboard-section">
                    <h3>Recent Activity</h3>
                    <div class="recent-items">
                        ${metrics.recent_audit_logs && metrics.recent_audit_logs.length > 0 ? 
                            metrics.recent_audit_logs.map(log => `
                                <div class="recent-item">
                                    <div>${log.action_type}</div>
                                    <div>${log.description.substring(0, 60)}${log.description.length > 60 ? '...' : ''}</div>
                                    <div>${new Date(log.created_at).toLocaleString()}</div>
                                </div>
                            `).join('') : 
                            '<p>No recent activity</p>'
                        }
                    </div>
                </div>
            </div>
        </div>
    `;
}

        document.addEventListener('DOMContentLoaded', function() {
   
    renderSidebar();
    
    // Load dashboard by default for admin users
    if (userRole.toLowerCase() === 'admin') {
        loadSection('dashboard');
    } 
});
    </script>
</body>
</html>