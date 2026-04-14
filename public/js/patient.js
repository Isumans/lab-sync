function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function initialsFromName(name) {
  const cleaned = String(name || '').trim();
  if (!cleaned) {
    return 'NA';
  }

  const parts = cleaned.split(/\s+/).filter(Boolean);
  if (parts.length === 1) {
    return parts[0].slice(0, 2).toUpperCase();
  }

  return (parts[0][0] + parts[1][0]).toUpperCase();
}

function debounce(fn, delay) {
  let timeoutId;
  return function debounced(...args) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn.apply(this, args), delay);
  };
}

function normalizeDate(value) {
  if (!value) {
    return '';
  }

  const date = new Date(value);
  if (Number.isNaN(date.getTime())) {
    return '';
  }

  return date.toISOString().slice(0, 10);
}

function parseComparableDate(value) {
  const normalized = normalizeDate(value);
  return normalized ? Date.parse(normalized) : Number.NEGATIVE_INFINITY;
}

function formatDateDisplay(value) {
  const normalized = normalizeDate(value);
  if (!normalized) {
    return 'N/A';
  }

  const [year, month, day] = normalized.split('-');
  return `${month}/${day}/${year}`;
}

function animateCount(el, target, duration) {
  const safeTarget = Number(target) || 0;
  if (el.getAttribute('data-animated') === '1') {
    return;
  }

  if (safeTarget <= 0) {
    el.textContent = '0';
    el.setAttribute('data-animated', '1');
    return;
  }

  let startTime = null;
  function step(timestamp) {
    if (!startTime) {
      startTime = timestamp;
    }

    const progress = Math.min((timestamp - startTime) / duration, 1);
    const value = Math.floor(progress * safeTarget);
    el.textContent = String(value);

    if (progress < 1) {
      requestAnimationFrame(step);
    } else {
      el.textContent = String(safeTarget);
      el.setAttribute('data-animated', '1');
    }
  }

  requestAnimationFrame(step);
}

function animateStats() {
  const countNodes = document.querySelectorAll('.countup');
  const percentNodes = document.querySelectorAll('.countup-percent');

  let maxTarget = 0;
  const nodes = [];

  countNodes.forEach((node) => {
    const target = Number(node.getAttribute('data-target')) || 0;
    maxTarget = Math.max(maxTarget, target);
    nodes.push({ node, target });
    node.textContent = '0';
  });

  percentNodes.forEach((node) => {
    const target = Number(node.getAttribute('data-target')) || 0;
    maxTarget = Math.max(maxTarget, target);
    nodes.push({ node, target });
    node.textContent = '0';
  });

  const duration = Math.min(1400, 300 + (maxTarget * 12));
  nodes.forEach(({ node, target }) => animateCount(node, target, duration));
}

document.addEventListener('DOMContentLoaded', () => {
  const config = window.patientTableConfig || {};
  const role = String(config.role || '');
  const patientsRaw = Array.isArray(config.patients) ? config.patients : [];

  const dom = {
    search: document.getElementById('ptSearch'),
    gender: document.getElementById('ptGender'),
    sortBy: document.getElementById('ptSortBy'),
    sortDir: document.getElementById('ptSortDir'),
    dateFrom: document.getElementById('ptDateFrom'),
    dateTo: document.getElementById('ptDateTo'),
    clear: document.getElementById('ptClearBtn'),
    tableBody: document.getElementById('ptTableBody'),
    pagination: document.getElementById('ptPagination'),
    summary: document.getElementById('ptResultSummary'),
    sortableHeaders: document.querySelectorAll('.pt-sortable'),
    editModal: document.getElementById('editModal'),
    editForm: document.getElementById('editPatientForm'),
    closeModal: document.getElementById('editModalClose'),
    cancelModal: document.getElementById('cancelEdit')
  };

  if (!dom.tableBody || !dom.pagination || !dom.summary) {
    return;
  }

  const patients = patientsRaw.map((patient) => ({
    patient_id: Number(patient.patient_id) || 0,
    patient_name: String(patient.patient_name || '').trim(),
    email: String(patient.email || '').trim(),
    contact_number: String(patient.contact_number || '').trim(),
    gender: String(patient.gender || '').trim(),
    gender_key: String(patient.gender || '').trim().toLowerCase(),
    date_of_birth: normalizeDate(patient.date_of_birth)
  }));

  const state = {
    search: '',
    gender: 'all',
    sortBy: 'date_of_birth',
    sortDir: 'desc',
    dateFrom: '',
    dateTo: '',
    page: 1,
    pageSize: 7,
    filteredRows: []
  };

  function updateSortHeaderUi() {
    dom.sortableHeaders.forEach((header) => {
      const key = header.dataset.sort || '';
      header.classList.remove('is-active', 'is-asc', 'is-desc');
      if (key === state.sortBy) {
        header.classList.add('is-active');
        header.classList.add(state.sortDir === 'asc' ? 'is-asc' : 'is-desc');
      }
    });
  }

  function compareValues(a, b, key) {
    if (key === 'patient_id') {
      return (a.patient_id - b.patient_id);
    }

    if (key === 'date_of_birth') {
      return parseComparableDate(a.date_of_birth) - parseComparableDate(b.date_of_birth);
    }

    const left = String(a[key] || '').toLowerCase();
    const right = String(b[key] || '').toLowerCase();
    return left.localeCompare(right);
  }

  function getFilteredRows() {
    const query = state.search.toLowerCase();
    const fromDate = state.dateFrom ? Date.parse(state.dateFrom) : Number.NEGATIVE_INFINITY;
    const toDate = state.dateTo ? Date.parse(state.dateTo) : Number.POSITIVE_INFINITY;

    const rows = patients.filter((patient) => {
      if (state.gender !== 'all' && patient.gender_key !== state.gender) {
        return false;
      }

      const searchable = `${patient.patient_id} ${patient.patient_name} ${patient.email} ${patient.contact_number}`.toLowerCase();
      if (query && !searchable.includes(query)) {
        return false;
      }

      if (state.dateFrom || state.dateTo) {
        const patientDate = parseComparableDate(patient.date_of_birth);
        if (patientDate < fromDate || patientDate > toDate) {
          return false;
        }
      }

      return true;
    });

    rows.sort((left, right) => {
      const base = compareValues(left, right, state.sortBy);
      return state.sortDir === 'asc' ? base : -base;
    });

    return rows;
  }

  function renderRows(rows) {
    if (!rows.length) {
      dom.tableBody.innerHTML = `
        <tr class="rd-empty-row">
          <td colspan="5">No patients found for the selected filters.</td>
        </tr>
      `;
      return;
    }

    dom.tableBody.innerHTML = rows.map((patient) => {
      const initials = escapeHtml(initialsFromName(patient.patient_name));
      const genderClass = patient.gender_key === 'male'
        ? 'status-male'
        : (patient.gender_key === 'female' ? 'status-female' : 'status-inactive');

      return `
        <tr data-id="${escapeHtml(patient.patient_id)}"
          data-name="${escapeHtml(patient.patient_name)}"
          data-email="${escapeHtml(patient.email)}"
          data-contact="${escapeHtml(patient.contact_number)}">
          <td>
            <div class="rd-patient-cell">
              <span class="rd-patient-initials">${initials}</span>
              <div>
                <div>${escapeHtml(patient.patient_name || 'Unknown')}</div>
                <small>${escapeHtml(patient.email || 'No email')}</small>
              </div>
            </div>
          </td>
          <td>
            <span class="status-badge ${genderClass}">${escapeHtml(patient.gender || 'Unknown')}</span>
          </td>
          <td>${escapeHtml(patient.contact_number || 'N/A')}</td>
          <td>${escapeHtml(formatDateDisplay(patient.date_of_birth))}</td>
          <td>
            <div class="user-actions">
              <button type="button" class="action-btn-edit edit-btn" title="Edit" aria-label="Edit patient">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
              </button>
              <button type="button" class="action-btn-delete delete-btn" title="Delete" aria-label="Delete patient">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
              </button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  function renderPagination(totalRows) {
    const totalPages = Math.max(1, Math.ceil(totalRows / state.pageSize));
    state.page = Math.min(state.page, totalPages);
    const page = state.page;

    const buttons = [];
    buttons.push(`<button type="button" class="rd-page-btn" data-page="${page - 1}" ${page <= 1 ? 'disabled' : ''}>&lt;</button>`);

    for (let index = 1; index <= totalPages; index += 1) {
      const active = index === page ? 'is-active' : '';
      buttons.push(`<button type="button" class="rd-page-btn ${active}" data-page="${index}">${index}</button>`);
    }

    buttons.push(`<button type="button" class="rd-page-btn" data-page="${page + 1}" ${page >= totalPages ? 'disabled' : ''}>&gt;</button>`);
    dom.pagination.innerHTML = buttons.join('');
  }

  function renderSummary(totalRows, visibleRowsCount) {
    if (!totalRows) {
      dom.summary.textContent = 'Showing 0 to 0 of 0 patients';
      return;
    }

    const start = ((state.page - 1) * state.pageSize) + 1;
    const end = Math.min(start + visibleRowsCount - 1, totalRows);
    dom.summary.textContent = `Showing ${start} to ${end} of ${totalRows} patients`;
  }

  function render() {
    state.filteredRows = getFilteredRows();
    const totalRows = state.filteredRows.length;
    const start = (state.page - 1) * state.pageSize;
    const pageRows = state.filteredRows.slice(start, start + state.pageSize);

    renderRows(pageRows);
    renderPagination(totalRows);
    renderSummary(totalRows, pageRows.length);
    updateSortHeaderUi();
  }

  function resetAndRender() {
    state.page = 1;
    render();
  }

  if (dom.search) {
    dom.search.addEventListener('input', debounce((event) => {
      state.search = String(event.target.value || '').trim();
      resetAndRender();
    }, 220));
  }

  if (dom.gender) {
    dom.gender.addEventListener('change', (event) => {
      state.gender = String(event.target.value || 'all').toLowerCase();
      resetAndRender();
    });
  }

  if (dom.sortBy) {
    dom.sortBy.addEventListener('change', (event) => {
      state.sortBy = String(event.target.value || 'date_of_birth');
      resetAndRender();
    });
  }

  if (dom.sortDir) {
    dom.sortDir.addEventListener('change', (event) => {
      state.sortDir = String(event.target.value || 'desc');
      resetAndRender();
    });
  }

  if (dom.dateFrom) {
    dom.dateFrom.addEventListener('change', (event) => {
      state.dateFrom = String(event.target.value || '');
      resetAndRender();
    });
  }

  if (dom.dateTo) {
    dom.dateTo.addEventListener('change', (event) => {
      state.dateTo = String(event.target.value || '');
      resetAndRender();
    });
  }

  if (dom.clear) {
    dom.clear.addEventListener('click', () => {
      state.search = '';
      state.gender = 'all';
      state.sortBy = 'date_of_birth';
      state.sortDir = 'desc';
      state.dateFrom = '';
      state.dateTo = '';
      state.page = 1;

      if (dom.search) dom.search.value = '';
      if (dom.gender) dom.gender.value = 'all';
      if (dom.sortBy) dom.sortBy.value = 'date_of_birth';
      if (dom.sortDir) dom.sortDir.value = 'desc';
      if (dom.dateFrom) dom.dateFrom.value = '';
      if (dom.dateTo) dom.dateTo.value = '';

      render();
    });
  }

  dom.sortableHeaders.forEach((header) => {
    header.addEventListener('click', () => {
      const nextSort = String(header.dataset.sort || '');
      if (!nextSort) {
        return;
      }

      if (state.sortBy === nextSort) {
        state.sortDir = state.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        state.sortBy = nextSort;
        state.sortDir = 'asc';
      }

      if (dom.sortBy) dom.sortBy.value = state.sortBy;
      if (dom.sortDir) dom.sortDir.value = state.sortDir;
      resetAndRender();
    });

    header.addEventListener('keydown', (event) => {
      if (event.key !== 'Enter' && event.key !== ' ') {
        return;
      }
      event.preventDefault();
      header.click();
    });
  });

  dom.pagination.addEventListener('click', (event) => {
    const button = event.target.closest('button[data-page]');
    if (!button || button.disabled) {
      return;
    }

    const page = Number(button.dataset.page);
    if (!Number.isFinite(page) || page < 1) {
      return;
    }

    state.page = page;
    render();
  });

  function openModalWithRow(row) {
    if (!dom.editModal || !dom.editForm) {
      return;
    }

    dom.editForm.elements.patient_id.value = row.dataset.id || '';
    dom.editForm.elements.patient_name.value = row.dataset.name || '';
    dom.editForm.elements.patient_email.value = row.dataset.email || '';
    dom.editForm.elements.contact_number.value = row.dataset.contact || '';
    dom.editModal.style.display = 'block';
  }

  dom.tableBody.addEventListener('click', (event) => {
    const row = event.target.closest('tr[data-id]');
    if (!row) {
      return;
    }

    if (event.target.closest('.edit-btn')) {
      openModalWithRow(row);
      return;
    }

    if (event.target.closest('.delete-btn')) {
      const patientId = row.dataset.id || '';
      if (!patientId) {
        return;
      }

      if (!window.confirm('Delete this patient? This action cannot be undone.')) {
        return;
      }

      const form = document.createElement('form');
      form.method = 'post';
      form.action = `/lab_sync/index.php?controller=patientController&action=edit_patient&role=${encodeURIComponent(role)}`;

      const patientIdInput = document.createElement('input');
      patientIdInput.type = 'hidden';
      patientIdInput.name = 'patient_id';
      patientIdInput.value = patientId;
      form.appendChild(patientIdInput);

      const deleteInput = document.createElement('input');
      deleteInput.type = 'hidden';
      deleteInput.name = 'delete';
      deleteInput.value = '1';
      form.appendChild(deleteInput);

      document.body.appendChild(form);
      form.submit();
    }
  });

  function closeModal() {
    if (dom.editModal) {
      dom.editModal.style.display = 'none';
    }
  }

  if (dom.closeModal) {
    dom.closeModal.addEventListener('click', closeModal);
  }

  if (dom.cancelModal) {
    dom.cancelModal.addEventListener('click', closeModal);
  }

  window.addEventListener('click', (event) => {
    if (event.target === dom.editModal) {
      closeModal();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeModal();
    }
  });

  animateStats();
  updateSortHeaderUi();
  render();
});
