<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Employee Dashboard</title>
    @vite(['resources/css/app.css'])
    @vite(['resources/css/employee.css'])
</head>
<body>
    <nav class="navbar">
        <div class="navbar-brand">Loan Management System</div>
        <div class="user-info">
            <div class="user-profile" id="user-initial">{{ strtoupper(substr(Auth::user()->username, 0, 1)) }}</div>
            <span id="username-display">{{ Auth::user()->username }}</span>
            <button onclick="handleLogout()" class="logout-btn">Logout</button>
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
                <div class="card-title">Recommended</div>
                <div class="card-value" id="approved-applications">0</div>
            </div>
            <div class="card">
                <div class="card-title">Not Recommended</div>
                <div class="card-value" id="rejected-applications">0</div>
            </div>
        </div>

        <div class="actions">
            <button class="btn btn-primary" onclick="window.location.href='{{ route('employee.apply') }}'">
                Apply for New Loan
            </button>
        </div>

        <h2 class="section-title">Your Loan Applications</h2>
        
        <div id="employee-loans" class="loan-list">
            <!-- Loan items will be added here -->
        </div>
    </div>

    <!-- Modal structure for loan details -->
    <div id="loan-detail-modal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Loan Details</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modal-content">
                <!-- Loan details will be inserted here -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
        // Authentication and API Client
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let currentUser = @json(Auth::user());

        const apiClient = {
            get: async (url) => {
                return fetch(`/api/employee${url}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
                    },
                    credentials: 'include'
                });
            },
            post: async (url, data) => {
                return fetch(`/api/employee${url}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
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

        async function loadEmployeeLoans() {
            try {
                const response = await apiClient.get('/loans');
                if (!response.ok) throw new Error('Failed to load loans');
                
                const data = await response.json();
                renderLoans(data.loans);
                updateDashboardStats(data.stats);
            } catch (error) {
                showNotification(error.message, 'error');
                if (error.response && error.response.status === 401) {
                    handleLogout();
                }
            }
        }

        function renderLoans(loans) {
            const container = document.getElementById('employee-loans');
            container.innerHTML = loans.length > 0 ? 
                loans.map(loan => `
                    <div class="loan-item">
                        <div>
                            <div class="loan-id">#${loan.loan_id}</div>
                            <div class="loan-name">${loan.purpose}</div>
                        </div>
                        <div class="loan-amount">$${loan.amount}</div>
                        <div>
                            <span class="status ${getStatusClass(loan.status)}">${loan.status}</span>
                        </div>
                        <div>
                            <button onclick="showLoanDetail(${loan.loan_id})" class="btn btn-primary">
                                View Details
                            </button>
                        </div>
                    </div>
                `).join('') : 
                `<div class="empty-state">No loan applications found</div>`;
        }

        async function showLoanDetail(loanId) {
            try {
                // Show loading state in modal
                const modalContent = document.getElementById('modal-content');
                modalContent.innerHTML = `<div class="loading">Loading loan details...</div>`;
                
                // Show the modal
                document.getElementById('loan-detail-modal').classList.add('active');
                
                // Prevent body scrolling when modal is open
                document.body.style.overflow = 'hidden';
                
                const response = await apiClient.get(`/loans/${loanId}`);
                if (!response.ok) throw new Error('Failed to load loan details');
                
                const loan = await response.json();
                renderLoanDetailInModal(loan);
            } catch (error) {
                showNotification(error.message, 'error');
                closeModal();
            }
        }

        function renderLoanDetailInModal(loan) {
            const modalContent = document.getElementById('modal-content');
            
            modalContent.innerHTML = `
                <div class="loan-detail-grid">
                    <div class="loan-detail-item">
                        <div class="loan-detail-label">Loan ID</div>
                        <div class="loan-detail-value">#${loan.loan_id}</div>
                    </div>
                    <div class="loan-detail-item">
                        <div class="loan-detail-label">Amount</div>
                        <div class="loan-detail-value">$${loan.amount}</div>
                    </div>
                    <div class="loan-detail-item">
                        <div class="loan-detail-label">Status</div>
                        <div class="loan-detail-value">
                            <span class="status ${getStatusClass(loan.status)}">${loan.status}</span>
                        </div>
                    </div>
                    <div class="loan-detail-item">
                        <div class="loan-detail-label">Application Date</div>
                        <div class="loan-detail-value">${new Date(loan.created_at).toLocaleDateString()}</div>
                    </div>
                    <div class="loan-detail-item">
                        <div class="loan-detail-label">Purpose</div>
                        <div class="loan-detail-value">${loan.purpose}</div>
                    </div>
                </div>
                ${loan.review_notes ? `
                    <div class="review-block">
                        <div class="loan-detail-label">Review Comments</div>
                        <div class="loan-detail-value">${loan.review_notes}</div>
                    </div>
                ` : ''}
            `;
        }

        function closeModal() {
            document.getElementById('loan-detail-modal').classList.remove('active');
            document.body.style.overflow = 'auto'; // Restore body scrolling
        }

        // Close modal if clicking outside content area
        document.getElementById('loan-detail-modal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeModal();
            }
        });

        // Close modal on ESC key press
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && document.getElementById('loan-detail-modal').classList.contains('active')) {
                closeModal();
            }
        });

        function updateDashboardStats(stats) {
            document.getElementById('total-applications').textContent = stats.total;
            document.getElementById('pending-applications').textContent = stats.pending;
            document.getElementById('approved-applications').textContent = stats.recommended;
            document.getElementById('rejected-applications').textContent = stats.not_recommended;
        }

        async function handleLogout() {
            try {
                await fetch('/logout', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
                localStorage.removeItem('auth_token');
                window.location.href = '/login';
            } catch (error) {
                showNotification('Logout failed', 'error');
            }
        }

        // Initialize Dashboard
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                await loadEmployeeLoans();
                // Verify session every 5 minutes
                setInterval(async () => {
                    try {
                        await apiClient.get('/check-session');
                    } catch (error) {
                        handleLogout();
                    }
                }, 300000);
            } catch (error) {
                showNotification('Failed to initialize dashboard', 'error');
            }
        });

        function getStatusClass(status) {
            status = status.toLowerCase();
            if (status === 'recommended') return 'approved';
            if (status === 'not recommended') return 'rejected';
            return 'pending'; // Default or for 'pending' status
        }
    </script>
</body>
</html>