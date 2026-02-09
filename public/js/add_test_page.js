document.addEventListener('DOMContentLoaded', function () {
    (function(){
        let rangeIndex = 1;
        let externalIndex = 1;

        const addRangeBtn = document.getElementById('addRangeBtn');
        const rangesBody = document.getElementById('rangesBody');
        const addExternalBtn = document.getElementById('addExternalBtn');
        const externalBody = document.getElementById('externalBody');
        const reportEditor = document.getElementById('reportEditor');
        const reportComments = document.getElementById('report_comments');
        const addTestForm = document.getElementById('addTestForm');

        if (addRangeBtn && rangesBody) {
            addRangeBtn.addEventListener('click', function(){
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <select name="ranges[${rangeIndex}][gender]">
                            <option value="Both">Both</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </td>
                    <td><input type="number" name="ranges[${rangeIndex}][from_age]"></td>
                    <td><input type="number" name="ranges[${rangeIndex}][to_age]"></td>
                    <td><input type="text" name="ranges[${rangeIndex}][range]"></td>
                    <td><input type="text" name="ranges[${rangeIndex}][critical]"></td>
                    <td><button type="button" class="btn delete-range">Remove</button></td>
                `;
                rangesBody.appendChild(tr);
                rangeIndex++;
            });

            rangesBody.addEventListener('click', function(e){
                if (e.target && e.target.classList.contains('delete-range')) {
                    const row = e.target.closest('tr');
                    if (row) row.remove();
                }
            });
        }

        if (addExternalBtn && externalBody) {
            addExternalBtn.addEventListener('click', function(){
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><input type="text" name="external[${externalIndex}][test_code]"></td>
                    <td>
                        <select name="external[${externalIndex}][hospital]"><option value="">Select Hospital</option><option value="HospA">Hospital A</option><option value="HospB">Hospital B</option></select>
                    </td>
                    <td><input type="number" name="external[${externalIndex}][cost]"></td>
                    <td><button type="button" class="btn delete-external">Remove</button></td>
                `;
                externalBody.appendChild(tr);
                externalIndex++;
            });

            externalBody.addEventListener('click', function(e){
                if (e.target && e.target.classList.contains('delete-external')) {
                    const row = e.target.closest('tr');
                    if (row) row.remove();
                }
            });
        }

        // Report editor toolbar
        document.querySelectorAll('.toolbar button').forEach(function(btn){
            btn.addEventListener('click', function(){
                const cmd = btn.getAttribute('data-cmd');
                document.execCommand(cmd, false, null);
                if (reportEditor) reportEditor.focus();
            });
        });

        // Sync report editor content to hidden textarea before submit
        if (addTestForm && reportEditor && reportComments) {
            addTestForm.addEventListener('submit', function(e){
                const html = reportEditor.innerHTML;
                reportComments.value = html;
            });
        }

        // Placeholder for report editor
        if (reportEditor) {
            reportEditor.addEventListener('focus', function(){ if (reportEditor.textContent.trim() === reportEditor.getAttribute('data-placeholder')) { reportEditor.innerHTML = ''; } });
            if (!reportEditor.innerHTML.trim()) reportEditor.innerHTML = reportEditor.getAttribute('data-placeholder');
        }

    })();
});