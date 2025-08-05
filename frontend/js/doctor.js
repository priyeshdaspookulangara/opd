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

    // Listener for consultation form submission
    document.getElementById('consultation-form').addEventListener('submit', saveConsultation);

    // Listener for dispense form submission
    document.getElementById('dispense-form').addEventListener('submit', handleDispenseSubmit);

    // Listener for lab test order form submission
    document.getElementById('order-lab-test-form').addEventListener('submit', handleOrderLabTestSubmit);

    // Listener for radiology test order form submission
    document.getElementById('order-radiology-test-form').addEventListener('submit', handleOrderRadiologyTestSubmit);
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
    const consultationSection = document.getElementById('consultation-section');
    consultationSection.style.display = 'block';

    document.getElementById('consult-patient-name').textContent = patientName;
    document.getElementById('consult-op-id').value = opId;
    document.getElementById('dispense-op-id').value = opId;
    document.getElementById('dispense-patient-id').value = patientId;
    document.getElementById('order-op-id').value = opId;
    document.getElementById('order-patient-id').value = patientId;
    document.getElementById('order-radiology-op-id').value = opId;
    document.getElementById('order-radiology-patient-id').value = patientId;

    const vitalsDiv = document.getElementById('consult-vitals');
    vitalsDiv.innerHTML = '<p>Loading vitals...</p>';

    fetch(`../api/v1/vitals/read_for_visit.php?op_id=${opId}`)
        .then(response => {
            if (!response.ok) throw new Error('No vitals found for this visit.');
            return response.json();
        })
        .then(vitals => {
            vitalsDiv.innerHTML = `
                <ul>
                    <li><strong>Temperature:</strong> ${vitals.temperature} °C</li>
                    <li><strong>Blood Pressure:</strong> ${vitals.blood_pressure}</li>
                    <li><strong>Heart Rate:</strong> ${vitals.heart_rate} bpm</li>
                    <li><strong>Respiratory Rate:</strong> ${vitals.respiratory_rate} breaths/min</li>
                    <li><strong>Recorded at:</strong> ${vitals.recorded_at}</li>
                </ul>
            `;
        })
        .catch(error => {
            vitalsDiv.innerHTML = `<p style="color:red;">${error.message}</p>`;
        });

    consultationSection.scrollIntoView({ behavior: 'smooth' });
}

function saveConsultation(event) {
    event.preventDefault();
    const responseDiv = document.getElementById('consultation-response');

    const formData = {
        op_id: document.getElementById('consult-op-id').value,
        diagnosis: document.getElementById('diagnosis').value,
        prescription: document.getElementById('prescription').value,
        notes: ''
    };

    fetch('../api/v1/consultations/create.php', {
        method: 'POST',
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
        responseDiv.innerHTML = `<p style="color:green;">${data.message} Ready for next actions.</p>`;
        document.getElementById('dispense-section').style.display = 'block';
        document.getElementById('lab-test-section').style.display = 'block';
        document.getElementById('radiology-test-section').style.display = 'block';
        populateDrugSelect();
        populateLabTestSelect();
        populateRadiologyTestSelect();
    })
    .catch(error => {
        responseDiv.innerHTML = `<p style="color:red;">Error: ${error.message}</p>`;
        console.error('Error saving consultation:', error);
    });
}

function populateDrugSelect() {
    const drugSelect = document.getElementById('drug-select');
    drugSelect.innerHTML = '<option value="">-- Select a Drug --</option>';

    fetch('../api/v1/pharmacy/drugs_read.php')
        .then(response => response.json())
        .then(content => {
            if (content.data && content.data.length > 0) {
                content.data.forEach(drug => {
                    if (drug.stock_quantity > 0) {
                        const option = document.createElement('option');
                        option.value = drug.drug_id;
                        option.textContent = `${drug.drug_name} (Stock: ${drug.stock_quantity})`;
                        drugSelect.appendChild(option);
                    }
                });
            }
        });
}

function handleDispenseSubmit(event) {
    event.preventDefault();
    const responseDiv = document.getElementById('dispense-response');

    const formData = {
        op_id: document.getElementById('dispense-op-id').value,
        patient_id: document.getElementById('dispense-patient-id').value,
        drug_id: document.getElementById('drug-select').value,
        quantity: document.getElementById('quantity').value
    };

    if (!formData.drug_id) {
        responseDiv.innerHTML = '<p style="color:red;">Please select a drug.</p>';
        return;
    }

    fetch('../api/v1/pharmacy/dispense.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        responseDiv.innerHTML = `<p style="color:green;">${data.message}</p>`;
        document.getElementById('dispense-form').reset();
        populateDrugSelect();
    })
    .catch(error => {
        responseDiv.innerHTML = `<p style="color:red;">Error: ${error.message}</p>`;
        console.error('Error dispensing drug:', error);
    });
}

function populateLabTestSelect() {
    const labTestSelect = document.getElementById('lab-test-select');
    labTestSelect.innerHTML = '<option value="">-- Select a Lab Test --</option>';

    fetch('../api/v1/lab/tests_read.php')
        .then(response => response.json())
        .then(content => {
            if (content.data && content.data.length > 0) {
                content.data.forEach(test => {
                    if (test.is_available) {
                        const option = document.createElement('option');
                        option.value = test.available_test_id;
                        option.textContent = `${test.test_name} - ${test.cost}`;
                        labTestSelect.appendChild(option);
                    }
                });
            }
        });
}

function handleOrderLabTestSubmit(event) {
    event.preventDefault();
    const responseDiv = document.getElementById('order-lab-test-response');

    const formData = {
        op_id: document.getElementById('order-op-id').value,
        patient_id: document.getElementById('order-patient-id').value,
        available_test_id: document.getElementById('lab-test-select').value
    };

    if (!formData.available_test_id) {
        responseDiv.innerHTML = '<p style="color:red;">Please select a lab test.</p>';
        return;
    }

    fetch('../api/v1/lab/order_test.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        responseDiv.innerHTML = `<p style="color:green;">${data.message}</p>`;
        document.getElementById('order-lab-test-form').reset();
    })
    .catch(error => {
        responseDiv.innerHTML = `<p style="color:red;">Error: ${error.message}</p>`;
        console.error('Error ordering lab test:', error);
    });
}

function populateRadiologyTestSelect() {
    const radiologyTestSelect = document.getElementById('radiology-test-select');
    radiologyTestSelect.innerHTML = '<option value="">-- Select a Radiology Test --</option>';

    fetch('../api/v1/radiology/tests_read.php')
        .then(response => response.json())
        .then(content => {
            if (content.data && content.data.length > 0) {
                content.data.forEach(test => {
                    if (test.is_available) {
                        const option = document.createElement('option');
                        option.value = test.available_test_id;
                        option.textContent = `${test.test_name} - ${test.cost}`;
                        radiologyTestSelect.appendChild(option);
                    }
                });
            }
        });
}

function handleOrderRadiologyTestSubmit(event) {
    event.preventDefault();
    const responseDiv = document.getElementById('order-radiology-test-response');

    const formData = {
        op_id: document.getElementById('order-radiology-op-id').value,
        patient_id: document.getElementById('order-radiology-patient-id').value,
        available_test_id: document.getElementById('radiology-test-select').value
    };

    if (!formData.available_test_id) {
        responseDiv.innerHTML = '<p style="color:red;">Please select a radiology test.</p>';
        return;
    }

    fetch('../api/v1/radiology/order_test.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        responseDiv.innerHTML = `<p style="color:green;">${data.message}</p>`;
        document.getElementById('order-radiology-test-form').reset();
    })
    .catch(error => {
        responseDiv.innerHTML = `<p style="color:red;">Error: ${error.message}</p>`;
        console.error('Error ordering radiology test:', error);
    });
}
