document.addEventListener('DOMContentLoaded', () => {
    getPendingRadiologyTests();

    // Listener for table clicks
    document.getElementById('pending-radiology-tests-table').addEventListener('click', (e) => {
        if (e.target && e.target.classList.contains('enter-report-btn')) {
            const testId = e.target.getAttribute('data-test-id');
            showReportEntryForm(testId);
        }
    });

    // Listener for form submission
    document.getElementById('report-entry-form').addEventListener('submit', saveReport);
});

function getPendingRadiologyTests() {
    fetch('../api/v1/radiology/pending_tests.php')
        .then(response => response.json())
        .then(content => {
            const tableBody = document.getElementById('pending-radiology-tests-body');
            tableBody.innerHTML = '';
            if (content.data && content.data.length > 0) {
                content.data.forEach(test => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${test.radiology_order_id}</td>
                        <td>${test.patient_name}</td>
                        <td>${test.test_name}</td>
                        <td>${test.order_date}</td>
                        <td>
                            <button class="enter-report-btn" data-test-id="${test.radiology_order_id}">
                                Enter Report
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="5">No pending radiology tests.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error fetching pending tests:', error);
            const tableBody = document.getElementById('pending-radiology-tests-body');
            tableBody.innerHTML = '<tr><td colspan="5">Error loading pending tests.</td></tr>';
        });
}

function showReportEntryForm(testId) {
    const reportSection = document.getElementById('report-entry-section');
    reportSection.style.display = 'block';

    document.getElementById('report-test-id').textContent = testId;
    document.getElementById('report-radiology-order-id').value = testId;

    reportSection.scrollIntoView({ behavior: 'smooth' });
}

function saveReport(event) {
    event.preventDefault();
    const responseDiv = document.getElementById('report-response');

    const formData = {
        radiology_order_id: document.getElementById('report-radiology-order-id').value,
        report: document.getElementById('report').value,
        notes: document.getElementById('radiology-notes').value
    };

    fetch('../api/v1/radiology/update_report.php', {
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
            document.getElementById('report-entry-section').style.display = 'none';
            document.getElementById('report-entry-form').reset();
            responseDiv.innerHTML = '';
            getPendingRadiologyTests();
        }, 1500);
    })
    .catch(error => {
        responseDiv.innerHTML = `<p style="color:red;">Error: ${error.message}</p>`;
        console.error('Error saving report:', error);
    });
}
