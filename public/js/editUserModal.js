// Edit User Modal Functionality with Data Attributes

class EditUserModal {
    constructor() {
        this.modal = null;
        this.currentUserId = null;
        this.init();
    }

    init() {
        // Always wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupModal());
        } else {
            // If DOM is already loaded, setup immediately with a small delay
            setTimeout(() => this.setupModal(), 100);
        }
    }

    setupModal() {
        try {
            // Create modal if it doesn't exist
            let modal = document.getElementById('editUserModal');
            if (!modal) {
                this.createModal();
                modal = document.getElementById('editUserModal');
            }
            this.modal = modal;

            // Attach event listeners - both document level and element level
            this.attachEventListeners();
            console.log('Modal setup completed successfully');
        } catch (error) {
            console.error('Error setting up modal:', error);
        }
    }

    createModal() {
        const modalHTML = `
            <div id="editUserModal" class="modal-overlay">
                <div class="modal-container">
                    <div class="modal-header">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2z"/>
                            <path d="M16 3v4M8 3v4M3 8h18M3 14h6"/>
                        </svg>
                        <h2>Account Information</h2>
                    </div>

                    <form id="editUserForm">
                        <!-- Account Information Section -->
                        <div class="modal-section">
                            <div class="modal-section-title">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                </svg>
                                Account Information
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" name="username" required placeholder="e.g. jdoe_tech">
                                </div>
                                <div class="form-group">
                                    <label for="contactNumber">Contact Number</label>
                                    <input type="tel" id="contactNumber" name="contact_number" placeholder="+1 (555) 000-0000">
                                </div>
                            </div>

                            <div class="form-row full">
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" required placeholder="john.doe@labsystem.com">
                                </div>
                            </div>
                        </div>

                        <!-- Role Assignment Section -->
                        <div class="modal-section">
                            <div class="modal-section-title">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                                    <path d="M12 5v7l6 3.5"/>
                                </svg>
                                Role Assignment
                            </div>

                            <div class="role-options">
                                <label class="role-option" data-role="Admin">
                                    <input type="radio" name="role" value="Admin" required>
                                    <div class="role-info">
                                        <div class="role-name">System Administrator</div>
                                        <div class="role-description">Full system access, managing users, laboratory settings, and security audits.</div>
                                    </div>
                                </label>

                                <label class="role-option" data-role="Receptionist">
                                    <input type="radio" name="role" value="Receptionist" required>
                                    <div class="role-info">
                                        <div class="role-name">Receptionist</div>
                                        <div class="role-description">Manage patient registration, appointment scheduling, and front-desk billing workflows.</div>
                                    </div>
                                </label>

                                <label class="role-option" data-role="Technician">
                                    <input type="radio" name="role" value="Technician" required>
                                    <div class="role-info">
                                        <div class="role-name">Laboratory Technician</div>
                                        <div class="role-description">Direct access to lab results entry, sample processing queues, and equipment calibration logs.</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Hidden field for user_id -->
                        <input type="hidden" id="userId" name="user_id">

                        <!-- Modal Footer -->
                        <div class="modal-footer">
                            <button type="button" class="modal-btn modal-btn-cancel" id="cancelBtn">Cancel</button>
                            <button type="submit" class="modal-btn modal-btn-save">Save User</button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        // Insert modal into the page
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modal = document.getElementById('editUserModal');
    }

    attachEventListeners() {
        // Use event delegation for edit button clicks
        document.addEventListener('click', (e) => {
            if (e.target.closest('.action-btn-edit')) {
                e.preventDefault();
                this.handleEditClick(e);
            }
        });

        // Direct event listeners for modal elements
        const cancelBtn = document.getElementById('cancelBtn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.closeModal();
            });
        }

        // Form submission
        const form = document.getElementById('editUserForm');
        if (form) {
            form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }

        // Role option radio buttons
        document.addEventListener('change', (e) => {
            if (e.target.name === 'role' && e.target.closest('.role-option')) {
                this.handleRoleChange(e);
            }
        });

        // Close modal on overlay click
        if (this.modal) {
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) {
                    this.closeModal();
                }
            });
        }
    }

    handleEditClick(e) {
        e.preventDefault();

        // Get the button element using closest()
        const button = e.target.closest('.action-btn-edit');
        if (!button) return;

        // Get user data from button data attributes
        const userId = button.getAttribute('data-user-id');
        const username = button.getAttribute('data-username');
        const email = button.getAttribute('data-email');
        const role = button.getAttribute('data-role');
        const contactNumber = button.getAttribute('data-contact-number') || '';

        console.log('Opening modal for user:', { userId, username, email, role });

        // Populate modal with user data
        this.populateModalData(userId, username, email, contactNumber, role);

        // Open modal
        this.openModal();
    }

    populateModalData(userId, username, email, contactNumber, role) {
        document.getElementById('userId').value = userId;
        document.getElementById('username').value = username;
        document.getElementById('email').value = email;
        document.getElementById('contactNumber').value = contactNumber;

        // Set role - Scope to the modal form to avoid selecting hidden inputs in the table
        const modalForm = document.getElementById('editUserForm');
        if (!modalForm) return;

        const roleInputs = modalForm.querySelectorAll('input[name="role"]');
        roleInputs.forEach(input => {
            // Case-insensitive comparison
            const isMatch = input.value.toLowerCase() === role.toLowerCase();
            input.checked = isMatch;

            const roleLabel = input.closest('.role-option');
            if (roleLabel) {
                if (isMatch) {
                    roleLabel.classList.add('selected');
                } else {
                    roleLabel.classList.remove('selected');
                }
            }
        });
    }

    handleRoleChange(e) {
        const roleOptions = document.querySelectorAll('.role-option');
        roleOptions.forEach(option => option.classList.remove('selected'));

        const selected = e.target.closest('.role-option');
        if (selected) {
            selected.classList.add('selected');
        }
    }

    handleFormSubmit(e) {
        e.preventDefault();

        const formData = new FormData(document.getElementById('editUserForm'));
        const userId = formData.get('user_id');

        // Create the data object to send
        const data = {
            user_id: userId,
            username: formData.get('username'),
            email: formData.get('email'),
            contact_number: formData.get('contact_number'),
            role: formData.get('role'),
            action: 'editUser'
        };

        // Send data via AJAX
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(data)
        })
            .then(response => response.text())
            .then(result => {
                // Show success message and close modal
                alert('User updated successfully!');
                this.closeModal();
                // Reload the page to reflect changes
                location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating user. Please try again.');
            });
    }

    openModal() {
        if (this.modal) {
            this.modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    closeModal() {
        if (this.modal) {
            this.modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    }
}

// Initialize modal when DOM is ready
const editUserModal = new EditUserModal();
