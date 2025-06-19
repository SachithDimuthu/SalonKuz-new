<?php
/**
 * User Details
 * 
 * This page displays detailed information about a specific user.
 */

// Set page title
$pageTitle = "User Details";

// Include header
require_once '../includes/header.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'You do not have permission to access the admin dashboard.');
    redirect('../login.php');
}

// Include models
require_once '../models/User.php';
require_once '../models/Booking.php';

// Create instances
$userModel = new User($conn);
$bookingModel = new Booking($conn);

// Get user ID from URL
$userId = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$userId) {
    setFlashMessage('error', 'Invalid user ID.');
    redirect('users.php');
}

// Get user details
$user = $userModel->getUserById($userId);

if (!$user) {
    setFlashMessage('error', 'User not found.');
    redirect('users.php');
}

// Get user bookings if customer
$userBookings = [];
$bookingCount = 0;
if ($user['role'] === 'customer') {
    $userBookings = $bookingModel->getBookingsByUserId($userId, 5);
    $bookingCount = $bookingModel->countUserBookings($userId);
}

// Get employee bookings if employee
$employeeBookings = [];
$employeeBookingCount = 0;
if ($user['role'] === 'employee') {
    $employeeBookings = $bookingModel->getBookingsByEmployeeId($userId, 5);
    $employeeBookingCount = $bookingModel->countEmployeeBookings($userId);
    
    // Get services assigned to employee
    $employeeServices = $userModel->getEmployeeServices($userId);
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
                                <a href="users.php" class="block px-6 py-3 bg-pink-50 text-pink-500 font-semibold">
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
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">User Details</h1>
                    <div class="flex space-x-2">
                        <a href="edit-user.php?id=<?php echo $userId; ?>" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                            <i class="fas fa-edit mr-2"></i> Edit User
                        </a>
                        <a href="users.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Users
                        </a>
                    </div>
                </div>
                
                <!-- User Profile Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-gray-800 text-white py-4 px-6">
                        <h2 class="text-xl font-bold">User Profile</h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row">
                            <!-- Profile Image -->
                            <div class="md:w-1/4 flex justify-center mb-6 md:mb-0">
                                <div class="w-32 h-32 bg-gray-200 rounded-full overflow-hidden">
                                    <?php if (!empty($user['profile_image'])): ?>
                                        <img src="../uploads/profiles/<?php echo $user['profile_image']; ?>" alt="<?php echo $user['first_name']; ?>" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center">
                                            <i class="fas fa-user text-gray-400 text-5xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- User Information -->
                            <div class="md:w-3/4 md:pl-6">
                                <div class="flex items-center mb-4">
                                    <h3 class="text-2xl font-bold mr-3"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h3>
                                    <?php
                                    $roleClass = '';
                                    switch ($user['role']) {
                                        case 'admin':
                                            $roleClass = 'bg-blue-100 text-blue-700';
                                            break;
                                        case 'employee':
                                            $roleClass = 'bg-green-100 text-green-700';
                                            break;
                                        case 'customer':
                                            $roleClass = 'bg-purple-100 text-purple-700';
                                            break;
                                    }
                                    ?>
                                    <span class="px-3 py-1 text-sm rounded-full <?php echo $roleClass; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-gray-600 mb-2">
                                            <i class="fas fa-user mr-2 text-gray-400"></i> 
                                            <span class="font-semibold">Username:</span> <?php echo $user['username']; ?>
                                        </p>
                                        <p class="text-gray-600 mb-2">
                                            <i class="fas fa-envelope mr-2 text-gray-400"></i> 
                                            <span class="font-semibold">Email:</span> <?php echo $user['email']; ?>
                                        </p>
                                        <p class="text-gray-600 mb-2">
                                            <i class="fas fa-phone mr-2 text-gray-400"></i> 
                                            <span class="font-semibold">Phone:</span> <?php echo !empty($user['phone']) ? $user['phone'] : 'Not provided'; ?>
                                        </p>
                                    </div>
                                    <div>
                                        <?php if ($user['role'] === 'employee' && isset($user['position'])): ?>
                                            <p class="text-gray-600 mb-2">
                                                <i class="fas fa-briefcase mr-2 text-gray-400"></i> 
                                                <span class="font-semibold">Position:</span> <?php echo $user['position']; ?>
                                            </p>
                                        <?php endif; ?>
                                        <p class="text-gray-600 mb-2">
                                            <i class="fas fa-calendar-plus mr-2 text-gray-400"></i> 
                                            <span class="font-semibold">Joined:</span> <?php echo formatDate($user['created_at']); ?>
                                        </p>
                                        <p class="text-gray-600 mb-2">
                                            <i class="fas fa-clock mr-2 text-gray-400"></i> 
                                            <span class="font-semibold">Last Updated:</span> <?php echo formatDate($user['updated_at']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($user['role'] === 'employee' && isset($employeeServices)): ?>
                <!-- Employee Services -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-gray-800 text-white py-4 px-6 flex justify-between items-center">
                        <h2 class="text-xl font-bold">Assigned Services</h2>
                        <a href="edit-employee-services.php?id=<?php echo $userId; ?>" class="text-white hover:text-pink-200 text-sm">
                            <i class="fas fa-edit mr-1"></i> Edit Services
                        </a>
                    </div>
                    
                    <div class="p-6">
                        <?php if (empty($employeeServices)): ?>
                            <div class="text-center py-8">
                                <div class="text-gray-400 mb-3">
                                    <i class="fas fa-spa text-5xl"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-700 mb-1">No Services Assigned</h3>
                                <p class="text-gray-500">This employee has not been assigned to any services yet.</p>
                                <a href="edit-employee-services.php?id=<?php echo $userId; ?>" class="mt-4 inline-block bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                                    <i class="fas fa-plus mr-2"></i> Assign Services
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <?php foreach ($employeeServices as $service): ?>
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                        <h3 class="font-semibold text-lg mb-1"><?php echo $service['name']; ?></h3>
                                        <p class="text-gray-600 text-sm mb-2"><?php echo $service['duration']; ?> min | <?php echo formatPrice($service['price']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo truncateText($service['description'], 100); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($user['role'] === 'customer' && !empty($userBookings)): ?>
                <!-- Customer Bookings -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-gray-800 text-white py-4 px-6 flex justify-between items-center">
                        <h2 class="text-xl font-bold">Recent Bookings</h2>
                        <span class="bg-gray-700 text-white text-xs px-2 py-1 rounded-full">
                            Total: <?php echo $bookingCount; ?>
                        </span>
                    </div>
                    
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Booking ID</th>
                                        <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Service</th>
                                        <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date & Time</th>
                                        <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                        <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($userBookings as $booking): ?>
                                        <tr>
                                            <td class="py-3 px-4 border-b border-gray-200">
                                                #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?>
                                            </td>
                                            <td class="py-3 px-4 border-b border-gray-200">
                                                <?php echo $booking['service_name']; ?>
                                            </td>
                                            <td class="py-3 px-4 border-b border-gray-200">
                                                <?php echo formatDate($booking['booking_date']); ?><br>
                                                <span class="text-xs text-gray-500"><?php echo formatTime($booking['start_time']); ?> - <?php echo formatTime($booking['end_time']); ?></span>
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
                                                <span class="px-2 py-1 text-xs rounded-full <?php echo $statusClass; ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 border-b border-gray-200">
                                                <a href="booking-details.php?id=<?php echo $booking['id']; ?>" class="text-blue-500 hover:text-blue-700" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if ($bookingCount > 5): ?>
                            <div class="mt-4 text-center">
                                <a href="bookings.php?user_id=<?php echo $userId; ?>" class="text-pink-500 hover:text-pink-700">
                                    View All <?php echo $bookingCount; ?> Bookings <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($user['role'] === 'employee' && !empty($employeeBookings)): ?>
                <!-- Employee Bookings -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-gray-800 text-white py-4 px-6 flex justify-between items-center">
                        <h2 class="text-xl font-bold">Recent Appointments</h2>
                        <span class="bg-gray-700 text-white text-xs px-2 py-1 rounded-full">
                            Total: <?php echo $employeeBookingCount; ?>
                        </span>
                    </div>
                    
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Booking ID</th>
                                        <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Customer</th>
                                        <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Service</th>
                                        <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date & Time</th>
                                        <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                        <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($employeeBookings as $booking): ?>
                                        <tr>
                                            <td class="py-3 px-4 border-b border-gray-200">
                                                #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?>
                                            </td>
                                            <td class="py-3 px-4 border-b border-gray-200">
                                                <?php echo $booking['customer_name']; ?>
                                            </td>
                                            <td class="py-3 px-4 border-b border-gray-200">
                                                <?php echo $booking['service_name']; ?>
                                            </td>
                                            <td class="py-3 px-4 border-b border-gray-200">
                                                <?php echo formatDate($booking['booking_date']); ?><br>
                                                <span class="text-xs text-gray-500"><?php echo formatTime($booking['start_time']); ?> - <?php echo formatTime($booking['end_time']); ?></span>
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
                                                <span class="px-2 py-1 text-xs rounded-full <?php echo $statusClass; ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                            <td class="py-3 px-4 border-b border-gray-200">
                                                <a href="booking-details.php?id=<?php echo $booking['id']; ?>" class="text-blue-500 hover:text-blue-700" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if ($employeeBookingCount > 5): ?>
                            <div class="mt-4 text-center">
                                <a href="bookings.php?employee_id=<?php echo $userId; ?>" class="text-pink-500 hover:text-pink-700">
                                    View All <?php echo $employeeBookingCount; ?> Appointments <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
require_once '../includes/footer.php';
?>
