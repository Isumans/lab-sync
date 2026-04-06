// Test Catalog Table JavaScript

const itemsPerPage = 7;
let currentPage = 1;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize everything in the right order
    initializeTableControls();
    initializePagination();
    // Initial pagination setup
    applyPagination();
});

function initializeTableControls() {
    const searchInput = document.getElementById('test-search');
    const departmentFilter = document.getElementById('department-filter');
    const editButtons = document.querySelectorAll('.action-btn-edit');
    const deleteButtons = document.querySelectorAll('.action-btn-delete');

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentPage = 1;
            applyPagination();
        });
    }

    // Department filter
    if (departmentFilter) {
        departmentFilter.addEventListener('change', function() {
            currentPage = 1;
            applyPagination();
        });
    }

    // Edit buttons
    editButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const row = this.closest('tr');
            openEditModal(row);
        });
    });

    // Delete buttons
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const row = this.closest('tr');
            deleteTest(row);
        });
    });
}

function initializePagination() {
    const prevBtn = document.getElementById('pagination-prev');
    const nextBtn = document.getElementById('pagination-next');
    const paginationNumbers = document.querySelector('.pagination-numbers');

    if (prevBtn) {
        prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                applyPagination();
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const totalPages = getTotalPages();
            if (currentPage < totalPages) {
                currentPage++;
                applyPagination();
            }
        });
    }

    // Page number buttons delegation
    if (paginationNumbers) {
        paginationNumbers.addEventListener('click', function(e) {
            if (e.target.classList.contains('pagination-num')) {
                e.preventDefault();
                currentPage = parseInt(e.target.textContent);
                applyPagination();
            }
        });
    }
}

/**
 * Apply pagination: filter rows based on search/department, then show only current page
 */
function applyPagination() {
    const searchInput = document.getElementById('test-search');
    const departmentFilter = document.getElementById('department-filter');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const departmentTerm = departmentFilter ? departmentFilter.value : '';
    const allRows = Array.from(document.querySelectorAll('.test-catalog-table tbody .test-row'));

    // Step 1: Filter rows based on search and department
    const filteredRows = allRows.filter(row => {
        const testId = (row.dataset.id || '').toLowerCase();
        const testName = (row.dataset.name || '').toLowerCase();
        const libId = (row.dataset.libId || '').toLowerCase();
        const department = (row.dataset.department || '').toLowerCase();

        const matchesSearch = !searchTerm || 
            testId.includes(searchTerm) || 
            testName.includes(searchTerm) || 
            libId.includes(searchTerm);

        const matchesDepartment = !departmentTerm || department === departmentTerm.toLowerCase();

        return matchesSearch && matchesDepartment;
    });

    // Step 2: Calculate total pages
    const totalPages = Math.max(1, Math.ceil(filteredRows.length / itemsPerPage));

    // Reset current page if it exceeds total pages
    if (currentPage > totalPages) {
        currentPage = totalPages;
    }

    // Step 3: Calculate what to show on current page
    const startIdx = (currentPage - 1) * itemsPerPage;
    const endIdx = startIdx + itemsPerPage;

    // Step 4: Hide all rows initially
    allRows.forEach(row => {
        row.style.display = 'none';
    });

    // Step 5: Show only the rows for current page
    filteredRows.slice(startIdx, endIdx).forEach(row => {
        row.style.display = '';
    });

    // Step 6: Show empty state if no results
    const tbody = document.querySelector('.test-catalog-table tbody');
    removeEmptyState();
    
    if (filteredRows.length === 0) {
        const emptyRow = document.createElement('tr');
        emptyRow.className = 'empty-state-row';
        emptyRow.innerHTML = `
            <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                <div style="font-size: 48px; margin-bottom: 10px;">🔍</div>
                <div>No tests found matching your search</div>
            </td>
        `;
        tbody.appendChild(emptyRow);
    }

    // Step 7: Update pagination info
    const displayStart = filteredRows.length ? startIdx + 1 : 0;
    const displayEnd = Math.min(endIdx, filteredRows.length);

    const paginationStart = document.getElementById('pagination-start');
    const paginationEnd = document.getElementById('pagination-end');
    const paginationTotal = document.getElementById('pagination-total');

    if (paginationStart) paginationStart.textContent = displayStart;
    if (paginationEnd) paginationEnd.textContent = displayEnd;
    if (paginationTotal) paginationTotal.textContent = filteredRows.length;

    // Step 8: Update pagination buttons
    updatePaginationButtons(totalPages);
}

function removeEmptyState() {
    const emptyRow = document.querySelector('.empty-state-row');
    if (emptyRow) {
        emptyRow.remove();
    }
}

function getTotalPages() {
    const searchInput = document.getElementById('test-search');
    const departmentFilter = document.getElementById('department-filter');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const departmentTerm = departmentFilter ? departmentFilter.value : '';
    const allRows = Array.from(document.querySelectorAll('.test-catalog-table tbody .test-row'));

    const filteredRows = allRows.filter(row => {
        const testId = (row.dataset.id || '').toLowerCase();
        const testName = (row.dataset.name || '').toLowerCase();
        const libId = (row.dataset.libId || '').toLowerCase();
        const department = (row.dataset.department || '').toLowerCase();

        const matchesSearch = !searchTerm || 
            testId.includes(searchTerm) || 
            testName.includes(searchTerm) || 
            libId.includes(searchTerm);

        const matchesDepartment = !departmentTerm || department === departmentTerm.toLowerCase();

        return matchesSearch && matchesDepartment;
    });

    return Math.max(1, Math.ceil(filteredRows.length / itemsPerPage));
}

function updatePaginationButtons(totalPages) {
    const prevBtn = document.getElementById('pagination-prev');
    const nextBtn = document.getElementById('pagination-next');
    const paginationContainer = document.querySelector('.pagination-numbers');

    // Update prev/next button states
    if (prevBtn) prevBtn.disabled = currentPage === 1;
    if (nextBtn) nextBtn.disabled = currentPage === totalPages;

    if (!paginationContainer) return;

    // Clear existing page buttons
    paginationContainer.innerHTML = '';

    // Generate new page buttons
    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.classList.add('pagination-num');
        if (i === currentPage) {
            btn.classList.add('active');
        }
        btn.textContent = i;
        paginationContainer.appendChild(btn);
    }
}

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
