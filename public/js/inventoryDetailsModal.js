document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('inventoryDetailsModal');
    var modalBody = document.getElementById('inventoryDetailsBody');
    var closeBtn = document.getElementById('inventoryDetailsClose');

    if (!modal || !modalBody) {
        return;
    }

    var endpoint = '/lab_sync/index.php?controller=inventoryController&action=getInventoryDetails';
    var activeTrigger = null;

    var loadingHtml = '' +
        '<div class="inventory-details-loading">' +
            '<div class="spinner" aria-hidden="true"></div>' +
            '<p>Loading inventory details...</p>' +
        '</div>';

    function renderError(message) {
        modalBody.innerHTML = '' +
            '<div class="inventory-details-error-state">' +
                '<h3>Unable to load details</h3>' +
                '<p>' + String(message || 'A network error occurred.') + '</p>' +
            '</div>';
    }

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        if (closeBtn) {
            closeBtn.focus();
        }
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        modalBody.innerHTML = '';
        document.body.style.overflow = '';

        if (activeTrigger) {
            activeTrigger.focus();
        }
    }

    function fetchInventoryDetails(inventoryId) {
        modalBody.innerHTML = loadingHtml;
        openModal();

        fetch(endpoint + '&inventory_id=' + encodeURIComponent(inventoryId), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    return response.text().then(function (text) {
                        throw new Error(text || ('Request failed with status ' + response.status + '.'));
                    });
                }
                return response.text();
            })
            .then(function (html) {
                if (!String(html || '').trim()) {
                    throw new Error('The server returned an empty response.');
                }
                modalBody.innerHTML = html;
            })
            .catch(function (error) {
                renderError(error.message || 'A network error occurred.');
                console.error('Error loading inventory details:', error);
            });
    }

    document.addEventListener('click', function (event) {
        var viewButton = event.target.closest('.js-view-inventory-btn');
        if (viewButton) {
            event.preventDefault();
            var inventoryId = viewButton.getAttribute('data-inventory-id');
            if (!inventoryId) {
                return;
            }

            activeTrigger = viewButton;
            fetchInventoryDetails(inventoryId);
            return;
        }

        if (event.target === modal || event.target.closest('#inventoryDetailsClose')) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
});
