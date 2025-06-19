<?php
/**
 * Admin Bookings Management
 * 
 * This page allows administrators to view, filter, and manage all bookings.
 */

// Set page title
$pageTitle = "Manage Bookings";

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

// Create instances
$bookingModel = new Booking($conn);
$serviceModel = new Service($conn);
$userModel = new User($conn);

// Get all services for filter
$services = $serviceModel->getAllServices();

// Get all employees for filter
$employees = $userModel->getAllUsers('employee');

// Handle status update if provided
if (isset($_GET['id']) && isset($_GET['status']) && in_array($_GET['status'], ['pending', 'confirmed', 'cancelled', 'completed'])) {
    $bookingId = intval($_GET['id']);
    $status = $_GET['status'];
    
    $updated = $bookingModel->updateBookingStatus($bookingId, $status);
    
    if ($updated) {
        setFlashMessage('success', 'Booking status updated successfully.');
    } else {
        setFlashMessage('error', 'Failed to update booking status.');
    }
    
    // Redirect to remove parameters from URL
    redirect('bookings.php');
}

// Set up pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Set up filters
$filters = [];

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $filters['status'] = $_GET['status'];
}

if (isset($_GET['service_id']) && $_GET['service_id'] !== '') {
    $filters['service_id'] = intval($_GET['service_id']);
}

if (isset($_GET['employee_id']) && $_GET['employee_id'] !== '') {
    $filters['employee_id'] = intval($_GET['employee_id']);
}

if (isset($_GET['date_from']) && $_GET['date_from'] !== '') {
    $filters['date_from'] = $_GET['date_from'];
}

if (isset($_GET['date_to']) && $_GET['date_to'] !== '') {
    $filters['date_to'] = $_GET['date_to'];
}

if (isset($_GET['search']) && $_GET['search'] !== '') {
    $filters['search'] = $_GET['search'];
}

// Get bookings with filters
$bookings = $bookingModel->getFilteredBookings($filters, $limit, $offset);

// Get total count for pagination
$totalBookings = $bookingModel->countFilteredBookings($filters);
$totalPages = ceil($totalBookings / $limit);
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
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-gray-800 text-white py-4 px-6">
                        <h2 class="text-xl font-bold">Manage Bookings</h2>
                    </div>
                    
                    <!-- Filters -->
                    <div class="p-6 border-b border-gray-200">
                        <form action="bookings.php" method="GET" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50">
                                        <option value="">All Statuses</option>
                                        <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo (isset($_GET['status']) && $_GET['status'] === 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="completed" <?php echo (isset($_GET['status']) && $_GET['status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="service_id" class="block text-sm font-medium text-gray-700 mb-1">Service</label>
                                    <select name="service_id" id="service_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50">
                                        <option value="">All Services</option>
                                        <?php foreach ($services as $service): ?>
                                            <option value="<?php echo $service['id']; ?>" <?php echo (isset($_GET['service_id']) && $_GET['service_id'] == $service['id']) ? 'selected' : ''; ?>>
                                                <?php echo $service['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                                    <select name="employee_id" id="employee_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50">
                                        <option value="">All Employees</option>
                                        <?php foreach ($employees as $employee): ?>
                                            <option value="<?php echo $employee['id']; ?>" <?php echo (isset($_GET['employee_id']) && $_GET['employee_id'] == $employee['id']) ? 'selected' : ''; ?>>
                                                <?php echo $employee['first_name'] . ' ' . $employee['last_name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                                    <input type="date" name="date_from" id="date_from" value="<?php echo isset($_GET['date_from']) ? $_GET['date_from'] : ''; ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50">
                                </div>
                                
                                <div>
                                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                                    <input type="date" name="date_to" id="date_to" value="<?php echo isset($_GET['date_to']) ? $_GET['date_to'] : ''; ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50">
                                </div>
                                
                                <div>
                                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                    <input type="text" name="search" id="search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" placeholder="Search by customer name or email" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50">
                                </div>
                            </div>
                            
                            <div class="flex justify-between">
                                <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                                    <i class="fas fa-filter mr-2"></i> Apply Filters
                                </button>
                                
                                <a href="bookings.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                                    <i class="fas fa-times mr-2"></i> Clear Filters
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Bookings Table -->
                    <div class="p-6">
                        <?php if (empty($bookings)): ?>
                            <div class="text-center py-8">
                                <div class="text-gray-400 mb-3">
                                    <i class="fas fa-calendar-times text-5xl"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-700 mb-1">No Bookings Found</h3>
                                <p class="text-gray-500">Try adjusting your filters or search criteria.</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead>
                                        <tr>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Customer</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Service</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Employee</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date & Time</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bookings as $booking): ?>
                                            <tr>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <div class="font-semibold"><?php echo htmlspecialchars($booking['user_first_name'] . ' ' . $booking['user_last_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($booking['user_email'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <?php echo htmlspecialchars($booking['service_name'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <?php echo htmlspecialchars($booking['employee_first_name'] . ' ' . $booking['employee_last_name'], ENT_QUOTES, 'UTF-8'); ?>
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
                                                    <div class="flex items-center space-x-2">
                                                        <a href="booking-details.php?id=<?php echo $booking['id']; ?>" class="text-blue-500 hover:text-blue-700" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit-booking.php?id=<?php echo $booking['id']; ?>" class="text-green-500 hover:text-green-700" title="Edit Booking">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        
                                                        <!-- Status Update Dropdown -->
                                                        <div class="relative inline-block text-left">
                                                            <button type="button" class="text-gray-500 hover:text-gray-700 focus:outline-none" id="status-menu-<?php echo $booking['id']; ?>" aria-expanded="true" aria-haspopup="true" onclick="toggleDropdown(<?php echo $booking['id']; ?>)">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </button>
                                                            <div id="dropdown-<?php echo $booking['id']; ?>" class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10" role="menu" aria-orientation="vertical" aria-labelledby="status-menu-<?php echo $booking['id']; ?>">
                                                                <div class="py-1" role="none">
                                                                    <?php if ($booking['status'] !== 'pending'): ?>
                                                                        <a href="?id=<?php echo $booking['id']; ?>&status=pending" class="text-yellow-500 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem">Mark as Pending</a>
                                                                    <?php endif; ?>
                                                                    
                                                                    <?php if ($booking['status'] !== 'confirmed'): ?>
                                                                        <a href="?id=<?php echo $booking['id']; ?>&status=confirmed" class="text-green-500 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem">Mark as Confirmed</a>
                                                                    <?php endif; ?>
                                                                    
                                                                    <?php if ($booking['status'] !== 'completed'): ?>
                                                                        <a href="?id=<?php echo $booking['id']; ?>&status=completed" class="text-blue-500 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem">Mark as Completed</a>
                                                                    <?php endif; ?>
                                                                    
                                                                    <?php if ($booking['status'] !== 'cancelled'): ?>
                                                                        <a href="?id=<?php echo $booking['id']; ?>&status=cancelled" class="text-red-500 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem">Mark as Cancelled</a>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($totalPages > 1): ?>
                                <div class="flex justify-center mt-6">
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                        <?php if ($page > 1): ?>
                                            <a href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['service_id']) ? '&service_id=' . $_GET['service_id'] : ''; ?><?php echo isset($_GET['employee_id']) ? '&employee_id=' . $_GET['employee_id'] : ''; ?><?php echo isset($_GET['date_from']) ? '&date_from=' . $_GET['date_from'] : ''; ?><?php echo isset($_GET['date_to']) ? '&date_to=' . $_GET['date_to'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                <span class="sr-only">Previous</span>
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <a href="?page=<?php echo $i; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['service_id']) ? '&service_id=' . $_GET['service_id'] : ''; ?><?php echo isset($_GET['employee_id']) ? '&employee_id=' . $_GET['employee_id'] : ''; ?><?php echo isset($_GET['date_from']) ? '&date_from=' . $_GET['date_from'] : ''; ?><?php echo isset($_GET['date_to']) ? '&date_to=' . $_GET['date_to'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo $i === $page ? 'text-pink-500 bg-pink-50' : 'text-gray-700 hover:bg-gray-50'; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <a href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['service_id']) ? '&service_id=' . $_GET['service_id'] : ''; ?><?php echo isset($_GET['employee_id']) ? '&employee_id=' . $_GET['employee_id'] : ''; ?><?php echo isset($_GET['date_from']) ? '&date_from=' . $_GET['date_from'] : ''; ?><?php echo isset($_GET['date_to']) ? '&date_to=' . $_GET['date_to'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                <span class="sr-only">Next</span>
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        <?php endif; ?>
                                    </nav>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function toggleDropdown(id) {
    const dropdown = document.getElementById(`dropdown-${id}`);
    const allDropdowns = document.querySelectorAll('[id^="dropdown-"]');
    
    // Close all other dropdowns
    allDropdowns.forEach(menu => {
        if (menu.id !== `dropdown-${id}`) {
            menu.classList.add('hidden');
        }
    });
    
    // Toggle the selected dropdown
    dropdown.classList.toggle('hidden');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const isDropdownButton = event.target.closest('[id^="status-menu-"]');
    if (!isDropdownButton) {
        const allDropdowns = document.querySelectorAll('[id^="dropdown-"]');
        allDropdowns.forEach(menu => {
            menu.classList.add('hidden');
        });
    }
});
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>
