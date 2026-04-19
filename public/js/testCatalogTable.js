const itemsPerPage = 7;

const tcState = {
    page: 1,
    search: '',
    department: 'all',
    sortBy: 'test_id',
    sortDir: 'asc',
    rows: []
};

function tcDebounce(fn, delay) {
    let timer = null;
    return function debounced(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

function getTcDom() {
    return {
        searchInput: document.getElementById('tcSearch'),
        departmentInput: document.getElementById('tcDepartment'),
        sortByInput: document.getElementById('tcSortBy'),
        sortDirInput: document.getElementById('tcSortDir'),
        clearBtn: document.getElementById('tcClearBtn'),
        tableBody: document.getElementById('tcTableBody'),
        showingText: document.getElementById('tcShowingText'),
        pagination: document.getElementById('tcPagination'),
        sortableHeaders: Array.from(document.querySelectorAll('.test-catalog-table .rd-sortable'))
    };
}

function getRowMeta(row) {
    return {
        node: row,
        test_id: String(row.dataset.id || '').trim(),
        test_name: String(row.dataset.name || '').trim(),
        department: String(row.dataset.department || '').trim(),
        price: Number(row.dataset.price || 0)
    };
}

function tcCompare(left, right, sortBy) {
    if (sortBy === 'price') {
        return left.price - right.price;
    }

    const a = String(left[sortBy] || '').toLowerCase();
    const b = String(right[sortBy] || '').toLowerCase();
    return a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' });
}

function clearEmptyStateRow(tbody) {
    const row = tbody.querySelector('.empty-state-row');
    if (row) {
        row.remove();
    }
}

function renderPagination(totalPages, dom) {
    const buttons = [];
    buttons.push(`<button type="button" class="rd-page-btn" data-page="prev" ${tcState.page <= 1 ? 'disabled' : ''}>&lt;</button>`);

    for (let i = 1; i <= totalPages; i += 1) {
        buttons.push(`<button type="button" class="rd-page-btn ${i === tcState.page ? 'is-active' : ''}" data-page="${i}">${i}</button>`);
    }

    buttons.push(`<button type="button" class="rd-page-btn" data-page="next" ${tcState.page >= totalPages ? 'disabled' : ''}>&gt;</button>`);
    dom.pagination.innerHTML = buttons.join('');
}

function updateSortableHeaderUi(dom) {
    dom.sortableHeaders.forEach((header) => {
        const key = String(header.dataset.sort || '');
        header.classList.remove('is-active', 'is-asc', 'is-desc');
        if (key === tcState.sortBy) {
            header.classList.add('is-active');
            header.classList.add(tcState.sortDir === 'asc' ? 'is-asc' : 'is-desc');
        }
    });

    if (dom.sortByInput) {
        dom.sortByInput.value = tcState.sortBy;
    }
    if (dom.sortDirInput) {
        dom.sortDirInput.value = tcState.sortDir;
    }
}

function renderTestCatalogTable(dom) {
    const tbody = dom.tableBody;
    if (!tbody) {
        return;
    }

    clearEmptyStateRow(tbody);

    const query = tcState.search.toLowerCase();
    const filtered = tcState.rows.filter((item) => {
        if (tcState.department !== 'all' && item.department.toLowerCase() !== tcState.department) {
            return false;
        }

        if (!query) {
            return true;
        }

        const searchText = `${item.test_id} ${item.test_name} ${item.department}`.toLowerCase();
        return searchText.includes(query);
    });

    filtered.sort((a, b) => {
        const base = tcCompare(a, b, tcState.sortBy);
        return tcState.sortDir === 'asc' ? base : -base;
    });

    const totalRows = filtered.length;
    const totalPages = Math.max(1, Math.ceil(totalRows / itemsPerPage));
    tcState.page = Math.min(tcState.page, totalPages);

    const start = (tcState.page - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    const visible = filtered.slice(start, end);

    tcState.rows.forEach((item) => {
        item.node.style.display = 'none';
    });
    visible.forEach((item) => {
        item.node.style.display = '';
    });

    if (!visible.length) {
        const emptyRow = document.createElement('tr');
        emptyRow.className = 'rd-empty-row empty-state-row';
        emptyRow.innerHTML = '<td colspan="6">No tests found matching your current filters.</td>';
        tbody.appendChild(emptyRow);
    }

    const displayStart = totalRows ? start + 1 : 0;
    const displayEnd = totalRows ? Math.min(end, totalRows) : 0;
    dom.showingText.textContent = `Showing ${displayStart}-${displayEnd} of ${totalRows} tests`;

    renderPagination(totalPages, dom);
    updateSortableHeaderUi(dom);
}

document.addEventListener('DOMContentLoaded', function () {
    const dom = getTcDom();
    if (!dom.tableBody || !dom.pagination || !dom.showingText) {
        return;
    }

    tcState.rows = Array.from(dom.tableBody.querySelectorAll('.test-row')).map(getRowMeta);

    if (dom.searchInput) {
        dom.searchInput.addEventListener('input', tcDebounce(function (event) {
            tcState.search = String(event.target.value || '').trim();
            tcState.page = 1;
            renderTestCatalogTable(dom);
        }, 220));
    }

    if (dom.departmentInput) {
        dom.departmentInput.addEventListener('change', function (event) {
            tcState.department = String(event.target.value || 'all').toLowerCase();
            tcState.page = 1;
            renderTestCatalogTable(dom);
        });
    }

    if (dom.sortByInput) {
        dom.sortByInput.addEventListener('change', function (event) {
            tcState.sortBy = String(event.target.value || 'test_id');
            tcState.page = 1;
            renderTestCatalogTable(dom);
        });
    }

    if (dom.sortDirInput) {
        dom.sortDirInput.addEventListener('change', function (event) {
            tcState.sortDir = String(event.target.value || 'asc');
            tcState.page = 1;
            renderTestCatalogTable(dom);
        });
    }

    if (dom.clearBtn) {
        dom.clearBtn.addEventListener('click', function () {
            tcState.search = '';
            tcState.department = 'all';
            tcState.sortBy = 'test_id';
            tcState.sortDir = 'asc';
            tcState.page = 1;

            if (dom.searchInput) dom.searchInput.value = '';
            if (dom.departmentInput) dom.departmentInput.value = 'all';
            if (dom.sortByInput) dom.sortByInput.value = 'test_id';
            if (dom.sortDirInput) dom.sortDirInput.value = 'asc';

            renderTestCatalogTable(dom);
        });
    }

    dom.pagination.addEventListener('click', function (event) {
        const button = event.target.closest('.rd-page-btn');
        if (!button || button.disabled) {
            return;
        }

        const target = button.dataset.page;
        const maxPage = Math.max(1, Math.ceil(tcState.rows.length / itemsPerPage));
        if (target === 'prev') {
            tcState.page = Math.max(1, tcState.page - 1);
        } else if (target === 'next') {
            tcState.page = Math.min(maxPage, tcState.page + 1);
        } else {
            tcState.page = Math.max(1, Number(target || 1));
        }

        renderTestCatalogTable(dom);
    });

    dom.sortableHeaders.forEach((header) => {
        header.addEventListener('click', function () {
            const key = String(header.dataset.sort || 'test_id');
            if (tcState.sortBy === key) {
                tcState.sortDir = tcState.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                tcState.sortBy = key;
                tcState.sortDir = String(header.dataset.direction || 'asc');
            }
            tcState.page = 1;
            renderTestCatalogTable(dom);
        });

        header.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                header.click();
            }
        });
    });

    renderTestCatalogTable(dom);
});

// Legacy stubs — superseded by testCatalogViewModal.js, testCatalogEditModal.js, testCatalogDeleteModal.js
function openEditModal(row) {}
function deleteTest(row) {}
function viewTest(button) {}
function showTestDetailsModal(testData) {}
function editTestFromDetails(testId) {}

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

// Export table data
function exportTableToCSV(filename = 'test-catalog.csv') {
    const table = document.querySelector('.test-catalog-table');
    let csv = [];

    // Add headers
    const headers = Array.from(table.querySelectorAll('thead th'))
        .map(th => th.textContent.trim());
    csv.push(headers.join(','));

    // Add rows
    const rows = table.querySelectorAll('tbody .test-row');
    rows.forEach(row => {
        if (row.style.display !== 'none') {
            const cells = Array.from(row.querySelectorAll('td'))
                .map((td, index) => {
                    // Skip actions column
                    if (index === 5) return '';
                    return '"' + td.textContent.trim().replace(/"/g, '""') + '"';
                });
            csv.push(cells.join(','));
        }
    });

    // Create download link
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
}
