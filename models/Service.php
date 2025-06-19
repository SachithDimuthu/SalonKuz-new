<?php
/**
 * Service Model
 * 
 * This class handles all database operations related to services.
 */
class Service {
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
     * Add a new service
     * 
     * @param string $name Service name
     * @param string $description Service description
     * @param float $price Service price
     * @param int $duration Service duration in minutes
     * @param string $category Service category
     * @param string $image Service image (optional)
     * @return bool|string True if successful, error message otherwise
     */
    public function addService($name, $description, $price, $duration, $category, $image = null) {
        // Prepare statement
        $stmt = $this->conn->prepare("INSERT INTO services (name, description, price, duration, category, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdiis", $name, $description, $price, $duration, $category, $image);
        
        // Execute statement
        if ($stmt->execute()) {
            return true;
        } else {
            return "Error: " . $stmt->error;
        }
    }
    
    /**
     * Update a service
     * 
     * @param int $id Service ID
     * @param string $name Service name
     * @param string $description Service description
     * @param float $price Service price
     * @param int $duration Service duration in minutes
     * @param string $category Service category
     * @param string $image Service image (optional)
     * @return bool True if successful, false otherwise
     */
    public function updateService($id, $name, $description, $price, $duration, $category, $image = null) {
        // Prepare statement
        if ($image) {
            $stmt = $this->conn->prepare("UPDATE services SET name = ?, description = ?, price = ?, duration = ?, category = ?, image = ? WHERE id = ?");
            $stmt->bind_param("ssdissi", $name, $description, $price, $duration, $category, $image, $id);
        } else {
            $stmt = $this->conn->prepare("UPDATE services SET name = ?, description = ?, price = ?, duration = ?, category = ? WHERE id = ?");
            $stmt->bind_param("ssdisi", $name, $description, $price, $duration, $category, $id);
        }
        
        // Execute statement
        return $stmt->execute();
    }
    
    /**
     * Delete a service
     * 
     * @param int $id Service ID
     * @return bool True if successful, false otherwise
     */
    public function deleteService($id) {
        // Prepare statement
        $stmt = $this->conn->prepare("DELETE FROM services WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        // Execute statement
        return $stmt->execute();
    }
    
    /**
     * Get service by ID
     * 
     * @param int $id Service ID
     * @return array|bool Service data if found, false otherwise
     */
    public function getServiceById($id) {
        // Prepare statement
        $stmt = $this->conn->prepare("SELECT * FROM services WHERE id = ?");
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
     * Get all services
     * 
     * @param string $category Filter by category (optional)
     * @param int $limit Limit results (optional)
     * @param int $offset Offset for pagination (optional)
     * @return array Array of services
     */
    public function getAllServices($category = null, $limit = null, $offset = null) {
        // Base query
        $query = "SELECT * FROM services";
        
        // Add category filter if provided
        if ($category) {
            $query .= " WHERE category = '$category'";
        }
        
        // Add order by
        $query .= " ORDER BY name ASC";
        
        // Add limit and offset if provided
        if ($limit) {
            $query .= " LIMIT $limit";
            
            if ($offset) {
                $query .= " OFFSET $offset";
            }
        }
        
        // Execute query
        $result = $this->conn->query($query);
        $services = [];
        
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
        
        return $services;
    }
    
    /**
     * Count services
     * 
     * @param string $category Filter by category (optional)
     * @return int Number of services
     */
    public function countServices($category = null) {
        // Base query
        $query = "SELECT COUNT(*) as count FROM services";
        
        // Add category filter if provided
        if ($category) {
            $query .= " WHERE category = '$category'";
        }
        
        // Execute query
        $result = $this->conn->query($query);
        $row = $result->fetch_assoc();
        
        return $row['count'];
    }
    
    /**
     * Search services
     * 
     * @param string $searchTerm Search term
     * @param string $category Filter by category (optional)
     * @return array Array of services
     */
    public function searchServices($searchTerm, $category = null) {
        // Base query
        $query = "SELECT * FROM services WHERE (name LIKE '%$searchTerm%' OR description LIKE '%$searchTerm%')";
        
        // Add category filter if provided
        if ($category) {
            $query .= " AND category = '$category'";
        }
        
        // Add order by
        $query .= " ORDER BY name ASC";
        
        // Execute query
        $result = $this->conn->query($query);
        $services = [];
        
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
        
        return $services;
    }
    
    /**
     * Get service categories
     * 
     * @return array Array of categories
     */
    public function getServiceCategories() {
        // Execute query
        $result = $this->conn->query("SELECT DISTINCT category FROM services ORDER BY category ASC");
        $categories = [];
        
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category'];
        }
        
        return $categories;
    }
    
    /**
     * Get services with active deals
     * 
     * @return array Array of services with deal information
     */
    public function getServicesWithDeals() {
        $currentDate = date('Y-m-d');
        
        // Execute query
        $query = "
            SELECT s.*, d.id as deal_id, d.title as deal_title, d.discount_percentage
            FROM services s
            JOIN deal_services ds ON s.id = ds.service_id
            JOIN deals d ON ds.deal_id = d.id
            WHERE d.start_date <= '$currentDate' AND d.end_date >= '$currentDate'
            ORDER BY s.name ASC
        ";
        
        $result = $this->conn->query($query);
        $services = [];
        
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
        
        return $services;
    }
    
    /**
     * Get services by employee
     * 
     * @param int $employeeId Employee ID
     * @return array Array of services
     */
    public function getServicesByEmployee($employeeId) {
        // Prepare statement
        $stmt = $this->conn->prepare("
            SELECT s.* FROM services s
            JOIN employee_services es ON s.id = es.service_id
            WHERE es.employee_id = ?
            ORDER BY s.name ASC
        ");
        $stmt->bind_param("i", $employeeId);
        
        // Execute statement
        $stmt->execute();
        $result = $stmt->get_result();
        $services = [];
        
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
        
        return $services;
    }
    
    /**
     * Get popular services
     * 
     * @param int $limit Limit results
     * @return array Array of services with booking count
     */
    public function getPopularServices($limit = 5) {
        // Execute query
        $query = "
            SELECT s.*, COUNT(b.id) as booking_count
            FROM services s
            LEFT JOIN bookings b ON s.id = b.service_id
            GROUP BY s.id
            ORDER BY booking_count DESC
            LIMIT $limit
        ";
        
        $result = $this->conn->query($query);
        $services = [];
        
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
        
        return $services;
    }
}
?>
