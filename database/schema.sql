-- VENDORS TABLE
CREATE TABLE vendors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    parent_id INT DEFAULT NULL,
    role ENUM('super', 'regional', 'city', 'local') DEFAULT 'local',
    type ENUM('super_vendor', 'sub_vendor') DEFAULT 'sub_vendor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES vendors(id) ON DELETE SET NULL
);

-- VEHICLES TABLE
CREATE TABLE vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT NOT NULL,
    number VARCHAR(20) NOT NULL UNIQUE,   -- changed from reg_no to number for consistency
    type VARCHAR(30),                     -- added for vehicle type (e.g., Truck, Bus)
    model VARCHAR(50),
    capacity INT DEFAULT 0,
    fuel_type VARCHAR(20),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE
);

-- DRIVERS TABLE
CREATE TABLE drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendor_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    license_number VARCHAR(50) NOT NULL UNIQUE,
    license_expiry DATE NOT NULL,
    assigned_vehicle_id INT DEFAULT NULL,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL
);

-- DRIVER DOCUMENTS TABLE
CREATE TABLE driver_docs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL,
    doc_type ENUM('DL', 'RC', 'Permit', 'Pollution') NOT NULL,
    doc_file VARCHAR(255) NOT NULL,
    uploaded_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry_date DATE,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE
);
