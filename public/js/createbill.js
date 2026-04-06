// Handle test selection and price updates
function updateTestRow(selectElement) {
    const row = selectElement.closest('tr');
    const option = selectElement.options[selectElement.selectedIndex];
    const price = parseFloat(option.dataset.price) || 0;
    const category = option.dataset.category || '';
    const qty = parseInt(row.querySelector('.quantity-input').value) || 1;
    
    row.querySelector('.test-category').textContent = category;
    row.querySelector('.test-price').textContent = price.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    row.querySelector('.test-subtotal').textContent = (price * qty).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    updateFinancialSummary();
}

function updateFinancialSummary() {
    let subtotal = 0;
    document.querySelectorAll('#testsBody tr').forEach(row => {
        const priceText = row.querySelector('.test-price').textContent.replace(/,/g, '');
        const price = parseFloat(priceText) || 0;
        const qty = parseInt(row.querySelector('.quantity-input').value) || 1;
        subtotal += price * qty;
    });
    
    const tax = subtotal * 0.02;
    const serviceFee = parseFloat(document.getElementById('service_fee').value) || 0;
    const discountValue = subtotal * 0.1; // 10% discount for WINTER promo
    const total = subtotal + tax + serviceFee - discountValue;
    
    document.getElementById('subtotal').textContent = 'Rs. ' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('tax').textContent = 'Rs. ' + tax.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('service-fee-display').textContent = serviceFee.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('discount').textContent = '- Rs. ' + discountValue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('totalAmount').textContent = 'Rs. ' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// Event listeners
document.querySelectorAll('.test-select').forEach(select => {
    select.addEventListener('change', function() { updateTestRow(this); });
});

document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('change', function() { 
        updateTestRow(this.closest('tr').querySelector('.test-select')); 
    });
});

document.getElementById('service_fee').addEventListener('change', updateFinancialSummary);

// Add test button
document.getElementById('addTestBtn').addEventListener('click', function(e) {
    e.preventDefault();
    const tbody = document.getElementById('testsBody');
    const rowCount = tbody.querySelectorAll('tr').length;
    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td>
            <select name="tests[${rowCount}][test_name]" class="test-select">
                <option value="">Select Test</option>
                <option value="Full Blood Count (FBC)" data-price="1250" data-category="Hematology">Full Blood Count (FBC)</option>
                <option value="Lipid Profile" data-price="3400" data-category="Biochemistry">Lipid Profile</option>
                <option value="HbA1c (Glycated Hemoglobin)" data-price="2100" data-category="Diabetes">HbA1c (Glycated Hemoglobin)</option>
                <option value="Serum Creatinine" data-price="950" data-category="Renal Function">Serum Creatinine</option>
            </select>
        </td>
        <td><span class="test-category">-</span></td>
        <td><span class="test-price">0.00</span></td>
        <td><input type="number" name="tests[${rowCount}][quantity]" value="1" min="1" class="test-qty quantity-input"></td>
        <td><span class="test-subtotal">0.00</span></td>
        <td><button type="button" class="remove-test-btn" title="Remove">×</button></td>
    `;
    tbody.appendChild(newRow);
    
    newRow.querySelector('.test-select').addEventListener('change', function() { updateTestRow(this); });
    newRow.querySelector('.quantity-input').addEventListener('change', function() { 
        updateTestRow(this.closest('tr').querySelector('.test-select')); 
    });
});

// Remove test button
document.getElementById('testsBody').addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-test-btn')) {
        e.preventDefault();
        const row = e.target.closest('tr');
        if (document.getElementById('testsBody').querySelectorAll('tr').length > 1) {
            row.remove();
            updateFinancialSummary();
        }
    }
});

// Initialize on load
updateFinancialSummary();
