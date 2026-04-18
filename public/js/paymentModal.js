(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var config      = window.LAB_SYNC_BOOK_CONFIG || {};
        var baseUrl     = String(config.baseUrl || '/lab_sync').replace(/\/$/, '');
        var csrfToken   = String(config.csrfToken || '');
        var fromRequest = Number(config.fromRequest || 0);

        var modal        = document.getElementById('paymentModal');
        var btnPayNow    = document.getElementById('btnPayNow');
        var btnCancel    = document.getElementById('btnCancelPayment');
        var pmOrderLines = document.getElementById('pmOrderLines');
        var pmTotal      = document.getElementById('pmTotal');
        var pmError      = document.getElementById('pmError');
        var spinnerOverlay = document.getElementById('pmSpinnerOverlay');

        if (!modal) return;

        var pendingAppointmentId = null;
        var pendingOrderId       = null;

        // --- Payhere callbacks (must be set before startPayment) ---
        if (typeof payhere !== 'undefined') {
            payhere.onCompleted = function (orderId) {
                pendingOrderId = orderId;
                confirmPayment(orderId);
            };
            payhere.onDismissed = function () {
                hideSpinner();
            };
            payhere.onError = function (error) {
                hideSpinner();
                showError('Payment failed: ' + (error || 'Unknown error'));
            };
        }

        // --- Exposed globally so book.php inline handler can call it ---
        window.openPaymentModal = function (event) {
            if (event) event.preventDefault();

            var selectedTests  = getSelectedTests();
            var date           = document.getElementById('date') ? document.getElementById('date').value : '';
            var time           = document.getElementById('selectedTime') ? document.getElementById('selectedTime').value : '';
            var homeCollection = document.getElementById('homeCollectionToggle') && document.getElementById('homeCollectionToggle').checked;
            var address        = document.getElementById('collectionAddress') ? document.getElementById('collectionAddress').value.trim() : '';

            if (selectedTests.length === 0 || !date || !time) {
                alert('Please select at least one test, date and time.');
                return false;
            }
            if (homeCollection && !address) {
                alert('Please enter a collection address for home sample collection.');
                return false;
            }

            // Format time to HH:MM:SS if needed
            var timeInput = document.getElementById('selectedTime');
            if (timeInput && timeInput.value && timeInput.value.length === 5) {
                timeInput.value = timeInput.value + ':00';
            }

            populateModal(selectedTests);
            openModal();
            return false;
        };

        // --- Internal helpers ---

        function getSelectedTests() {
            return Array.from(document.querySelectorAll('.test-checkbox:checked')).map(function (cb) {
                return {
                    id:    cb.value,
                    name:  cb.dataset.name || 'Test',
                    price: Number(cb.dataset.price || 0)
                };
            });
        }

        function populateModal(tests) {
            var total = tests.reduce(function (s, t) { return s + t.price; }, 0);
            pmOrderLines.innerHTML = tests.map(function (t) {
                return '<div class="pm-line">' +
                    '<span class="pm-line-name">' + escapeHtml(t.name) + '</span>' +
                    '<span class="pm-line-price">LKR ' + t.price.toFixed(2) + '</span>' +
                    '</div>';
            }).join('');
            pmTotal.textContent = 'LKR ' + total.toFixed(2);
            hideError();
            hideSpinner();
        }

        function openModal() {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        function showSpinner() {
            if (spinnerOverlay) spinnerOverlay.classList.add('active');
            if (btnPayNow) btnPayNow.disabled = true;
        }

        function hideSpinner() {
            if (spinnerOverlay) spinnerOverlay.classList.remove('active');
            if (btnPayNow) btnPayNow.disabled = false;
        }

        function showError(msg) {
            if (pmError) {
                pmError.textContent = msg;
                pmError.style.display = 'block';
            }
        }

        function hideError() {
            if (pmError) {
                pmError.textContent = '';
                pmError.style.display = 'none';
            }
        }

        function escapeHtml(str) {
            var d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        }

        function buildPayload() {
            return {
                test_ids:           Array.from(document.querySelectorAll('.test-checkbox:checked')).map(function (cb) { return cb.value; }),
                appointment_date:   document.getElementById('date') ? document.getElementById('date').value : '',
                appointment_time:   document.getElementById('selectedTime') ? document.getElementById('selectedTime').value : '',
                home_collection:    (document.getElementById('homeCollectionToggle') && document.getElementById('homeCollectionToggle').checked) ? 1 : 0,
                collection_address: document.getElementById('collectionAddress') ? document.getElementById('collectionAddress').value.trim() : '',
                from_request:       fromRequest
            };
        }

        // --- Pay Now click ---
        btnPayNow && btnPayNow.addEventListener('click', function () {
            hideError();
            showSpinner();

            fetch(baseUrl + '/index.php?controller=payment&action=initiate', {
                method: 'POST',
                headers: {
                    'Content-Type':     'application/json',
                    'X-CSRF-Token':     csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(buildPayload())
            })
            .then(function (r) { return r.json(); })
            .then(function (payload) {
                hideSpinner();
                if (!payload || payload.status !== 'success') {
                    showError(payload && payload.message ? payload.message : 'Failed to initiate payment.');
                    return;
                }

                pendingAppointmentId = payload.appointment_id;
                pendingOrderId       = payload.order_id;

                if (typeof payhere === 'undefined') {
                    showError('Payment library failed to load. Please refresh the page and try again.');
                    return;
                }

                payhere.startPayment({
                    sandbox:          payload.sandbox,
                    merchant_id:      payload.merchant_id,
                    return_url:       payload.return_url,
                    cancel_url:       payload.cancel_url,
                    notify_url:       payload.notify_url,
                    order_id:         payload.order_id,
                    items:            'Lab Tests',
                    amount:           payload.amount,
                    currency:         payload.currency,
                    hash:             payload.hash,
                    first_name:       payload.first_name,
                    last_name:        payload.last_name,
                    email:            payload.email,
                    phone:            payload.phone,
                    address:          '',
                    city:             '',
                    country:          'Sri Lanka',
                    delivery_address: '',
                    delivery_city:    '',
                    delivery_country: 'Sri Lanka'
                });
            })
            .catch(function () {
                hideSpinner();
                showError('Network error. Please check your connection and try again.');
            });
        });

        // --- Cancel / close ---
        btnCancel && btnCancel.addEventListener('click', closeModal);
        var btnCancelBottom = document.getElementById('btnCancelPaymentBottom');
        btnCancelBottom && btnCancelBottom.addEventListener('click', closeModal);

        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('is-open')) {
                closeModal();
            }
        });

        // --- Confirm after Payhere completes ---
        function confirmPayment(orderId) {
            showSpinner();
            fetch(baseUrl + '/index.php?controller=payment&action=confirmPayment', {
                method: 'POST',
                headers: {
                    'Content-Type':     'application/json',
                    'X-CSRF-Token':     csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    appointment_id: pendingAppointmentId,
                    order_id:       orderId,
                    from_request:   fromRequest
                })
            })
            .then(function (r) { return r.json(); })
            .then(function (payload) {
                if (payload && payload.status === 'success' && payload.redirect) {
                    window.location.href = payload.redirect;
                } else {
                    hideSpinner();
                    showError('Payment was received but booking confirmation failed. Please contact support with your order ID: ' + orderId);
                }
            })
            .catch(function () {
                hideSpinner();
                showError('Network error after payment. Please contact support with your order ID: ' + orderId);
            });
        }
    });
})();
