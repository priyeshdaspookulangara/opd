document.addEventListener('DOMContentLoaded', () => {
    getPendingTests();

    // Listener for table clicks to redirect to the result entry page
    document.getElementById('pending-tests-table').addEventListener('click', (e) => {
        if (e.target && e.target.classList.contains('enter-result-btn')) {
            const testId = e.target.getAttribute('data-test-id');
            window.location.href = `lab_result_entry.html?lab_test_id=${testId}`;
        }
    });
});

function getPendingTests() {
    fetch('../api/v1/lab/pending_tests.php')
        .then(response => response.json())
        .then(content => {
            const tableBody = document.getElementById('pending-tests-body');
            tableBody.innerHTML = '';
            if (content.data && content.data.length > 0) {
                content.data.forEach(test => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${test.lab_test_id}</td>
                        <td>${test.patient_name}</td>
                        <td>${test.test_name}</td>
                        <td>${test.test_date}</td>
                        <td>
                            <button class="enter-result-btn" data-test-id="${test.lab_test_id}">
                                Enter Result
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="5">No pending lab tests.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error fetching pending tests:', error);
            const tableBody = document.getElementById('pending-tests-body');
            tableBody.innerHTML = '<tr><td colspan="5">Error loading pending tests.</td></tr>';
        });
}
