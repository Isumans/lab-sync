document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('editTestModal');
    const form = document.getElementById('editTestForm');
    const closeBtn = document.getElementById('editTestModalClose');
    const cancelBtn = document.getElementById('cancelEditTest');

    const container = document.querySelector('.Tmain-content');
    const role = container ? container.dataset.role || '' : '';

    function openModalWithData(data) {
        if (!form) return;
        // Set defaults if missing
        const defaultName = data.name || 'Comprehensive Metabolic Panel';
        const defaultCategory = data.category || 'Biochemistry';
        const defaultPrice = (data.price && !isNaN(data.price)) ? parseFloat(data.price).toFixed(2) : '145.00';

        form.elements['test_id'].value = data.id || '';
        form.elements['test_name'].value = defaultName;
        form.elements['category'].value = defaultCategory;
        form.elements['price'].value = defaultPrice;

        // Save original state to revert on cancel
        form.dataset.original = JSON.stringify({ name: form.elements['test_name'].value, category: form.elements['category'].value, price: form.elements['price'].value });

        if (modal) modal.style.display = 'flex';
    }

    document.querySelectorAll('.edit-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            const tr = e.currentTarget.closest('tr');
            if (!tr) return;
            const data = {
                id: tr.dataset.id,
                name: tr.dataset.name,
                category: tr.dataset.category,
                price: tr.dataset.price
            };
            openModalWithData(data);
        });
    });

    // Close/Cancel handlers
    function closeModal() {
        if (!form) return;
        // Revert any unsaved changes
        try {
            const orig = JSON.parse(form.dataset.original || '{}');
            if (orig.name) form.elements['test_name'].value = orig.name;
            if (orig.category) form.elements['category'].value = orig.category;
            if (orig.price) form.elements['price'].value = orig.price;
        } catch (err) {}
        if (modal) modal.style.display = 'none';
    }

    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    window.addEventListener('click', function (e) { if (e.target === modal) closeModal(); });

    // Row delete button: confirm and submit a small form
    document.querySelectorAll('.delete-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            const tr = e.currentTarget.closest('tr');
            if (!tr) return;
            const id = tr.dataset.id;
            if (!id) return;
            if (!confirm('Delete this test? This action cannot be undone.')) return;
            const f = document.createElement('form');
            f.method = 'post';
            f.action = '/lab_sync/index.php?controller=TestCatalog&action=edit_test&role=' + encodeURIComponent(role);
            const inp = document.createElement('input'); inp.type = 'hidden'; inp.name = 'test_id'; inp.value = id; f.appendChild(inp);
            const del = document.createElement('input'); del.type = 'hidden'; del.name = 'delete'; del.value = '1'; f.appendChild(del);
            document.body.appendChild(f);
            f.submit();
        });
    });

    // No need to add a navigation click handler for Add Test — the anchor has an href
});