document.addEventListener('DOMContentLoaded', function() 
{
    // Get form elements
    const loanApplicationForm = document.getElementById('loan-application-form');
    const steps = document.querySelectorAll('.step');
    const progressBar = document.getElementById('progress-bar-fill');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const submitBtn = document.getElementById('submit-btn');
    const statusCheckForm = document.getElementById('status-check-form');
    let currentStep = 0;
  
    // Update step visibility function
    function showStep(index) {
      steps.forEach((step, i) => {
        step.classList.toggle('active', i === index);
      });
      progressBar.style.width = ((index + 1) / steps.length) * 100 + '%';
      prevBtn.classList.toggle('hidden', index === 0);
      nextBtn.classList.toggle('hidden', index === steps.length - 1);
      submitBtn.classList.toggle('hidden', index !== steps.length - 1);
    }
  
    // Navigation Button Click Handlers
    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        // Validate current step fields before proceeding
        if (validateCurrentStep(currentStep) && currentStep < steps.length - 1) {
          currentStep++;
          showStep(currentStep);
          window.scrollTo(0, 0); // Scroll to top of form
        }
      });
    }
  
    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        if (currentStep > 0) {
          currentStep--;
          showStep(currentStep);
          window.scrollTo(0, 0); // Scroll to top of form
        }
      });
    }
  
    // Form validation function
    function validateCurrentStep(stepIndex) {
      const currentStepElement = steps[stepIndex];
      const inputs = currentStepElement.querySelectorAll('input, select, textarea');
      let isValid = true;
  
      inputs.forEach(input => {
        // Reset previous error messages
        const errorElement = input.nextElementSibling;
        if (errorElement && errorElement.classList.contains('error-message')) {
          errorElement.remove();
        }
  
        // Clear previous error styling
        input.classList.remove('border-red-500');
  
        // Check if the field is required and empty
        if (input.hasAttribute('required') && !input.value.trim()) {
          displayError(input, 'This field is required');
          isValid = false;
          return;
        }
  
        // Validate specific fields based on their types
        switch (input.type) {
          case 'email':
            if (input.value && !validateEmail(input.value)) {
              displayError(input, 'Please enter a valid email address');
              isValid = false;
            }
            break;
          case 'number':
            if (input.value && !validateNumber(input, input.value)) {
              displayError(input, `Please enter a valid number ${input.min ? 'greater than ' + input.min : ''}`);
              isValid = false;
            }
            break;
          case 'date':
            if (input.value && !validateDate(input)) {
              displayError(input, 'Please enter a valid date');
              isValid = false;
            }
            break;
          case 'tel':
            if (input.value && !validatePhone(input.value)) {
              displayError(input, 'Please enter a valid phone number');
              isValid = false;
            }
            break;
        }
  
        // Validate field by name
        if (input.name === 'national_id' && input.value && !validateNationalId(input.value)) {
          displayError(input, 'Please enter a valid National ID');
          isValid = false;
        }
  
        if (input.name === 'salary_net' && input.value) {
          const grossSalary = document.querySelector('input[name="salary_gross"]').value;
          if (parseFloat(input.value) > parseFloat(grossSalary)) {
            displayError(input, 'Net salary cannot be greater than gross salary');
            isValid = false;
          }
        }
      });
  
      return isValid;
    }
  
    // Helper validation functions
    function validateEmail(email) {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return re.test(email);
    }
  
    function validateNumber(input, value) {
      const num = parseFloat(value);
      if (isNaN(num)) return false;
      if (input.min && num < parseFloat(input.min)) return false;
      if (input.max && num > parseFloat(input.max)) return false;
      return true;
    }
  
    function validateDate(input) {
      const value = new Date(input.value);
      if (isNaN(value.getTime())) return false;
      
      // Check if date is before today for DOB
      if (input.name === 'date_of_birth') {
        return value < new Date();
      }
      
      // Check if maturity date is after loan date
      if (input.name === 'maturity_date') {
        const loanDate = new Date(document.querySelector('input[name="loan_date"]').value);
        return value > loanDate;
      }
      
      return true;
    }
  
    function validatePhone(phone) {
      // Basic phone validation - can be customized for specific formats
      const re = /^[+]?[\d\s-]{8,20}$/;
      return re.test(phone);
    }
  
    function validateNationalId(id) {
      // Implementation can be customized for specific country ID formats
      return id.length >= 5 && id.length <= 30;
    }
  
    // Display error message function
    function displayError(inputElement, message) {
      const errorElement = document.createElement('div');
      errorElement.className = 'error-message text-red-500 text-sm mt-1';
      errorElement.textContent = message;
      
      // Insert error message after the input
      inputElement.parentNode.insertBefore(errorElement, inputElement.nextSibling);
      
      // Highlight the input
      inputElement.classList.add('border-red-500');
    }
  
    // Form Submission Handler
    if (loanApplicationForm) {
      loanApplicationForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Validate all steps before submission
        let allValid = true;
        for (let i = 0; i < steps.length; i++) {
          if (!validateCurrentStep(i)) {
            allValid = false;
            currentStep = i;
            showStep(i);
            window.scrollTo(0, 0);
            break;
          }
        }
        
        if (!allValid) {
          return;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
        
        try {
          // Collect form data
          const formData = new FormData(loanApplicationForm);
          
          // Convert checkbox values to boolean
          ['has_zwmb_account', 'had_previous_loan'].forEach(field => {
            formData.set(field, formData.get(field) === 'on' ? true : false);
          });
          
          // Convert to JSON for API submission
          const formDataJson = {};
          formData.forEach((value, key) => {
            formDataJson[key] = value;
          });
          
          // Submit the application
          const response = await fetch('/api/loan/submit', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(formDataJson)
          });
          
          const result = await response.json();
          
          if (response.ok) {
            // Show success message
            const successMessage = document.createElement('div');
            successMessage.className = 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mt-4';
            successMessage.innerHTML = `
              <h3 class="font-bold">Application Submitted Successfully!</h3>
              <p>Your loan application ID is: ${result.loan_id}</p>
              <p>Please save this ID to check your application status later.</p>
            `;
            
            loanApplicationForm.innerHTML = '';
            loanApplicationForm.appendChild(successMessage);
            
            // Hide navigation buttons
            prevBtn.classList.add('hidden');
            nextBtn.classList.add('hidden');
            submitBtn.classList.add('hidden');
          } else {
            throw new Error(result.message || 'Failed to submit application');
          }
        } catch (error) {
          // Show error message
          const errorBanner = document.createElement('div');
          errorBanner.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mt-4';
          errorBanner.innerHTML = `
            <h3 class="font-bold">Error Submitting Application</h3>
            <p>${error.message}</p>
          `;
          
          loanApplicationForm.prepend(errorBanner);
          
          // Reset button state
          submitBtn.disabled = false;
          submitBtn.textContent = 'Submit Application';
          
          // Auto-remove error after 5 seconds
          setTimeout(() => {
            errorBanner.remove();
          }, 5000);
        }
      });
    }
    
    // Application Status Check
    if (statusCheckForm) {
      statusCheckForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const nationalId = document.getElementById('check-national-id').value;
        const loanId = document.getElementById('check-loan-id').value;
        const resultDiv = document.getElementById('status-result');
        
        if (!nationalId || !loanId) {
          resultDiv.innerHTML = `
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
              Please enter both National ID and Loan Application ID
            </div>
          `;
          return;
        }
        
        try {
          const response = await fetch('/api/loan/status', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
              national_id: nationalId,
              loan_id: parseInt(loanId)
            })
          });
          
          const result = await response.json();
          
          if (response.ok) {
            const statusClass = getStatusClass(result.application_status);
            
            resultDiv.innerHTML = `
              <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
                <h3 class="font-bold">Loan Application #${result.loan_id}</h3>
                <p>Status: <span class="${statusClass}">${result.application_status}</span></p>
                <p>Application Date: ${new Date(result.application_date).toLocaleDateString()}</p>
                ${result.processed_date ? `<p>Processed Date: ${new Date(result.processed_date).toLocaleDateString()}</p>` : ''}
              </div>
            `;
          } else {
            throw new Error(result.message || 'Failed to check application status');
          }
        } catch (error) {
          resultDiv.innerHTML = `
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
              ${error.message}
            </div>
          `;
        }
      });
    }
    
    // Helper function for status styling
    function getStatusClass(status) {
      switch (status) {
        case 'Approved':
          return 'text-green-600 font-bold';
        case 'Pending':
          return 'text-yellow-600 font-bold';
        case 'Rejected':
          return 'text-red-600 font-bold';
        default:
          return 'font-bold';
      }
    }
    
    // Load loan types dynamically
    async function loadLoanTypes() {
      const loanTypeSelect = document.querySelector('select[name="loan_type_id"]');
      if (!loanTypeSelect) return;
      
      try {
        const response = await fetch('/api/loan-types');
        const loanTypes = await response.json();
        
        loanTypes.forEach(type => {
          const option = document.createElement('option');
          option.value = type.loan_type_id;
          option.textContent = type.name;
          loanTypeSelect.appendChild(option);
        });
      } catch (error) {
        console.error('Failed to load loan types:', error);
      }
    }
    
    // Load branches dynamically
    async function loadBranches() {
      const branchSelect = document.querySelector('select[name="branch_id"]');
      if (!branchSelect) return;
      
      try {
        const response = await fetch('/api/branches');
        const branches = await response.json();
        
        branches.forEach(branch => {
          const option = document.createElement('option');
          option.value = branch.branch_id;
          option.textContent = branch.name;
          branchSelect.appendChild(option);
        });
      } catch (error) {
        console.error('Failed to load branches:', error);
      }
    }
    
    // Conditional field visibility
    function setupConditionalFields() {
      const hasZwmbAccountField = document.querySelector('input[name="has_zwmb_account"]');
      const zwmbFields = document.getElementById('zwmb-account-fields');
      
      const hadPreviousLoanField = document.querySelector('input[name="had_previous_loan"]');
      const previousLoanFields = document.getElementById('previous-loan-fields');
      
      if (hasZwmbAccountField && zwmbFields) {
        hasZwmbAccountField.addEventListener('change', () => {
          zwmbFields.classList.toggle('hidden', !hasZwmbAccountField.checked);
        });
        
        // Initial setup
        zwmbFields.classList.toggle('hidden', !hasZwmbAccountField.checked);
      }
      
      if (hadPreviousLoanField && previousLoanFields) {
        hadPreviousLoanField.addEventListener('change', () => {
          previousLoanFields.classList.toggle('hidden', !hadPreviousLoanField.checked);
        });
        
        // Initial setup
        previousLoanFields.classList.toggle('hidden', !hadPreviousLoanField.checked);
      }
    }

    // Loan calculator functionality
function setupLoanCalculator() {
    const loanAmountInput = document.querySelector('input[name="loan_amount"]');
    const termMonthsInput = document.querySelector('input[name="term_months"]');
    const calcBtn = document.getElementById('calculate-loan-btn');
    const monthlyPaymentElement = document.getElementById('calculated-monthly-payment');
    const totalPaymentElement = document.getElementById('calculated-total-payment');
    const interestElement = document.getElementById('calculated-interest');
    
    if (calcBtn && loanAmountInput && termMonthsInput) {
      calcBtn.addEventListener('click', () => {
        const amount = parseFloat(loanAmountInput.value) || 0;
        const term = parseInt(termMonthsInput.value) || 1;
        const annualInterestRate = 0.15; // 15% annual interest rate
        const monthlyInterestRate = annualInterestRate / 12;
  
        // Calculate monthly payment using the formula
        const monthlyPayment = (amount * monthlyInterestRate) / 
          (1 - Math.pow(1 + monthlyInterestRate, -term));
        
        const totalPayment = monthlyPayment * term;
        const totalInterest = totalPayment - amount;
  
        // Update display elements
        if (monthlyPaymentElement) {
          monthlyPaymentElement.textContent = `$${monthlyPayment.toFixed(2)}`;
        }
        if (totalPaymentElement) {
          totalPaymentElement.textContent = `$${totalPayment.toFixed(2)}`;
        }
        if (interestElement) {
          interestElement.textContent = `$${totalInterest.toFixed(2)}`;
        }
      });
    }
  }
    
})
    