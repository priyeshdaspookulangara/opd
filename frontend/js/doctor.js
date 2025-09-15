document.addEventListener('DOMContentLoaded', () => {
    getPatientQueue();

    // Listener for table clicks
    document.getElementById('patient-queue-table').addEventListener('click', (e) => {
        if (e.target && e.target.classList.contains('start-consult-btn')) {
            const opId = e.target.getAttribute('data-op-id');
            const patientId = e.target.getAttribute('data-patient-id');
            const patientName = e.target.getAttribute('data-patient-name');
            startConsultation(opId, patientName, patientId);
        }
    });
});

function getPatientQueue() {
    fetch('../api/v1/visits/active.php')
        .then(response => response.json())
        .then(content => {
            const tableBody = document.getElementById('patient-queue-body');
            tableBody.innerHTML = '';
            if (content.data && content.data.length > 0) {
                content.data.forEach(visit => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${visit.op_number}</td>
                        <td>${visit.first_name} ${visit.last_name}</td>
                        <td>${visit.visit_date}</td>
                        <td>
                            <button class="start-consult-btn" data-op-id="${visit.op_id}" data-patient-id="${visit.patient_id}" data-patient-name="${visit.first_name} ${visit.last_name}">
                                Start Consultation
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="4">Patient queue is empty.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error fetching patient queue:', error);
            const tableBody = document.getElementById('patient-queue-body');
            tableBody.innerHTML = '<tr><td colspan="4">Error loading patient queue.</td></tr>';
        });
}

function startConsultation(opId, patientName, patientId) {
    window.location.href = `consultation.html?op_id=${opId}&patient_id=${patientId}&patient_name=${encodeURIComponent(patientName)}`;
}
