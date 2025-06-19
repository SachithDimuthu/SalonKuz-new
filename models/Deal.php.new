<?php
/**
 * Deal Model
 * 
 * This class handles all database operations related to deals and discounts.
 */
class Deal {
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
     * Create a new deal
     * 
     * @param string $name Deal name
     * @param string $description Deal description
     * @param int $serviceId Service ID
     * @param float $discountPercentage Discount percentage
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return int|bool Deal ID if successful, false otherwise
     */
    public function createDeal($name, $description, $serviceId, $discountPercentage, $startDate, $endDate) {
        // Begin transaction
        $this->conn->begin_transaction();
        
        try {
            // Prepare statement for deal
            $stmt = $this->conn->prepare("
                INSERT INTO deals (name, description, service_id, discount_percentage, start_date, end_date, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->bind_param("ssidss", $name, $description, $serviceId, $discountPercentage, $startDate, $endDate);
            
            // Execute statement
            if (!$stmt->execute()) {
                throw new Exception("Error adding deal: " . $stmt->error);
            }
            
            // Get the deal ID
            $dealId = $this->conn->insert_id;
            
            // Commit transaction
            $this->conn->commit();
            return $dealId;
        } catch (Exception $e) {
            // Rollback transaction
            $this->conn->rollback();
            error_log('Deal creation error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update an existing deal
     * 
     * @param int $id Deal ID
     * @param string $name Deal name
     * @param string $description Deal description
     * @param int $serviceId Service ID
     * @param float $discountPercentage Discount percentage
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return bool True if successful, false otherwise
     */
    public function updateDeal($id, $name, $description, $serviceId, $discountPercentage, $startDate, $endDate) {
        // Begin transaction
        $this->conn->begin_transaction();
        
        try {
            // Prepare statement for deal
            $stmt = $this->conn->prepare("
                UPDATE deals 
                SET name = ?, 
                    description = ?, 
                    service_id = ?, 
                    discount_percentage = ?, 
                    start_date = ?, 
                    end_date = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("ssidssi", $name, $description, $serviceId, $discountPercentage, $startDate, $endDate, $id);
            
            // Execute statement
            if (!$stmt->execute()) {
                throw new Exception("Error updating deal: " . $stmt->error);
            }
            
            // Commit transaction
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction
            $this->conn->rollback();
            error_log('Deal update error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a deal
     * 
     * @param int $id Deal ID
     * @return bool True if successful, false otherwise
     */
    public function deleteDeal($id) {
        try {
            // Prepare statement
            $stmt = $this->conn->prepare("DELETE FROM deals WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            // Execute statement
            return $stmt->execute();
        } catch (Exception $e) {
            error_log('Deal deletion error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get deal by ID with service information
     * 
     * @param int $id Deal ID
     * @return array|bool Deal data if found, false otherwise
     */
    public function getDealById($id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT d.*, s.name as service_name, s.price as service_price
                FROM deals d
                JOIN services s ON d.service_id = s.id
                WHERE d.id = ?
            ");
            
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                return $result->fetch_assoc();
            }
            
            return false;
        } catch (Exception $e) {
            error_log('Get deal error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all deals with service information
     * 
     * @param int $limit Limit results (optional)
     * @param int $offset Offset for pagination (optional)
     * @return array Array of deals
     */
    public function getAllDeals($limit = null, $offset = null) {
        try {
            $query = "
                SELECT d.*, s.name as service_name, s.price as service_price
                FROM deals d
                JOIN services s ON d.service_id = s.id
                ORDER BY d.start_date DESC
            ";
            
            // Add limit and offset if provided
            if ($limit !== null) {
                $query .= " LIMIT ?";
                if ($offset !== null) {
                    $query .= " OFFSET ?";
                }
            }
            
            $stmt = $this->conn->prepare($query);
            
            if ($limit !== null) {
                if ($offset !== null) {
                    $stmt->bind_param("ii", $limit, $offset);
                } else {
                    $stmt->bind_param("i", $limit);
                }
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log('Get all deals error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get filtered deals with service information
     * 
     * @param array $filters Array of filters (status, service_id, search)
     * @param int $limit Limit results (optional)
     * @param int $offset Offset for pagination (optional)
     * @return array Array of deals
     */
    public function getFilteredDeals($filters = [], $limit = null, $offset = null) {
        try {
            $query = "
                SELECT d.*, s.name as service_name, s.price as service_price
                FROM deals d
                JOIN services s ON d.service_id = s.id
                WHERE 1=1
            ";
            
            $bindTypes = "";
            $bindParams = [];
            
            // Apply filters
            if (!empty($filters['status'])) {
                $currentDate = date('Y-m-d');
                
                if ($filters['status'] === 'active') {
                    $query .= " AND d.start_date <= ? AND d.end_date >= ?";
                    $bindTypes .= "ss";
                    $bindParams[] = $currentDate;
                    $bindParams[] = $currentDate;
                } elseif ($filters['status'] === 'upcoming') {
                    $query .= " AND d.start_date > ?";
                    $bindTypes .= "s";
                    $bindParams[] = $currentDate;
                } elseif ($filters['status'] === 'expired') {
                    $query .= " AND d.end_date < ?";
                    $bindTypes .= "s";
                    $bindParams[] = $currentDate;
                }
            }
            
            if (!empty($filters['service_id'])) {
                $query .= " AND d.service_id = ?";
                $bindTypes .= "i";
                $bindParams[] = $filters['service_id'];
            }
            
            if (!empty($filters['search'])) {
                $searchTerm = "%" . $filters['search'] . "%";
                $query .= " AND (d.name LIKE ? OR d.description LIKE ? OR s.name LIKE ?)";
                $bindTypes .= "sss";
                $bindParams[] = $searchTerm;
                $bindParams[] = $searchTerm;
                $bindParams[] = $searchTerm;
            }
            
            // Add order by
            $query .= " ORDER BY d.start_date DESC";
            
            // Add limit and offset if provided
            if ($limit !== null) {
                $query .= " LIMIT ?";
                $bindTypes .= "i";
                $bindParams[] = $limit;
                
                if ($offset !== null) {
                    $query .= " OFFSET ?";
                    $bindTypes .= "i";
                    $bindParams[] = $offset;
                }
            }
            
            $stmt = $this->conn->prepare($query);
            
            if (!empty($bindParams)) {
                // Create the bind_param arguments dynamically
                $bindParamsRef = [];
                foreach ($bindParams as $key => $value) {
                    $bindParamsRef[] = &$bindParams[$key];
                }
                array_unshift($bindParamsRef, $bindTypes);
                call_user_func_array([$stmt, 'bind_param'], $bindParamsRef);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log('Get filtered deals error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Count filtered deals
     * 
     * @param array $filters Array of filters (status, service_id, search)
     * @return int Number of deals
     */
    public function countFilteredDeals($filters = []) {
        try {
            $query = "
                SELECT COUNT(*) as total
                FROM deals d
                JOIN services s ON d.service_id = s.id
                WHERE 1=1
            ";
            
            $bindTypes = "";
            $bindParams = [];
            
            // Apply filters
            if (!empty($filters['status'])) {
                $currentDate = date('Y-m-d');
                
                if ($filters['status'] === 'active') {
                    $query .= " AND d.start_date <= ? AND d.end_date >= ?";
                    $bindTypes .= "ss";
                    $bindParams[] = $currentDate;
                    $bindParams[] = $currentDate;
                } elseif ($filters['status'] === 'upcoming') {
                    $query .= " AND d.start_date > ?";
                    $bindTypes .= "s";
                    $bindParams[] = $currentDate;
                } elseif ($filters['status'] === 'expired') {
                    $query .= " AND d.end_date < ?";
                    $bindTypes .= "s";
                    $bindParams[] = $currentDate;
                }
            }
            
            if (!empty($filters['service_id'])) {
                $query .= " AND d.service_id = ?";
                $bindTypes .= "i";
                $bindParams[] = $filters['service_id'];
            }
            
            if (!empty($filters['search'])) {
                $searchTerm = "%" . $filters['search'] . "%";
                $query .= " AND (d.name LIKE ? OR d.description LIKE ? OR s.name LIKE ?)";
                $bindTypes .= "sss";
                $bindParams[] = $searchTerm;
                $bindParams[] = $searchTerm;
                $bindParams[] = $searchTerm;
            }
            
            $stmt = $this->conn->prepare($query);
            
            if (!empty($bindParams)) {
                // Create the bind_param arguments dynamically
                $bindParamsRef = [];
                foreach ($bindParams as $key => $value) {
                    $bindParamsRef[] = &$bindParams[$key];
                }
                array_unshift($bindParamsRef, $bindTypes);
                call_user_func_array([$stmt, 'bind_param'], $bindParamsRef);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return (int) $row['total'];
        } catch (Exception $e) {
            error_log('Count filtered deals error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Count deals by status
     * 
     * @param string $status Status (active, upcoming, expired)
     * @return int Number of deals
     */
    public function countDealsByStatus($status) {
        try {
            $query = "SELECT COUNT(*) as total FROM deals WHERE 1=1";
            $bindTypes = "";
            $bindParams = [];
            
            $currentDate = date('Y-m-d');
            
            if ($status === 'active') {
                $query .= " AND start_date <= ? AND end_date >= ?";
                $bindTypes .= "ss";
                $bindParams[] = $currentDate;
                $bindParams[] = $currentDate;
            } elseif ($status === 'upcoming') {
                $query .= " AND start_date > ?";
                $bindTypes .= "s";
                $bindParams[] = $currentDate;
            } elseif ($status === 'expired') {
                $query .= " AND end_date < ?";
                $bindTypes .= "s";
                $bindParams[] = $currentDate;
            }
            
            $stmt = $this->conn->prepare($query);
            
            if (!empty($bindParams)) {
                // Create the bind_param arguments dynamically
                $bindParamsRef = [];
                foreach ($bindParams as $key => $value) {
                    $bindParamsRef[] = &$bindParams[$key];
                }
                array_unshift($bindParamsRef, $bindTypes);
                call_user_func_array([$stmt, 'bind_param'], $bindParamsRef);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return (int) $row['total'];
        } catch (Exception $e) {
            error_log('Count deals by status error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get active deal for a service
     * 
     * @param int $serviceId Service ID
     * @return array|bool Deal data if found, false otherwise
     */
    public function getActiveDealForService($serviceId) {
        try {
            $currentDate = date('Y-m-d');
            
            $stmt = $this->conn->prepare("
                SELECT d.*, s.name as service_name, s.price as service_price
                FROM deals d
                JOIN services s ON d.service_id = s.id
                WHERE d.service_id = ? 
                AND d.start_date <= ? 
                AND d.end_date >= ?
                ORDER BY d.discount_percentage DESC
                LIMIT 1
            ");
            
            $stmt->bind_param("iss", $serviceId, $currentDate, $currentDate);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                return $result->fetch_assoc();
            }
            
            return false;
        } catch (Exception $e) {
            error_log('Get active deal error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get bookings that used a specific deal
     * 
     * @param int $dealId Deal ID
     * @return array Array of bookings
     */
    public function getBookingsWithDeal($dealId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT b.*, 
                       s.name as service_name, 
                       s.price as service_price,
                       c.name as customer_name,
                       e.name as employee_name,
                       c.id as customer_id,
                       e.id as employee_id
                FROM bookings b
                JOIN services s ON b.service_id = s.id
                JOIN users c ON b.user_id = c.id
                JOIN users e ON b.employee_id = e.id
                WHERE b.deal_id = ?
                ORDER BY b.booking_date DESC, b.booking_time DESC
            ");
            
            $stmt->bind_param("i", $dealId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log('Get bookings with deal error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if a deal exists and is valid for a service
     * 
     * @param int $dealId Deal ID
     * @param int $serviceId Service ID
     * @return bool True if deal is valid, false otherwise
     */
    public function isValidDealForService($dealId, $serviceId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as count
                FROM deals
                WHERE id = ? AND service_id = ?
            ");
            
            $stmt->bind_param("ii", $dealId, $serviceId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            return (int) $row['count'] > 0;
        } catch (Exception $e) {
            error_log('Check valid deal error: ' . $e->getMessage());
            return false;
        }
    }
}
