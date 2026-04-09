/**
 * Inventory Soft Delete Handler
 * Handles soft delete operations for inventory items, categories, and purchases with AJAX
 */

console.log('✓ inventorySoftDelete.js loaded');

document.addEventListener('DOMContentLoaded', function() {
    console.log('✓ DOM Content Loaded - Initializing soft delete handlers');
    // Initialize delete handlers for all delete buttons
    initializeSoftDeleteHandlers();
});

/**
 * Initialize soft delete handlers for all delete buttons
 */
function initializeSoftDeleteHandlers() {
    console.log('Initializing soft delete handlers...');
    
    // Method 1: Find by class selector (most common)
    let deleteButtons = document.querySelectorAll('.action-btn-delete');
    console.log(`Method 1 (.action-btn-delete): Found ${deleteButtons.length} buttons`);
    
    // Method 2: Find by onclick attribute (fallback)
    if (deleteButtons.length === 0) {
        deleteButtons = document.querySelectorAll('[onclick*="showAlertAndSubmit"]');
        console.log(`Method 2 ([onclick*="showAlertAndSubmit"]): Found ${deleteButtons.length} buttons`);
    }
    
    // Method 3: Find by form structure (last resort)
    if (deleteButtons.length === 0) {
        const forms = document.querySelectorAll('form');
        deleteButtons = [];
        forms.forEach(form => {
            const deleteBtn = form.querySelector('[name="delete"]') || 
                             form.querySelector('[value="Delete"]') ||
                             form.querySelector('button[class*="delete"]');
            if (deleteBtn) {
                deleteButtons.push(deleteBtn);
            }
        });
        console.log(`Method 3 (form search): Found ${deleteButtons.length} buttons`);
    }
    
    console.log('All delete buttons:', deleteButtons);
    
    if (deleteButtons.length === 0) {
        console.warn('No delete buttons found');
        return;
    }
    
    // Attach click handlers to all delete buttons
    deleteButtons.forEach((button, index) => {
        console.log(`Attaching handler to delete button ${index + 1}:`, button);
        console.log(`  - Button text: "${button.textContent}"`);
        console.log(`  - Button classes: "${button.className}"`);
        console.log(`  - Button onclick: "${button.onclick}"`);
        
        // Remove any existing onclick handler
        button.onclick = null;
        
        // Add direct click listener
        button.addEventListener('click', function(e) {
            console.log(`\n=== Delete button ${index + 1} clicked ===`);
            console.log('Event:', e);
            console.log('Target:', e.target);
            console.log('Current target:', e.currentTarget);
            
            e.preventDefault();
            e.stopPropagation();
            
            handleSoftDelete(e);
        }, false); // Use capture: false to ensure bubbling works
        
        console.log(`✓ Handler attached to button ${index + 1}`);
    });
    
    console.log(`✓ Initialized ${deleteButtons.length} delete button(s)`);
}

/**
 * Handle soft delete with AJAX - Universal handler for items, categories, purchases
 */
function handleSoftDelete(event) {
    console.log('handleSoftDelete called');
    console.log('Event:', event);
    console.log('Event target:', event.target);
    
    event.preventDefault();
    event.stopPropagation();
    
    // Get the button that was clicked (could be the button itself or an SVG inside)
    let deleteBtn = event.target;
    
    // If we clicked an SVG or other element inside the button, find the button
    if (!deleteBtn.classList || !deleteBtn.classList.contains('action-btn-delete')) {
        deleteBtn = event.target.closest('.action-btn-delete');
    }
    
    console.log('Delete button found:', deleteBtn);
    
    if (!deleteBtn) {
        console.error('Delete button not found');
        alert('Error: Delete button not found');
        return;
    }
    
    // Get the form - try multiple approaches
    let form = deleteBtn.closest('form');
    
    // If not found, try to find from the closest table row
    if (!form) {
        const row = deleteBtn.closest('tr');
        if (row) {
            form = row.closest('form');
        }
    }
    
    // If still not found, try to find any parent form
    if (!form) {
        form = deleteBtn.parentElement;
        while (form && form.tagName !== 'FORM') {
            form = form.parentElement;
        }
    }
    
    console.log('Form found:', form);
    
    if (!form) {
        console.error('Form not found - trying alternative method');
        // Last resort: create form data from button's attributes
        form = deleteBtn.closest('[name]')?.parentElement;
        if (!form) {
            alert('Error: Cannot find form - please try again');
            return;
        }
    }
    
    // Determine entity type and ID
    const inventoryId = form.querySelector('[name="inventory_id"]')?.value;
    const categoryId = form.querySelector('[name="category_id"]')?.value;
    const purchaseId = form.querySelector('[name="purchase_id"]')?.value;
    
    console.log('inventoryId:', inventoryId);
    console.log('categoryId:', categoryId);
    console.log('purchaseId:', purchaseId);
    
    let entityId, entityName, endpoint, paramName;
    
    if (inventoryId) {
        entityId = inventoryId;
        entityName = form.querySelector('[name="item_name"]')?.value || 'item';
        endpoint = 'soft_delete_item';
        paramName = 'inventory_id';
    } else if (categoryId) {
        entityId = categoryId;
        entityName = form.querySelector('[name="category_name"]')?.value || 'category';
        endpoint = 'soft_delete_category';
        paramName = 'category_id';
    } else if (purchaseId) {
        entityId = purchaseId;
        entityName = 'purchase';
        endpoint = 'soft_delete_purchase';
        paramName = 'purchase_id';
    } else {
        console.error('No entity ID found');
        alert('Error: Cannot identify item to delete');
        return;
    }
    
    console.log('Entity:', {entityId, entityName, endpoint, paramName});
    
    if (!entityId) {
        alert('Error: Cannot find entity ID');
        return;
    }
    
    // Show confirmation to user
    const confirmDelete = confirm(`Are you sure you want to delete "${entityName}"? This action is recorded in the system.`);
    
    if (!confirmDelete) {
        console.log('User cancelled deletion');
        return;
    }
    
    // Show loading state
    deleteBtn.disabled = true;
    deleteBtn.style.opacity = '0.6';
    deleteBtn.innerHTML = 'Deleting...';
    
    // Prepare AJAX request
    const formData = new FormData();
    formData.append(paramName, entityId);
    
    console.log('Sending AJAX request to:', endpoint);
    console.log('With parameter:', paramName, '=', entityId);
    
    // Send AJAX request to soft delete endpoint
    fetch('/lab_sync/index.php?controller=inventoryController&action=' + endpoint, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        console.log('Response received:', response);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        
        if (data.success) {
            // Remove the row from the table with animation
            const row = form.closest('tr');
            console.log('Row to remove:', row);
            
            if (row) {
                // Fade out animation
                row.style.transition = 'opacity 0.3s ease, height 0.3s ease';
                row.style.opacity = '0';
                row.style.height = '0';
                
                // Remove from DOM after animation
                setTimeout(() => {
                    row.remove();
                    console.log('Row removed from DOM');
                    
                    // Show success message
                    showNotification(`"${entityName}" deleted successfully`, 'success');
                    
                    // Refresh stats if available
                    if (typeof startCounterAnimation === 'function') {
                        startCounterAnimation();
                    }
                }, 300);
            } else {
                console.log('No row found, reloading page');
                alert('Item deleted successfully');
                location.reload();
            }
        } else {
            console.error('Deletion failed:', data.error);
            showNotification('Error: ' + (data.error || 'Failed to delete item'), 'error');
            deleteBtn.disabled = false;
            deleteBtn.style.opacity = '1';
            deleteBtn.innerHTML = ''; // Restore original button content
        }
    })
    .catch(error => {
        console.error('AJAX Error:', error);
        showNotification('Error: ' + error.message, 'error');
        deleteBtn.disabled = false;
        deleteBtn.style.opacity = '1';
        deleteBtn.innerHTML = ''; // Restore original button content
    });
}

/**
 * Override showAlertAndSubmit for delete operations
 * This function is called from the view when delete button is clicked
 */
function showAlertAndSubmit(event, action) {
    // Check if this is a delete action
    if (action === 'delete') {
        handleSoftDelete(event);
    } else if (action === 'edit') {
        // Let the form submit normally for edit requests
        event.target.closest('form')?.submit();
    }
}

/**
 * Show notification message
 */
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background-color: ${type === 'success' ? '#d1fae5' : '#fee2e2'};
        color: ${type === 'success' ? '#065f46' : '#991b1b'};
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        animation: slideIn 0.3s ease;
        font-weight: 500;
    `;
    notification.textContent = message;
    
    // Add animation CSS
    const style = document.createElement('style');
    if (!document.querySelector('style[data-notification-animation]')) {
        style.setAttribute('data-notification-animation', 'true');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(notification);
    
    // Auto-remove after 4 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}
