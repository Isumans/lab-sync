    
    <div class="partner-labs-header">
        <div class="header-content">
            <h2>Partner Labs</h2>
            <p>Manage external laboratory partners, integration protocols, and outsourcing workflows for specialized diagnostic testing.</p>
        </div>
        <button class="add-user-button"><a href="/lab_sync/index.php?controller=partnerLabController&action=RegisterLab">+ Add Partner Lab</a></button>
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
                    <th>LAB NAME</th>
                    <th>CONTACT PERSON</th>
                    <th>CONTACT NUMBER</th>
                    <th>TOTAL TESTS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($partnerLabs)): ?>
                    <?php foreach ($partnerLabs as $lab): ?>
                        <tr>
                            <td>
                                <div class="lab-info-cell">
                                    <div class="lab-avatar" style="background: #e8f5e9;"><?php echo strtoupper(substr($lab['lab_name'], 0, 2)); ?></div>
                                    <div class="lab-details">
                                        <div class="lab-name"><?php echo htmlspecialchars($lab['lab_name']); ?></div>
                                        <div class="lab-contact"><?php echo htmlspecialchars($lab['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <!-- Contact Person -->
                                <div class="lab-contact-person">
                                    <?php echo htmlspecialchars($lab['contact_person_name']); ?>
                                </div>
                            </td>
                            <td>
                                <!-- Contact Number -->
                                <div class="lab-phone">
                                    <?php echo htmlspecialchars($lab['contact_person_phone']); ?>
                                </div>
                            </td>
                            <td>
                                <!-- Placeholder for total tests or services -->
                                <span class="test-count-badge" style="background: #e3f2fd; color: #1565c0; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;"><?php echo $lab['total_tests'] ?? 0; ?> Tests</span>
                            </td>
                            <td class="user-actions">
                                    <button type="submit" name="view" class="action-btn-view" title="View Details">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M1 8C1 8 3.5 2 8 2C12.5 2 15 8 15 8C15 8 12.5 14 8 14C3.5 14 1 8 1 8Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M8 5C6.34315 5 5 6.34315 5 8C5 9.65685 6.34315 11 8 11C9.65685 11 11 9.65685 11 8C11 6.34315 9.65685 5 8 5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
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
                           </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 20px;">No partner labs found.</td>
                    </tr>
                <?php endif; ?>
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
