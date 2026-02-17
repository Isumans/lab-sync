<div id="partner-labs" class="section" style="display:none;">
    
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
                    <th>EMAIL</th>
                    <th>TOTAL TESTS</th>
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
