<?php
/**
 * Booking Model
 * 
 * This class handles all database operations related to bookings.
 */
class Booking {
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
     * Create a new booking
     * 
     * @param int $userId User ID
     * @param int $serviceId Service ID
     * @param int $employeeId Employee ID
     * @param string $bookingDate Booking date (YYYY-MM-DD)
     * @param string $bookingTime Booking time (HH:MM:SS)
     * @param string $status Booking status (default: pending)
     * @param int $dealId Deal ID (optional)
     * @param string $notes Booking notes (optional)
     * @return bool|string True if successful, error message otherwise
     */
    public function createBooking($userId, $serviceId, $employeeId, $bookingDate, $bookingTime, $status = 'pending', $dealId = null, $notes = null) {
        // Check if employee is available at the specified time
        if (!$this->isEmployeeAvailable($employeeId, $bookingDate, $bookingTime, $serviceId)) {
            return "Employee is not available at the specified time";
        }
        
        // Prepare statement
        $stmt = $this->conn->prepare("INSERT INTO bookings (user_id, service_id, employee_id, booking_date, booking_time, status, deal_id, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisssss", $userId, $serviceId, $employeeId, $bookingDate, $bookingTime, $status, $dealId, $notes);
        
        // Execute statement
        if ($stmt->execute()) {
            return true;
        } else {
            return "Error: " . $stmt->error;
        }
    }
    
    /**
     * Update a booking
     * 
     * @param int $id Booking ID
     * @param int $serviceId Service ID
     * @param int $employeeId Employee ID
     * @param string $bookingDate Booking date (YYYY-MM-DD)
     * @param string $bookingTime Booking time (HH:MM:SS)
     * @param string $status Booking status
     * @param int $dealId Deal ID (optional)
     * @param string $notes Booking notes (optional)
     * @return bool|string True if successful, error message otherwise
     */
    public function updateBooking($id, $serviceId, $employeeId, $bookingDate, $bookingTime, $status, $dealId = null, $notes = null) {
        // Check if employee is available at the specified time (excluding this booking)
        if (!$this->isEmployeeAvailable($employeeId, $bookingDate, $bookingTime, $serviceId, $id)) {
            return "Employee is not available at the specified time";
        }
        
        // Prepare statement
        $stmt = $this->conn->prepare("UPDATE bookings SET service_id = ?, employee_id = ?, booking_date = ?, booking_time = ?, status = ?, deal_id = ?, notes = ? WHERE id = ?");
        $stmt->bind_param("iiissssi", $serviceId, $employeeId, $bookingDate, $bookingTime, $status, $dealId, $notes, $id);
        
        // Execute statement
        if ($stmt->execute()) {
            return true;
        } else {
            return "Error: " . $stmt->error;
        }
    }
    
    /**
     * Update booking status
     * 
     * @param int $id Booking ID
     * @param string $status New status
     * @return bool True if successful, false otherwise
     */
    public function updateBookingStatus($id, $status) {
        // Prepare statement
        $stmt = $this->conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        
        // Execute statement
        return $stmt->execute();
    }
    
    /**
     * Delete a booking
     * 
     * @param int $id Booking ID
     * @return bool True if successful, false otherwise
     */
    public function deleteBooking($id) {
        // Prepare statement
        $stmt = $this->conn->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        // Execute statement
        return $stmt->execute();
    }
    
    /**
     * Get booking by ID
     * 
     * @param int $id Booking ID
     * @return array|bool Booking data if found, false otherwise
     */
    public function getBookingById($id) {
        // Prepare statement
        $stmt = $this->conn->prepare("
            SELECT b.*, 
                   u.first_name as user_first_name, u.last_name as user_last_name,
                   e.first_name as employee_first_name, e.last_name as employee_last_name,
                   s.name as service_name, s.price as service_price, s.duration as service_duration,
                   d.title as deal_title, d.discount_percentage
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN users e ON b.employee_id = e.id
            JOIN services s ON b.service_id = s.id
            LEFT JOIN deals d ON b.deal_id = d.id
            WHERE b.id = ?
        ");
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
     * Get bookings by user
     * 
     * @param int $userId User ID
     * @param string $status Filter by status (optional)
     * @param int $limit Limit results (optional)
     * @param int $offset Offset for pagination (optional)
     * @return array Array of bookings
     */
    public function getBookingsByUser($userId, $status = null, $limit = null, $offset = null) {
        // Base query
        $query = "
            SELECT b.*, 
                   u.first_name as user_first_name, u.last_name as user_last_name,
                   e.first_name as employee_first_name, e.last_name as employee_last_name,
                   s.name as service_name, s.price as service_price, s.duration as service_duration,
                   d.title as deal_title, d.discount_percentage
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN users e ON b.employee_id = e.id
            JOIN services s ON b.service_id = s.id
            LEFT JOIN deals d ON b.deal_id = d.id
            WHERE b.user_id = ?
        ";
        
        // Add status filter if provided
        $params = [$userId];
        $types = "i";
        
        if ($status) {
            $query .= " AND b.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        // Add order by
        $query .= " ORDER BY b.booking_date DESC, b.booking_time DESC";
        
        // Add limit and offset if provided
        if ($limit) {
            $query .= " LIMIT ?";
            $params[] = $limit;
            $types .= "i";
            
            if ($offset) {
                $query .= " OFFSET ?";
                $params[] = $offset;
                $types .= "i";
            }
        }
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        // Execute statement
        $stmt->execute();
        $result = $stmt->get_result();
        $bookings = [];
        
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        
        return $bookings;
    }
    
    /**
     * Get bookings by employee
     * 
     * @param int $employeeId Employee ID
     * @param string $status Filter by status (optional)
     * @param string $date Filter by date (optional)
     * @param int $limit Limit results (optional)
     * @param int $offset Offset for pagination (optional)
     * @return array Array of bookings
     */
    public function getBookingsByEmployee($employeeId, $status = null, $date = null, $limit = null, $offset = null) {
        // Base query
        $query = "
            SELECT b.*, 
                   u.first_name as user_first_name, u.last_name as user_last_name,
                   e.first_name as employee_first_name, e.last_name as employee_last_name,
                   s.name as service_name, s.price as service_price, s.duration as service_duration,
                   d.title as deal_title, d.discount_percentage
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN users e ON b.employee_id = e.id
            JOIN services s ON b.service_id = s.id
            LEFT JOIN deals d ON b.deal_id = d.id
            WHERE b.employee_id = ?
        ";
        
        // Add filters if provided
        $params = [$employeeId];
        $types = "i";
        
        if ($status) {
            $query .= " AND b.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        if ($date) {
            $query .= " AND b.booking_date = ?";
            $params[] = $date;
            $types .= "s";
        }
        
        // Add order by
        $query .= " ORDER BY b.booking_date ASC, b.booking_time ASC";
        
        // Add limit and offset if provided
        if ($limit) {
            $query .= " LIMIT ?";
            $params[] = $limit;
            $types .= "i";
            
            if ($offset) {
                $query .= " OFFSET ?";
                $params[] = $offset;
                $types .= "i";
            }
        }
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        // Execute statement
        $stmt->execute();
        $result = $stmt->get_result();
        $bookings = [];
        
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        
        return $bookings;
    }
    
    /**
     * Get all bookings
     * 
     * @param string $status Filter by status (optional)
     * @param string $date Filter by date (optional)
     * @param int $limit Limit results (optional)
     * @param int $offset Offset for pagination (optional)
     * @return array Array of bookings
     */
    public function getAllBookings($status = null, $date = null, $limit = null, $offset = null) {
        // Base query
        $query = "
            SELECT b.*, 
                   u.first_name as user_first_name, u.last_name as user_last_name,
                   e.first_name as employee_first_name, e.last_name as employee_last_name,
                   s.name as service_name, s.price as service_price, s.duration as service_duration,
                   d.title as deal_title, d.discount_percentage
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN users e ON b.employee_id = e.id
            JOIN services s ON b.service_id = s.id
            LEFT JOIN deals d ON b.deal_id = d.id
            WHERE 1=1
        ";
        
        // Add filters if provided
        $params = [];
        $types = "";
        
        if ($status) {
            $query .= " AND b.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        if ($date) {
            $query .= " AND b.booking_date = ?";
            $params[] = $date;
            $types .= "s";
        }
        
        // Add order by
        $query .= " ORDER BY b.booking_date DESC, b.booking_time DESC";
        
        // Add limit and offset if provided
        if ($limit) {
            $query .= " LIMIT ?";
            $params[] = $limit;
            $types .= "i";
            
            if ($offset) {
                $query .= " OFFSET ?";
                $params[] = $offset;
                $types .= "i";
            }
        }
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        // Execute statement
        $stmt->execute();
        $result = $stmt->get_result();
        $bookings = [];
        
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        
        return $bookings;
    }
    
    /**
     * Count bookings
     * 
     * @param int $userId Filter by user ID (optional)
     * @param int $employeeId Filter by employee ID (optional)
     * @param string $status Filter by status (optional)
     * @param string $date Filter by date (optional)
     * @return int Number of bookings
     */
    public function countBookings($userId = null, $employeeId = null, $status = null, $date = null) {
        // Base query
        $query = "SELECT COUNT(*) as count FROM bookings WHERE 1=1";
        
        // Add filters if provided
        $params = [];
        $types = "";
        
        if ($userId) {
            $query .= " AND user_id = ?";
            $params[] = $userId;
            $types .= "i";
        }
        
        if ($employeeId) {
            $query .= " AND employee_id = ?";
            $params[] = $employeeId;
            $types .= "i";
        }
        
        if ($status) {
            $query .= " AND status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        if ($date) {
            $query .= " AND booking_date = ?";
            $params[] = $date;
            $types .= "s";
        }
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        // Execute statement
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'];
    }
    
    /**
     * Check if employee is available at the specified time
     * 
     * @param int $employeeId Employee ID
     * @param string $date Date (YYYY-MM-DD)
     * @param string $time Time (HH:MM:SS)
     * @param int $serviceId Service ID (to get duration)
     * @param int $excludeBookingId Exclude this booking ID (for updates)
     * @return bool True if employee is available, false otherwise
     */
    public function isEmployeeAvailable($employeeId, $date, $time, $serviceId, $excludeBookingId = null) {
        // Get service duration
        $serviceStmt = $this->conn->prepare("SELECT duration FROM services WHERE id = ?");
        $serviceStmt->bind_param("i", $serviceId);
        $serviceStmt->execute();
        $serviceResult = $serviceStmt->get_result();
        $serviceRow = $serviceResult->fetch_assoc();
        $duration = $serviceRow['duration'];
        
        // Calculate end time
        $startTime = strtotime($time);
        $endTime = $startTime + ($duration * 60);
        $endTimeStr = date('H:i:s', $endTime);
        
        // Check if employee has any overlapping bookings
        $query = "
            SELECT COUNT(*) as count FROM bookings b
            JOIN services s ON b.service_id = s.id
            WHERE b.employee_id = ?
            AND b.booking_date = ?
            AND b.status != 'cancelled'
            AND (
                (b.booking_time <= ? AND ADDTIME(b.booking_time, SEC_TO_TIME(s.duration * 60)) > ?)
                OR
                (b.booking_time < ? AND ADDTIME(b.booking_time, SEC_TO_TIME(s.duration * 60)) >= ?)
            )
        ";
        
        $params = [$employeeId, $date, $time, $time, $endTimeStr, $endTimeStr];
        $types = "isssss";
        
        // Exclude the current booking if updating
        if ($excludeBookingId) {
            $query .= " AND b.id != ?";
            $params[] = $excludeBookingId;
            $types .= "i";
        }
        
        // Prepare statement
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        // Execute statement
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] == 0;
    }
    
    /**
     * Get available time slots for an employee on a specific date
     * 
     * @param int $employeeId Employee ID
     * @param string $date Date (YYYY-MM-DD)
     * @param int $serviceId Service ID
     * @return array Array of available time slots
     */
    public function getAvailableTimeSlots($employeeId, $date, $serviceId) {
        // Get service duration
        $serviceStmt = $this->conn->prepare("SELECT duration FROM services WHERE id = ?");
        $serviceStmt->bind_param("i", $serviceId);
        $serviceStmt->execute();
        $serviceResult = $serviceStmt->get_result();
        $serviceRow = $serviceResult->fetch_assoc();
        $duration = $serviceRow['duration'];
        
        // Define business hours (9 AM to 7 PM)
        $startHour = 9;
        $endHour = 19;
        
        // Get all bookings for the employee on the specified date
        $bookingsStmt = $this->conn->prepare("
            SELECT b.booking_time, s.duration
            FROM bookings b
            JOIN services s ON b.service_id = s.id
            WHERE b.employee_id = ? AND b.booking_date = ? AND b.status != 'cancelled'
            ORDER BY b.booking_time ASC
        ");
        $bookingsStmt->bind_param("is", $employeeId, $date);
        $bookingsStmt->execute();
        $bookingsResult = $bookingsStmt->get_result();
        
        $bookedSlots = [];
        while ($row = $bookingsResult->fetch_assoc()) {
            $startTime = strtotime($row['booking_time']);
            $endTime = $startTime + ($row['duration'] * 60);
            
            $bookedSlots[] = [
                'start' => $startTime,
                'end' => $endTime
            ];
        }
        
        // Generate available time slots
        $availableSlots = [];
        $slotInterval = 30; // 30-minute intervals
        
        for ($hour = $startHour; $hour < $endHour; $hour++) {
            for ($minute = 0; $minute < 60; $minute += $slotInterval) {
                $slotStart = strtotime($date . ' ' . sprintf('%02d:%02d:00', $hour, $minute));
                $slotEnd = $slotStart + ($duration * 60);
                
                // Skip if slot is in the past
                if ($slotStart < time()) {
                    continue;
                }
                
                // Check if slot overlaps with any booked slots
                $isAvailable = true;
                foreach ($bookedSlots as $bookedSlot) {
                    if (
                        ($slotStart < $bookedSlot['end'] && $slotEnd > $bookedSlot['start']) ||
                        ($slotStart == $bookedSlot['start'])
                    ) {
                        $isAvailable = false;
                        break;
                    }
                }
                
                // Check if slot ends before business hours end
                if ($slotEnd > strtotime($date . ' ' . sprintf('%02d:00:00', $endHour))) {
                    $isAvailable = false;
                }
                
                if ($isAvailable) {
                    $availableSlots[] = date('H:i:s', $slotStart);
                }
            }
        }
        
        return $availableSlots;
    }
    
    /**
     * Get booking statistics
     * 
     * @param string $period Period (day, week, month, year)
     * @param string $startDate Start date for custom period (optional)
     * @param string $endDate End date for custom period (optional)
     * @return array Booking statistics
     */
    public function getBookingStatistics($period = 'month', $startDate = null, $endDate = null) {
        // Define date range based on period
        $today = date('Y-m-d');
        
        if ($period == 'day') {
            $startDate = $today;
            $endDate = $today;
        } elseif ($period == 'week') {
            $startDate = date('Y-m-d', strtotime('monday this week'));
            $endDate = date('Y-m-d', strtotime('sunday this week'));
        } elseif ($period == 'month') {
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        } elseif ($period == 'year') {
            $startDate = date('Y-01-01');
            $endDate = date('Y-12-31');
        }
        
        // Get total bookings
        $totalStmt = $this->conn->prepare("
            SELECT COUNT(*) as total FROM bookings
            WHERE booking_date BETWEEN ? AND ?
        ");
        $totalStmt->bind_param("ss", $startDate, $endDate);
        $totalStmt->execute();
        $totalResult = $totalStmt->get_result();
        $totalRow = $totalResult->fetch_assoc();
        $total = $totalRow['total'];
        
        // Get bookings by status
        $statusStmt = $this->conn->prepare("
            SELECT status, COUNT(*) as count FROM bookings
            WHERE booking_date BETWEEN ? AND ?
            GROUP BY status
        ");
        $statusStmt->bind_param("ss", $startDate, $endDate);
        $statusStmt->execute();
        $statusResult = $statusStmt->get_result();
        
        $byStatus = [];
        while ($row = $statusResult->fetch_assoc()) {
            $byStatus[$row['status']] = $row['count'];
        }
        
        // Get bookings by service
        $serviceStmt = $this->conn->prepare("
            SELECT s.name, COUNT(*) as count FROM bookings b
            JOIN services s ON b.service_id = s.id
            WHERE b.booking_date BETWEEN ? AND ?
            GROUP BY b.service_id
            ORDER BY count DESC
            LIMIT 5
        ");
        $serviceStmt->bind_param("ss", $startDate, $endDate);
        $serviceStmt->execute();
        $serviceResult = $serviceStmt->get_result();
        
        $byService = [];
        while ($row = $serviceResult->fetch_assoc()) {
            $byService[$row['name']] = $row['count'];
        }
        
        // Get bookings by employee
        $employeeStmt = $this->conn->prepare("
            SELECT CONCAT(u.first_name, ' ', u.last_name) as name, COUNT(*) as count FROM bookings b
            JOIN users u ON b.employee_id = u.id
            WHERE b.booking_date BETWEEN ? AND ?
            GROUP BY b.employee_id
            ORDER BY count DESC
            LIMIT 5
        ");
        $employeeStmt->bind_param("ss", $startDate, $endDate);
        $employeeStmt->execute();
        $employeeResult = $employeeStmt->get_result();
        
        $byEmployee = [];
        while ($row = $employeeResult->fetch_assoc()) {
            $byEmployee[$row['name']] = $row['count'];
        }
        
        // Get bookings by date
        $dateStmt = $this->conn->prepare("
            SELECT booking_date, COUNT(*) as count FROM bookings
            WHERE booking_date BETWEEN ? AND ?
            GROUP BY booking_date
            ORDER BY booking_date ASC
        ");
        $dateStmt->bind_param("ss", $startDate, $endDate);
        $dateStmt->execute();
        $dateResult = $dateStmt->get_result();
        
        $byDate = [];
        while ($row = $dateResult->fetch_assoc()) {
            $byDate[$row['booking_date']] = $row['count'];
        }
        
        return [
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total' => $total,
            'by_status' => $byStatus,
            'by_service' => $byService,
            'by_employee' => $byEmployee,
            'by_date' => $byDate
        ];
    }
    
    /**
     * Get revenue statistics
     * 
     * @param string $period Period (day, week, month, year)
     * @param string $startDate Start date for custom period (optional)
     * @param string $endDate End date for custom period (optional)
     * @return array Revenue statistics
     */
    public function getRevenueStatistics($period = 'month', $startDate = null, $endDate = null) {
        // Define date range based on period
        $today = date('Y-m-d');
        
        if ($period == 'day') {
            $startDate = $today;
            $endDate = $today;
        } elseif ($period == 'week') {
            $startDate = date('Y-m-d', strtotime('monday this week'));
            $endDate = date('Y-m-d', strtotime('sunday this week'));
        } elseif ($period == 'month') {
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        } elseif ($period == 'year') {
            $startDate = date('Y-01-01');
            $endDate = date('Y-12-31');
        }
        
        // Get total revenue
        $totalStmt = $this->conn->prepare("
            SELECT 
                SUM(
                    CASE 
                        WHEN b.deal_id IS NOT NULL THEN s.price * (1 - d.discount_percentage / 100)
                        ELSE s.price
                    END
                ) as total
            FROM bookings b
            JOIN services s ON b.service_id = s.id
            LEFT JOIN deals d ON b.deal_id = d.id
            WHERE b.booking_date BETWEEN ? AND ?
            AND b.status IN ('confirmed', 'completed')
        ");
        $totalStmt->bind_param("ss", $startDate, $endDate);
        $totalStmt->execute();
        $totalResult = $totalStmt->get_result();
        $totalRow = $totalResult->fetch_assoc();
        $total = $totalRow['total'] ? $totalRow['total'] : 0;
        
        // Get revenue by service category
        $categoryStmt = $this->conn->prepare("
            SELECT 
                s.category,
                SUM(
                    CASE 
                        WHEN b.deal_id IS NOT NULL THEN s.price * (1 - d.discount_percentage / 100)
                        ELSE s.price
                    END
                ) as revenue
            FROM bookings b
            JOIN services s ON b.service_id = s.id
            LEFT JOIN deals d ON b.deal_id = d.id
            WHERE b.booking_date BETWEEN ? AND ?
            AND b.status IN ('confirmed', 'completed')
            GROUP BY s.category
            ORDER BY revenue DESC
        ");
        $categoryStmt->bind_param("ss", $startDate, $endDate);
        $categoryStmt->execute();
        $categoryResult = $categoryStmt->get_result();
        
        $byCategory = [];
        while ($row = $categoryResult->fetch_assoc()) {
            $byCategory[$row['category']] = $row['revenue'];
        }
        
        // Get revenue by date
        $dateStmt = $this->conn->prepare("
            SELECT 
                b.booking_date,
                SUM(
                    CASE 
                        WHEN b.deal_id IS NOT NULL THEN s.price * (1 - d.discount_percentage / 100)
                        ELSE s.price
                    END
                ) as revenue
            FROM bookings b
            JOIN services s ON b.service_id = s.id
            LEFT JOIN deals d ON b.deal_id = d.id
            WHERE b.booking_date BETWEEN ? AND ?
            AND b.status IN ('confirmed', 'completed')
            GROUP BY b.booking_date
            ORDER BY b.booking_date ASC
        ");
        $dateStmt->bind_param("ss", $startDate, $endDate);
        $dateStmt->execute();
        $dateResult = $dateStmt->get_result();
        
        $byDate = [];
        while ($row = $dateResult->fetch_assoc()) {
            $byDate[$row['booking_date']] = $row['revenue'];
        }
        
        return [
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total' => $total,
            'by_category' => $byCategory,
            'by_date' => $byDate
        ];
    }
}
?>
