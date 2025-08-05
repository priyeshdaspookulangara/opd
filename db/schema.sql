-- Main Entities
CREATE TABLE patients (
    patient_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    phone_number VARCHAR(20),
    email VARCHAR(100) UNIQUE,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Doctor', 'Front Desk', 'Pharmacist', 'Lab Technician') NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Default Admin User
INSERT INTO `users` (`user_id`, `username`, `password_hash`, `role`, `first_name`, `last_name`, `is_active`) VALUES
(1, 'admin@his.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Admin', 'User', 1);


-- Financial & Service Management
CREATE TABLE services (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(100) NOT NULL,
    description TEXT,
    cost DECIMAL(10, 2) NOT NULL
);

CREATE TABLE packages (
    package_id INT AUTO_INCREMENT PRIMARY KEY,
    package_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    duration_days INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

-- Linker table for many-to-many relationship between packages and services
CREATE TABLE package_services (
    package_service_id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    service_id INT NOT NULL,
    FOREIGN KEY (package_id) REFERENCES packages(package_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE CASCADE
);

-- Patient-specific records
CREATE TABLE accounts (
    account_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    balance DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE
);

CREATE TABLE opd_visits (
    op_id INT AUTO_INCREMENT PRIMARY KEY,
    op_number VARCHAR(20) NOT NULL UNIQUE,
    patient_id INT NOT NULL,
    visit_date DATE NOT NULL,
    doctor_id INT,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES users(user_id)
);

CREATE TABLE vitals (
    vital_id INT AUTO_INCREMENT PRIMARY KEY,
    op_id INT NOT NULL,
    temperature DECIMAL(4, 1),
    blood_pressure VARCHAR(20),
    heart_rate INT,
    respiratory_rate INT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (op_id) REFERENCES opd_visits(op_id) ON DELETE CASCADE
);

CREATE TABLE patient_packages (
    patient_package_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    package_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active BOOLEAN GENERATED ALWAYS AS (end_date >= CURDATE()) VIRTUAL,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(package_id)
);

-- Billing and Transactions
CREATE TABLE billings (
    bill_id INT AUTO_INCREMENT PRIMARY KEY,
    op_id INT, -- Can be NULL for non-visit related charges
    patient_id INT NOT NULL,
    service_id INT,
    patient_package_id INT, -- Link to the package instance if the service is covered
    amount DECIMAL(10, 2) NOT NULL,
    bill_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description VARCHAR(255),
    FOREIGN KEY (op_id) REFERENCES opd_visits(op_id),
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(service_id),
    FOREIGN KEY (patient_package_id) REFERENCES patient_packages(patient_package_id)
);

CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    amount_paid DECIMAL(10, 2) NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_method VARCHAR(50),
    notes TEXT,
    FOREIGN KEY (account_id) REFERENCES accounts(account_id)
);

-- Clinical Records
CREATE TABLE doctor_consultations (
    consultation_id INT AUTO_INCREMENT PRIMARY KEY,
    op_id INT NOT NULL,
    doctor_id INT NOT NULL,
    diagnosis TEXT,
    prescription TEXT,
    notes TEXT,
    consultation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (op_id) REFERENCES opd_visits(op_id),
    FOREIGN KEY (doctor_id) REFERENCES users(user_id)
);

-- Example of other modules' tables
CREATE TABLE pharmacy_drugs (
    drug_id INT AUTO_INCREMENT PRIMARY KEY,
    drug_name VARCHAR(100) NOT NULL,
    description TEXT,
    cost_per_unit DECIMAL(10, 2) NOT NULL,
    stock_quantity INT NOT NULL
);

CREATE TABLE pharmacy_dispensations (
    dispensation_id INT AUTO_INCREMENT PRIMARY KEY,
    op_id INT NOT NULL,
    drug_id INT NOT NULL,
    quantity INT NOT NULL,
    total_cost DECIMAL(10, 2) NOT NULL,
    dispensed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (op_id) REFERENCES opd_visits(op_id),
    FOREIGN KEY (drug_id) REFERENCES pharmacy_drugs(drug_id)
);

CREATE TABLE available_lab_tests (
    available_test_id INT AUTO_INCREMENT PRIMARY KEY,
    test_name VARCHAR(100) NOT NULL,
    description TEXT,
    cost DECIMAL(10, 2) NOT NULL,
    is_available BOOLEAN DEFAULT TRUE
);

CREATE TABLE lab_tests (
    lab_test_id INT AUTO_INCREMENT PRIMARY KEY,
    op_id INT NOT NULL,
    available_test_id INT NOT NULL,
    status ENUM('Pending', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    result TEXT,
    notes TEXT,
    test_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (op_id) REFERENCES opd_visits(op_id),
    FOREIGN KEY (available_test_id) REFERENCES available_lab_tests(available_test_id)
);

CREATE TABLE available_radiology_tests (
    available_test_id INT AUTO_INCREMENT PRIMARY KEY,
    test_name VARCHAR(100) NOT NULL,
    description TEXT,
    cost DECIMAL(10, 2) NOT NULL,
    is_available BOOLEAN DEFAULT TRUE
);

CREATE TABLE radiology_orders (
    radiology_order_id INT AUTO_INCREMENT PRIMARY KEY,
    op_id INT NOT NULL,
    available_test_id INT NOT NULL,
    status ENUM('Pending', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    report TEXT,
    notes TEXT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (op_id) REFERENCES opd_visits(op_id),
    FOREIGN KEY (available_test_id) REFERENCES available_radiology_tests(available_test_id)
);

-- Inpatient Management Tables
CREATE TABLE wards (
    ward_id INT AUTO_INCREMENT PRIMARY KEY,
    ward_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE beds (
    bed_id INT AUTO_INCREMENT PRIMARY KEY,
    bed_number VARCHAR(20) NOT NULL,
    ward_id INT NOT NULL,
    is_occupied BOOLEAN DEFAULT FALSE,
    UNIQUE (ward_id, bed_number),
    FOREIGN KEY (ward_id) REFERENCES wards(ward_id) ON DELETE CASCADE
);

CREATE TABLE admissions (
    admission_id INT AUTO_INCREMENT PRIMARY KEY,
    ipd_number VARCHAR(20) NOT NULL UNIQUE,
    patient_id INT NOT NULL,
    bed_id INT NOT NULL,
    admission_date DATETIME NOT NULL,
    discharge_date DATETIME,
    notes TEXT,
    is_active BOOLEAN GENERATED ALWAYS AS (discharge_date IS NULL) VIRTUAL,
    FOREIGN KEY (patient_id) REFERENCES patients(patient_id),
    FOREIGN KEY (bed_id) REFERENCES beds(bed_id)
);
