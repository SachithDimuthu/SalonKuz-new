<?php
/**
 * User Model
 * 
 * This class handles all database operations related to users.
 */
class User {
    private $conn;

    /**
     * Constructor
     *
     * @param mysqli $conn Database connection
     */
    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Authenticate a user by email, password, and expected role.
     *
     * @param mysqli $conn Database connection
     * @param string $email User's email
     * @param string $password User's password
     * @param string $expectedRole Expected role ('admin' or 'customer')
     * @return array|bool User data if successful, false otherwise
     */
    public static function authenticate($conn, $email, $password, $expectedRole) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        if (!$stmt) {
            // Handle error, e.g., log or return false
            error_log('MySQLi prepare failed: ' . $conn->error);
            return false;
        }
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
             error_log('MySQLi execute failed: ' . $stmt->error);
             return false;
        }
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verify password and role
            if (password_verify($password, $user['password']) && $user['role'] === $expectedRole) {
                return $user;
            }
        }
        return false;
    }

    /**
     * Register a new user
     * 
     * @param string $username Username
     * @param string $password Password
     * @param string $email Email
     * @param string $firstName First name
     * @param string $lastName Last name
     * @param string $phone Phone number (optional)
     * @param string $role User role (default: customer)
     * @return bool|string True if successful, error message otherwise
     */
    public function register($name, $email, $password, $phone = null, $role = 'customer') {
        // Username check removed as we are simplifying to name and email for login
        
        // Check if email already exists
        if ($this->emailExists($email)) {
            return "Email already exists";
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare statement
        // The 'users' table in the provided schema has 'name', 'email', 'password', 'phone', 'role'
        $stmt = $this->conn->prepare("INSERT INTO users (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            return "Error preparing statement: " . $this->conn->error;
        }
        $stmt->bind_param("sssss", $name, $email, $hashedPassword, $phone, $role);
        
        // Execute statement
        if ($stmt->execute()) {
            return true;
        } else {
            return "Error: " . $stmt->error;
        }
    }
    
    /**
     * Login a user
     * 
     * @param string $username Username
     * @param string $password Password
     * @return array|bool User data if successful, false otherwise
     */
    public function login($username, $password) {
        // Prepare statement
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        
        // Execute statement
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        
        return false;
    }
    
    /**
     * Get user by ID
     * 
     * @param int $id User ID
     * @return array|bool User data if found, false otherwise
     */
    public function getUserById($id) {
        // Prepare statement
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        // Execute statement
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    /**
     * Update user profile
     * 
     * @param int $id User ID
     * @param string $firstName First name
     * @param string $lastName Last name
     * @param string $email Email
     * @param string $phone Phone number
     * @param string $profileImage Profile image (optional)
     * @return bool True if successful, false otherwise
     */
    public function updateProfile($id, $firstName, $lastName, $email, $phone, $profileImage = null) {
        // Check if email already exists for another user
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return false; // Email already exists for another user
        }
        
        // Prepare update statement
        if ($profileImage) {
            $stmt = $this->conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, profile_image = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $firstName, $lastName, $email, $phone, $profileImage, $id);
        } else {
            $stmt = $this->conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $firstName, $lastName, $email, $phone, $id);
        }
        
        // Execute statement
        return $stmt->execute();
    }
    
    /**
     * Change user password
     * 
     * @param int $id User ID
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return bool|string True if successful, error message otherwise
     */
    public function changePassword($id, $currentPassword, $newPassword) {
        // Get user
        $user = $this->getUserById($id);
        
        if (!$user) {
            return "User not found";
        }
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            return "Current password is incorrect";
        }
        
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Prepare statement
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $id);
        
        // Execute statement
        if ($stmt->execute()) {
            return true;
        } else {
            return "Error: " . $stmt->error;
        }
    }
    
    /**
     * Check if username exists
     * 
     * @param string $username Username
     * @return bool True if username exists, false otherwise
     */
    public function usernameExists($username) {
        // Prepare statement
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        
        // Execute statement
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
    
    /**
     * Check if email exists
     * 
     * @param string $email Email
     * @return bool True if email exists, false otherwise
     */
    public function emailExists($email) {
        // Prepare statement
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        
        // Execute statement
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
    
    /**
     * Get all users
     * 
     * @param string $role Filter by role (optional)
     * @param int $limit Limit results (optional)
     * @param int $offset Offset for pagination (optional)
     * @return array Array of users
     */
    public function getAllUsers($role = null, $limit = null, $offset = null) {
        // Base query
        $query = "SELECT * FROM users";
        
        // Add role filter if provided
        if ($role) {
            $query .= " WHERE role = '$role'";
        }
        
        // Add order by
        $query .= " ORDER BY id DESC";
        
        // Add limit and offset if provided
        if ($limit) {
            $query .= " LIMIT $limit";
            
            if ($offset) {
                $query .= " OFFSET $offset";
            }
        }
        
        // Execute query
        $result = $this->conn->query($query);
        $users = [];
        
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        return $users;
    }
    
    /**
     * Count users
     * 
     * @param string $role Filter by role (optional)
     * @return int Number of users
     */
    public function countUsers($role = null) {
        // Base query
        $query = "SELECT COUNT(*) as count FROM users";
        
        // Add role filter if provided
        if ($role) {
            $query .= " WHERE role = '$role'";
        }
        
        // Execute query
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();
        
        return $row['count'];
    }
    
    /**
     * Delete user
     * 
     * @param int $id User ID
     * @return bool True if successful, false otherwise
     */
    public function deleteUser($id) {
        // Prepare statement
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        // Execute statement
        return $stmt->execute();
    }
    
    /**
     * Update user role
     * 
     * @param int $id User ID
     * @param string $role New role
     * @return bool True if successful, false otherwise
     */
    public function updateUserRole($id, $role) {
        // Prepare statement
        $stmt = $this->conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $role, $id);
        
        // Execute statement
        return $stmt->execute();
    }
    
    /**
     * Search users
     * 
     * @param string $searchTerm Search term
     * @param string $role Filter by role (optional)
     * @return array Array of users
     */
    public function searchUsers($searchTerm, $role = null) {
        // Base query
        $query = "SELECT * FROM users WHERE (username LIKE '%$searchTerm%' OR email LIKE '%$searchTerm%' OR first_name LIKE '%$searchTerm%' OR last_name LIKE '%$searchTerm%')";
        
        // Add role filter if provided
        if ($role) {
            $query .= " AND role = '$role'";
        }
        
        // Add order by
        $query .= " ORDER BY id DESC";
        
        // Execute query
        $result = $this->conn->query($query);
        $users = [];
        
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        return $users;
    }
    
    /**
     * Get employees who can perform a specific service
     * 
     * @param int $serviceId Service ID
     * @return array Array of employees
     */
    public function getEmployeesByService($serviceId) {
        // Prepare statement
        $stmt = $this->conn->prepare("
            SELECT u.* FROM users u
            JOIN employee_services es ON u.id = es.employee_id
            WHERE es.service_id = ? AND u.role = 'employee'
        ");
        $stmt->bind_param("i", $serviceId);
        
        // Execute statement
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = [];
        
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
        
        return $employees;
    }
    
    /**
     * Assign service to employee
     * 
     * @param int $employeeId Employee ID
     * @param int $serviceId Service ID
     * @return bool True if successful, false otherwise
     */
    public function assignServiceToEmployee($employeeId, $serviceId) {
        // Check if already assigned
        $stmt = $this->conn->prepare("SELECT id FROM employee_services WHERE employee_id = ? AND service_id = ?");
        $stmt->bind_param("ii", $employeeId, $serviceId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return true; // Already assigned
        }
        
        // Prepare statement
        $stmt = $this->conn->prepare("INSERT INTO employee_services (employee_id, service_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $employeeId, $serviceId);
        
        // Execute statement
        return $stmt->execute();
    }
    
    /**
     * Remove service from employee
     * 
     * @param int $employeeId Employee ID
     * @param int $serviceId Service ID
     * @return bool True if successful, false otherwise
     */
    public function removeServiceFromEmployee($employeeId, $serviceId) {
        // Prepare statement
        $stmt = $this->conn->prepare("DELETE FROM employee_services WHERE employee_id = ? AND service_id = ?");
        $stmt->bind_param("ii", $employeeId, $serviceId);
        
        // Execute statement
        return $stmt->execute();
    }
    
    /**
     * Get services assigned to employee
     * 
     * @param int $employeeId Employee ID
     * @return array Array of service IDs
     */
    public function getEmployeeServices($employeeId) {
        // Prepare statement
        $stmt = $this->conn->prepare("SELECT service_id FROM employee_services WHERE employee_id = ?");
        $stmt->bind_param("i", $employeeId);
        
        // Execute statement
        $stmt->execute();
        $result = $stmt->get_result();
        $serviceIds = [];
        
        while ($row = $result->fetch_assoc()) {
            $serviceIds[] = $row['service_id'];
        }
        
        return $serviceIds;
    }
}
?>
