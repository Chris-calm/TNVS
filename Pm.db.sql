CREATE TABLE facilities (
    facility_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_name VARCHAR(150) NOT NULL,
    facility_image VARCHAR(255),
    status ENUM('Available', 'Under Maintenance') DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


CREATE TABLE reservations (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    reserved_by VARCHAR(100) NOT NULL, -- just a name instead of user account
    reservation_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected', 'Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_facility FOREIGN KEY (facility_id) REFERENCES facilities(facility_id) ON DELETE CASCADE
);

CREATE TABLE maintenance_logs (
    maintenance_id INT AUTO_INCREMENT PRIMARY KEY,
    facility_id INT NOT NULL,
    reason TEXT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_facility_maintenance FOREIGN KEY (facility_id) REFERENCES facilities(facility_id) ON DELETE CASCADE
);
