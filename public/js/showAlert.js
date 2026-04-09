
function showAlertAndSubmit(event, action) {
    console.log('showAlertAndSubmit called with action:', action);
    
    if (action === 'delete') {
        event.preventDefault();
        event.stopPropagation();
        console.log('Delete action - calling handleSoftDelete');
        // For delete operations, dispatch to soft delete handler if available
        if (typeof handleSoftDelete === 'function') {
            handleSoftDelete(event);
        } else {
            console.error('handleSoftDelete function not found');
        }
    } else if (action === 'edit') {
        // For edit operations, submit the form normally
        console.log('Edit action - submitting form');
        event.preventDefault();
        event.stopPropagation();
        event.target.closest('form')?.submit();
    }
}