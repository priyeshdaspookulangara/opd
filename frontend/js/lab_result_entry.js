document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const labTestId = urlParams.get('lab_test_id');

    if (!labTestId) {
        document.body.innerHTML = '<h1>Error: Missing lab test ID in URL.</h1>';
        return;
    }

    // Display the test ID and set it in the form
    document.getElementById('result-test-id').textContent = labTestId;
    document.getElementById('result-lab-test-id').value = labTestId;

    // Add form submission listener
    document.getElementById('result-entry-form').addEventListener('submit', saveResult);
});

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
        responseDiv.innerHTML = `<p style="color:green;">${data.message}. Redirecting... </p>`;
        setTimeout(() => {
            window.location.href = 'lab_technician.html';
        }, 2000);
    })
    .catch(error => {
        responseDiv.innerHTML = `<p style="color:red;">Error: ${error.message}</p>`;
        console.error('Error saving result:', error);
    });
}
