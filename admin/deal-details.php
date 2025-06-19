<?php
/**
 * Deal Details
 * 
 * This page displays detailed information about a specific deal.
 */

// Set page title
$pageTitle = "Deal Details";

// Include header
require_once '../includes/header.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'You do not have permission to access the admin dashboard.');
    redirect('../login.php');
}

// Include models
require_once '../models/Deal.php';
require_once '../models/Service.php';
require_once '../models/Booking.php';

// Create instances
$dealModel = new Deal($conn);
$serviceModel = new Service($conn);
$bookingModel = new Booking($conn);

// Get deal ID from URL
$dealId = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$dealId) {
    setFlashMessage('error', 'Invalid deal ID.');
    redirect('deals.php');
}

// Get deal details
$deal = $dealModel->getDealById($dealId);

if (!$deal) {
    setFlashMessage('error', 'Deal not found.');
    redirect('deals.php');
}

// Get service details
$service = $serviceModel->getServiceById($deal['service_id']);

// Get bookings that used this deal
$bookings = $dealModel->getBookingsWithDeal($dealId);

// Calculate statistics
$totalBookings = count($bookings);
$totalRevenue = 0;
$totalSavings = 0;

foreach ($bookings as $booking) {
    $originalPrice = $booking['service_price'];
    $discountedPrice = $originalPrice * (1 - ($deal['discount_percentage'] / 100));
    $savings = $originalPrice - $discountedPrice;
    
    $totalRevenue += $discountedPrice;
    $totalSavings += $savings;
}

// Determine deal status
$now = new DateTime();
$startDate = new DateTime($deal['start_date']);
$endDate = new DateTime($deal['end_date']);

if ($now < $startDate) {
    $status = 'upcoming';
    $statusClass = 'bg-blue-100 text-blue-700';
} elseif ($now > $endDate) {
    $status = 'expired';
    $statusClass = 'bg-gray-100 text-gray-700';
} else {
    $status = 'active';
    $statusClass = 'bg-green-100 text-green-700';
}

// Calculate days remaining or days until start
$daysRemaining = null;
$daysUntilStart = null;

if ($status === 'active') {
    $daysRemaining = $now->diff($endDate)->days;
} elseif ($status === 'upcoming') {
    $daysUntilStart = $now->diff($startDate)->days;
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
                                <a href="deals.php" class="block px-6 py-3 bg-pink-50 text-pink-500 font-semibold">
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
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Deal Details</h1>
                    <div class="flex space-x-3">
                        <a href="edit-deal.php?id=<?php echo $dealId; ?>" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                            <i class="fas fa-edit mr-2"></i> Edit Deal
                        </a>
                        <a href="deals.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Deals
                        </a>
                    </div>
                </div>
                
                <!-- Deal Info Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-gray-800 text-white py-4 px-6 flex justify-between items-center">
                        <h2 class="text-xl font-bold">Deal Information</h2>
                        <span class="<?php echo $statusClass; ?> text-xs px-3 py-1 rounded-full capitalize">
                            <?php echo $status; ?>
                        </span>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Deal Details -->
                            <div class="md:col-span-2">
                                <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo $deal['name']; ?></h3>
                                <p class="text-gray-600 mb-4"><?php echo nl2br($deal['description']); ?></p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-500 uppercase mb-1">Service</h4>
                                        <p class="font-medium">
                                            <a href="service-details.php?id=<?php echo $service['id']; ?>" class="text-blue-500 hover:text-blue-700">
                                                <?php echo $service['name']; ?>
                                            </a>
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-500 uppercase mb-1">Discount</h4>
                                        <p class="font-medium text-green-600"><?php echo $deal['discount_percentage']; ?>% off</p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-500 uppercase mb-1">Start Date</h4>
                                        <p class="font-medium"><?php echo date('F j, Y', strtotime($deal['start_date'])); ?></p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-500 uppercase mb-1">End Date</h4>
                                        <p class="font-medium"><?php echo date('F j, Y', strtotime($deal['end_date'])); ?></p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-500 uppercase mb-1">Created On</h4>
                                        <p class="font-medium"><?php echo date('F j, Y', strtotime($deal['created_at'])); ?></p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-500 uppercase mb-1">Last Updated</h4>
                                        <p class="font-medium"><?php echo date('F j, Y', strtotime($deal['updated_at'])); ?></p>
                                    </div>
                                </div>
                                
                                <?php if ($status === 'active' && $daysRemaining !== null): ?>
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4 mb-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-clock text-yellow-400"></i>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-yellow-800">Deal Expires Soon</h3>
                                                <div class="mt-2 text-sm text-yellow-700">
                                                    <p>This deal will expire in <?php echo $daysRemaining; ?> day<?php echo $daysRemaining !== 1 ? 's' : ''; ?>.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php elseif ($status === 'upcoming' && $daysUntilStart !== null): ?>
                                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-calendar-alt text-blue-400"></i>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-blue-800">Upcoming Deal</h3>
                                                <div class="mt-2 text-sm text-blue-700">
                                                    <p>This deal will start in <?php echo $daysUntilStart; ?> day<?php echo $daysUntilStart !== 1 ? 's' : ''; ?>.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Price Comparison -->
                            <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Price Comparison</h3>
                                
                                <div class="mb-4">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-600">Regular Price:</span>
                                        <span class="font-semibold"><?php echo formatPrice($service['price']); ?></span>
                                    </div>
                                    
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-600">Discount:</span>
                                        <span class="font-semibold text-red-500">-<?php echo formatPrice($service['price'] * $deal['discount_percentage'] / 100); ?></span>
                                    </div>
                                    
                                    <div class="flex justify-between items-center pt-2 border-t border-gray-300">
                                        <span class="text-gray-700 font-medium">Final Price:</span>
                                        <span class="font-bold text-green-600"><?php echo formatPrice($service['price'] * (1 - $deal['discount_percentage'] / 100)); ?></span>
                                    </div>
                                </div>
                                
                                <div class="text-center">
                                    <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                        Save <?php echo formatPrice($service['price'] * $deal['discount_percentage'] / 100); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-calendar-check text-pink-500 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Total Bookings</h3>
                                <p class="text-2xl font-bold"><?php echo $totalBookings; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-dollar-sign text-green-500 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Revenue Generated</h3>
                                <p class="text-2xl font-bold"><?php echo formatPrice($totalRevenue); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-piggy-bank text-blue-500 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Customer Savings</h3>
                                <p class="text-2xl font-bold"><?php echo formatPrice($totalSavings); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Bookings with this Deal -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-gray-800 text-white py-4 px-6">
                        <h2 class="text-xl font-bold">Bookings with this Deal</h2>
                    </div>
                    
                    <div class="p-6">
                        <?php if (empty($bookings)): ?>
                            <div class="text-center py-8">
                                <div class="text-gray-400 mb-3">
                                    <i class="fas fa-calendar-alt text-5xl"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-700 mb-1">No Bookings Found</h3>
                                <p class="text-gray-500">This deal hasn't been used in any bookings yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead>
                                        <tr>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Customer</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Employee</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date & Time</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Original Price</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Discounted Price</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bookings as $booking): ?>
                                            <?php
                                            $originalPrice = $booking['service_price'];
                                            $discountedPrice = $originalPrice * (1 - ($deal['discount_percentage'] / 100));
                                            
                                            // Determine status class
                                            switch ($booking['status']) {
                                                case 'pending':
                                                    $statusClass = 'bg-yellow-100 text-yellow-700';
                                                    break;
                                                case 'confirmed':
                                                    $statusClass = 'bg-blue-100 text-blue-700';
                                                    break;
                                                case 'completed':
                                                    $statusClass = 'bg-green-100 text-green-700';
                                                    break;
                                                case 'cancelled':
                                                    $statusClass = 'bg-red-100 text-red-700';
                                                    break;
                                                default:
                                                    $statusClass = 'bg-gray-100 text-gray-700';
                                            }
                                            ?>
                                            <tr>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    #<?php echo $booking['id']; ?>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <a href="user-details.php?id=<?php echo $booking['customer_id']; ?>" class="text-blue-500 hover:text-blue-700">
                                                        <?php echo $booking['customer_name']; ?>
                                                    </a>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <a href="user-details.php?id=<?php echo $booking['employee_id']; ?>" class="text-blue-500 hover:text-blue-700">
                                                        <?php echo $booking['employee_name']; ?>
                                                    </a>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <div><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></div>
                                                    <div class="text-xs text-gray-500"><?php echo date('g:i A', strtotime($booking['booking_time'])); ?></div>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <span class="line-through text-gray-500"><?php echo formatPrice($originalPrice); ?></span>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <span class="font-semibold text-green-600"><?php echo formatPrice($discountedPrice); ?></span>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <span class="<?php echo $statusClass; ?> text-xs px-2 py-1 rounded-full capitalize">
                                                        <?php echo $booking['status']; ?>
                                                    </span>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <div class="flex items-center space-x-3">
                                                        <a href="booking-details.php?id=<?php echo $booking['id']; ?>" class="text-blue-500 hover:text-blue-700" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit-booking.php?id=<?php echo $booking['id']; ?>" class="text-green-500 hover:text-green-700" title="Edit Booking">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex justify-between">
                    <a href="edit-deal.php?id=<?php echo $dealId; ?>" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                        <i class="fas fa-edit mr-2"></i> Edit Deal
                    </a>
                    
                    <a href="deals.php?action=delete&id=<?php echo $dealId; ?>" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition duration-300" onclick="return confirm('Are you sure you want to delete this deal? This action cannot be undone.');">
                        <i class="fas fa-trash-alt mr-2"></i> Delete Deal
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
require_once '../includes/footer.php';
?>
