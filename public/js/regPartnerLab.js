document.addEventListener('DOMContentLoaded', function() {
    // === 1. Search Functionality ===
    const searchInput = document.getElementById('testSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const testsGrid = document.getElementById('testsGrid');
            const serviceItems = testsGrid.querySelectorAll('.service-item');

            serviceItems.forEach(item => {
                const testName = item.querySelector('h4').textContent.toLowerCase();
                const testDesc = item.querySelector('p').textContent.toLowerCase();
                
                if (testName.includes(searchValue) || testDesc.includes(searchValue)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // === 2. Form Validation ===
    const form = document.getElementById('partnerLabForm');
    if (form) {
        form.addEventListener('submit', function(event) {
            let isValid = true;
            let firstErrorField = null;
            const errors = [];

            // Helper function to show error (simplified for now, can be enhanced)
            const addError = (field, message) => {
                errors.push(message);
                if (!firstErrorField) firstErrorField = field;
                field.classList.add('error-border'); // Optional: Add a CSS class for styling
            };

            const removeError = (field) => {
                field.classList.remove('error-border');
            };

            // 1. Lab Name
            const labName = form.querySelector('input[name="lab_name"]');
            removeError(labName);
            if (!labName.value.trim()) {
                isValid = false;
                addError(labName, "Lab Name cannot be empty.");
            }

            // 2. Email
            const email = form.querySelector('input[name="email"]');
            removeError(email);
            if (!email.value.trim()) {
                isValid = false;
                addError(email, "Email cannot be empty.");
            } else {
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailPattern.test(email.value.trim())) {
                    isValid = false;
                    addError(email, "Please enter a valid email address.");
                }
            }

            // 3. Contact Person Name
            const contactPerson = form.querySelector('input[name="contact_person"]');
            removeError(contactPerson);
            if (!contactPerson.value.trim()) {
                isValid = false;
                addError(contactPerson, "Contact Person Name cannot be empty.");
            }

            // 4. Phone Number
            const phone = form.querySelector('input[name="phone"]');
            removeError(phone);
            if (!phone.value.trim()) {
                isValid = false;
                addError(phone, "Phone Number cannot be empty.");
            } else {
                // Allow digits, optional +, 8-15 chars
                // Regex: ^\+?\d{8,15}$
                // Note: The user said "Allow digits + optional +".
                // Let's be slightly flexible but strict on length.
                const phonePattern = /^\+?[0-9]{8,15}$/;
                // Remove spaces/dashes for validation check, or strictly validate input?
                // The placeholder shows "+1 (555) 000-0000", so we should probably strip non-digits first for length check,
                // or use a regex that allows spaces/hyphens but validates digit count.
                // Re-reading requirements: "Allow digits + optional +", "Length validation (e.g., 8–15 digits)"
                // This implies logical length, not just string length.
                
                const cleanPhone = phone.value.replace(/[^0-9+]/g, '');
                if (!phonePattern.test(cleanPhone)) {
                     isValid = false;
                     addError(phone, "Phone number must be 8-15 digits and can start with +.");
                }
            }

             // 5. Website (Optional)
            const website = form.querySelector('input[name="website"]');
            removeError(website);
            if (website.value.trim() !== "") {
                try {
                    new URL(website.value.trim());
                } catch (_) {
                    isValid = false;
                    addError(website, "Please enter a valid URL (e.g., https://example.com).");
                }
            }

            // 6. Address
            const address = form.querySelector('textarea[name="address"]');
            removeError(address);
            if (!address.value.trim()) {
                isValid = false;
                addError(address, "Address cannot be empty.");
            }

            // 7. At least one test selected
            const services = form.querySelectorAll('input[name="services[]"]:checked');
            const servicesContainer = document.getElementById('testsGrid'); // For scrolling to
            if (services.length === 0) {
                isValid = false;
                errors.push("Please select at least one test.");
                if (!firstErrorField) firstErrorField = servicesContainer;
            }

            if (!isValid) {
                event.preventDefault();
                alert("Please correct the following errors:\n\n" + errors.join("\n"));
                if (firstErrorField) {
                     firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                     firstErrorField.focus();
                }
            }
        });
    }
});
