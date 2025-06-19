<?php
/**
 * Admin Dashboard
 * 
 * This is the main dashboard for administrators to manage the salon website.
 */

// Set page title
$pageTitle = "Admin Dashboard";

// Include header
require_once '../includes/header.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'You do not have permission to access the admin dashboard.');
    redirect('../login.php');
}

// Include models
require_once '../models/User.php';
require_once '../models/Service.php';
require_once '../models/Deal.php';
require_once '../models/Booking.php';

// Create instances
$userModel = new User($conn);
$serviceModel = new Service($conn);
$dealModel = new Deal($conn);
$bookingModel = new Booking($conn);

// Get statistics
$totalUsers = $userModel->countUsers();
$totalServices = $serviceModel->countServices();
$totalDeals = $dealModel->countDeals();
$totalBookings = $bookingModel->countBookings();
$pendingBookings = $bookingModel->countBookingsByStatus('pending');
$confirmedBookings = $bookingModel->countBookingsByStatus('confirmed');
$cancelledBookings = $bookingModel->countBookingsByStatus('cancelled');
$completedBookings = $bookingModel->countBookingsByStatus('completed');

// Get recent bookings
$recentBookings = $bookingModel->getAllBookings(5, 0);

// Get revenue statistics
$currentMonth = date('Y-m');
$previousMonth = date('Y-m', strtotime('-1 month'));

$currentMonthRevenue = $bookingModel->getRevenueByPeriod($currentMonth . '-01', date('Y-m-t'));
$previousMonthRevenue = $bookingModel->getRevenueByPeriod($previousMonth . '-01', date('Y-m-t', strtotime('-1 month')));
$totalRevenue = $bookingModel->getTotalRevenue();

// Calculate revenue change percentage
$revenueChangePercentage = 0;
if ($previousMonthRevenue > 0) {
    $revenueChangePercentage = (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100;
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
                                <a href="dashboard.php" class="block px-6 py-3 bg-pink-50 text-pink-500 font-semibold">
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
                                <a href="bookings.php" class="block px-6 py-3 hover:bg-pink-50 text-gray-700 hover:text-pink-500">
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
                <h1 class="text-3xl font-bold mb-6">Admin Dashboard</h1>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-users text-blue-500 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Total Users</h3>
                                <p class="text-2xl font-bold"><?php echo $totalUsers; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-spa text-green-500 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Total Services</h3>
                                <p class="text-2xl font-bold"><?php echo $totalServices; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-tags text-yellow-500 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Active Deals</h3>
                                <p class="text-2xl font-bold"><?php echo $dealModel->countDealsByStatus('active'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-calendar-check text-purple-500 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Total Bookings</h3>
                                <p class="text-2xl font-bold"><?php echo $totalBookings; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Revenue Card -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-xl font-semibold mb-4">Revenue Overview</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="border-r border-gray-200 pr-6">
                            <h4 class="text-sm text-gray-500 mb-1">Current Month</h4>
                            <p class="text-2xl font-bold text-gray-800"><?php echo formatPrice($currentMonthRevenue); ?></p>
                            <p class="text-sm <?php echo $revenueChangePercentage >= 0 ? 'text-green-500' : 'text-red-500'; ?>">
                                <?php echo $revenueChangePercentage >= 0 ? '+' : ''; ?><?php echo number_format($revenueChangePercentage, 1); ?>%
                                <span class="text-gray-500">vs last month</span>
                            </p>
                        </div>
                        <div class="border-r border-gray-200 px-6">
                            <h4 class="text-sm text-gray-500 mb-1">Previous Month</h4>
                            <p class="text-2xl font-bold text-gray-800"><?php echo formatPrice($previousMonthRevenue); ?></p>
                        </div>
                        <div class="pl-6">
                            <h4 class="text-sm text-gray-500 mb-1">Total Revenue</h4>
                            <p class="text-2xl font-bold text-gray-800"><?php echo formatPrice($totalRevenue); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Booking Stats -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-xl font-semibold mb-4">Booking Statistics</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                            <h4 class="text-sm text-yellow-700 mb-1">Pending</h4>
                            <p class="text-2xl font-bold text-yellow-700"><?php echo $pendingBookings; ?></p>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                            <h4 class="text-sm text-green-700 mb-1">Confirmed</h4>
                            <p class="text-2xl font-bold text-green-700"><?php echo $confirmedBookings; ?></p>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                            <h4 class="text-sm text-blue-700 mb-1">Completed</h4>
                            <p class="text-2xl font-bold text-blue-700"><?php echo $completedBookings; ?></p>
                        </div>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                            <h4 class="text-sm text-red-700 mb-1">Cancelled</h4>
                            <p class="text-2xl font-bold text-red-700"><?php echo $cancelledBookings; ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Bookings -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gray-800 text-white py-4 px-6 flex justify-between items-center">
                        <h3 class="text-xl font-semibold">Recent Bookings</h3>
                        <a href="bookings.php" class="text-white text-sm hover:underline">View All</a>
                    </div>
                    <div class="p-6">
                        <?php if (empty($recentBookings)): ?>
                            <p class="text-gray-700 text-center py-4">No bookings found.</p>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead>
                                        <tr>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Customer</th>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Service</th>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date & Time</th>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentBookings as $booking): ?>
                                            <tr>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <?php echo htmlspecialchars($booking['user_first_name'] . ' ' . $booking['user_last_name'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <?php echo $booking['service_name']; ?>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <div><?php echo formatDate($booking['booking_date']); ?></div>
                                                    <div class="text-xs text-gray-500"><?php echo formatTime($booking['start_time']); ?> - <?php echo formatTime($booking['end_time']); ?></div>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <?php
                                                    $statusClass = '';
                                                    switch ($booking['status']) {
                                                        case 'confirmed':
                                                            $statusClass = 'bg-green-100 text-green-700';
                                                            break;
                                                        case 'pending':
                                                            $statusClass = 'bg-yellow-100 text-yellow-700';
                                                            break;
                                                        case 'cancelled':
                                                            $statusClass = 'bg-red-100 text-red-700';
                                                            break;
                                                        case 'completed':
                                                            $statusClass = 'bg-blue-100 text-blue-700';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="px-2 py-1 text-xs rounded-full <?php echo $statusClass; ?>">
                                                        <?php echo ucfirst($booking['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <a href="booking-details.php?id=<?php echo $booking['id']; ?>" class="text-blue-500 hover:text-blue-700 mr-2">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit-booking.php?id=<?php echo $booking['id']; ?>" class="text-green-500 hover:text-green-700 mr-2">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($booking['status'] === 'pending'): ?>
                                                        <a href="update-booking-status.php?id=<?php echo $booking['id']; ?>&status=confirmed" class="text-yellow-500 hover:text-yellow-700 mr-2" title="Confirm Booking">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if ($booking['status'] === 'confirmed'): ?>
                                                        <a href="update-booking-status.php?id=<?php echo $booking['id']; ?>&status=completed" class="text-blue-500 hover:text-blue-700 mr-2" title="Mark as Completed">
                                                            <i class="fas fa-check-double"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if ($booking['status'] !== 'cancelled' && $booking['status'] !== 'completed'): ?>
                                                        <a href="../cancel-booking.php?id=<?php echo $booking['id']; ?>" class="text-red-500 hover:text-red-700" title="Cancel Booking" onclick="return confirm('Are you sure you want to cancel this booking?');">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
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
