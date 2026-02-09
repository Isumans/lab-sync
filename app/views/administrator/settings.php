<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
    $role=$_GET['user_role'] ?? '';
}

?>


<html>
    <head>
        <title>Settings</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
        <link rel="stylesheet" href="/lab_sync/public/table.css">
        <link rel="stylesheet" href="/lab_sync/public/formStyles.css">
        <link rel="stylesheet" href="/lab_sync/public/teamStyles.css">
    </head>
    <body>
        <!-- Navigation Bar -->
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container">
            <!-- Sidebar -->
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

            <!-- Main Body Section -->
            <main class="main-content">
                <div class="main-content-header">
                    <h1>Settings</h1>
                    <p class="MC-p">Settings-></p>
                </div>
                <div class="nav-bar-container">
                    <div class="nav-bar-line">
                        <a class="navItem active" onclick="showSection('team', event)" href="#">Team</a>

                        <a class="navItem" onclick="showSection('partner-labs', event)" href="#">Partner Labs</a>
                        
                        <a class="navItem" onclick="showSection('configuration', event)" href="#">Lab Configuration</a>

                        <a class="navItem" onclick="showSection('general', event)" href="#">General Settings</a>

                    </div>
                </div>
                
                
                <div id="content-area" class="content-area" >
                    <div id="team" class="section" class="Tmain-content">
                        <!-- Team Section Header with Stats -->
                        <div class="team-header-container">
                            <div class="team-header">
                                <h2>Team Management</h2>
                                <button class="add-user-button"><a href="/lab_sync/index.php?controller=administratorController&action=add_user&role=<?php echo htmlspecialchars($role); ?>">+ Add New User</a></button>
                            </div>

                            <!-- Stats Cards -->
                            <div class="team-stats-grid">
                                <div class="stat-card-team">
                                    <div class="stat-label-team">TOTAL STAFF</div>
                                    <div class="stat-value-team"><?php echo count($users) ?? 0; ?></div>
                                    <div class="stat-change">+2 this month</div>
                                </div>

                                <div class="stat-card-team">
                                    <div class="stat-label-team">ACTIVE NOW</div>
                                    <div class="stat-value-team" style="color: #10b981;"><?php echo count(array_filter($users, fn($u) => $u['status'] === 'Active')) ?? 0; ?></div>
                                    <div class="stat-change" style="color: #10b981;">● Real-time</div>
                                </div>

                                <div class="stat-card-team">
                                    <div class="stat-label-team">PENDING INVITES</div>
                                    <div class="stat-value-team" style="color: #f97316;"><?php echo count(array_filter($users, fn($u) => $u['status'] === 'Inactive')) ?? 0; ?></div>
                                    <div class="stat-change">Expires in 48h</div>
                                </div>
                            </div>
                        </div>

                        

                        <div class="team-controls">
                            <input type="text" class="team-search-bar" placeholder="🔍 Search Users..." id="userSearchInput">
                            <button class="team-filter-button">↓ Filter</button>
                        </div>

                        <div class="nav-bar-container" style="margin-top: 20px; margin-bottom: 20px;">
                            <div class="team-tabs">
                                <button class="team-tab active" data-filter="all">All Users</button>
                                <button class="team-tab" data-filter="admin">Administrators</button>
                                <button class="team-tab" data-filter="technician">Technicians</button>
                                <button class="team-tab" data-filter="receptionist">Receptionists</button>
                            </div>
                        </div> 
                        
                        <!-- Users Table -->
                        <div class="team-table-container">
                            <table class="team-users-table">
                                <thead>
                                    <tr>
                                        <th style="width: 35%;">NAME</th>
                                        <th style="width: 20%;text-align: center;">ROLE</th>
                                        <th style="width: 20%;text-align: center;">STATUS</th>
                                        <th style="width: 25%;text-align: center;">ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody id="usersTableBody">
                                    <?php if (is_array($users) && count($users) > 0): ?>
                                        <?php foreach ($users as $user): ?>
                                            <tr class="user-row" data-role="<?php echo htmlspecialchars(strtolower($user['role'])); ?>">
                                                <td class="user-name-cell">
                                                    <div class="user-avatar"><?php echo strtoupper(substr($user['username'], 0, 2)); ?></div>
                                                    <div class="user-info">
                                                        <div class="user-name"><?php echo htmlspecialchars($user['username']); ?></div>
                                                        <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                                    </div>
                                                </td>
                                                <td class="user-role">
                                                    <span class="role-badge role-<?php echo strtolower($user['role']); ?>"><?php echo ucfirst($user['role']); ?></span>
                                                </td>
                                                <td class="user-status">
                                                    <span class="status-badge <?php echo strtolower($user['status']) === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                                        <?php echo ucfirst($user['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="user-actions" style="align-items: center; justify-content: center;">
                                                    <form method="POST" action="/lab_sync/index.php?controller=administratorController&action=usersByRole&role=<?php echo htmlspecialchars($role); ?>" class="user-action-form">
                                                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
                                                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                                                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                                        <input type="hidden" name="role" value="<?php echo htmlspecialchars($user['role']); ?>">
                                                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($user['status']); ?>">
                                                        <button type="submit" name="edit" class="action-btn-edit" title="Edit" onclick="showAlertAndSubmit(event,'edit')">
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                                <path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </button>
                                                        <button type="submit" name="delete" class="action-btn-delete" title="Delete" onclick="showAlertAndSubmit(event,'delete')">
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                                <path d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" style="text-align: center; padding: 40px;">
                                                <p>No users found. <a href="/lab_sync/index.php?controller=administratorController&action=add_user&role=<?php echo htmlspecialchars($role); ?>">Add your first user</a></p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="team-pagination">
                            <span class="pagination-info">Showing <span id="startNum">1</span> to <span id="endNum">4</span> of <span id="totalNum"><?php echo count($users) ?? 0; ?></span> users</span>
                            <div class="pagination-buttons">
                                <button class="pagination-btn" id="prevBtn">‹</button>
                                <span class="pagination-numbers">
                                    <!-- Dynamic Buttons generated by JS -->
                                </span>
                                <button class="pagination-btn" id="nextBtn">›</button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="partner-labs" class="section" style="display:none;">
                       
                        <div class="partner-labs-header">
                            <div class="header-content">
                                <h2>Partner Labs</h2>
                                <p>Manage external laboratory partners, integration protocols, and outsourcing workflows for specialized diagnostic testing.</p>
                            </div>
                            <button class="add-partner-btn">+ Add Partner Lab</button>
                        </div>

                        <!-- Search and Filters -->
                        <div class="partner-labs-controls">
                            <div class="search-container">
                                <svg class="search-icon" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <circle cx="6.5" cy="6.5" r="5.5" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M10 10L14.5 14.5" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                                <input type="text" class="search-input" placeholder="Search by lab name, contact, or specialization...">
                            </div>
                            <div class="filter-buttons">
                                <select class="filter-select">
                                    <option>All Statuses</option>
                                    <option>Active</option>
                                    <option>Inactive</option>
                                    <option>Pending</option>
                                </select>
                                <button class="filter-btn active-filter">Active</button>
                                <button class="filter-btn">Pending</button>
                            </div>
                        </div>

                        <!-- Partner Labs Table -->
                        <div class="partner-labs-table-container">
                            <table class="partner-labs-table">
                                <thead>
                                    <tr>
                                        <th>LAB INFORMATION</th>
                                        <th>SPECIALIZATION</th>
                                        <th>OUTSOURCING MODE</th>
                                        <th>STATUS</th>
                                        <th>ACTIONS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="lab-info-cell">
                                                <div class="lab-avatar">LH</div>
                                                <div class="lab-details">
                                                    <div class="lab-name">Lanka Hospital Diagnostics</div>
                                                    <div class="lab-contact">contact@lankahospital.lk</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="specialization-badges">
                                                <span class="spec-badge">MOLECULAR</span>
                                                <span class="spec-badge">HISTOPATHOLOGY</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="outsourcing-mode">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M1 8H15M12 5L15 8L12 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                                API Integrated
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge active">Active</span>
                                        </td>
                                        <td>
                                            <button class="action-menu"><span>⋮</span></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="lab-info-cell">
                                                <div class="lab-avatar" style="background: #e8f5e9;">PD</div>
                                                <div class="lab-details">
                                                    <div class="lab-name">Precision Diagnostics</div>
                                                    <div class="lab-contact">logistics@precisionlabs.com</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="specialization-badges">
                                                <span class="spec-badge">GENETICS</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="outsourcing-mode">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M2 3H14C14.55 3 15 3.45 15 4V12C15 12.55 14.55 13 14 13H2C1.45 13 1 12.55 1 12V4C1 3.45 1.45 3 2 3Z" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M1 5H15" stroke="currentColor" stroke-width="1.5"/>
                                                </svg>
                                                Manual Reporting
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge active">Active</span>
                                        </td>
                                        <td>
                                            <button class="action-menu"><span>⋮</span></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="lab-info-cell">
                                                <div class="lab-avatar" style="background: #fff3e0;">CL</div>
                                                <div class="lab-details">
                                                    <div class="lab-name">City Lab Central</div>
                                                    <div class="lab-contact">info@citylabs.io</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="specialization-badges">
                                                <span class="spec-badge">BIOCHEMISTRY</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="outsourcing-mode">
                                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                    <path d="M2 2H14V14H2V2Z" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M2 5H14" stroke="currentColor" stroke-width="1.5"/>
                                                    <path d="M8 5V14" stroke="currentColor" stroke-width="1.5"/>
                                                </svg>
                                                Legacy Fax
                                            </div>
                                        </td>
                                        <td>
                                            <span class="status-badge inactive">Inactive</span>
                                        </td>
                                        <td>
                                            <button class="action-menu"><span>⋮</span></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>


                        <div class="pagination">
                            <span class="pagination-info">Showing 1 to 3 of 12 partner labs</span>
                            <div class="pagination-controls">
                                <button class="pagination-btn">‹</button>
                                <button class="pagination-btn">›</button>
                            </div>
                        </div>


                        <div class="partner-stats-grid">
                            <div class="stat-card">
                                <div class="stat-icon" style="background: #e8f5e9; color: #2e7d32;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                                    </svg>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-label">ACTIVE JOBS</div>
                                    <div class="stat-value">142</div>
                                    <div class="stat-description">Tests currently outsourced</div>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon" style="background: #e3f2fd; color: #1565c0;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                                    </svg>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-label">AVG TAT</div>
                                    <div class="stat-value">1.2 Days</div>
                                    <div class="stat-description">Turnaround time across partners</div>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon" style="background: #f3e5f5; color: #6a1b9a;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                                    </svg>
                                </div>
                                <div class="stat-content">
                                    <div class="stat-label">EST. MONTHLY</div>
                                    <div class="stat-value">$4.2k</div>
                                    <div class="stat-description">Pending partner payouts</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="configuration" class="section" style="display:none;">
                        <form class="config-form">
                            <!-- General Information Section -->
                            <div class="config-section">
                                <h3>General Information</h3>
                                
                                <div class="config-row">
                                    <div class="config-group">
                                        <label>Laboratory Logo</label>
                                        <div class="logo-upload">
                                            <div class="logo-preview">
                                                <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
                                                    <rect width="80" height="80" fill="#F0F0F0" rx="8"/>
                                                    <path d="M40 35C42.2091 35 44 33.2091 44 31C44 28.7909 42.2091 27 40 27C37.7909 27 36 28.7909 36 31C36 33.2091 37.7909 35 40 35Z" fill="#4A5568"/>
                                                    <path d="M40 37C34.4772 37 30 41.4772 30 47V53H50V47C50 41.4772 45.5228 37 40 37Z" fill="#4A5568"/>
                                                </svg>
                                            </div>
                                            <div class="logo-upload-area">
                                                <input type="file" id="logo-input" accept="image/*" style="display:none;">
                                                <label for="logo-input" class="upload-label">
                                                    <p>Click to upload or drag and drop</p>
                                                    <small>PNG, JPG (max 2MB)</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="config-column">
                                        <div class="config-group">
                                            <label for="lab-name">Lab Official Name</label>
                                            <input type="text" id="lab-name" name="lab_name" placeholder="Enter lab official name" value="Metropolis Diagnostics Center">
                                        </div>

                                        <div class="config-group">
                                            <label for="accreditation">Accreditation Number</label>
                                            <input type="text" id="accreditation" name="accreditation" placeholder="Enter accreditation number" value="ISO-15189-2023">
                                        </div>
                                    </div>
                                </div>

                                <div class="config-row">
                                    <div class="config-group full-width">
                                        <label for="address">Physical Address</label>
                                        <textarea id="address" name="address" placeholder="Enter physical address" rows="3">Suite 402, Medical Arts Building, Downtown Metro</textarea>
                                    </div>
                                </div>

                                <div class="config-row">
                                    <div class="config-group">
                                        <label for="phone">Primary Phone</label>
                                        <input type="tel" id="phone" name="phone" placeholder="Enter primary phone" value="+1 (555) 123-4567">
                                    </div>

                                    <div class="config-group">
                                        <label for="email">Official Email</label>
                                        <input type="email" id="email" name="email" placeholder="Enter official email" value="contact@metrodiagnostics.com">
                                    </div>
                                </div>
                            </div>

                            <!-- Operational Hours Section -->
                            <div class="config-section">
                                <div class="operational-header">
                                    <h3>Operational Hours</h3>
                                    <a href="#" class="apply-all-btn">💬 Apply to all days</a>
                                </div>

                                <div class="operational-hours">
                                    <div class="hours-row">
                                        <div class="day-label">Monday - Friday</div>
                                        <div class="time-inputs">
                                            <div class="time-group">
                                                <input type="time" value="08:00">
                                                <span class="time-to">to</span>
                                                <input type="time" value="08:00">
                                            </div>
                                            <label class="toggle">
                                                <input type="checkbox" checked>
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <span class="status-open">Open</span>
                                        </div>
                                    </div>

                                    <div class="hours-row">
                                        <div class="day-label">Saturday</div>
                                        <div class="time-inputs">
                                            <div class="time-group">
                                                <input type="time" value="09:00">
                                                <span class="time-to">to</span>
                                                <input type="time" value="04:00">
                                            </div>
                                            <label class="toggle">
                                                <input type="checkbox" checked>
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <span class="status-open">Open</span>
                                        </div>
                                    </div>

                                    <div class="hours-row">
                                        <div class="day-label">Sunday</div>
                                        <div class="time-inputs">
                                            <div class="time-group">
                                                <input type="time" value="12:00">
                                                <span class="time-to">to</span>
                                                <input type="time" value="12:00">
                                            </div>
                                            <label class="toggle">
                                                <input type="checkbox">
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <span class="status-closed">Closed</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Features Section -->
                            <div class="config-section">
                                <div class="features-grid">
                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M20 10c0 7-3 10-8 10S4 17 4 10 7 0 12 0s8 3 8 10z"></path>
                                            </svg>
                                        </div>
                                        <div class="feature-info">
                                            <h4>Allow Walk-ins</h4>
                                            <p>Patients can visit without appointment</p>
                                        </div>
                                        <label class="toggle">
                                            <input type="checkbox" checked>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>

                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <polyline points="12 6 12 12 16 14"></polyline>
                                            </svg>
                                        </div>
                                        <div class="feature-info">
                                            <h4>Auto-Email Reports</h4>
                                            <p>Send results since verified</p>
                                        </div>
                                        <label class="toggle">
                                            <input type="checkbox">
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="config-actions">
                                <button type="submit" class="save-btn">Save Changes</button>
                                <button type="reset" class="cancel-btn">Cancel</button>
                            </div>
                        </form>
                    </div>
                    <div id="general" class="section" style="display:none;">
                        <form class="config-form">
                            <!-- Notifications Section -->
                            <div class="config-section">
                                <div class="section-header">
                                    <span class="section-icon">🔔</span>
                                    <h3>Notifications</h3>
                                </div>

                                <div class="notification-items">
                                    <div class="notification-item">
                                        <div class="notification-info">
                                            <h4>SMS Alerts</h4>
                                            <p>Send automated text notifications for critical test results.</p>
                                        </div>
                                        <label class="toggle">
                                            <input type="checkbox" checked>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>

                                    <div class="notification-item">
                                        <div class="notification-info">
                                            <h4>Email Reports</h4>
                                            <p>Weekly laboratory performance summaries and batch test reports.</p>
                                        </div>
                                        <label class="toggle">
                                            <input type="checkbox" checked>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Security & Access Section -->
                            <div class="config-section">
                                <div class="section-header">
                                    <span class="section-icon">🔒</span>
                                    <h3>Security & Access</h3>
                                </div>

                                <div class="config-row">
                                    <div class="config-group">
                                        <label for="password-policy">Password Policy</label>
                                        <p class="field-description">Force users to change password regularly.</p>
                                        <select id="password-policy" name="password_policy">
                                            <option>Every 30 Days</option>
                                            <option selected>Every 60 Days</option>
                                            <option>Every 90 Days</option>
                                            <option>Every 180 Days</option>
                                        </select>
                                    </div>

                                    <div class="config-group">
                                        <label for="session-timeout">Session Timeout</label>
                                        <p class="field-description">Inactivity period before automatic logout.</p>
                                        <div class="input-with-unit">
                                            <input type="number" id="session-timeout" name="session_timeout" value="15" min="1" max="480">
                                            <span class="unit">Minutes</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- System Preferences Section -->
                            <div class="config-section">
                                <div class="section-header">
                                    <span class="section-icon">⚙️</span>
                                    <h3>System Preferences</h3>
                                </div>

                                <div class="config-row">
                                    <div class="config-group">
                                        <label for="language">Default Language</label>
                                        <select id="language" name="language">
                                            <option selected>English (US)</option>
                                            <option>English (UK)</option>
                                            <option>Spanish</option>
                                            <option>French</option>
                                            <option>German</option>
                                        </select>
                                    </div>

                                    <div class="config-group">
                                        <label for="timezone">Laboratory Timezone</label>
                                        <select id="timezone" name="timezone">
                                            <option>(GMT-08:00) Pacific Time (US & Canada)</option>
                                            <option>(GMT-07:00) Mountain Time (US & Canada)</option>
                                            <option selected>(GMT-05:00) Eastern Time (US & Canada)</option>
                                            <option>(GMT+00:00) GMT/UTC</option>
                                            <option>(GMT+01:00) Central European Time</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="config-row">
                                    <div class="config-group">
                                        <label for="currency">Currency Format</label>
                                        <select id="currency" name="currency">
                                            <option selected>USD ($)</option>
                                            <option>EUR (€)</option>
                                            <option>GBP (£)</option>
                                            <option>CAD ($)</option>
                                            <option>AUD ($)</option>
                                        </select>
                                    </div>

                                    <div class="config-group">
                                        <label>Date Format</label>
                                        <div class="radio-group">
                                            <label class="radio-option">
                                                <input type="radio" name="date_format" value="dd/mm/yyyy" checked>
                                                <span>DD/MM/YYYY</span>
                                            </label>
                                            <label class="radio-option">
                                                <input type="radio" name="date_format" value="mm/dd/yyyy">
                                                <span>MM/DD/YYYY</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="config-actions">
                                <button type="reset" class="cancel-btn">Cancel</button>
                                <button type="submit" class="save-btn">Save Changes</button>
                            </div>
                        </form>
                    </div>
                        
                </div>
            </main>
        </div>

        <script src="/lab_sync/public/js/showSection.js"></script>
        <script src="/lab_sync/public/js/showAlert.js"></script>
        <script src="/lab_sync/public/js/teamManagement.js"></script>
    </body>
</html>