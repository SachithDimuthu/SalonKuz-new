<?php
/**
 * Database Schema File
 * 
 * This file contains the SQL statements to create all the necessary tables
 * for the Salon Kuz website database.
 */

require_once 'database.php';

$conn = getDBConnection();

// Create users table
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'employee', 'customer') NOT NULL DEFAULT 'customer',
    profile_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Create services table
$sql_services = "CREATE TABLE IF NOT EXISTS services (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration INT NOT NULL COMMENT 'Duration in minutes',
    category VARCHAR(50) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Create deals table
$sql_deals = "CREATE TABLE IF NOT EXISTS deals (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    discount_percentage DECIMAL(5,2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Create deal_services table (for linking deals to specific services)
$sql_deal_services = "CREATE TABLE IF NOT EXISTS deal_services (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    deal_id INT(11) NOT NULL,
    service_id INT(11) NOT NULL,
    FOREIGN KEY (deal_id) REFERENCES deals(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    UNIQUE KEY unique_deal_service (deal_id, service_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Create bookings table
$sql_bookings = "CREATE TABLE IF NOT EXISTS bookings (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    service_id INT(11) NOT NULL,
    employee_id INT(11) NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    deal_id INT(11) DEFAULT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (deal_id) REFERENCES deals(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Create employee_services table (to track which employees can perform which services)
$sql_employee_services = "CREATE TABLE IF NOT EXISTS employee_services (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    employee_id INT(11) NOT NULL,
    service_id INT(11) NOT NULL,
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_service (employee_id, service_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Create reviews table
$sql_reviews = "CREATE TABLE IF NOT EXISTS reviews (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    service_id INT(11) NOT NULL,
    booking_id INT(11) NOT NULL,
    rating INT(1) NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    UNIQUE KEY unique_booking_review (booking_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Execute the SQL statements
if (!mysqli_query($conn, $sql_users)) {
    die("Error creating users table: " . mysqli_error($conn));
}

if (!mysqli_query($conn, $sql_services)) {
    die("Error creating services table: " . mysqli_error($conn));
}

if (!mysqli_query($conn, $sql_deals)) {
    die("Error creating deals table: " . mysqli_error($conn));
}

if (!mysqli_query($conn, $sql_deal_services)) {
    die("Error creating deal_services table: " . mysqli_error($conn));
}

if (!mysqli_query($conn, $sql_bookings)) {
    die("Error creating bookings table: " . mysqli_error($conn));
}

if (!mysqli_query($conn, $sql_employee_services)) {
    die("Error creating employee_services table: " . mysqli_error($conn));
}

if (!mysqli_query($conn, $sql_reviews)) {
    die("Error creating reviews table: " . mysqli_error($conn));
}

// Insert default admin user
$default_admin_password = password_hash("admin123", PASSWORD_DEFAULT);
$check_admin = "SELECT * FROM users WHERE username = 'admin'";
$result = mysqli_query($conn, $check_admin);

if (mysqli_num_rows($result) == 0) {
    $insert_admin = "INSERT INTO users (username, password, email, first_name, last_name, role) 
                     VALUES ('admin', '$default_admin_password', 'admin@salonkuz.com', 'Admin', 'User', 'admin')";
    if (!mysqli_query($conn, $insert_admin)) {
        die("Error inserting default admin: " . mysqli_error($conn));
    }
    echo "Default admin user created successfully.<br>";
}

echo "Database schema created successfully.<br>";
?>
