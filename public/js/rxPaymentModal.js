(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var config      = window.LAB_SYNC_RX_CONFIG || {};
        var baseUrl     = String(config.baseUrl || '/lab_sync').replace(/\/$/, '');
        var csrfToken   = String(config.csrfToken || '');

        var modal           = document.getElementById('rxPaymentModal');
        var btnPayNow       = document.getElementById('rxBtnPayNow');
        var btnCancel       = document.getElementById('rxBtnCancelPayment');
        var btnCancelBottom = document.getElementById('rxBtnCancelPaymentBottom');
        var pmOrderLines    = document.getElementById('rxPmOrderLines');
        var pmTotal         = document.getElementById('rxPmTotal');
        var pmError         = document.getElementById('rxPmError');
        var spinnerOverlay  = document.getElementById('rxPmSpinnerOverlay');

        if (!modal) return;

        var pendingAppointmentId = null;
        var pendingOrderId       = null;
        var rxData               = null;

        // --- Payhere callbacks ---
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

        // --- Exposed globally so dashboard.php submitRxBooking() can call it ---
        // rxData shape: { tests:[{test_id, test_name, unit_price}], date, time, requestId, homeCollection, address }
        window.openRxPaymentModal = function (data) {
            rxData = data;
            populateModal(data.tests);
            openModal();
        };

        // --- Helpers ---

        function populateModal(tests) {
            var total = tests.reduce(function (s, t) { return s + Number(t.unit_price || 0); }, 0);
            pmOrderLines.innerHTML = tests.map(function (t) {
                return '<div class="pm-line">' +
                    '<span class="pm-line-name">' + escapeHtml(t.test_name || 'Test') + '</span>' +
                    '<span class="pm-line-price">LKR ' + Number(t.unit_price || 0).toFixed(2) + '</span>' +
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

        // --- Pay Now click ---
        btnPayNow && btnPayNow.addEventListener('click', function () {
            hideError();
            showSpinner();

            var payload = {
                test_ids:           rxData.tests.map(function (t) { return String(t.test_id); }),
                appointment_date:   rxData.date,
                appointment_time:   rxData.time,
                home_collection:    rxData.homeCollection ? 1 : 0,
                collection_address: rxData.address || '',
                from_request:       rxData.requestId || 0
            };

            fetch(baseUrl + '/index.php?controller=payment&action=initiate', {
                method: 'POST',
                headers: {
                    'Content-Type':     'application/json',
                    'X-CSRF-Token':     csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
            .then(function (r) { return r.json(); })
            .then(function (resp) {
                hideSpinner();
                if (!resp || resp.status !== 'success') {
                    showError(resp && resp.message ? resp.message : 'Failed to initiate payment.');
                    return;
                }

                pendingAppointmentId = resp.appointment_id;
                pendingOrderId       = resp.order_id;

                if (typeof payhere === 'undefined') {
                    showError('Payment library failed to load. Please refresh and try again.');
                    return;
                }

                payhere.startPayment({
                    sandbox:          resp.sandbox,
                    merchant_id:      resp.merchant_id,
                    return_url:       resp.return_url,
                    cancel_url:       resp.cancel_url,
                    notify_url:       resp.notify_url,
                    order_id:         resp.order_id,
                    items:            'Lab Tests',
                    amount:           resp.amount,
                    currency:         resp.currency,
                    hash:             resp.hash,
                    first_name:       resp.first_name,
                    last_name:        resp.last_name,
                    email:            resp.email,
                    phone:            resp.phone,
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
        btnCancel       && btnCancel.addEventListener('click', closeModal);
        btnCancelBottom && btnCancelBottom.addEventListener('click', closeModal);

        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
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
                    from_request:   rxData ? (rxData.requestId || 0) : 0
                })
            })
            .then(function (r) { return r.json(); })
            .then(function (resp) {
                if (resp && resp.status === 'success') {
                    window.location.reload();
                } else {
                    hideSpinner();
                    showError('Payment received but booking confirmation failed. Contact support. Order ID: ' + orderId);
                }
            })
            .catch(function () {
                hideSpinner();
                showError('Network error after payment. Contact support. Order ID: ' + orderId);
            });
        }
    });
})();
