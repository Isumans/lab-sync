function filterAppointments(filterType, tabElement, event) {
    event.preventDefault();

    // Fetch filtered appointments from backend
    fetch('/lab_sync/index.php?controller=appointmentsController&action=filterAppointments&filter=' + filterType)
        .then(response => response.json())
        .then(appointments => {
            // Update all tabs' active state
            document.querySelectorAll('.team-tab').forEach(function(tab) {
                tab.classList.remove('active');
            });
            tabElement.classList.add('active');

            // Update the appropriate table
            updateAppointmentTable(filterType, appointments);
        })
        .catch(error => console.error('Error fetching appointments:', error));
}

function updateAppointmentTable(filterType, appointments) {
    let tableBody;
    let tableHTML = '';

    if (filterType === 'all') {
        tableBody = document.querySelector('#all .team-users-table tbody');
        tableHTML = generateAllAppointmentsHTML(appointments);
    } else if (filterType === 'online') {
        tableBody = document.querySelector('#online .team-users-table tbody');
        tableHTML = generateOnlineAppointmentsHTML(appointments);
    } else if (filterType === 'physical') {
        tableBody = document.querySelector('#physical .team-users-table tbody');
        tableHTML = generatePhysicalAppointmentsHTML(appointments);
    }

    if (tableBody) {
        tableBody.innerHTML = tableHTML;
    }

    // Show/hide sections
    document.querySelectorAll('.content-area .section').forEach(sec => {
        sec.style.display = 'none';
    });
    document.getElementById(filterType).style.display = 'block';
}

function generateAllAppointmentsHTML(appointments) {
    if (!appointments || appointments.length === 0) {
        return '<tr><td colspan="5" style="text-align: center; padding: 40px;">No appointments found.</td></tr>';
    }

    return appointments.map(apt => `
        <tr>
            <td><strong>${escapeHtml(apt.appointment_id)}</strong></td>
            <td>${escapeHtml(apt.patient_id)}</td>
            <td style="text-align: center;">${escapeHtml(apt.appointment_date)}</td>
            <td style="text-align: center;">${escapeHtml(apt.appointment_time)}</td>
            <td style="text-align: center;">
                <span class="status-badge ${apt.appointment_id.includes('ONLINE') ? 'status-active' : 'status-inactive'}">
                    ${apt.appointment_id.includes('ONLINE') ? 'Online' : 'Physical'}
                </span>
            </td>
        </tr>
    `).join('');
}

function generateOnlineAppointmentsHTML(appointments) {
    if (!appointments || appointments.length === 0) {
        return '<tr><td colspan="4" style="text-align: center; padding: 40px;">No online appointments found.</td></tr>';
    }

    return appointments.map(apt => `
        <tr>
            <td><strong>${escapeHtml(apt.appointment_id)}</strong></td>
            <td>${escapeHtml(apt.patient_id)}</td>
            <td style="text-align: center;">${escapeHtml(apt.appointment_date)}</td>
            <td style="text-align: center;">${escapeHtml(apt.appointment_time)}</td>
        </tr>
    `).join('');
}

function generatePhysicalAppointmentsHTML(appointments) {
    if (!appointments || appointments.length === 0) {
        return '<tr><td colspan="5" style="text-align: center; padding: 40px;">No physical appointments found.</td></tr>';
    }

    return appointments.map(apt => `
        <tr>
            <td><strong>${escapeHtml(apt.appointment_id)}</strong></td>
            <td>${escapeHtml(apt.patient_id)}</td>
            <td style="text-align: center;">${escapeHtml(apt.appointment_date)}</td>
            <td style="text-align: center;">${escapeHtml(apt.appointment_time)}</td>
            <td style="text-align: center;">
                <div style="display: flex; gap: 8px; align-items: center; justify-content: center;">
                    <button type="button" class="action-btn-edit" title="Reschedule" onclick="alert('Reschedule functionality')">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <button type="button" class="action-btn-delete" title="Cancel" onclick="alert('Cancel functionality')">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
