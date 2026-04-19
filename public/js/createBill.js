(function () {
    var boot = window.__BILLING_BOOTSTRAP || null;
    if (!boot) {
        return;
    }

    var itemsBody = document.getElementById("billItemsBody");
    var paymentGrid = document.getElementById("paymentMethodGrid");
    var amountTenderedInput = document.getElementById("amountTenderedInput");
    var referenceNumberInput = document.getElementById("referenceNumberInput");
    var discountInput = document.getElementById("discountInput");
    var taxPercentInput = document.getElementById("taxPercentInput");

    var summarySubtotal = document.getElementById("summarySubtotal");
    var summaryGrandTotal = document.getElementById("summaryGrandTotal");
    var focusTotalDue = document.getElementById("focusTotalDue");
    var focusAmountPaid = document.getElementById("focusAmountPaid");
    var focusBalanceDue = document.getElementById("focusBalanceDue");
    var previouslyPaidValue = document.getElementById("previouslyPaidValue");
    var remainingPayableValue = document.getElementById("remainingPayableValue");
    var statusBadge = document.getElementById("billingStatusBadge");
    var formMessage = document.getElementById("billingFormMessage");

    var addCustomItemBtn = document.getElementById("addCustomItemBtn");
    var saveDraftBtn = document.getElementById("saveDraftBtn");
    var saveAndPrintBtn = document.getElementById("saveAndPrintBtn");
    var cancelBtn = document.getElementById("cancelBillingBtn");

    var state = {
        billId: Number(boot.bill_id || 0),
        status: String(boot.status || "PENDING").toUpperCase(),
        existingPaid: Number(boot.paid_amount || 0),
        existingTotal: Number(boot.total_amount || 0),
        existingBalance: Number(boot.balance_due || 0),
        paymentMethod: String(boot.payment_method || "CASH").toUpperCase(),
        items: Array.isArray(boot.items) ? boot.items.map(function (item, index) {
            return {
                row_id: "row_" + index,
                test_id: Number(item.test_id || 0),
                test_name: String(item.test_name || ""),
                unit_price: Number(item.unit_price || 0),
                quantity: Math.max(1, Number(item.quantity || 1)),
                selected: item.selected !== false,
                is_custom: !!item.is_custom
            };
        }) : []
    };

    function isPaidLocked() {
        return state.status === "PAID";
    }

    function isSettlementOnlyMode() {
        return state.status === "PARTIALLY_PAID";
    }

    function canEditStructure() {
        return !isPaidLocked() && !isSettlementOnlyMode();
    }

    var initialTendered = Number(boot.amount_tendered || 0);
    amountTenderedInput.value = initialTendered > 0 ? initialTendered.toFixed(2) : "";
    referenceNumberInput.value = String(boot.reference_no || "");
    discountInput.value = Number(boot.discount_amount || 0).toFixed(2);
    taxPercentInput.value = Number(boot.tax_percent || 0).toFixed(2);

    function money(value) {
        return Number(value || 0).toFixed(2);
    }

    function toNonNegativeNumber(value, fallback) {
        var parsed = Number(value);
        if (!isFinite(parsed) || parsed < 0) {
            return fallback;
        }
        return parsed;
    }

    function isValidReferenceNumber(value) {
        if (value === "") {
            return true;
        }
        return /^[A-Za-z0-9_\-\/ ]{1,64}$/.test(value);
    }

    function selectedItems() {
        return state.items.filter(function (item) {
            return item.selected;
        });
    }

    function recalculate() {
        var subtotal = 0;
        selectedItems().forEach(function (item) {
            subtotal += item.unit_price * item.quantity;
        });

        var discount = Math.max(0, Number(discountInput.value || 0));
        if (discount > subtotal) {
            discount = subtotal;
            discountInput.value = money(discount);
        }

        var taxPercent = Math.max(0, Number(taxPercentInput.value || 0));
        var taxable = Math.max(0, subtotal - discount);
        var tax = (taxable * taxPercent) / 100;
        var total = taxable + tax;

        if (isSettlementOnlyMode() && Number(boot.total_amount || 0) > 0) {
            total = Number(boot.total_amount || 0);
            subtotal = Number(boot.subtotal || subtotal);
        }

        var rawTendered = amountTenderedInput.value.trim();
        var normalizedTendered = rawTendered.replace(/,/g, "");
        var parsedTendered = Number(normalizedTendered || 0);
        if (!isFinite(parsedTendered)) {
            parsedTendered = 0;
        }
        var previouslyPaid = 0;
        if (isSettlementOnlyMode() || isPaidLocked()) {
            previouslyPaid = Math.max(0, Number(boot.paid_amount || 0));
        }

        var remainingBeforePayment = Math.max(0, total - previouslyPaid);
        var newPayment = rawTendered === "" ? 0 : Math.max(0, parsedTendered);

        if (isPaidLocked()) {
            newPayment = 0;
            amountTenderedInput.value = "";
        }

        if (newPayment > remainingBeforePayment) {
            newPayment = remainingBeforePayment;
            amountTenderedInput.value = money(remainingBeforePayment);
            formMessage.textContent = "Amount paid cannot be higher than total due.";
        } else if (formMessage.textContent === "Amount paid cannot be higher than total due.") {
            formMessage.textContent = "";
        }

        var amountPaid = previouslyPaid + newPayment;
        var balanceDue = Math.max(0, total - amountPaid);

        summarySubtotal.textContent = money(subtotal);
        summaryGrandTotal.textContent = money(total);
        focusTotalDue.textContent = money(total);
        focusAmountPaid.textContent = money(amountPaid);
        focusBalanceDue.textContent = money(balanceDue);

        if (previouslyPaidValue) {
            previouslyPaidValue.textContent = money(previouslyPaid);
        }
        if (remainingPayableValue) {
            remainingPayableValue.textContent = money(remainingBeforePayment);
        }
    }

    function rowTemplate(item) {
        var checked = item.selected ? "checked" : "";
        var customClass = item.is_custom ? "is-custom-row" : "";
        var structureEditable = canEditStructure();
        var disabledAttr = structureEditable ? "" : "disabled";

        return (
            '<tr class="' + customClass + '" data-row-id="' + item.row_id + '">' +
            '<td><input type="checkbox" class="js-item-check" ' + checked + ' ' + disabledAttr + '></td>' +
            '<td>' +
            (item.is_custom
                ? '<input type="text" class="js-custom-name" value="' + escapeHtml(item.test_name) + '" placeholder="Custom item name" ' + disabledAttr + '>'
                : '<span class="item-name-text">' + escapeHtml(item.test_name) + '</span>') +
            '</td>' +
            '<td>' +
            (item.is_custom
                ? '<input type="number" class="js-custom-price" min="0" step="0.01" value="' + money(item.unit_price) + '" ' + disabledAttr + '>'
                : '<span class="item-price-text">' + money(item.unit_price) + '</span>') +
            '</td>' +
            '<td><span class="item-qty-text">' + Number(item.quantity) + '</span></td>' +
            '<td><strong class="js-row-total">' + money(item.unit_price * item.quantity) + '</strong></td>' +
            '</tr>'
        );
    }

    function escapeHtml(raw) {
        return String(raw)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/\"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function renderRows() {
        itemsBody.innerHTML = state.items.map(rowTemplate).join("");
        bindRows();
        recalculate();
    }

    function bindRows() {
        var rows = itemsBody.querySelectorAll("tr[data-row-id]");
        Array.prototype.forEach.call(rows, function (row) {
            var rowId = row.getAttribute("data-row-id");
            var item = state.items.find(function (candidate) {
                return candidate.row_id === rowId;
            });
            if (!item) {
                return;
            }

            var check = row.querySelector(".js-item-check");
            var rowTotal = row.querySelector(".js-row-total");
            var customName = row.querySelector(".js-custom-name");
            var customPrice = row.querySelector(".js-custom-price");

            if (canEditStructure()) {
                check.addEventListener("change", function () {
                    item.selected = check.checked;
                    recalculate();
                });
            }

            if (customName && canEditStructure()) {
                customName.addEventListener("input", function () {
                    item.test_name = customName.value;
                });
            }

            if (customPrice && canEditStructure()) {
                customPrice.addEventListener("input", function () {
                    item.unit_price = Math.max(0, Number(customPrice.value || 0));
                    rowTotal.textContent = money(item.unit_price * item.quantity);
                    recalculate();
                });
            }
        });
    }

    function updatePaymentSelection() {
        var cards = paymentGrid.querySelectorAll(".payment-method-card");
        Array.prototype.forEach.call(cards, function (card) {
            var method = card.getAttribute("data-method");
            if (method === state.paymentMethod) {
                card.classList.add("is-active");
            } else {
                card.classList.remove("is-active");
            }
        });
    }

    function buildPayload() {
        var entered = toNonNegativeNumber(amountTenderedInput.value, 0);
        var totalDue = Number(focusTotalDue.textContent || 0);
        var previouslyPaid = (isSettlementOnlyMode() || isPaidLocked()) ? Math.max(0, Number(boot.paid_amount || 0)) : 0;
        var remaining = Math.max(0, totalDue - previouslyPaid);
        var discount = toNonNegativeNumber(discountInput.value, 0);
        var taxPercent = toNonNegativeNumber(taxPercentInput.value, 0);
        var reference = String(referenceNumberInput.value || "").trim();

        if (taxPercent > 100) {
            taxPercent = 100;
            taxPercentInput.value = "100.00";
        }

        if (reference.length > 64) {
            reference = reference.slice(0, 64);
            referenceNumberInput.value = reference;
        }

        if (!isValidReferenceNumber(reference)) {
            throw new Error("Reference number contains invalid characters.");
        }

        return {
            appointment_id: Number(boot.appointment_id || 0),
            patient_id: Number(boot.patient_id || 0),
            discount_amount: discount,
            tax_percent: taxPercent,
            amount_tendered: Math.min(entered, remaining),
            payment_method: state.paymentMethod,
            reference_no: reference,
            items: state.items.map(function (item) {
                return {
                    test_id: item.test_id,
                    test_name: item.test_name,
                    unit_price: item.unit_price,
                    quantity: item.quantity,
                    selected: item.selected,
                    is_custom: item.is_custom
                };
            })
        };
    }

    function save(actionName) {
        if (isPaidLocked()) {
            formMessage.textContent = "This bill is fully paid and can no longer be edited.";
            return Promise.reject(new Error("Bill already paid."));
        }

        if (isSettlementOnlyMode() && actionName !== "finalizeBill") {
            formMessage.textContent = "Draft save is not allowed. Please pay the full remaining balance to settle this bill.";
            return Promise.reject(new Error("Draft disabled in settlement mode."));
        }

        if (actionName === "finalizeBill") {
            var enteredAmt = toNonNegativeNumber(amountTenderedInput.value, 0);
            var totalDueAmt = Number(focusTotalDue.textContent || 0);
            var prevPaidAmt = isSettlementOnlyMode() ? Math.max(0, Number(boot.paid_amount || 0)) : 0;
            var remainingAmt = Math.max(0, totalDueAmt - prevPaidAmt);
            if (enteredAmt > 0 && enteredAmt < remainingAmt - 0.001) {
                formMessage.textContent = "Partial payments are not allowed. Please pay the full amount (LKR " + money(remainingAmt) + ") or leave blank to save as pending.";
                return Promise.reject(new Error("Partial payment not allowed."));
            }
        }

        var endpoint = "/lab_sync/index.php?controller=billingController&action=" + encodeURIComponent(actionName);
        formMessage.textContent = "Saving...";

        var payload;
        try {
            payload = buildPayload();
        } catch (error) {
            formMessage.textContent = error.message || "Invalid billing data.";
            return Promise.reject(error);
        }

        return fetch(endpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify(payload)
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (json) {
                if (!json || json.status !== "success") {
                    throw new Error((json && json.message) || "Billing save failed.");
                }
                state.billId = Number((json.data && json.data.bill_id) || 0);
                if (json.data && json.data.status) {
                    state.status = String(json.data.status).toUpperCase();
                    boot.status = state.status;
                    statusBadge.textContent = "STATUS " + String(json.data.status).replace(/_/g, " ");
                }
                if (json.data) {
                    boot.total_amount = Number(json.data.total_amount || 0);
                    boot.paid_amount = Number(json.data.paid_amount || 0);
                    boot.balance_due = Number(json.data.balance_due || 0);
                }

                if (actionName === "finalizeBill") {
                    amountTenderedInput.value = "";
                }

                applyLockMode();
                recalculate();
                formMessage.textContent = json.message || "Saved.";
                return json;
            })
            .catch(function (error) {
                formMessage.textContent = error.message;
                throw error;
            });
    }

    function applyLockMode() {
        var structureLocked = !canEditStructure();
        addCustomItemBtn.disabled = structureLocked;
        discountInput.disabled = structureLocked;
        taxPercentInput.disabled = structureLocked;

        if (isPaidLocked()) {
            amountTenderedInput.disabled = true;
            referenceNumberInput.disabled = true;
            saveDraftBtn.disabled = true;
            saveAndPrintBtn.disabled = true;
            formMessage.textContent = "This bill is fully paid. Editing is disabled.";

            var cards = paymentGrid.querySelectorAll(".payment-method-card");
            Array.prototype.forEach.call(cards, function (card) {
                card.disabled = true;
            });
            return;
        }

        amountTenderedInput.disabled = false;
        referenceNumberInput.disabled = false;
        saveAndPrintBtn.disabled = false;
        saveDraftBtn.disabled = isSettlementOnlyMode();

        var cards = paymentGrid.querySelectorAll(".payment-method-card");
        Array.prototype.forEach.call(cards, function (card) {
            card.disabled = false;
        });

        if (isSettlementOnlyMode()) {
            formMessage.textContent = "Balance outstanding: please pay the full remaining balance to settle this bill.";
        } else if (formMessage.textContent === "Balance outstanding: please pay the full remaining balance to settle this bill.") {
            formMessage.textContent = "";
        }
    }

    addCustomItemBtn.addEventListener("click", function () {
        if (!canEditStructure()) {
            return;
        }
        state.items.push({
            row_id: "row_" + Date.now(),
            test_id: 0,
            test_name: "",
            unit_price: 0,
            quantity: 1,
            selected: true,
            is_custom: true
        });
        renderRows();
    });

    paymentGrid.addEventListener("click", function (event) {
        if (isPaidLocked()) {
            return;
        }
        var card = event.target.closest(".payment-method-card");
        if (!card) {
            return;
        }
        state.paymentMethod = String(card.getAttribute("data-method") || "CASH").toUpperCase();
        updatePaymentSelection();
    });

    [amountTenderedInput, discountInput, taxPercentInput].forEach(function (node) {
        node.addEventListener("input", recalculate);
    });

    saveDraftBtn.addEventListener("click", function () {
        save("saveDraft").catch(function () {
            return;
        });
    });

    saveAndPrintBtn.addEventListener("click", function () {
        save("finalizeBill")
            .then(function (json) {
                if (json.data && json.data.print_url) {
                    window.open(json.data.print_url, "_blank");
                }
            })
            .catch(function () {
                return;
            });
    });

    cancelBtn.addEventListener("click", function () {
        window.location.href = "/lab_sync/index.php?controller=appointmentsController&action=index&role=receptionist";
    });

    updatePaymentSelection();
    applyLockMode();
    renderRows();
})();
