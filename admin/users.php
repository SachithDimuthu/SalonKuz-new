<?php
/**
 * Admin Users Management
 * 
 * This page allows administrators to view, filter, and manage all users.
 */

// Set page title
$pageTitle = "Manage Users";

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

// Handle user deletion if requested
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $userId = intval($_GET['id']);
    
    // Check if user exists and is not the current user
    $user = $userModel->getUserById($userId);
    
    if (!$user) {
        setFlashMessage('error', 'User not found.');
    } elseif ($userId === $_SESSION['user_id']) {
        setFlashMessage('error', 'You cannot delete your own account.');
    } else {
        // Check if user has bookings
        $userBookings = $bookingModel->countUserBookings($userId);
        
        if ($userBookings > 0) {
            setFlashMessage('error', 'Cannot delete user with existing bookings. Please cancel all bookings first.');
        } else {
            // Delete user
            $deleted = $userModel->deleteUser($userId);
            
            if ($deleted) {
                setFlashMessage('success', 'User deleted successfully.');
            } else {
                setFlashMessage('error', 'Failed to delete user.');
            }
        }
    }
    
    // Redirect to remove parameters from URL
    redirect('users.php');
}

// Set up pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Set up filters
$filters = [];

if (isset($_GET['role']) && $_GET['role'] !== '') {
    $filters['role'] = $_GET['role'];
}

if (isset($_GET['search']) && $_GET['search'] !== '') {
    $filters['search'] = $_GET['search'];
}

// Get users with filters
$users = $userModel->getFilteredUsers($filters, $limit, $offset);

// Get total count for pagination
$totalUsers = $userModel->countFilteredUsers($filters);
$totalPages = ceil($totalUsers / $limit);

// Get role counts
$adminCount = $userModel->countUsersByRole('admin');
$employeeCount = $userModel->countUsersByRole('employee');
$customerCount = $userModel->countUsersByRole('customer');
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
                    <h1 class="text-2xl font-bold">Manage Users</h1>
                    <a href="add-user.php" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                        <i class="fas fa-plus mr-2"></i> Add New User
                    </a>
                </div>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-user-shield text-blue-500 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Admins</h3>
                                <p class="text-2xl font-bold"><?php echo $adminCount; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-user-tie text-green-500 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Employees</h3>
                                <p class="text-2xl font-bold"><?php echo $employeeCount; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-user text-purple-500 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Customers</h3>
                                <p class="text-2xl font-bold"><?php echo $customerCount; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-gray-800 text-white py-4 px-6">
                        <h2 class="text-xl font-bold">Users List</h2>
                    </div>
                    
                    <!-- Filters -->
                    <div class="p-6 border-b border-gray-200">
                        <form action="users.php" method="GET" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                    <select name="role" id="role" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50">
                                        <option value="">All Roles</option>
                                        <option value="admin" <?php echo (isset($_GET['role']) && $_GET['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                        <option value="employee" <?php echo (isset($_GET['role']) && $_GET['role'] === 'employee') ? 'selected' : ''; ?>>Employee</option>
                                        <option value="customer" <?php echo (isset($_GET['role']) && $_GET['role'] === 'customer') ? 'selected' : ''; ?>>Customer</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                    <input type="text" name="search" id="search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" placeholder="Search by name, email or phone" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50">
                                </div>
                            </div>
                            
                            <div class="flex justify-between">
                                <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                                    <i class="fas fa-filter mr-2"></i> Apply Filters
                                </button>
                                
                                <a href="users.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                                    <i class="fas fa-times mr-2"></i> Clear Filters
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Users Table -->
                    <div class="p-6">
                        <?php if (empty($users)): ?>
                            <div class="text-center py-8">
                                <div class="text-gray-400 mb-3">
                                    <i class="fas fa-users text-5xl"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-700 mb-1">No Users Found</h3>
                                <p class="text-gray-500">Try adjusting your filters or search criteria.</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead>
                                        <tr>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contact</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Joined</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    #<?php echo $user['id']; ?>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <div class="flex items-center">
                                                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center mr-3 overflow-hidden">
                                                            <?php if (!empty($user['profile_image'])): ?>
                                                                <img src="../uploads/profiles/<?php echo $user['profile_image']; ?>" alt="<?php echo $user['first_name']; ?>" class="w-full h-full object-cover">
                                                            <?php else: ?>
                                                                <i class="fas fa-user text-gray-400"></i>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div>
                                                            <div class="font-semibold"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></div>
                                                            <div class="text-xs text-gray-500"><?php echo $user['username']; ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <div><?php echo $user['email']; ?></div>
                                                    <div class="text-xs text-gray-500"><?php echo !empty($user['phone']) ? $user['phone'] : 'No phone'; ?></div>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
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
                                                    <span class="px-2 py-1 text-xs rounded-full <?php echo $roleClass; ?>">
                                                        <?php echo ucfirst($user['role']); ?>
                                                    </span>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <?php echo formatDate($user['created_at']); ?>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <div class="flex items-center space-x-3">
                                                        <a href="user-details.php?id=<?php echo $user['id']; ?>" class="text-blue-500 hover:text-blue-700" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="text-green-500 hover:text-green-700" title="Edit User">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                                            <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" class="text-red-500 hover:text-red-700" title="Delete User" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </a>
                                                        <?php endif; ?>
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
                                            <a href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['role']) ? '&role=' . $_GET['role'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                <span class="sr-only">Previous</span>
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <a href="?page=<?php echo $i; ?><?php echo isset($_GET['role']) ? '&role=' . $_GET['role'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo $i === $page ? 'text-pink-500 bg-pink-50' : 'text-gray-700 hover:bg-gray-50'; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <a href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['role']) ? '&role=' . $_GET['role'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
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

<?php
// Include footer
require_once '../includes/footer.php';
?>
