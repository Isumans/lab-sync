    
    <div class="partner-labs-header">
        <div class="header-content">
            <h2>Partner Labs</h2>
            <p>Manage external laboratory partners, integration protocols, and outsourcing workflows for specialized diagnostic testing.</p>
        </div>
        <button class="add-user-button"><a href="/lab_sync/index.php?controller=partnerLabController&action=RegisterLab">+ Add Partner Lab</a></button>
    </div>

    <!-- Search and Filters -->
    <section class="rd-filter-card">
        <div class="rd-filter-grid" style="grid-template-columns: 2fr 1fr auto;">
            <div class="rd-filter-field rd-filter-field-search">
                <label for="plSearch">Search Labs</label>
                <input id="plSearch" type="text" placeholder="Search by lab name, contact, or email..." />
            </div>
            <div class="rd-filter-field">
                <label for="plStatus">Status</label>
                <select id="plStatus">
                    <option value="all">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
            <div class="rd-filter-field" style="justify-content: flex-end;">
                <label class="rd-hidden-label">Clear</label>
                <button type="button" class="rd-clear-btn" id="plClearBtn">Clear Filters</button>
            </div>
        </div>
    </section>

    <!-- Partner Labs Table -->
    <section class="rd-table-card">
    <div class="rd-table-wrap">
        <table class="rd-table">
            <thead>
                <tr>
                    <th class="rd-sortable is-active is-asc" data-sort="lab_name" data-direction="asc">LAB NAME</th>
                    <th class="rd-sortable" data-sort="contact_person" data-direction="asc">CONTACT PERSON</th>
                    <th class="rd-sortable" data-sort="contact_number" data-direction="asc">CONTACT NUMBER</th>
                    <th class="rd-sortable" data-sort="total_tests" data-direction="desc">TOTAL TESTS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody id="plTableBody">
                <?php if (!empty($partnerLabs)): ?>
                    <?php foreach ($partnerLabs as $lab): ?>
                        <?php
                            $statusValue = strtolower(trim((string)($lab['status'] ?? 'active')));
                            $labNameValue = (string)($lab['lab_name'] ?? '');
                            $labEmailValue = (string)($lab['email'] ?? '');
                            $contactPersonValue = (string)($lab['contact_person_name'] ?? '');
                            $contactPhoneValue = (string)($lab['contact_person_phone'] ?? '');
                            $totalTestsValue = (int)($lab['total_tests'] ?? 0);
                        ?>
                        <tr
                            data-status="<?php echo htmlspecialchars($statusValue); ?>"
                            data-lab-name="<?php echo htmlspecialchars(strtolower($labNameValue)); ?>"
                            data-email="<?php echo htmlspecialchars(strtolower($labEmailValue)); ?>"
                            data-contact-person="<?php echo htmlspecialchars(strtolower($contactPersonValue)); ?>"
                            data-contact-number="<?php echo htmlspecialchars(strtolower($contactPhoneValue)); ?>"
                            data-total-tests="<?php echo htmlspecialchars((string)$totalTestsValue); ?>">
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
                                    <a href="/lab_sync/index.php?controller=partnerLabController&action=editLab&lab_id=<?php echo htmlspecialchars((string)($lab['id'] ?? '')); ?>" class="action-btn-edit" title="Edit">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                            <path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                    <button type="button" class="action-btn-delete js-settings-delete-btn" title="Delete"
                                        data-delete-type="lab"
                                        data-entity-id="<?php echo htmlspecialchars((string)($lab['id'] ?? '')); ?>"
                                        data-entity-name="<?php echo htmlspecialchars($lab['lab_name']); ?>">
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
    <div class="rd-table-footer">
        <p id="plShowingText">Showing 0–0 of 0 partner labs</p>
        <div class="rd-pagination" id="plPagination"></div>
    </div>
    </section>


    
