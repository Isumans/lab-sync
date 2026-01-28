// Team Management JavaScript

const itemsPerPage = 4;
let currentPage = 1;

document.addEventListener('DOMContentLoaded', function() {
    const userSearchInput = document.getElementById('userSearchInput');
    const usersTableBody = document.getElementById('usersTableBody');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const teamTabs = document.querySelectorAll('.team-tab');
    
    if (!usersTableBody) return;
    
    let allRows = Array.from(usersTableBody.querySelectorAll('.user-row'));
    
    // Initialize pagination
    updatePagination();
    
    // Search functionality
    if (userSearchInput) {
        userSearchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            currentPage = 1; // Reset to first page on search
            
            allRows.forEach(row => {
                const userName = row.querySelector('.user-name').textContent.toLowerCase();
                const userEmail = row.querySelector('.user-email').textContent.toLowerCase();
                
                if (userName.includes(searchTerm) || userEmail.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            updatePagination();
        });
    }
    
    // Tab filtering functionality
    teamTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const filter = this.dataset.filter;
            currentPage = 1; // Reset to first page on filter change
            
            // Update active tab
            teamTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Filter rows
            allRows.forEach(row => {
                if (filter === 'all') {
                    row.style.display = '';
                } else {
                    const role = row.dataset.role.toLowerCase();
                    row.style.display = role === filter.toLowerCase() ? '' : 'none';
                }
            });
            
            updatePagination();
        });
    });
    
    // Pagination button handlers
    if (prevBtn) {
        prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const visibleRows = getVisibleRows();
            const totalPages = Math.ceil(visibleRows.length / itemsPerPage);
            if (currentPage > 1) {
                currentPage--;
                updatePagination();
            }
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const visibleRows = getVisibleRows();
            const totalPages = Math.ceil(visibleRows.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                updatePagination();
            }
        });
    }
    
    // Page number buttons
    const pageNumberButtons = document.querySelectorAll('.pagination-num');
    pageNumberButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const pageNum = parseInt(this.textContent);
            currentPage = pageNum;
            updatePagination();
        });
    });
});

function getVisibleRows() {
    const usersTableBody = document.getElementById('usersTableBody');
    if (!usersTableBody) return [];
    return Array.from(usersTableBody.querySelectorAll('.user-row')).filter(row => row.style.display !== 'none');
}

function updatePagination() {
    const visibleRows = getVisibleRows();
    const totalPages = Math.ceil(visibleRows.length / itemsPerPage) || 1;
    
    // Reset current page if it exceeds total pages
    if (currentPage > totalPages) {
        currentPage = totalPages;
    }
    
    // Hide all rows first
    visibleRows.forEach(row => row.style.display = 'none');
    
    // Show current page rows
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, visibleRows.length);
    
    for (let i = startIndex; i < endIndex; i++) {
        visibleRows[i].style.display = '';
    }
    
    // Update pagination info
    const startNum = document.getElementById('startNum');
    const endNum = document.getElementById('endNum');
    const totalNum = document.getElementById('totalNum');
    
    if (startNum) startNum.textContent = visibleRows.length > 0 ? startIndex + 1 : 0;
    if (endNum) endNum.textContent = endIndex;
    if (totalNum) totalNum.textContent = visibleRows.length;
    
    // Update pagination buttons
    updatePaginationButtons(totalPages);
}

function updatePaginationButtons(totalPages) {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const pageNumbers = document.querySelectorAll('.pagination-num');
    
    // Update Previous button
    if (prevBtn) {
        prevBtn.disabled = currentPage === 1;
        prevBtn.style.opacity = currentPage === 1 ? '0.5' : '1';
        prevBtn.style.cursor = currentPage === 1 ? 'not-allowed' : 'pointer';
    }
    
    // Update Next button
    if (nextBtn) {
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;
        nextBtn.style.opacity = currentPage === totalPages || totalPages === 0 ? '0.5' : '1';
        nextBtn.style.cursor = currentPage === totalPages || totalPages === 0 ? 'not-allowed' : 'pointer';
    }
    
    // Update page number buttons
    pageNumbers.forEach((btn, index) => {
        const pageNum = index + 1;
        btn.textContent = pageNum;
        
        if (pageNum <= totalPages) {
            btn.style.display = '';
            if (pageNum === currentPage) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        } else {
            btn.style.display = 'none';
        }
    });
}

// View user details function
function viewUserDetails(button) {
    const row = button.closest('.user-row');
    const userName = row.querySelector('.user-name').textContent;
    const userEmail = row.querySelector('.user-email').textContent;
    const userRole = row.querySelector('.role-badge').textContent;
    const userStatus = row.querySelector('.status-badge').textContent;
    
    alert(`User Details:\n\nName: ${userName}\nEmail: ${userEmail}\nRole: ${userRole}\nStatus: ${userStatus}`);
}