const searchInput = document.getElementById('patient-search');
const suggestionsBox = document.getElementById('patient-suggestions');
const searchBy = document.getElementById('patient-search-by');

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
    if (e.target.classList.contains('suggestion-item')) {
        searchInput.value = e.target.dataset.name;
        // set hidden patient_id input if present
        const patientIdInput = document.getElementById('patient_id');
        if (patientIdInput) {
            patientIdInput.value = e.target.dataset.id;
        }
        suggestionsBox.style.display = 'none';
    }
});