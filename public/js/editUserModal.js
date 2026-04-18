// Edit User Modal Functionality with Data Attributes

class EditUserModal {
    constructor() {
        this.modal = null;
        this.currentUserId = null;
        this.init();
    }

    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupModal());
        } else {
            setTimeout(() => this.setupModal(), 100);
        }
    }

    setupModal() {
        try {
            let modal = document.getElementById('editUserModal');
            if (!modal) {
                this.createModal();
                modal = document.getElementById('editUserModal');
            }
            this.modal = modal;
            this.attachEventListeners();
        } catch (error) {
            console.error('Error setting up modal:', error);
        }
    }

    createModal() {
        const modalHTML = `
            <div id="editUserModal" class="modal-overlay" aria-hidden="true">
                <div class="modal-container" role="dialog" aria-modal="true" aria-labelledby="editUserModalTitle">
                    <div class="modal-header">
                        <div class="modal-header-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                            <h2 id="editUserModalTitle">Account Information</h2>
                        </div>
                        <button type="button" class="modal-close" id="editUserClose" aria-label="Close">&times;</button>
                    </div>

                    <div id="editUserAlert" class="modal-alert" role="alert"></div>

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

        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modal = document.getElementById('editUserModal');
    }

    attachEventListeners() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('.action-btn-edit[data-user-id]')) {
                e.preventDefault();
                this.handleEditClick(e);
            }
        });

        const cancelBtn = document.getElementById('cancelBtn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.closeModal();
            });
        }

        const closeBtn = document.getElementById('editUserClose');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.closeModal());
        }

        const form = document.getElementById('editUserForm');
        if (form) {
            form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }

        document.addEventListener('change', (e) => {
            if (e.target.name === 'role' && e.target.closest('.role-option')) {
                this.handleRoleChange(e);
            }
        });

        if (this.modal) {
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) {
                    this.closeModal();
                }
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal && this.modal.classList.contains('is-open')) {
                this.closeModal();
            }
        });
    }

    handleEditClick(e) {
        e.preventDefault();

        const button = e.target.closest('.action-btn-edit');
        if (!button) return;

        const userId = button.getAttribute('data-user-id');
        const username = button.getAttribute('data-username');
        const email = button.getAttribute('data-email');
        const role = button.getAttribute('data-role');
        const contactNumber = button.getAttribute('data-contact-number') || '';

        this.populateModalData(userId, username, email, contactNumber, role);
        this.openModal();
    }

    populateModalData(userId, username, email, contactNumber, role) {
        document.getElementById('userId').value = userId;
        document.getElementById('username').value = username;
        document.getElementById('email').value = email;
        document.getElementById('contactNumber').value = contactNumber;

        const modalForm = document.getElementById('editUserForm');
        if (!modalForm) return;

        const roleInputs = modalForm.querySelectorAll('input[name="role"]');
        roleInputs.forEach(input => {
            const isMatch = input.value.toLowerCase() === role.toLowerCase();
            input.checked = isMatch;

            const roleLabel = input.closest('.role-option');
            if (roleLabel) {
                roleLabel.classList.toggle('selected', isMatch);
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

    showAlert(message, type) {
        const alertEl = document.getElementById('editUserAlert');
        if (!alertEl) return;
        alertEl.textContent = message;
        alertEl.className = 'modal-alert ' + (type === 'success' ? 'is-success' : 'is-error');
    }

    hideAlert() {
        const alertEl = document.getElementById('editUserAlert');
        if (!alertEl) return;
        alertEl.textContent = '';
        alertEl.className = 'modal-alert';
    }

    handleFormSubmit(e) {
        e.preventDefault();
        this.hideAlert();

        const formData = new FormData(document.getElementById('editUserForm'));
        const userId = formData.get('user_id');

        const data = {
            user_id: userId,
            username: formData.get('username'),
            email: formData.get('email'),
            contact_number: formData.get('contact_number'),
            role: formData.get('role'),
            action: 'editUser'
        };

        fetch(window.location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(data)
        })
            .then(response => response.text())
            .then(() => {
                this.showAlert('User updated successfully.', 'success');
                setTimeout(() => {
                    this.closeModal();
                    location.reload();
                }, 800);
            })
            .catch(() => {
                this.showAlert('Error updating user. Please try again.', 'error');
            });
    }

    openModal() {
        if (this.modal) {
            this.hideAlert();
            this.modal.classList.add('is-open');
            this.modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }
    }

    closeModal() {
        if (this.modal) {
            this.modal.classList.remove('is-open');
            this.modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }
    }
}

const editUserModal = new EditUserModal();
