const addTestButton = document.getElementById('add-test-button');
        const additionalTestsContainer = document.getElementById('additional-tests');

        const allTests = [
            "Blood Test",
            "Urine Test",
            "X-Ray",
            "MRI",
            "CT Scan",
            "Other"
        ];

        let testCount = 1;

        // Helper function to get selected test values
        function getSelectedTests() {
            return Array.from(document.querySelectorAll('.test-select'))
                        .map(select => select.value)
                        .filter(v => v !== "");
        }

        // Function to create a new select dropdown
        function createTestDropdown(index) {
            const selectedTests = getSelectedTests();
            const availableTests = allTests.filter(test => !selectedTests.includes(test));

            // If no tests are left, disable the add button
            if (availableTests.length === 0) {
                alert("✅ All tests have been selected.");
                addTestButton.disabled = true;
                return null;
            }

            const newTestGroup = document.createElement('div');
            newTestGroup.classList.add('test-group');

            const selectOptions = availableTests.map(test => `<option value="${test}">${test}</option>`).join('');

            newTestGroup.innerHTML = `
                <label for="test-type-${index}">Test ${index} type:</label>
                <select id="test-type-${index}" name="test-types[]" class="test-select" required>
                    <option value="">Select Test Type</option>
                    ${selectOptions}
                </select>
            `;

            return newTestGroup;
        }

        // Add new test dropdown
        addTestButton.addEventListener('click', () => {
            testCount++;
            const newTestGroup = createTestDropdown(testCount);
            if (newTestGroup) {
                additionalTestsContainer.appendChild(newTestGroup);
            }
        });

        // Update dropdowns dynamically when a test is changed
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('test-select')) {
                const selects = document.querySelectorAll('.test-select');
                const selectedTests = getSelectedTests();

                selects.forEach(select => {
                    const currentValue = select.value;
                    const availableTests = allTests.filter(test => 
                        !selectedTests.includes(test) || test === currentValue
                    );

                    // rebuild options
                    const optionsHTML = `<option value="">Select Test Type</option>` + 
                        availableTests.map(test => 
                            `<option value="${test}" ${test === currentValue ? 'selected' : ''}>${test}</option>`
                        ).join('');

                    select.innerHTML = optionsHTML;
                });

                // Re-enable add button if a test is unselected
                addTestButton.disabled = getSelectedTests().length >= allTests.length;
            }
        });