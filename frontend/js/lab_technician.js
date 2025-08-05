document.addEventListener('DOMContentLoaded', () => {
    getPendingTests();

    // Listener for table clicks
    document.getElementById('pending-tests-table').addEventListener('click', (e) => {
        if (e.target && e.target.classList.contains('enter-result-btn')) {
            const testId = e.target.getAttribute('data-test-id');
            showResultEntryForm(testId);
        }
    });

    // Listener for form submission
    document.getElementById('result-entry-form').addEventListener('submit', saveResult);
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

function showResultEntryForm(testId) {
    const resultSection = document.getElementById('result-entry-section');
    resultSection.style.display = 'block';

    document.getElementById('result-test-id').textContent = testId;
    document.getElementById('result-lab-test-id').value = testId;

    resultSection.scrollIntoView({ behavior: 'smooth' });
}

function saveResult(event) {
    event.preventDefault();
    const responseDiv = document.getElementById('result-response');

    const formData = {
        lab_test_id: document.getElementById('result-lab-test-id').value,
        result: document.getElementById('result').value,
        notes: document.getElementById('notes').value
    };

    fetch('../api/v1/lab/update_result.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw new Error(err.message) });
        }
        return response.json();
    })
    .then(data => {
        responseDiv.innerHTML = `<p style="color:green;">${data.message}</p>`;
        setTimeout(() => {
            // Hide form, clear fields, and refresh list
            document.getElementById('result-entry-section').style.display = 'none';
            document.getElementById('result-entry-form').reset();
            responseDiv.innerHTML = '';
            getPendingTests();
        }, 1500);
    })
    .catch(error => {
        responseDiv.innerHTML = `<p style="color:red;">Error: ${error.message}</p>`;
        console.error('Error saving result:', error);
    });
}
