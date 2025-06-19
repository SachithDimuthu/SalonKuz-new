<?php
/**
 * Service Details
 * 
 * This page displays detailed information about a specific service.
 */

// Set page title
$pageTitle = "Service Details";

// Include header
require_once '../includes/header.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'You do not have permission to access the admin dashboard.');
    redirect('../login.php');
}

// Include models
require_once '../models/Service.php';
require_once '../models/User.php';
require_once '../models/Deal.php';
require_once '../models/Booking.php';

// Create instances
$serviceModel = new Service($conn);
$userModel = new User($conn);
$dealModel = new Deal($conn);
$bookingModel = new Booking($conn);

// Get service ID from URL
$serviceId = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$serviceId) {
    setFlashMessage('error', 'Invalid service ID.');
    redirect('services.php');
}

// Get service details
$service = $serviceModel->getServiceById($serviceId);

if (!$service) {
    setFlashMessage('error', 'Service not found.');
    redirect('services.php');
}

// Get active deal for this service
$deal = $dealModel->getActiveDealForService($serviceId);

// Get employees who can perform this service
$employees = $userModel->getEmployeesByServiceId($serviceId);

// Get recent bookings for this service (limit to 10)
$recentBookings = $bookingModel->getBookingsByServiceId($serviceId, 10);

// Get booking statistics for this service
$totalBookings = $bookingModel->countBookingsByServiceId($serviceId);
$completedBookings = $bookingModel->countBookingsByServiceIdAndStatus($serviceId, 'completed');
$pendingBookings = $bookingModel->countBookingsByServiceIdAndStatus($serviceId, 'pending');
$confirmedBookings = $bookingModel->countBookingsByServiceIdAndStatus($serviceId, 'confirmed');
$cancelledBookings = $bookingModel->countBookingsByServiceIdAndStatus($serviceId, 'cancelled');

// Calculate revenue from this service
$revenue = $bookingModel->calculateRevenueByServiceId($serviceId);
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
                                <a href="services.php" class="block px-6 py-3 bg-pink-50 text-pink-500 font-semibold">
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
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Service Details</h1>
                    <div class="space-x-2">
                        <a href="edit-service.php?id=<?php echo $serviceId; ?>" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                            <i class="fas fa-edit mr-2"></i> Edit Service
                        </a>
                        <a href="services.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Services
                        </a>
                    </div>
                </div>
                
                <!-- Service Details Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-gray-800 text-white py-4 px-6">
                        <h2 class="text-xl font-bold">Service Information</h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row">
                            <!-- Service Image -->
                            <div class="md:w-1/3 mb-6 md:mb-0 md:pr-6">
                                <?php if ($service['image']): ?>
                                    <div class="w-full h-64 bg-gray-100 rounded overflow-hidden">
                                        <img src="../uploads/services/<?php echo $service['image']; ?>" alt="<?php echo $service['name']; ?>" class="w-full h-full object-cover">
                                    </div>
                                <?php else: ?>
                                    <div class="w-full h-64 bg-gray-100 rounded flex items-center justify-center">
                                        <i class="fas fa-spa text-gray-400 text-5xl"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Status Badge -->
                                <div class="mt-4">
                                    <?php if ($service['is_active']): ?>
                                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-semibold">
                                            <i class="fas fa-check-circle mr-1"></i> Active
                                        </span>
                                    <?php else: ?>
                                        <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm font-semibold">
                                            <i class="fas fa-times-circle mr-1"></i> Inactive
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Service Details -->
                            <div class="md:w-2/3">
                                <h3 class="text-2xl font-bold text-gray-800 mb-2"><?php echo $service['name']; ?></h3>
                                
                                <div class="flex items-center mb-4">
                                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm font-semibold mr-2">
                                        <i class="fas fa-folder mr-1"></i> <?php echo $service['category']; ?>
                                    </span>
                                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm font-semibold mr-2">
                                        <i class="fas fa-clock mr-1"></i> <?php echo $service['duration']; ?> min
                                    </span>
                                    <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm font-semibold">
                                        <i class="fas fa-tag mr-1"></i> <?php echo formatPrice($service['price']); ?>
                                    </span>
                                </div>
                                
                                <?php if ($deal): ?>
                                    <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-percentage text-green-600"></i>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-green-800">Active Deal</h3>
                                                <div class="mt-2 text-sm text-green-700">
                                                    <p><?php echo $deal['name']; ?> - <?php echo $deal['discount_percentage']; ?>% Off</p>
                                                    <p class="mt-1">Valid until: <?php echo date('F j, Y', strtotime($deal['end_date'])); ?></p>
                                                    <p class="mt-1">Discounted Price: <strong><?php echo formatPrice($service['price'] * (1 - $deal['discount_percentage'] / 100)); ?></strong></p>
                                                </div>
                                                <div class="mt-3">
                                                    <a href="edit-deal.php?id=<?php echo $deal['id']; ?>" class="text-sm font-medium text-green-800 hover:text-green-700">
                                                        <i class="fas fa-edit mr-1"></i> Edit Deal
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mb-4">
                                    <h4 class="text-lg font-semibold text-gray-700 mb-2">Description</h4>
                                    <p class="text-gray-600"><?php echo nl2br($service['description']); ?></p>
                                </div>
                                
                                <div class="flex flex-wrap gap-2 mt-6">
                                    <a href="edit-service.php?id=<?php echo $serviceId; ?>" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                                        <i class="fas fa-edit mr-2"></i> Edit Service
                                    </a>
                                    
                                    <?php if (!$deal): ?>
                                        <a href="add-deal.php?service_id=<?php echo $serviceId; ?>" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                                            <i class="fas fa-percentage mr-2"></i> Add Deal
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="services.php?action=delete&id=<?php echo $serviceId; ?>" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition duration-300" onclick="return confirm('Are you sure you want to delete this service? This action cannot be undone.');">
                                        <i class="fas fa-trash-alt mr-2"></i> Delete Service
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-md p-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-calendar-check text-blue-500"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Total Bookings</p>
                                <p class="text-lg font-bold"><?php echo $totalBookings; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-check-circle text-green-500"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Completed</p>
                                <p class="text-lg font-bold"><?php echo $completedBookings; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-clock text-yellow-500"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Pending/Confirmed</p>
                                <p class="text-lg font-bold"><?php echo $pendingBookings + $confirmedBookings; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-pink-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-dollar-sign text-pink-500"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Revenue</p>
                                <p class="text-lg font-bold"><?php echo formatPrice($revenue); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Employees Who Perform This Service -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-gray-800 text-white py-4 px-6">
                        <h2 class="text-xl font-bold">Employees Who Perform This Service</h2>
                    </div>
                    
                    <div class="p-6">
                        <?php if (empty($employees)): ?>
                            <div class="text-center py-8">
                                <div class="text-gray-400 mb-3">
                                    <i class="fas fa-user-slash text-5xl"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-700 mb-1">No Employees Assigned</h3>
                                <p class="text-gray-500 mb-4">This service is not currently assigned to any employees.</p>
                                <a href="users.php?role=employee" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                                    <i class="fas fa-user-plus mr-2"></i> Assign Employees
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <?php foreach ($employees as $employee): ?>
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                        <div class="flex items-center">
                                            <div class="w-12 h-12 bg-gray-200 rounded-full overflow-hidden mr-4">
                                                <?php if (!empty($employee['profile_image'])): ?>
                                                    <img src="../uploads/profiles/<?php echo $employee['profile_image']; ?>" alt="<?php echo $employee['first_name']; ?>" class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <i class="fas fa-user text-gray-400 text-lg"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <h3 class="font-semibold text-gray-800"><?php echo $employee['first_name'] . ' ' . $employee['last_name']; ?></h3>
                                                <p class="text-gray-600 text-sm"><?php echo $employee['position']; ?></p>
                                            </div>
                                        </div>
                                        <div class="mt-3 flex justify-end">
                                            <a href="user-details.php?id=<?php echo $employee['id']; ?>" class="text-blue-500 hover:text-blue-700 text-sm">
                                                <i class="fas fa-eye mr-1"></i> View Profile
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent Bookings -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gray-800 text-white py-4 px-6">
                        <h2 class="text-xl font-bold">Recent Bookings</h2>
                    </div>
                    
                    <div class="p-6">
                        <?php if (empty($recentBookings)): ?>
                            <div class="text-center py-8">
                                <div class="text-gray-400 mb-3">
                                    <i class="fas fa-calendar-times text-5xl"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-700 mb-1">No Bookings Found</h3>
                                <p class="text-gray-500">This service has not been booked yet.</p>
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
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentBookings as $booking): ?>
                                            <tr>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    #<?php echo $booking['id']; ?>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <?php echo $booking['customer_name']; ?>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <?php echo $booking['employee_name']; ?>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <?php echo date('M j, Y', strtotime($booking['date'])); ?><br>
                                                    <span class="text-gray-500 text-sm"><?php echo date('g:i A', strtotime($booking['time'])); ?></span>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <?php
                                                    $statusClass = '';
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
                                                    }
                                                    ?>
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
                            
                            <?php if ($totalBookings > count($recentBookings)): ?>
                                <div class="mt-4 text-center">
                                    <a href="bookings.php?service_id=<?php echo $serviceId; ?>" class="text-blue-500 hover:text-blue-700">
                                        <i class="fas fa-list mr-1"></i> View All Bookings
                                    </a>
                                </div>
                            <?php endif; ?>
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
