/* script.js */
const steps = document.querySelectorAll('.step');
const progressBar = document.getElementById('progress-bar-fill');
const prevBtn = document.getElementById('prev-btn');
const nextBtn = document.getElementById('next-btn');
const submitBtn = document.getElementById('submit-btn');
let currentStep = 0;

// Update step visibility
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
nextBtn.addEventListener('click', () => {
  if (currentStep < steps.length - 1) {
    currentStep++;
    showStep(currentStep);
  }
});

prevBtn.addEventListener('click', () => {
  if (currentStep > 0) {
    currentStep--;
    showStep(currentStep);
  }
});

// Form Submission
document.getElementById('loan-application-form').addEventListener('submit', (e) => {
  e.preventDefault();
  alert('Loan Application Submitted Successfully!');
});

// Show the first step on load
showStep(currentStep);