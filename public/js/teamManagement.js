

const itemsPerPage = 4;
let currentPage = 1;

document.addEventListener('DOMContentLoaded', () => {
    const userSearchInput = document.getElementById('userSearchInput');
    const usersTableBody = document.getElementById('usersTableBody');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const teamTabs = document.querySelectorAll('.team-tab');

    if (!usersTableBody) return;

    const allRows = Array.from(usersTableBody.querySelectorAll('.user-row'));

    // Initialize pagination on page load
    applyFilters();
    updatePagination();

    /* 🔍 Search */
    userSearchInput?.addEventListener('input', () => {
        currentPage = 1;
        applyFilters();
        updatePagination();
    });

    /* 🧑‍💼 Role Tabs */
    teamTabs.forEach(tab => {
        tab.addEventListener('click', e => {
            e.preventDefault();
            currentPage = 1;

            teamTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            applyFilters();
            updatePagination();
        });
    });

    /* ◀ ▶ Pagination buttons */
    prevBtn?.addEventListener('click', e => {
        e.preventDefault();
        if (currentPage > 1) {
            currentPage--;
            updatePagination();
        }
    });

    nextBtn?.addEventListener('click', e => {
        e.preventDefault();
        const totalPages = getTotalPages();
        if (currentPage < totalPages) {
            currentPage++;
            updatePagination();
        }
    });

    /* 🔢 Page numbers - DELEGATED EVENT LISTENER since buttons are dynamic */
    document.querySelector('.pagination-numbers')?.addEventListener('click', e => {
        if (e.target.classList.contains('pagination-num')) {
            e.preventDefault();
            currentPage = parseInt(e.target.textContent);
            updatePagination();
        }
    });

    /* ============ FUNCTIONS ============ */

    function applyFilters() {
        const searchTerm = userSearchInput?.value.toLowerCase() || '';
        const activeTab = document.querySelector('.team-tab.active')?.dataset.filter || 'all';

        allRows.forEach(row => {
            const name = row.querySelector('.user-name').textContent.toLowerCase();
            const email = row.querySelector('.user-email').textContent.toLowerCase();
            const role = row.dataset.role;

            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
            const matchesRole = activeTab === 'all' || role === activeTab;

            // Toggle filtered-out class based on filter criteria
            row.classList.toggle('filtered-out', !(matchesSearch && matchesRole));
        });
    }

    function getFilteredRows() {
        return allRows.filter(row => !row.classList.contains('filtered-out'));
    }

    function getTotalPages() {
        return Math.max(1, Math.ceil(getFilteredRows().length / itemsPerPage));
    }

    function updatePagination() {
        const filteredRows = getFilteredRows();
        const totalPages = getTotalPages();

        // Reset current page if it exceeds total pages
        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        // Calculate pagination range
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;

        // Show/hide rows based on pagination
        filteredRows.forEach((row, index) => {
            if (index >= start && index < end) {
                row.style.display = '';  // Show row
            } else {
                row.style.display = 'none';  // Hide row
            }
        });

        // Update pagination counters
        const displayStart = filteredRows.length ? start + 1 : 0;
        const displayEnd = Math.min(end, filteredRows.length);

        document.getElementById('startNum').textContent = displayStart;
        document.getElementById('endNum').textContent = displayEnd;
        document.getElementById('totalNum').textContent = filteredRows.length;

        // Update pagination buttons
        updatePaginationButtons(totalPages);
    }

    function updatePaginationButtons(totalPages) {
        // Update prev/next buttons
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages;

        const paginationContainer = document.querySelector('.pagination-numbers');
        if (!paginationContainer) return;

        paginationContainer.innerHTML = '';

        // Generate page numbers
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
});
