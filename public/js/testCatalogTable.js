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
        lab_id: String(row.dataset.libId || '').trim(),
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

        const searchText = `${item.test_id} ${item.test_name} ${item.department} ${item.lab_id}`.toLowerCase();
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

    dom.tableBody.addEventListener('click', function (event) {
        if (event.target.closest('.action-btn-edit')) {
            event.preventDefault();
            const row = event.target.closest('tr');
            if (row) {
                openEditModal(row);
            }
            return;
        }

        if (event.target.closest('.action-btn-delete')) {
            event.preventDefault();
            const row = event.target.closest('tr');
            if (row) {
                deleteTest(row);
            }
        }
    });

    renderTestCatalogTable(dom);
});

function openEditModal(row) {
    const testId = row.dataset.id;
    const testName = row.dataset.name;
    const department = row.dataset.department;
    const libId = row.dataset.libId;
    const price = row.dataset.price;
    const description = row.dataset.description;

    // Create a modal dynamically or use existing one
    const modal = document.getElementById('editTestModal');
    if (modal) {
        const form = document.getElementById('editTestForm');
        if (form) {
            form.elements['test_id'].value = testId || '';
            form.elements['test_name'].value = testName || '';
            form.elements['category'].value = department || '';
            form.elements['price'].value = price || '';
            modal.style.display = 'block';
        }
    } else {
        // Show alert if no modal exists
        alert('Edit functionality is not available yet. Please use the system\'s edit interface.');
    }
}

function deleteTest(row) {
    const testId = row.dataset.id;
    const testName = row.dataset.name;

    if (confirm(`Are you sure you want to delete "${testName}"? This action cannot be undone.`)) {
        // Create a hidden form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/lab_sync/index.php?controller=TestCatalog&action=delete_test';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'test_id';
        input.value = testId;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

function viewTest(button) {
    const row = button.closest('tr');
    const testId = row.dataset.id;
    const testName = row.dataset.name;
    const department = row.dataset.department;
    const libId = row.dataset.libId;
    const price = row.dataset.price;
    const description = row.dataset.description;

    // Create a view modal or redirect
    showTestDetailsModal({
        id: testId,
        name: testName,
        department: department,
        libId: libId,
        price: price,
        description: description
    });
}

function showTestDetailsModal(testData) {
    // Create a modal overlay
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    `;

    const modal = document.createElement('div');
    modal.className = 'test-details-modal';
    modal.style.cssText = `
        background: white;
        width: 90%;
        max-width: 600px;
        border-radius: 8px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        padding: 30px;
        animation: modalSlideIn 0.3s ease;
        overflow-y: auto;
        max-height: 90vh;
    `;

    // Add animation styles
    const style = document.createElement('style');
    style.textContent = `
        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);

    modal.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0; color: #333;">${escapeHtml(testData.name)}</h2>
            <button onclick="this.closest('.modal-overlay').remove()" style="background: none; border: none; font-size: 24px; cursor: pointer;">×</button>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div style="background: #f9f9f9; padding: 15px; border-radius: 6px; border-left: 4px solid #1bc47d;">
                <div style="font-size: 11px; font-weight: 700; color: #999; text-transform: uppercase; margin-bottom: 5px;">Test ID</div>
                <div style="font-size: 16px; font-weight: 600; color: #333;">${escapeHtml(testData.id)}</div>
            </div>
            <div style="background: #f9f9f9; padding: 15px; border-radius: 6px; border-left: 4px solid #1bc47d;">
                <div style="font-size: 11px; font-weight: 700; color: #999; text-transform: uppercase; margin-bottom: 5px;">Price</div>
                <div style="font-size: 16px; font-weight: 600; color: #1bc47d;">$${parseFloat(testData.price || 0).toFixed(2)}</div>
            </div>
            <div style="background: #f9f9f9; padding: 15px; border-radius: 6px; border-left: 4px solid #1bc47d;">
                <div style="font-size: 11px; font-weight: 700; color: #999; text-transform: uppercase; margin-bottom: 5px;">Department</div>
                <div style="font-size: 16px; font-weight: 600; color: #333;">${escapeHtml(testData.department)}</div>
            </div>
            <div style="background: #f9f9f9; padding: 15px; border-radius: 6px; border-left: 4px solid #1bc47d;">
                <div style="font-size: 11px; font-weight: 700; color: #999; text-transform: uppercase; margin-bottom: 5px;">Lab ID</div>
                <div style="font-size: 16px; font-weight: 600; color: #333;">${escapeHtml(testData.libId)}</div>
            </div>
        </div>

        ${testData.description ? `
            <div style="margin-bottom: 20px;">
                <div style="font-size: 11px; font-weight: 700; color: #999; text-transform: uppercase; margin-bottom: 8px;">Description</div>
                <p style="margin: 0; color: #666; line-height: 1.6;">${escapeHtml(testData.description)}</p>
            </div>
        ` : ''}

        <div style="display: flex; gap: 10px; margin-top: 25px; border-top: 1px solid #e0e0e0; padding-top: 20px;">
            <button onclick="this.closest('.modal-overlay').remove()" style="flex: 1; padding: 10px; background: #f0f0f0; border: 1px solid #ddd; border-radius: 4px; cursor: pointer; font-weight: 600; transition: all 0.2s; border: none;" onhover="this.style.background='#e0e0e0'">Close</button>
            <button style="flex: 1; padding: 10px; background: #1bc47d; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; transition: all 0.2s;" onclick="editTestFromDetails('${escapeHtml(testData.id)}')">Edit Test</button>
        </div>
    `;

    overlay.appendChild(modal);
    document.body.appendChild(overlay);

    // Close on overlay click
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            overlay.remove();
        }
    });
}

function editTestFromDetails(testId) {
    const row = document.querySelector(`[data-id="${testId}"]`);
    if (row) {
        document.querySelector('.modal-overlay').remove();
        openEditModal(row);
    }
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
