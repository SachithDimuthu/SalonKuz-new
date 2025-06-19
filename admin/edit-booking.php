<?php
/**
 * Edit Booking
 * 
 * This page allows administrators to edit booking details.
 */

// Set page title
$pageTitle = "Edit Booking";

// Include header
require_once '../includes/header.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'You do not have permission to access the admin dashboard.');
    redirect('../login.php');
}

// Include models
require_once '../models/Booking.php';
require_once '../models/Service.php';
require_once '../models/User.php';
require_once '../models/Deal.php';

// Create instances
$bookingModel = new Booking($conn);
$serviceModel = new Service($conn);
$userModel = new User($conn);
$dealModel = new Deal($conn);

// Get booking ID from URL
$bookingId = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$bookingId) {
    setFlashMessage('error', 'Invalid booking ID.');
    redirect('bookings.php');
}

// Get booking details
$booking = $bookingModel->getBookingById($bookingId);

if (!$booking) {
    setFlashMessage('error', 'Booking not found.');
    redirect('bookings.php');
}

// Get all services
$services = $serviceModel->getAllServices();

// Get all employees
$employees = $userModel->getUsersByRole('employee');

// Get all active deals
$deals = $dealModel->getAllActiveDeals();

// Get customer details
$customer = $userModel->getUserById($booking['user_id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $serviceId = sanitizeInput($_POST['service_id']);
    $employeeId = sanitizeInput($_POST['employee_id']);
    $bookingDate = sanitizeInput($_POST['booking_date']);
    $startTime = sanitizeInput($_POST['start_time']);
    $dealId = !empty($_POST['deal_id']) ? sanitizeInput($_POST['deal_id']) : null;
    $notes = sanitizeInput($_POST['notes']);
    $status = sanitizeInput($_POST['status']);
    
    // Validate inputs
    $errors = [];
    
    if (empty($serviceId)) {
        $errors[] = 'Service is required.';
    }
    
    if (empty($employeeId)) {
        $errors[] = 'Employee is required.';
    }
    
    if (empty($bookingDate)) {
        $errors[] = 'Booking date is required.';
    } elseif (strtotime($bookingDate) < strtotime(date('Y-m-d'))) {
        $errors[] = 'Booking date cannot be in the past.';
    }
    
    if (empty($startTime)) {
        $errors[] = 'Start time is required.';
    }
    
    if (!in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'])) {
        $errors[] = 'Invalid status.';
    }
    
    // If no errors, update the booking
    if (empty($errors)) {
        // Get service details for duration calculation
        $service = $serviceModel->getServiceById($serviceId);
        
        // Calculate end time based on service duration
        $endTime = date('H:i:s', strtotime($startTime) + ($service['duration'] * 60));
        
        // Check if the employee is available at the selected time
        $isAvailable = true;
        if ($employeeId != $booking['employee_id'] || $bookingDate != $booking['booking_date'] || $startTime != $booking['start_time']) {
            $isAvailable = $bookingModel->isEmployeeAvailable($employeeId, $bookingDate, $startTime, $endTime, $bookingId);
        }
        
        if (!$isAvailable) {
            $errors[] = 'The selected employee is not available at this time. Please choose a different time or employee.';
        } else {
            // Update booking
            $updated = $bookingModel->updateBooking(
                $bookingId,
                $serviceId,
                $booking['user_id'],
                $employeeId,
                $bookingDate,
                $startTime,
                $endTime,
                $dealId,
                $notes,
                $status
            );
            
            if ($updated) {
                setFlashMessage('success', 'Booking updated successfully.');
                redirect('booking-details.php?id=' . $bookingId);
            } else {
                $errors[] = 'Failed to update booking. Please try again.';
            }
        }
    }
}
?>

<section class="py-8 bg-gray-100">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row">
            <!-- Sidebar -->
            <div class="md:w-1/4 mb-6 md:mb-0">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gray-800 text-white py-4 px-6">
                        <h2 class="text-xl font-bold">Admin Menu</h2>
                    </div>
                    <div class="py-4">
                        <ul class="divide-y divide-gray-200">
                            <li>
                                <a href="dashboard.php" class="block px-6 py-3 hover:bg-pink-50 text-gray-700 hover:text-pink-500">
                                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="users.php" class="block px-6 py-3 hover:bg-pink-50 text-gray-700 hover:text-pink-500">
                                    <i class="fas fa-users mr-2"></i> Manage Users
                                </a>
                            </li>
                            <li>
                                <a href="services.php" class="block px-6 py-3 hover:bg-pink-50 text-gray-700 hover:text-pink-500">
                                    <i class="fas fa-spa mr-2"></i> Manage Services
                                </a>
                            </li>
                            <li>
                                <a href="deals.php" class="block px-6 py-3 hover:bg-pink-50 text-gray-700 hover:text-pink-500">
                                    <i class="fas fa-tags mr-2"></i> Manage Deals
                                </a>
                            </li>
                            <li>
                                <a href="bookings.php" class="block px-6 py-3 bg-pink-50 text-pink-500 font-semibold">
                                    <i class="fas fa-calendar-alt mr-2"></i> Manage Bookings
                                </a>
                            </li>
                            <li>
                                <a href="reports.php" class="block px-6 py-3 hover:bg-pink-50 text-gray-700 hover:text-pink-500">
                                    <i class="fas fa-chart-bar mr-2"></i> Reports & Analytics
                                </a>
                            </li>
                            <li>
                                <a href="../index.php" class="block px-6 py-3 hover:bg-pink-50 text-gray-700 hover:text-pink-500">
                                    <i class="fas fa-home mr-2"></i> Back to Website
                                </a>
                            </li>
                            <li>
                                <a href="../logout.php" class="block px-6 py-3 hover:bg-pink-50 text-gray-700 hover:text-pink-500">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="md:w-3/4 md:pl-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Edit Booking</h1>
                    <div class="flex space-x-2">
                        <a href="booking-details.php?id=<?php echo $bookingId; ?>" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Details
                        </a>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gray-800 text-white py-4 px-6">
                        <h2 class="text-xl font-bold">Edit Booking #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></h2>
                    </div>
                    
                    <div class="p-6">
                        <!-- Customer Information (Read-only) -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-6">
                            <h3 class="text-lg font-semibold mb-2">Customer Information</h3>
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center mr-4 overflow-hidden">
                                    <?php if (!empty($customer['profile_image'])): ?>
                                        <img src="../uploads/profiles/<?php echo $customer['profile_image']; ?>" alt="<?php echo $customer['first_name']; ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <i class="fas fa-user text-gray-400 text-xl"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h4 class="font-semibold"><?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?></h4>
                                    <p class="text-gray-600 text-sm"><?php echo $customer['email']; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Error Messages -->
                        <?php if (isset($errors) && !empty($errors)): ?>
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                                <ul class="list-disc pl-5">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Edit Form -->
                        <form action="edit-booking.php?id=<?php echo $bookingId; ?>" method="POST">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <!-- Service Selection -->
                                <div>
                                    <label for="service_id" class="block text-sm font-medium text-gray-700 mb-1">Service *</label>
                                    <select name="service_id" id="service_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required>
                                        <option value="">Select a Service</option>
                                        <?php foreach ($services as $service): ?>
                                            <option value="<?php echo $service['id']; ?>" <?php echo ($service['id'] == $booking['service_id']) ? 'selected' : ''; ?>>
                                                <?php echo $service['name']; ?> (<?php echo formatPrice($service['price']); ?> - <?php echo $service['duration']; ?> min)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Employee Selection -->
                                <div>
                                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Employee *</label>
                                    <select name="employee_id" id="employee_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required>
                                        <option value="">Select an Employee</option>
                                        <?php foreach ($employees as $employee): ?>
                                            <option value="<?php echo $employee['id']; ?>" <?php echo ($employee['id'] == $booking['employee_id']) ? 'selected' : ''; ?>>
                                                <?php echo $employee['first_name'] . ' ' . $employee['last_name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Date Selection -->
                                <div>
                                    <label for="booking_date" class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                                    <input type="date" name="booking_date" id="booking_date" value="<?php echo $booking['booking_date']; ?>" min="<?php echo date('Y-m-d'); ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required>
                                </div>
                                
                                <!-- Time Selection -->
                                <div>
                                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">Time *</label>
                                    <input type="time" name="start_time" id="start_time" value="<?php echo substr($booking['start_time'], 0, 5); ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required>
                                </div>
                                
                                <!-- Deal Selection -->
                                <div>
                                    <label for="deal_id" class="block text-sm font-medium text-gray-700 mb-1">Deal (Optional)</label>
                                    <select name="deal_id" id="deal_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50">
                                        <option value="">No Deal</option>
                                        <?php foreach ($deals as $deal): ?>
                                            <option value="<?php echo $deal['id']; ?>" <?php echo ($deal['id'] == $booking['deal_id']) ? 'selected' : ''; ?>>
                                                <?php echo $deal['title']; ?> (<?php echo $deal['discount_percentage']; ?>% Off)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Status Selection -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                                    <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required>
                                        <option value="pending" <?php echo ($booking['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo ($booking['status'] == 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="completed" <?php echo ($booking['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo ($booking['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Notes -->
                            <div class="mb-6">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Special Requests/Notes</label>
                                <textarea name="notes" id="notes" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50"><?php echo $booking['notes']; ?></textarea>
                            </div>
                            
                            <!-- Submit Buttons -->
                            <div class="flex justify-between">
                                <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-6 rounded transition duration-300">
                                    <i class="fas fa-save mr-2"></i> Save Changes
                                </button>
                                
                                <a href="booking-details.php?id=<?php echo $bookingId; ?>" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded transition duration-300">
                                    <i class="fas fa-times mr-2"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
require_once '../includes/footer.php';
?>
