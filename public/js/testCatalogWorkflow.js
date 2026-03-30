// Test Catalog Workflow JavaScript

let currentStep = 1;
const totalSteps = 3;
const formData = {};

// Initialize the form
document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
    loadSavedData();
});

function initializeForm() {
    // Set step 1 as active by default
    showStep(1);
}

// Load saved data from localStorage if available
function loadSavedData() {
    const savedData = localStorage.getItem('testCatalogForm');
    if (savedData) {
        try {
            const data = JSON.parse(savedData);
            Object.keys(data).forEach(key => {
                const field = document.querySelector(`[name="${key}"]`);
                if (field) {
                    if (field.type === 'checkbox') {
                        field.checked = data[key];
                    } else {
                        field.value = data[key];
                    }
                }
            });
        } catch (e) {
            console.error('Error loading saved data:', e);
        }
    }
}

// Show specific step
function showStep(step) {
    if (step < 1 || step > totalSteps) return;

    // Hide all steps
    document.querySelectorAll('.workflow-step').forEach(s => {
        s.classList.remove('active');
    });

    // Show current step
    const currentStepElement = document.getElementById(`step-${step}`);
    if (currentStepElement) {
        currentStepElement.classList.add('active');
    }

    // Update progress indicators
    updateProgressIndicators(step);

    currentStep = step;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Update progress indicators
function updateProgressIndicators(activeStep) {
    document.querySelectorAll('.progress-step').forEach((step, index) => {
        const stepNumber = index + 1;
        step.classList.remove('active', 'completed');

        if (stepNumber === activeStep) {
            step.classList.add('active');
        } else if (stepNumber < activeStep) {
            step.classList.add('completed');
        }
    });
}

// Navigate to next step
function nextStep(fromStep) {
    if (validateStep(fromStep)) {
        showStep(fromStep + 1);
        saveFormState();
    }
}

// Navigate to previous step
function prevStep(fromStep) {
    showStep(fromStep - 1);
}

// Validate current step
function validateStep(step) {
    const stepElement = document.getElementById(`step-${step}`);
    if (!stepElement) return false;

    const requiredFields = stepElement.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            field.focus();
            isValid = false;
            showErrorMessage(field, 'This field is required');
        } else {
            field.classList.remove('error');
        }
    });

    return isValid;
}

// Show error message
function showErrorMessage(field, message) {
    // Remove existing error message
    const existingError = field.parentElement.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }

    // Create and show error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.cssText = 'color: #ff4757; font-size: 12px; margin-top: 4px;';
    field.parentElement.appendChild(errorDiv);
}

// Save progress
function saveProgress(step) {
    saveFormState();
    showSuccessAlert('Progress saved successfully!');
}

// Save form state to localStorage
function saveFormState() {
    const form = document.getElementById('test-catalog-form');
    const formData = new FormData(form);
    const data = {};

    formData.forEach((value, key) => {
        if (data[key]) {
            // Handle multiple values for same key
            if (Array.isArray(data[key])) {
                data[key].push(value);
            } else {
                data[key] = [data[key], value];
            }
        } else {
            data[key] = value;
        }
    });

    localStorage.setItem('testCatalogForm', JSON.stringify(data));
}

// Discard draft
function discardDraft() {
    if (confirm('Are you sure you want to discard this draft? All unsaved progress will be lost.')) {
        localStorage.removeItem('testCatalogForm');
        // Reset form
        document.getElementById('test-catalog-form').reset();
        showStep(1);
        showSuccessAlert('Draft discarded successfully!');
    }
}

// Add new unit field
function addNewUnit() {
    const container = document.getElementById('units-container');
    const unitRow = document.createElement('div');
    unitRow.className = 'unit-row';
    unitRow.innerHTML = `
        <div class="form-group">
            <label>UNIT NAME</label>
            <input type="text" name="unit_names[]" placeholder="e.g., mg/dL">
        </div>
        <div class="form-group">
            <label>CONVERSION FACTOR</label>
            <input type="number" name="conversion_factors[]" placeholder="Optional" step="0.01">
        </div>
        <button type="button" class="btn-remove-unit" onclick="removeUnit(this)">×</button>
    `;
    container.appendChild(unitRow);
}

// Remove unit field
function removeUnit(button) {
    button.parentElement.remove();
}

// Add new reference range
function addNewRange() {
    const container = document.getElementById('reference-ranges-container');
    const rangeTable = container.querySelector('.range-table');

    const rangeRow = document.createElement('div');
    rangeRow.className = 'range-row';
    rangeRow.innerHTML = `
        <div class="range-cell">
            <select name="range_gender[]">
                <option value="">All</option>
                <option value="M">Male</option>
                <option value="F">Female</option>
            </select>
        </div>
        <div class="range-cell">
            <div class="age-inputs">
                <input type="number" name="range_age_min[]" placeholder="0">
                <span>-</span>
                <input type="number" name="range_age_max[]" placeholder="99">
            </div>
        </div>
        <div class="range-cell">
            <div class="ref-inputs">
                <input type="number" name="range_min[]" placeholder="70" step="0.01">
                <span>-</span>
                <input type="number" name="range_max[]" placeholder="110" step="0.01">
            </div>
        </div>
        <div class="range-cell">
            <input type="number" name="critical_range[]" placeholder="50" step="0.01">
        </div>
        <button type="button" class="btn-remove-range" onclick="removeRange(this)">×</button>
    `;

    rangeTable.appendChild(rangeRow);
}

// Remove reference range
function removeRange(button) {
    button.parentElement.remove();
}

// Show success alert
function showSuccessAlert(message) {
    const alert = document.createElement('div');
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #1bc47d;
        color: white;
        padding: 15px 20px;
        border-radius: 4px;
        box-shadow: 0 4px 12px rgba(27, 196, 125, 0.3);
        font-size: 14px;
        font-weight: 600;
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    alert.textContent = message;
    document.body.appendChild(alert);

    setTimeout(() => {
        alert.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => alert.remove(), 300);
    }, 3000);
}

// Show error alert
function showErrorAlert(message) {
    const alert = document.createElement('div');
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #ff4757;
        color: white;
        padding: 15px 20px;
        border-radius: 4px;
        box-shadow: 0 4px 12px rgba(255, 71, 87, 0.3);
        font-size: 14px;
        font-weight: 600;
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    alert.textContent = message;
    document.body.appendChild(alert);

    setTimeout(() => {
        alert.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => alert.remove(), 300);
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }

    .form-group input.error,
    .form-group textarea.error,
    .form-group select.error {
        border-color: #ff4757 !important;
        background-color: #ffe8e8;
    }

    .error-message {
        color: #ff4757;
        font-size: 12px;
        margin-top: 4px;
    }
`;
document.head.appendChild(style);

// Real-time field validation
document.addEventListener('input', function(e) {
    if (e.target.hasAttribute('required')) {
        if (e.target.value.trim()) {
            e.target.classList.remove('error');
        }
    }
}, true);

// Handle form submission
document.getElementById('test-catalog-form').addEventListener('submit', function(e) {
    // Form will submit normally to the server
    // You can add additional client-side checks here if needed
    console.log('Form submitted');
});

// Clear localStorage when form is successfully submitted
function onFormSuccess() {
    localStorage.removeItem('testCatalogForm');
}
