{{-- backend/resources/views/admin//reports/hr_reports.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HR Reports - Loan Management System</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        
        .reports-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
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
        
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .report-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .report-card h3 {
            color: #3498db;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-top: 0;
        }
        
        .report-stats {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin-top: 15px;
        }
        
        .stat-item {
            text-align: center;
            margin: 10px;
            min-width: 100px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .stat-value.total { color: #3498db; }
        .stat-value.approved { color: #2ecc71; }
        .stat-value.pending { color: #f39c12; }
        .stat-value.rejected { color: #e74c3c; }
        
        .department-list {
            max-height: 200px;
            overflow-y: auto;
            margin-top: 15px;
        }
        
        .dept-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .dept-item:hover {
            background-color: #f8f9fa;
        }
        
        .dept-name {
            font-weight: 500;
        }
        
        .dept-count {
            font-weight: bold;
            color: #3498db;
        }
        
        .chart-container {
            height: 250px;
            position: relative;
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
        
        button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid #3498db;
            color: #3498db;
        }
        
        .btn-outline:hover {
            background-color: #f0f7fc;
        }
        
        .btn-export {
            background-color: #2ecc71;
            color: white;
        }
        
        .btn-export:hover {
            background-color: #27ae60;
        }
        
        .btn-print {
            background-color: #9b59b6;
            color: white;
        }
        
        .btn-print:hover {
            background-color: #8e44ad;
        }
        
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 2s linear infinite;
            margin: 20px auto;
            display: none;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .error-message {
            background-color: #fde8e8;
            color: #e53e3e;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .clickable {
            cursor: pointer;
        }
        
        #detailed-report {
            margin-top: 30px;
        }
        
        .detailed-title {
            margin-top: 30px;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
        }
        
        tr:hover {
            background-color: #f8f9fa;
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
        
        @media print {
            body * {
                visibility: hidden;
            }
            .reports-container, .reports-container * {
                visibility: visible;
            }
            .reports-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .reports-actions, .report-filters, button {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="reports-container">
        <div class="reports-header">
            <h1 class="reports-title">HR Loan Reports</h1>
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
                    <!-- Departments will be populated dynamically -->
                </select>
            </div>
            
            <div class="filter-buttons">
                <button class="btn-primary" onclick="generateReport()">Generate Report</button>
                <button class="btn-outline" onclick="resetFilters()">Reset</button>
            </div>
        </div>
        
        <div id="loader" class="loader"></div>
        <div id="error-container"></div>
        
        <div class="reports-grid">
            <div class="report-card loan-summary">
                <h3>Loan Applications Summary</h3>
                <div class="report-stats">
                    <div class="stat-item">
                        <div class="stat-value total" id="total-loans">0</div>
                        <div class="stat-label">Total</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value approved" id="approved-loans">0</div>
                        <div class="stat-label">Approved</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value pending" id="pending-loans">0</div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value rejected" id="rejected-loans">0</div>
                        <div class="stat-label">Rejected</div>
                    </div>
                </div>
            </div>
            
            <div class="report-card">
                <h3>Department Loan Distribution</h3>
                <div class="department-list" id="department-distribution">
                    <p>No data available</p>
                </div>
            </div>
            
            <div class="report-card">
                <h3>Loan Metrics</h3>
                <div class="report-stats">
                    <div class="stat-item">
                        <div class="stat-value" id="avg-amount">$0</div>
                        <div class="stat-label">Avg. Loan Amount</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="most-common-type">N/A</div>
                        <div class="stat-label">Most Common Loan Type</div>
                    </div>
                </div>
            </div>
            
            <div class="report-card">
                <h3>Loan Processing Times</h3>
                <div class="report-stats">
                    <div class="stat-item">
                        <div class="stat-value" id="avg-processing">0</div>
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
                        <td colspan="7" style="text-align: center;">Generate a report to see detailed loan data</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
        // CSRF Token for API requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let departments = [];
        
        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeDateRange();
            fetchDepartments();
            generateReport(); // Load initial report
        });
        
        function periodChanged() {
            const period = document.getElementById('period').value;
            const dateRangeElements = document.querySelectorAll('.date-range');
            
            if (period === 'custom') {
                dateRangeElements.forEach(el => el.style.display = 'block');
            } else {
                dateRangeElements.forEach(el => el.style.display = 'none');
            }
        }
        
        function initializeDateRange() {
            const today = new Date();
            const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            
            document.getElementById('start-date').valueAsDate = startOfMonth;
            document.getElementById('end-date').valueAsDate = today;
        }
        
        function resetFilters() {
            document.getElementById('period').value = 'monthly';
            document.getElementById('department').value = '';
            initializeDateRange();
            periodChanged();
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
                departments = data;
                
                const departmentSelect = document.getElementById('department');
                departmentSelect.innerHTML = '<option value="">All Departments</option>';
                
                departments.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept;
                    option.textContent = dept;
                    departmentSelect.appendChild(option);
                });
            } catch (error) {
                showError('Could not load departments. Please refresh the page.');
                console.error('Error fetching departments:', error);
            }
        }
        
        async function generateReport() {
            showLoader(true);
            clearError();
            
            try {
                const params = new URLSearchParams();
                const period = document.getElementById('period').value;
                params.append('period', period);
                
                if (period === 'custom') {
                    params.append('start_date', document.getElementById('start-date').value);
                    params.append('end_date', document.getElementById('end-date').value);
                }
                
                const department = document.getElementById('department').value;
                if (department) {
                    params.append('department', department);
                }
                
                const response = await fetch(`/api/hr/reports?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
                
                if (!response.ok) throw new Error('Failed to generate report');
                
                const reportData = await response.json();
                updateReportUI(reportData);
                fetchDetailedLoanData(params);
            } catch (error) {
                showError('Error generating report. Please try again.');
                console.error('Report generation error:', error);
            } finally {
                showLoader(false);
            }
        }
        
        async function fetchDetailedLoanData(params) {
            try {
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
        
        function updateReportUI(data) {
            // Update summary counts
            document.getElementById('total-loans').textContent = data.total_applications || 0;
            document.getElementById('approved-loans').textContent = data.approved_applications || 0;
            document.getElementById('pending-loans').textContent = data.pending_applications || 0;
            document.getElementById('rejected-loans').textContent = data.rejected_applications || 0;
            
            // Update average loan amount
            const avgAmount = data.average_loan_amount ? 
                `$${parseFloat(data.average_loan_amount).toFixed(2)}` : '$0';
            document.getElementById('avg-amount').textContent = avgAmount;
            
            // Update most common loan type
            const mostCommonType = data.most_common_loan_type && data.most_common_loan_type.name ? 
                data.most_common_loan_type.name : 'N/A';
            document.getElementById('most-common-type').textContent = mostCommonType;
            
            // Update average processing time
            document.getElementById('avg-processing').textContent = 
                data.avg_processing_days ? data.avg_processing_days.toFixed(1) : '0';
            
            // Update department distribution
            const deptContainer = document.getElementById('department-distribution');
            if (data.department_breakdown && Object.keys(data.department_breakdown).length > 0) {
                let deptHTML = '';
                Object.entries(data.department_breakdown).forEach(([dept, count]) => {
                    deptHTML += `
                        <div class="dept-item clickable" onclick="filterByDepartment('${dept}')">
                            <span class="dept-name">${dept}</span>
                            <span class="dept-count">${count}</span>
                        </div>
                    `;
                });
                deptContainer.innerHTML = deptHTML;
            } else {
                deptContainer.innerHTML = '<p>No data available</p>';
            }
        }
        
        function updateDetailedLoanTable(loans) {
            const tableBody = document.getElementById('loan-details');
            
            if (!loans || loans.length === 0) {
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
        
        function filterByDepartment(department) {
            document.getElementById('department').value = department;
            generateReport();
        }
        
        function viewLoanDetails(loanId) {
            window.location.href = `/hr/loan-applications?loan_id=${loanId}`;
        }
        
        function exportReportCSV() {
            // Get the table data
            const table = document.querySelector('table');
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
            link.setAttribute('download', `loan_report_${new Date().toISOString().slice(0,10)}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        function showLoader(show) {
            document.getElementById('loader').style.display = show ? 'block' : 'none';
        }
        
        function showError(message) {
            const errorContainer = document.getElementById('error-container');
            errorContainer.innerHTML = `<div class="error-message">${message}</div>`;
        }
        
        function clearError() {
            document.getElementById('error-container').innerHTML = '';
        }
    </script>
</body>
</html>