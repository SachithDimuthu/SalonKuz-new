<?php
/**
 * Admin Booking Details
 * 
 * This page displays detailed information about a specific booking.
 */

// Set page title
$pageTitle = "Booking Details";

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

// Get service details
$service = $serviceModel->getServiceById($booking['service_id']);

// Get customer details
$customer = $userModel->getUserById($booking['user_id']);

// Get employee details
$employee = $userModel->getUserById($booking['employee_id']);

// Get deal details if applicable
$deal = null;
if (!empty($booking['deal_id'])) {
    $deal = $dealModel->getDealById($booking['deal_id']);
}

// Calculate price
$price = $service['price'];
if ($deal) {
    $price = calculateDiscountedPrice($price, $deal['discount_percentage']);
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
                    <h1 class="text-2xl font-bold">Booking Details</h1>
                    <div class="flex space-x-2">
                        <a href="bookings.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Bookings
                        </a>
                        <a href="edit-booking.php?id=<?php echo $booking['id']; ?>" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                            <i class="fas fa-edit mr-2"></i> Edit Booking
                        </a>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-gray-800 text-white py-4 px-6 flex justify-between items-center">
                        <h2 class="text-xl font-bold">Booking #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></h2>
                        <?php
                        $statusClass = '';
                        switch ($booking['status']) {
                            case 'confirmed':
                                $statusClass = 'bg-green-500';
                                break;
                            case 'pending':
                                $statusClass = 'bg-yellow-500';
                                break;
                            case 'cancelled':
                                $statusClass = 'bg-red-500';
                                break;
                            case 'completed':
                                $statusClass = 'bg-blue-500';
                                break;
                        }
                        ?>
                        <span class="px-3 py-1 text-sm rounded-full <?php echo $statusClass; ?> text-white font-semibold">
                            <?php echo ucfirst($booking['status']); ?>
                        </span>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Booking Information -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h3 class="text-lg font-semibold mb-4 border-b pb-2">Booking Information</h3>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Booking ID:</p>
                                        <p class="font-semibold">#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Status:</p>
                                        <p class="font-semibold">
                                            <span class="px-2 py-1 text-xs rounded-full <?php echo $statusClass; ?> text-white">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Date:</p>
                                        <p class="font-semibold"><?php echo formatDate($booking['booking_date']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Time:</p>
                                        <p class="font-semibold"><?php echo formatTime($booking['start_time']); ?> - <?php echo formatTime($booking['end_time']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Created On:</p>
                                        <p class="font-semibold"><?php echo formatDateTime($booking['created_at']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Last Updated:</p>
                                        <p class="font-semibold"><?php echo formatDateTime($booking['updated_at']); ?></p>
                                    </div>
                                </div>
                                
                                <?php if (!empty($booking['notes'])): ?>
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <p class="text-sm text-gray-600">Special Requests/Notes:</p>
                                        <p class="text-gray-700 mt-1"><?php echo nl2br($booking['notes']); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mt-6 pt-4 border-t border-gray-200">
                                    <h4 class="font-semibold mb-2">Booking Status Actions</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <?php if ($booking['status'] !== 'pending'): ?>
                                            <a href="update-booking-status.php?id=<?php echo $booking['id']; ?>&status=pending" class="bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-bold py-1 px-3 rounded transition duration-300">
                                                Mark as Pending
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($booking['status'] !== 'confirmed'): ?>
                                            <a href="update-booking-status.php?id=<?php echo $booking['id']; ?>&status=confirmed" class="bg-green-500 hover:bg-green-600 text-white text-sm font-bold py-1 px-3 rounded transition duration-300">
                                                Mark as Confirmed
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($booking['status'] !== 'completed'): ?>
                                            <a href="update-booking-status.php?id=<?php echo $booking['id']; ?>&status=completed" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-bold py-1 px-3 rounded transition duration-300">
                                                Mark as Completed
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($booking['status'] !== 'cancelled'): ?>
                                            <a href="update-booking-status.php?id=<?php echo $booking['id']; ?>&status=cancelled" class="bg-red-500 hover:bg-red-600 text-white text-sm font-bold py-1 px-3 rounded transition duration-300">
                                                Mark as Cancelled
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Customer Information -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h3 class="text-lg font-semibold mb-4 border-b pb-2">Customer Information</h3>
                                
                                <div class="flex items-center mb-4">
                                    <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mr-4 overflow-hidden">
                                        <?php if (!empty($customer['profile_image'])): ?>
                                            <img src="../uploads/profiles/<?php echo $customer['profile_image']; ?>" alt="<?php echo $customer['first_name']; ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <i class="fas fa-user text-gray-400 text-2xl"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-lg"><?php echo $customer['first_name'] . ' ' . $customer['last_name']; ?></h4>
                                        <p class="text-gray-600 text-sm"><?php echo $customer['email']; ?></p>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Phone:</p>
                                        <p class="font-semibold"><?php echo !empty($customer['phone']) ? $customer['phone'] : 'N/A'; ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Member Since:</p>
                                        <p class="font-semibold"><?php echo formatDate($customer['created_at']); ?></p>
                                    </div>
                                </div>
                                
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <div class="flex justify-between items-center mb-2">
                                        <h4 class="font-semibold">Customer Bookings</h4>
                                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded">
                                            Total: <?php echo $bookingModel->countUserBookings($customer['id']); ?>
                                        </span>
                                    </div>
                                    <a href="bookings.php?search=<?php echo urlencode($customer['email']); ?>" class="text-blue-500 hover:text-blue-700 text-sm">
                                        <i class="fas fa-external-link-alt mr-1"></i> View All Bookings by this Customer
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Service Information -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h3 class="text-lg font-semibold mb-4 border-b pb-2">Service Information</h3>
                                
                                <div class="mb-4">
                                    <h4 class="font-semibold text-lg"><?php echo $service['name']; ?></h4>
                                    <p class="text-gray-600 text-sm"><?php echo $service['category']; ?></p>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Duration:</p>
                                        <p class="font-semibold"><?php echo $service['duration']; ?> minutes</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Regular Price:</p>
                                        <p class="font-semibold"><?php echo formatPrice($service['price']); ?></p>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <p class="text-sm text-gray-600">Description:</p>
                                    <p class="text-gray-700 mt-1"><?php echo $service['description']; ?></p>
                                </div>
                                
                                <?php if ($deal): ?>
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <div class="flex items-center">
                                            <div class="bg-pink-500 text-white text-xs font-bold py-1 px-2 rounded-full inline-block mr-2">
                                                <?php echo $deal['discount_percentage']; ?>% OFF
                                            </div>
                                            <span class="text-sm text-pink-700 font-semibold"><?php echo $deal['title']; ?></span>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-2"><?php echo $deal['description']; ?></p>
                                        <p class="text-sm text-gray-600 mt-1">Valid until: <?php echo formatDate($deal['end_date']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Employee Information -->
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h3 class="text-lg font-semibold mb-4 border-b pb-2">Employee Information</h3>
                                
                                <div class="flex items-center mb-4">
                                    <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center mr-4 overflow-hidden">
                                        <?php if (!empty($employee['profile_image'])): ?>
                                            <img src="../uploads/profiles/<?php echo $employee['profile_image']; ?>" alt="<?php echo $employee['first_name']; ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <i class="fas fa-user text-gray-400 text-2xl"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-lg"><?php echo $employee['first_name'] . ' ' . $employee['last_name']; ?></h4>
                                        <p class="text-gray-600 text-sm"><?php echo $employee['email']; ?></p>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600">Phone:</p>
                                        <p class="font-semibold"><?php echo !empty($employee['phone']) ? $employee['phone'] : 'N/A'; ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Position:</p>
                                        <p class="font-semibold">
                                            <?php 
                                            echo !empty($employee['position']) ? $employee['position'] : 'Stylist/Therapist'; 
                                            ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <div class="flex justify-between items-center mb-2">
                                        <h4 class="font-semibold">Employee Schedule</h4>
                                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded">
                                            Today's Bookings: <?php echo $bookingModel->countEmployeeBookingsForDate($employee['id'], date('Y-m-d')); ?>
                                        </span>
                                    </div>
                                    <a href="bookings.php?employee_id=<?php echo $employee['id']; ?>" class="text-blue-500 hover:text-blue-700 text-sm">
                                        <i class="fas fa-external-link-alt mr-1"></i> View All Bookings for this Employee
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Information -->
                        <div class="bg-gray-50 rounded-lg p-6 mt-6">
                            <h3 class="text-lg font-semibold mb-4 border-b pb-2">Payment Information</h3>
                            
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-700"><?php echo $service['name']; ?></span>
                                <span class="text-gray-700"><?php echo formatPrice($service['price']); ?></span>
                            </div>
                            
                            <?php if ($deal): ?>
                                <div class="flex justify-between items-center mb-2 text-pink-500">
                                    <span>Discount (<?php echo $deal['title']; ?> - <?php echo $deal['discount_percentage']; ?>%)</span>
                                    <span>-<?php echo formatPrice($service['price'] - $price); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex justify-between items-center font-bold text-lg mt-4 pt-4 border-t border-gray-200">
                                <span>Total</span>
                                <span><?php echo formatPrice($price); ?></span>
                            </div>
                            
                            <div class="mt-4 text-sm text-gray-600">
                                <p>Payment will be collected at the salon after the service.</p>
                            </div>
                        </div>
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
