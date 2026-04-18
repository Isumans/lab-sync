<!-- Modal Styles and Scripts -->
<link rel="stylesheet" href="/lab_sync/public/editUserModal.css">

<div id="team" class="section Tmain-content">
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

    

    <section class="rd-filter-card">
        <div class="rd-filter-grid" style="grid-template-columns: 2fr 1fr auto;">
            <div class="rd-filter-field rd-filter-field-search">
                <label for="tmSearch">Search Users</label>
                <input id="tmSearch" type="text" placeholder="Search by name or email..." />
            </div>
            <div class="rd-filter-field">
                <label for="tmRoleFilter">Role</label>
                <select id="tmRoleFilter">
                    <option value="all">All Roles</option>
                    <option value="admin">Administrators</option>
                    <option value="technician">Technicians</option>
                    <option value="receptionist">Receptionists</option>
                </select>
            </div>
            <div class="rd-filter-field" style="justify-content: flex-end;">
                <label class="rd-hidden-label">Clear</label>
                <button type="button" class="rd-clear-btn" id="tmClearBtn">Clear Filters</button>
            </div>
        </div>
    </section>
    
    <!-- Users Table -->
    <section class="rd-table-card">
    <div class="rd-table-wrap">
        <table class="rd-table">
            <thead>
                <tr>
                    <th class="rd-sortable is-active is-asc" data-sort="name" data-direction="asc" style="width: 35%;">NAME</th>
                    <th class="rd-sortable" data-sort="role" data-direction="asc" style="width: 20%;text-align: center;">ROLE</th>
                    <th class="rd-sortable" data-sort="status" data-direction="asc" style="width: 20%;text-align: center;">STATUS</th>
                    <th style="width: 25%;text-align: center;">ACTIONS</th>
                </tr>
            </thead>
            <tbody id="usersTableBody">
                <?php if (is_array($users) && count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                        <tr class="user-row"
                            data-role="<?php echo htmlspecialchars(strtolower($user['role'])); ?>"
                            data-name="<?php echo htmlspecialchars(strtolower((string)$user['username'])); ?>"
                            data-email="<?php echo htmlspecialchars(strtolower((string)$user['email'])); ?>"
                            data-status="<?php echo htmlspecialchars(strtolower((string)$user['status'])); ?>">
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
                                    <button type="button" id="edit-user-<?php echo htmlspecialchars($user['user_id']); ?>" class="action-btn-edit" title="Edit" data-user-id="<?php echo htmlspecialchars($user['user_id']); ?>" data-username="<?php echo htmlspecialchars($user['username']); ?>" data-email="<?php echo htmlspecialchars($user['email']); ?>" data-role="<?php echo htmlspecialchars($user['role']); ?>" data-contact-number="">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <button type="button" class="action-btn-delete js-settings-delete-btn" title="Delete"
                                        data-delete-type="user"
                                        data-entity-name="<?php echo htmlspecialchars($user['username']); ?>">
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
    <div class="rd-table-footer">
        <p id="tmShowingText">Showing 0–0 of 0 users</p>
        <div class="rd-pagination" id="tmPagination"></div>
    </div>
    </section>
</div>

<script src="/lab_sync/public/js/editUserModal.js"></script>
