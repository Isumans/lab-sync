const searchInput = document.getElementById('patient-search');
const suggestionsBox = document.getElementById('patient-suggestions');
const searchBy = document.getElementById('patient-search-by');
const patientIdInput = document.getElementById('patient_id');
const createAppointmentForm = document.getElementById('createAppointmentForm');

// Trigger search when typing
searchInput.addEventListener('input', async () => {
    const query = searchInput.value.trim();
    const type = searchBy.value;

    if (query.length < 2) {
        suggestionsBox.style.display = 'none';
        return;
    }

    try {
        // Fetch results from PHP backend
        const response = await fetch(`/lab_sync/index.php?controller=appointmentsController&action=searchPatients&type=${type}&query=${query}`);
        const patients = await response.json();

        // Sort alphabetically
        patients.sort((a, b) => a.name.localeCompare(b.name));

        // Display results
        if (patients.length > 0) {
            suggestionsBox.innerHTML = patients.map(p => `
                <div class="suggestion-item" data-id="${p.id}" data-name="${p.name}" data-email="${p.email}">
                    ${p.name} (${p.email})
                </div>
            `).join('');
            suggestionsBox.style.display = 'block';
        } else {
            suggestionsBox.innerHTML = `<div class="suggestion-item">No results found</div>`;
            suggestionsBox.style.display = 'block';
        }
    } catch (err) {
        console.error("Error fetching patient data:", err);
    }
});

// Handle click on suggestion
suggestionsBox.addEventListener('click', (e) => {
    if (e.target.classList.contains('suggestion-item') && e.target.dataset.id) {
        searchInput.value = e.target.dataset.name;
        // set hidden patient_id input
        if (patientIdInput) {
            patientIdInput.value = e.target.dataset.id;
        }
        suggestionsBox.style.display = 'none';
        // Show success indicator
        searchInput.style.borderColor = '#4CAF50';
    }
});

// Close suggestions when clicking elsewhere
document.addEventListener('click', (e) => {
    if (e.target !== searchInput && e.target !== suggestionsBox) {
        suggestionsBox.style.display = 'none';
    }
});

// Form submission validation
if (createAppointmentForm) {
    createAppointmentForm.addEventListener('submit', (e) => {
        if (!patientIdInput || !patientIdInput.value) {
            e.preventDefault();
            alert('Please select a patient from the search results before submitting.');
            searchInput.focus();
            searchInput.style.borderColor = '#f44336';
            return false;
        }
        // Reset border color on successful validation
        searchInput.style.borderColor = '';
    });
}