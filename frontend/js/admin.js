document.addEventListener('DOMContentLoaded', () => {
    // Initialize User Management
    initializeUserManagement();
    // Initialize Service Management
    initializeServiceManagement();
    // Initialize Package Management
    initializePackageManagement();
    // Initialize Drug Management
    initializeDrugManagement();
    // Initialize Lab Test Management
    initializeLabTestManagement();
    // Initialize Radiology Test Management
    initializeRadiologyTestManagement();
    // Initialize Ward and Bed Management
    initializeWardAndBedManagement();
    // Initialize Inpatient Management
    initializeInpatientManagement();
});

// =================================================================
// USER MANAGEMENT
// =================================================================
const USER_API_URL = '../api/v1/users/';

// User form elements
const userForm = document.getElementById('user-form');
const userIdField = document.getElementById('user-id');
const firstNameField = document.getElementById('first_name');
const lastNameField = document.getElementById('last_name');
const usernameField = document.getElementById('username');
const passwordField = document.getElementById('password');
const roleField = document.getElementById('role');
const userIsActiveCheckbox = document.getElementById('user-is-active');
const cancelUserBtn = document.getElementById('cancel-user-edit-btn');
const userResponseDiv = document.getElementById('user-response');

function initializeUserManagement() {
    getUsers();
    userForm.addEventListener('submit', handleUserFormSubmit);
    document.getElementById('user-list-table').addEventListener('click', handleUserTableClick);
    cancelUserBtn.addEventListener('click', resetUserForm);
}

function getUsers() {
    fetch(USER_API_URL + 'read.php')
        .then(response => response.json())
        .then(content => {
            const tableBody = document.getElementById('user-list-body');
            tableBody.innerHTML = '';
            if (content.data && content.data.length > 0) {
                content.data.forEach(user => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${user.user_id}</td>
                        <td>${user.first_name} ${user.last_name}</td>
                        <td>${user.username}</td>
                        <td>${user.role}</td>
                        <td>${user.is_active ? 'Yes' : 'No'}</td>
                        <td>
                            <button class="edit-user-btn" data-id="${user.user_id}" data-first_name="${user.first_name}" data-last_name="${user.last_name}" data-username="${user.username}" data-role="${user.role}" data-is_active="${user.is_active}">Edit</button>
                            <button class="delete-user-btn" data-id="${user.user_id}">Delete</button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="6">No users found.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error fetching users:', error);
            const tableBody = document.getElementById('user-list-body');
            tableBody.innerHTML = '<tr><td colspan="6">Error loading users. Check console for details.</td></tr>';
        });
}

function handleUserFormSubmit(event) {
    event.preventDefault();
    const userId = userIdField.value;
    const userData = {
        first_name: firstNameField.value,
        last_name: lastNameField.value,
        username: usernameField.value,
        role: roleField.value,
        is_active: userIsActiveCheckbox.checked,
        password: passwordField.value // Include password, API will handle if it's empty
    };

    let url = USER_API_URL + 'create.php';
    let method = 'POST';

    if (userId) {
        userData.user_id = userId;
        url = USER_API_URL + 'update.php';
        method = 'PUT';
    }

    fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(userData)
    })
    .then(response => response.json())
    .then(data => {
        userResponseDiv.innerHTML = `<p style="color:green;">${data.message}</p>`;
        resetUserForm();
        getUsers();
    })
    .catch(error => {
        userResponseDiv.innerHTML = `<p style="color:red;">Error: ${error.message}</p>`;
    });
}

function handleUserTableClick(event) {
    const target = event.target;
    const id = target.getAttribute('data-id');

    if (target.classList.contains('edit-user-btn')) {
        userIdField.value = id;
        firstNameField.value = target.getAttribute('data-first_name');
        lastNameField.value = target.getAttribute('data-last_name');
        usernameField.value = target.getAttribute('data-username');
        roleField.value = target.getAttribute('data-role');
        userIsActiveCheckbox.checked = (target.getAttribute('data-is_active') == '1');
        passwordField.value = ''; // Clear password field
        cancelUserBtn.style.display = 'inline-block';
        userForm.scrollIntoView({ behavior: 'smooth' });
    }

    if (target.classList.contains('delete-user-btn')) {
        // Prevent deleting user with ID 1 (assuming it's the primary admin)
        if (id === '1') {
            alert('Cannot delete the primary admin user.');
            return;
        }

        if (confirm(`Are you sure you want to delete user ID ${id}? This action cannot be undone.`)) {
            fetch(USER_API_URL + 'delete.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: id })
            })
            .then(response => response.json())
            .then(data => {
                userResponseDiv.innerHTML = `<p style="color:green;">${data.message}</p>`;
                getUsers();
            });
        }
    }
}

function resetUserForm() {
    userForm.reset();
    userIdField.value = '';
    userIsActiveCheckbox.checked = true;
    cancelUserBtn.style.display = 'none';
    userResponseDiv.innerHTML = '';
}


// =================================================================
// SERVICE MANAGEMENT
// =================================================================
// ... (existing service management code) ...


// =================================================================
// PACKAGE MANAGEMENT
// =================================================================
// ... (existing package management code) ...


// =================================================================
// PHARMACY INVENTORY MANAGEMENT
// =================================================================
// ... (existing drug management code) ...


// =================================================================
// LAB TEST MANAGEMENT
// =================================================================
// ... (existing lab test management code) ...


// =================================================================
// RADIOLOGY TEST MANAGEMENT
// =================================================================
// ... (existing radiology test management code) ...


// =================================================================
// WARD AND BED MANAGEMENT
// =================================================================

const WARD_API_URL = '../api/v1/wards/';
const BED_API_URL = '../api/v1/beds/';

// Ward elements
const wardForm = document.getElementById('ward-form');
const wardIdField = document.getElementById('ward-id');
const wardNameField = document.getElementById('ward_name');
const wardDescriptionField = document.getElementById('ward-description');
const cancelWardBtn = document.getElementById('cancel-ward-edit-btn');
const wardResponseDiv = document.getElementById('ward-response');

// Bed elements
const bedForm = document.getElementById('bed-form');
const bedIdField = document.getElementById('bed-id');
const bedNumberField = document.getElementById('bed_number');
const bedWardSelect = document.getElementById('bed-ward-select');
const bedIsOccupiedCheckbox = document.getElementById('bed-is-occupied');
const cancelBedBtn = document.getElementById('cancel-bed-edit-btn');
const bedResponseDiv = document.getElementById('bed-response');


function initializeWardAndBedManagement() {
    getWards();
    getBeds();
    wardForm.addEventListener('submit', handleWardFormSubmit);
    bedForm.addEventListener('submit', handleBedFormSubmit);
    document.getElementById('ward-list-table').addEventListener('click', handleWardTableClick);
    document.getElementById('bed-list-table').addEventListener('click', handleBedTableClick);
    cancelWardBtn.addEventListener('click', resetWardForm);
    cancelBedBtn.addEventListener('click', resetBedForm);
}

function getWards() {
    fetch(WARD_API_URL + 'read.php')
        .then(response => response.json())
        .then(content => {
            const tableBody = document.getElementById('ward-list-body');
            const wardSelect = document.getElementById('bed-ward-select');
            tableBody.innerHTML = '';
            wardSelect.innerHTML = '<option value="">-- Select Ward --</option>';

            if (content.data && content.data.length > 0) {
                content.data.forEach(ward => {
                    // Populate table
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${ward.ward_id}</td>
                        <td>${ward.ward_name}</td>
                        <td>${ward.description}</td>
                        <td>
                            <button class="edit-ward-btn" data-id="${ward.ward_id}" data-name="${ward.ward_name}" data-description="${ward.description}">Edit</button>
                            <button class="delete-ward-btn" data-id="${ward.ward_id}">Delete</button>
                        </td>
                    `;
                    tableBody.appendChild(row);

                    // Populate select dropdown
                    const option = document.createElement('option');
                    option.value = ward.ward_id;
                    option.textContent = ward.ward_name;
                    wardSelect.appendChild(option);
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="4">No wards found.</td></tr>';
            }
        });
}

function getBeds() {
    fetch(BED_API_URL + 'read.php')
        .then(response => response.json())
        .then(content => {
            const tableBody = document.getElementById('bed-list-body');
            tableBody.innerHTML = '';
            if (content.data && content.data.length > 0) {
                content.data.forEach(bed => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${bed.bed_id}</td>
                        <td>${bed.bed_number}</td>
                        <td>${bed.ward_name}</td>
                        <td>${bed.is_occupied ? 'Yes' : 'No'}</td>
                        <td>
                            <button class="edit-bed-btn" data-id="${bed.bed_id}" data-number="${bed.bed_number}" data-ward_id="${bed.ward_id}" data-is_occupied="${bed.is_occupied}">Edit</button>
                            <button class="delete-bed-btn" data-id="${bed.bed_id}">Delete</button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="5">No beds found.</td></tr>';
            }
        });
}

function handleWardFormSubmit(event) {
    event.preventDefault();
    const wardId = wardIdField.value;
    const wardData = {
        ward_name: wardNameField.value,
        description: wardDescriptionField.value,
    };

    let url = WARD_API_URL + 'create.php';
    let method = 'POST';

    if (wardId) {
        wardData.ward_id = wardId;
        url = WARD_API_URL + 'update.php';
        method = 'PUT';
    }

    fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(wardData)
    })
    .then(response => response.json())
    .then(data => {
        wardResponseDiv.innerHTML = `<p style="color:green;">${data.message}</p>`;
        resetWardForm();
        getWards();
    });
}

function handleBedFormSubmit(event) {
    event.preventDefault();
    const bedId = bedIdField.value;
    const bedData = {
        bed_number: bedNumberField.value,
        ward_id: bedWardSelect.value,
        is_occupied: bedIsOccupiedCheckbox.checked
    };

    let url = BED_API_URL + 'create.php';
    let method = 'POST';

    if (bedId) {
        bedData.bed_id = bedId;
        url = BED_API_URL + 'update.php';
        method = 'PUT';
    }

    fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(bedData)
    })
    .then(response => response.json())
    .then(data => {
        bedResponseDiv.innerHTML = `<p style="color:green;">${data.message}</p>`;
        resetBedForm();
        getBeds();
    });
}

function handleWardTableClick(event) {
    const target = event.target;
    const id = target.getAttribute('data-id');

    if (target.classList.contains('edit-ward-btn')) {
        wardIdField.value = id;
        wardNameField.value = target.getAttribute('data-name');
        wardDescriptionField.value = target.getAttribute('data-description');
        cancelWardBtn.style.display = 'inline-block';
        wardForm.scrollIntoView({ behavior: 'smooth' });
    }

    if (target.classList.contains('delete-ward-btn')) {
        if (confirm(`Are you sure you want to delete ward ID ${id}? This will also delete all beds in this ward.`)) {
            fetch(WARD_API_URL + 'delete.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ward_id: id })
            })
            .then(response => response.json())
            .then(data => {
                wardResponseDiv.innerHTML = `<p style="color:green;">${data.message}</p>`;
                getWards();
                getBeds(); // Refresh beds list as well
            });
        }
    }
}

function handleBedTableClick(event) {
    const target = event.target;
    const id = target.getAttribute('data-id');

    if (target.classList.contains('edit-bed-btn')) {
        bedIdField.value = id;
        bedNumberField.value = target.getAttribute('data-number');
        bedWardSelect.value = target.getAttribute('data-ward_id');
        bedIsOccupiedCheckbox.checked = (target.getAttribute('data-is_occupied') == '1');
        cancelBedBtn.style.display = 'inline-block';
        bedForm.scrollIntoView({ behavior: 'smooth' });
    }

    if (target.classList.contains('delete-bed-btn')) {
        if (confirm(`Are you sure you want to delete bed ID ${id}?`)) {
            fetch(BED_API_URL + 'delete.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ bed_id: id })
            })
            .then(response => response.json())
            .then(data => {
                bedResponseDiv.innerHTML = `<p style="color:green;">${data.message}</p>`;
                getBeds();
            });
        }
    }
}

function resetWardForm() {
    wardForm.reset();
    wardIdField.value = '';
    cancelWardBtn.style.display = 'none';
}

function resetBedForm() {
    bedForm.reset();
    bedIdField.value = '';
    cancelBedBtn.style.display = 'none';
}

// =================================================================
// INPATIENT MANAGEMENT
// =================================================================

const ADMISSION_API_URL = '../api/v1/admissions/';
const PATIENT_API_URL = '../api/v1/patients/';

function initializeInpatientManagement() {
    loadPatientsIntoSelect('admit-patient-select');
    loadAvailableBedsIntoSelect('admit-bed-select');
    getInpatients();

    document.getElementById('admit-patient-form').addEventListener('submit', handleAdmitPatientFormSubmit);
    document.getElementById('inpatients-list-table').addEventListener('click', handleInpatientTableClick);
}

function loadPatientsIntoSelect(selectId) {
    fetch(PATIENT_API_URL + 'read.php')
        .then(response => response.json())
        .then(content => {
            const select = document.getElementById(selectId);
            select.innerHTML = '<option value="">-- Select Patient --</option>';
            if (content.data) {
                content.data.forEach(patient => {
                    const option = document.createElement('option');
                    option.value = patient.patient_id;
                    option.textContent = `${patient.name} (ID: ${patient.patient_id})`;
                    select.appendChild(option);
                });
            }
        });
}

function loadAvailableBedsIntoSelect(selectId) {
    fetch(BED_API_URL + 'read.php?available=true') // Assuming an API endpoint to get only available beds
        .then(response => response.json())
        .then(content => {
            const select = document.getElementById(selectId);
            select.innerHTML = '<option value="">-- Select Bed --</option>';
            if (content.data) {
                content.data.forEach(bed => {
                    const option = document.createElement('option');
                    option.value = bed.bed_id;
                    option.textContent = `Bed ${bed.bed_number} (Ward: ${bed.ward_name})`;
                    select.appendChild(option);
                });
            }
        });
}

function getInpatients() {
    fetch(ADMISSION_API_URL + 'read.php')
        .then(response => response.json())
        .then(content => {
            const tableBody = document.getElementById('inpatients-list-body');
            tableBody.innerHTML = '';
            if (content.data && content.data.length > 0) {
                content.data.forEach(admission => {
                    // Only show patients who have not been discharged
                    if (!admission.discharge_date) {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${admission.admission_id}</td>
                            <td>${admission.patient_name}</td>
                            <td>${admission.ward_name}</td>
                            <td>${admission.bed_number}</td>
                            <td>${new Date(admission.admission_date).toLocaleString()}</td>
                            <td>${admission.diagnosis || 'N/A'}</td>
                            <td>
                                <button class="discharge-btn" data-id="${admission.admission_id}">Discharge</button>
                            </td>
                        `;
                        tableBody.appendChild(row);
                    }
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="7">No inpatients found.</td></tr>';
            }
        });
}

function handleAdmitPatientFormSubmit(event) {
    event.preventDefault();
    const admissionData = {
        patient_id: document.getElementById('admit-patient-select').value,
        bed_id: document.getElementById('admit-bed-select').value,
        admission_date: document.getElementById('admission_date').value,
        diagnosis: document.getElementById('admission-diagnosis').value
    };

    fetch(ADMISSION_API_URL + 'create.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(admissionData)
    })
    .then(response => response.json())
    .then(data => {
        const responseDiv = document.getElementById('admission-response');
        responseDiv.innerHTML = `<p style="color:green;">${data.message}</p>`;
        document.getElementById('admit-patient-form').reset();
        // Refresh lists
        getInpatients();
        loadAvailableBedsIntoSelect('admit-bed-select');
        // also refresh the main bed list to show occupancy change
        getBeds();
    })
    .catch(error => {
        const responseDiv = document.getElementById('admission-response');
        responseDiv.innerHTML = `<p style="color:red;">Error: ${error.message}</p>`;
    });
}

function handleInpatientTableClick(event) {
    const target = event.target;
    if (target.classList.contains('discharge-btn')) {
        const admissionId = target.getAttribute('data-id');
        if (confirm(`Are you sure you want to discharge this patient (Admission ID: ${admissionId})?`)) {
            const dischargeData = {
                admission_id: admissionId,
                discharge_date: new Date().toISOString().slice(0, 19).replace('T', ' ') // Get current time in YYYY-MM-DD HH:MM:SS format
            };

            fetch(ADMISSION_API_URL + 'discharge.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dischargeData)
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                // Refresh lists
                getInpatients();
                loadAvailableBedsIntoSelect('admit-bed-select');
                 // also refresh the main bed list to show occupancy change
                getBeds();
            });
        }
    }
}
