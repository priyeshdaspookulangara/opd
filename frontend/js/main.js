document.addEventListener('DOMContentLoaded', () => {
    // Attach form listener for patient registration
    attachRegisterFormListener();
    // Attach a delegated listener for starting visits
    attachStartVisitListener();
    // Attach form listener for vitals submission
    attachVitalsFormListener();
    // Load initial patient list
    getPatients();

    // Initialize Patient Package Management
    initializePatientPackageManagement();

    // Attach listener for the billing button
    document.getElementById('bill-consultation-btn').addEventListener('click', handleBilling);
});

// ... (all existing functions: attachRegisterFormListener, getPatients, attachStartVisitListener, startVisit, showVitalsForm, attachVitalsFormListener, analyzeVitals, initializePatientPackageManagement, openPackageModal, closePackageModal, handleAssignPackage)

function attachRegisterFormListener() {
    document.getElementById('register-patient-form').addEventListener('submit', function(event) {
        event.preventDefault();

        const dob = document.getElementById('date_of_birth').value;
        const age = document.getElementById('age').value;

        if (!dob && !age) {
            alert('Please enter either Date of Birth or Age.');
            return;
        }
        if (dob && age) {
            alert('Please enter either Date of Birth or Age, not both.');
            return;
        }

        const formData = {
            first_name: document.getElementById('first_name').value,
            last_name: document.getElementById('last_name').value,
            date_of_birth: dob,
            age: age,
            gender: document.getElementById('gender').value,
            phone_number: document.getElementById('phone_number').value,
            email: document.getElementById('email').value,
            address: document.getElementById('address').value
        };
        const api_url = '../api/v1/patients/create.php';

        fetch(api_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) {
                response.text().then(text => console.error('Server response:', text));
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const responseDiv = document.getElementById('response');
            responseDiv.innerHTML = `<p><strong>Status:</strong> ${data.message}</p>`;
            if (data.patient_id) {
                responseDiv.innerHTML += `<p><strong>Patient ID:</strong> ${data.patient_id}</p>`;
            }
            if (data.message === 'Patient Created') {
                document.getElementById('register-patient-form').reset();
                responseDiv.style.color = 'green';
                getPatients(); // Refresh the list
            } else {
                responseDiv.style.color = 'red';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const responseDiv = document.getElementById('response');
            responseDiv.innerHTML = `<p>An error occurred. Please check the console for details.</p>`;
            responseDiv.style.color = 'red';
        });
    });
}

function getPatients() {
    const api_url = '../api/v1/patients/read.php';
    fetch(api_url)
        .then(response => {
            if (response.status === 404) { return { data: [] }; }
            if (!response.ok) { throw new Error(`HTTP error! status: ${response.status}`); }
            return response.json();
        })
        .then(content => {
            const tableBody = document.getElementById('patient-list-body');
            tableBody.innerHTML = ''; // Clear existing rows
            if (content.data && content.data.length > 0) {
                content.data.forEach(patient => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${patient.patient_id}</td>
                        <td>${patient.first_name}</td>
                        <td>${patient.last_name}</td>
                        <td>${patient.date_of_birth}</td>
                        <td>${patient.gender}</td>
                        <td>
                            <button class="start-visit-btn" data-patient-id="${patient.patient_id}">Start Visit</button>
                            <button class="manage-pkg-btn" data-patient-id="${patient.patient_id}" data-patient-name="${patient.first_name} ${patient.last_name}">Manage Packages</button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            } else {
                const row = document.createElement('tr');
                row.innerHTML = '<td colspan="6">No patients found.</td>';
                tableBody.appendChild(row);
            }
        })
        .catch(error => {
            console.error('Error fetching patients:', error);
            const tableBody = document.getElementById('patient-list-body');
            tableBody.innerHTML = '<tr><td colspan="6">Error loading patients. Please check the console.</td></tr>';
        });
}

function attachStartVisitListener() {
    document.getElementById('patient-list-table').addEventListener('click', function(event) {
        if (event.target && event.target.classList.contains('start-visit-btn')) {
            const patientId = event.target.getAttribute('data-patient-id');
            if (patientId) {
                startVisit(patientId);
            }
        }
    });
}

function startVisit(patientId) {
    const api_url = '../api/v1/visits/create.php';
    const responseDiv = document.getElementById('response');
    responseDiv.innerHTML = `<p>Starting visit for patient ${patientId}...</p>`;
    responseDiv.style.color = 'blue';

    fetch(api_url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ patient_id: patientId })
    })
    .then(response => {
        if (!response.ok) { throw new Error(`HTTP error! status: ${response.status}`); }
        return response.json();
    })
    .then(data => {
        if (data.message === 'Visit Created') {
            responseDiv.innerHTML = `<p><strong>Success:</strong> Visit started. Now recording vitals.</p>`;
            responseDiv.style.color = 'green';
            showVitalsForm(data.op_id, data.op_number, patientId);
        } else {
            responseDiv.innerHTML = `<p><strong>Error:</strong> Could not start visit. ${data.message || 'Unknown error'}</p>`;
            responseDiv.style.color = 'red';
        }
    })
    .catch(error => {
        console.error('Error starting visit:', error);
        responseDiv.innerHTML = `<p>An error occurred while starting the visit. See console for details.</p>`;
        responseDiv.style.color = 'red';
    });
}

function showVitalsForm(op_id, op_number, patient_id) {
    const container = document.getElementById('vitals-form-container');
    document.getElementById('vitals-op-id').value = op_id;
    document.getElementById('vitals-patient-id').value = patient_id;
    document.getElementById('vitals-op-number').textContent = op_number;
    container.style.display = 'block';
    document.getElementById('billing-container').style.display = 'none'; // Hide billing part initially
    document.getElementById('vitals-form').style.display = 'block'; // Ensure form is visible
    container.scrollIntoView({ behavior: 'smooth' });
}

function attachVitalsFormListener() {
    document.getElementById('vitals-form').addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = {
            op_id: document.getElementById('vitals-op-id').value,
            temperature: document.getElementById('temperature').value,
            blood_pressure: document.getElementById('blood_pressure').value,
            heart_rate: document.getElementById('heart_rate').value,
            respiratory_rate: document.getElementById('respiratory_rate').value
        };

        const api_url = '../api/v1/vitals/create.php';
        const responseDiv = document.getElementById('response');

        fetch(api_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) { throw new Error(`HTTP error! status: ${response.status}`); }
            return response.json();
        })
        .then(data => {
            if (data.message === 'Vitals Recorded') {
                const analysisReport = analyzeVitals(formData);
                responseDiv.innerHTML = `<p><strong>Success:</strong> Vitals recorded successfully!</p>${analysisReport}`;

                document.getElementById('vitals-form').style.display = 'none';
                document.getElementById('billing-container').style.display = 'block';
            } else {
                responseDiv.innerHTML = `<p><strong>Error:</strong> ${data.message || 'Could not save vitals.'}</p>`;
                responseDiv.style.color = 'red';
            }
        })
        .catch(error => {
            console.error('Error saving vitals:', error);
            responseDiv.innerHTML = `<p>An error occurred while saving vitals. See console for details.</p>`;
            responseDiv.style.color = 'red';
        });
    });
}

function analyzeVitals(vitals) {
    let analysisReport = '<h3>Vitals Analysis</h3><ul>';
    let isAbnormal = false;

    // Temperature
    if (vitals.temperature > 37.5) {
        analysisReport += '<li style="color: red;">High Temperature (Fever)</li>';
        isAbnormal = true;
    } else if (vitals.temperature < 36.5) {
        analysisReport += '<li style="color: blue;">Low Temperature (Hypothermia)</li>';
        isAbnormal = true;
    } else {
        analysisReport += '<li>Temperature: Normal</li>';
    }

    // Heart Rate
    if (vitals.heart_rate > 100) {
        analysisReport += '<li style="color: red;">High Heart Rate (Tachycardia)</li>';
        isAbnormal = true;
    } else if (vitals.heart_rate < 60) {
        analysisReport += '<li style="color: blue;">Low Heart Rate (Bradycardia)</li>';
        isAbnormal = true;
    } else {
        analysisReport += '<li>Heart Rate: Normal</li>';
    }

    // Respiratory Rate
    if (vitals.respiratory_rate > 20) {
        analysisReport += '<li style="color: red;">High Respiratory Rate (Tachypnea)</li>';
        isAbnormal = true;
    } else if (vitals.respiratory_rate < 12) {
        analysisReport += '<li style="color: blue;">Low Respiratory Rate (Bradypnea)</li>';
        isAbnormal = true;
    } else {
        analysisReport += '<li>Respiratory Rate: Normal</li>';
    }

    // Blood Pressure
    try {
        const [systolic, diastolic] = vitals.blood_pressure.split('/').map(Number);
        if (isNaN(systolic) || isNaN(diastolic)) throw new Error('Invalid BP format');

        if (systolic > 130 || diastolic > 85) {
            analysisReport += '<li style="color: red;">High Blood Pressure (Hypertension)</li>';
            isAbnormal = true;
        } else if (systolic < 90 || diastolic < 60) {
             analysisReport += '<li style="color: blue;">Low Blood Pressure (Hypotension)</li>';
             isAbnormal = true;
        } else {
            analysisReport += '<li>Blood Pressure: Normal</li>';
        }
    } catch (e) {
        analysisReport += '<li>Could not analyze blood pressure (ensure format is "120/80").</li>';
    }

    analysisReport += '</ul>';

    if (isAbnormal) {
        return '<div style="border: 2px solid red; padding: 10px;">' + analysisReport + '</div>';
    } else {
        return '<div style="border: 2px solid green; padding: 10px;">' + analysisReport + '</div>';
    }
}

const packageModal = document.getElementById('package-modal');
const closeModalBtn = document.querySelector('.close-btn');
const modalPatientName = document.getElementById('modal-patient-name');
const modalPatientId = document.getElementById('modal-patient-id');
const currentPackagesList = document.getElementById('current-packages-list');
const packageSelect = document.getElementById('package-select');
const assignPackageForm = document.getElementById('assign-package-form');
const modalResponseDiv = document.getElementById('modal-response');

function initializePatientPackageManagement() {
    document.getElementById('patient-list-body').addEventListener('click', (e) => {
        if (e.target && e.target.classList.contains('manage-pkg-btn')) {
            const patientId = e.target.getAttribute('data-patient-id');
            const patientName = e.target.getAttribute('data-patient-name');
            openPackageModal(patientId, patientName);
        }
    });

    closeModalBtn.addEventListener('click', closePackageModal);
    window.addEventListener('click', (e) => {
        if (e.target == packageModal) {
            closePackageModal();
        }
    });

    assignPackageForm.addEventListener('submit', handleAssignPackage);
}

function openPackageModal(patientId, patientName) {
    modalPatientId.value = patientId;
    modalPatientName.textContent = `Manage Packages for ${patientName}`;
    modalResponseDiv.innerHTML = '';

    fetch(`../api/v1/patient_packages/read_for_patient.php?patient_id=${patientId}`)
        .then(response => response.json())
        .then(content => {
            currentPackagesList.innerHTML = '';
            if (content.data && content.data.length > 0) {
                let list = '<ul>';
                content.data.forEach(pkg => {
                    list += `<li>${pkg.package_name} (Expires: ${pkg.end_date})</li>`;
                });
                list += '</ul>';
                currentPackagesList.innerHTML = list;
            } else {
                currentPackagesList.innerHTML = '<p>No active packages.</p>';
            }
        });

    fetch('../api/v1/packages/read.php')
        .then(response => response.json())
        .then(content => {
            packageSelect.innerHTML = '<option value="">-- Select a Package --</option>';
            if (content.data && content.data.length > 0) {
                content.data.forEach(pkg => {
                    if (pkg.is_active) {
                        const option = document.createElement('option');
                        option.value = pkg.package_id;
                        option.textContent = `${pkg.package_name} - ${pkg.price} (${pkg.duration_days} days)`;
                        packageSelect.appendChild(option);
                    }
                });
            }
        });

    packageModal.style.display = 'block';
}

function closePackageModal() {
    packageModal.style.display = 'none';
}

function handleAssignPackage(event) {
    event.preventDefault();
    const patientId = modalPatientId.value;
    const packageId = packageSelect.value;

    if (!packageId) {
        modalResponseDiv.innerHTML = '<p style="color:red;">Please select a package.</p>';
        return;
    }

    const data = {
        patient_id: patientId,
        package_id: packageId
    };

    fetch('../api/v1/patient_packages/create.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === 'Package assigned to patient.') {
            modalResponseDiv.innerHTML = '<p style="color:green;">Package assigned successfully!</p>';
            setTimeout(() => {
                openPackageModal(patientId, modalPatientName.textContent.replace('Manage Packages for ', ''));
            }, 1000);
        } else {
            modalResponseDiv.innerHTML = `<p style="color:red;">${data.message}</p>`;
        }
    })
    .catch(error => {
        console.error('Error assigning package:', error);
        modalResponseDiv.innerHTML = '<p style="color:red;">Error assigning package.</p>';
    });
}

function handleBilling() {
    const opId = document.getElementById('vitals-op-id').value;
    const patientId = document.getElementById('vitals-patient-id').value;

    // For this PoC, we assume 'Doctor Consultation' is service_id=1.
    // In a real app, you might get this from a dropdown or another source.
    const serviceId = 1;

    const data = {
        patient_id: patientId,
        service_id: serviceId,
        op_id: opId
    };

    const responseDiv = document.getElementById('response');
    responseDiv.innerHTML = '<p>Processing bill...</p>';

    fetch('../api/v1/billings/create.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === 'Billing record created.') {
            let billMsg = `Successfully billed for consultation. Amount: <strong>${data.amount_billed}</strong>.`;
            if (data.amount_billed == 0) {
                billMsg += ' (Covered by package)';
            }
            responseDiv.innerHTML = `<p style="color:green;">${billMsg}</p>`;
            document.getElementById('billing-container').style.display = 'none'; // Hide after billing
        } else {
            responseDiv.innerHTML = `<p style="color:red;">${data.message}</p>`;
        }
    })
    .catch(error => {
        console.error('Error processing bill:', error);
        responseDiv.innerHTML = '<p style="color:red;">Error processing bill.</p>';
    });
}
