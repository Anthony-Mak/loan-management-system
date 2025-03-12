// Function to validate the entire form
function validateForm(event) {
  event.preventDefault();
  
  let isValid = true;
  let firstInvalidField = null;
  
  // Basic required text fields validation
  const requiredFields = [
    { id: 'fullName', name: 'Name in Full' },
    { id: 'idNumber', name: 'ID/Passport/Driver\'s License No.' },
    { id: 'homeAddress', name: 'Physical Home Address' },
    { id: 'email', name: 'Email' },
    { id: 'nextOfKin', name: 'Next of Kin' },
    { id: 'nextOfKinAddress', name: 'Next of Kin Address' }
  ];
  
  // Validate each required field
  requiredFields.forEach(field => {
    const input = document.getElementById(field.id);
    if (!input.value.trim()) {
      showError(input, `${field.name} is required`);
      isValid = false;
      if (!firstInvalidField) firstInvalidField = input;
    } else {
      clearError(input);
    }
  });
  
  // Email validation
  const emailInput = document.getElementById('email');
  if (emailInput.value.trim() && !validateEmail(emailInput.value)) {
    showError(emailInput, 'Please enter a valid email address');
    isValid = false;
    if (!firstInvalidField) firstInvalidField = emailInput;
  }
  
  // Phone number validation
  const phoneInputs = document.querySelectorAll('input[type="tel"]');
  phoneInputs.forEach(input => {
    if (input.value.trim() && !validatePhone(input.value)) {
      showError(input, 'Please enter a valid phone number');
      isValid = false;
      if (!firstInvalidField) firstInvalidField = input;
    } else if (input.value.trim()) {
      clearError(input);
    }
  });
  
  // Loan amount validation
  const loanAmountFigures = document.getElementById('loanAmountFigures');
  if (loanAmountFigures.value.trim()) {
    if (isNaN(loanAmountFigures.value) || parseFloat(loanAmountFigures.value) <= 0) {
      showError(loanAmountFigures, 'Please enter a valid loan amount');
      isValid = false;
      if (!firstInvalidField) firstInvalidField = loanAmountFigures;
    } else {
      clearError(loanAmountFigures);
    }
  }
  
  // If form is not valid, focus on the first invalid field
  if (!isValid && firstInvalidField) {
    firstInvalidField.focus();
    return false;
  }
  
  // If form is valid, submit it
  if (isValid) {
    alert('Form validated successfully!');
    // Uncomment to actually submit the form
    // document.querySelector('form').submit();
  }
  
  return isValid;
}

// Helper function to validate email
function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

// Helper function to validate phone numbers
function validatePhone(phone) {
  // Basic phone validation - adjust regex as needed for your specific requirements
  const re = /^\+?[0-9\s\-\(\)]{8,}$/;
  return re.test(phone);
}

// Function to show error messages
function showError(input, message) {
  // Remove any existing error message
  clearError(input);
  
  // Create error message element
  const errorDiv = document.createElement('div');
  errorDiv.className = 'error-message';
  errorDiv.style.color = 'red';
  errorDiv.style.fontSize = '12px';
  errorDiv.style.marginTop = '4px';
  errorDiv.textContent = message;
  
  // Insert error message after the input
  input.parentNode.appendChild(errorDiv);
  
  // Highlight the input
  input.style.borderColor = 'red';
}

// Function to clear error messages
function clearError(input) {
  // Remove existing error message if any
  const parent = input.parentNode;
  const errorDiv = parent.querySelector('.error-message');
  if (errorDiv) {
    parent.removeChild(errorDiv);
  }
  
  // Reset input style
  input.style.borderColor = '';
}

// Add IDs to form elements for easier selection
function initializeForm() {
  // Add IDs to important form elements
  const nameInput = document.querySelector('tr:nth-child(2) input[type="text"]');
  if (nameInput) nameInput.id = 'fullName';
  
  const idInput = document.querySelector('tr:nth-child(3) input[type="text"]');
  if (idInput) idInput.id = 'idNumber';
  
  const addressInput = document.querySelector('tr:nth-child(8) input[type="text"]');
  if (addressInput) addressInput.id = 'homeAddress';
  
  const emailInput = document.querySelector('input[type="email"]');
  if (emailInput) emailInput.id = 'email';
  
  const nextOfKinInput = document.querySelector('tr:nth-child(14) input[type="text"]');
  if (nextOfKinInput) nextOfKinInput.id = 'nextOfKin';
  
  const nextOfKinAddressInput = document.querySelector('tr:nth-child(15) input[type="text"]');
  if (nextOfKinAddressInput) nextOfKinAddressInput.id = 'nextOfKinAddress';
  
  // Add submit event listener to the form
  const form = document.querySelector('form');
  if (form) {
    form.addEventListener('submit', validateForm);
  }
  
  // Add form styling
  const style = document.createElement('style');
  style.textContent = `
    input:focus, select:focus, textarea:focus {
      outline: none;
      border-color: #4a90e2;
    }
    input.invalid, select.invalid, textarea.invalid {
      border-color: red;
    }
  `;
  document.head.appendChild(style);
}

// Initialize the form when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', initializeForm);