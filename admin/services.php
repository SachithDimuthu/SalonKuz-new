<?php
/**
 * Admin Services Management
 * 
 * This page allows administrators to view, filter, and manage all salon services.
 */

// Set page title
$pageTitle = "Manage Services";

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

// Create instances
$serviceModel = new Service($conn);
$userModel = new User($conn);
$dealModel = new Deal($conn);

// Handle service deletion if requested
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $serviceId = intval($_GET['id']);
    
    // Check if service exists
    $service = $serviceModel->getServiceById($serviceId);
    
    if (!$service) {
        setFlashMessage('error', 'Service not found.');
    } else {
        // Check if service has bookings
        $hasBookings = $serviceModel->serviceHasBookings($serviceId);
        
        if ($hasBookings) {
            setFlashMessage('error', 'Cannot delete service with existing bookings. Please archive it instead.');
        } else {
            // Delete service
            $deleted = $serviceModel->deleteService($serviceId);
            
            if ($deleted) {
                setFlashMessage('success', 'Service deleted successfully.');
            } else {
                setFlashMessage('error', 'Failed to delete service.');
            }
        }
    }
    
    // Redirect to remove parameters from URL
    redirect('services.php');
}

// Set up pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Set up filters
$filters = [];

if (isset($_GET['category']) && $_GET['category'] !== '') {
    $filters['category'] = $_GET['category'];
}

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $filters['status'] = $_GET['status'] === 'active' ? 1 : 0;
}

if (isset($_GET['search']) && $_GET['search'] !== '') {
    $filters['search'] = $_GET['search'];
}

// Get services with filters
$services = $serviceModel->getFilteredServices($filters, $limit, $offset);

// Get total count for pagination
$totalServices = $serviceModel->countFilteredServices($filters);
$totalPages = ceil($totalServices / $limit);

// Get service categories
$categories = $serviceModel->getAllCategories();

// Get service counts
$activeCount = $serviceModel->countServicesByStatus(1);
$inactiveCount = $serviceModel->countServicesByStatus(0);
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
                    <h1 class="text-2xl font-bold">Manage Services</h1>
                    <a href="add-service.php" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                        <i class="fas fa-plus mr-2"></i> Add New Service
                    </a>
                </div>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-spa text-pink-500 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Total Services</h3>
                                <p class="text-2xl font-bold"><?php echo $totalServices; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Active Services</h3>
                                <p class="text-2xl font-bold"><?php echo $activeCount; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-times-circle text-gray-500 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-700">Inactive Services</h3>
                                <p class="text-2xl font-bold"><?php echo $inactiveCount; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-gray-800 text-white py-4 px-6">
                        <h2 class="text-xl font-bold">Services List</h2>
                    </div>
                    
                    <!-- Filters -->
                    <div class="p-6 border-b border-gray-200">
                        <form action="services.php" method="GET" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                    <select name="category" id="category" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] === $category) ? 'selected' : ''; ?>>
                                                <?php echo $category; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50">
                                        <option value="">All Statuses</option>
                                        <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                    <input type="text" name="search" id="search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" placeholder="Search by name or description" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50">
                                </div>
                            </div>
                            
                            <div class="flex justify-between">
                                <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                                    <i class="fas fa-filter mr-2"></i> Apply Filters
                                </button>
                                
                                <a href="services.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                                    <i class="fas fa-times mr-2"></i> Clear Filters
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Services Table -->
                    <div class="p-6">
                        <?php if (empty($services)): ?>
                            <div class="text-center py-8">
                                <div class="text-gray-400 mb-3">
                                    <i class="fas fa-spa text-5xl"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-700 mb-1">No Services Found</h3>
                                <p class="text-gray-500">Try adjusting your filters or search criteria.</p>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead>
                                        <tr>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Service</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Category</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Price</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Duration</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                            <th class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($services as $service): ?>
                                            <?php 
                                            // Check if service has an active deal
                                            $deal = $dealModel->getActiveDealForService($service['id']);
                                            ?>
                                            <tr>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    #<?php echo $service['id']; ?>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <div class="font-semibold"><?php echo $service['name']; ?></div>
                                                    <div class="text-xs text-gray-500"><?php echo truncateText($service['description'], 50); ?></div>
                                                    <?php if ($deal): ?>
                                                        <div class="mt-1">
                                                            <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">
                                                                <?php echo $deal['discount_percentage']; ?>% Off
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <?php echo $service['category']; ?>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <?php echo formatPrice($service['price']); ?>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <?php echo $service['duration']; ?> min
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <?php if ($service['is_active']): ?>
                                                        <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full">Active</span>
                                                    <?php else: ?>
                                                        <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <div class="flex items-center space-x-3">
                                                        <a href="service-details.php?id=<?php echo $service['id']; ?>" class="text-blue-500 hover:text-blue-700" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit-service.php?id=<?php echo $service['id']; ?>" class="text-green-500 hover:text-green-700" title="Edit Service">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="services.php?action=delete&id=<?php echo $service['id']; ?>" class="text-red-500 hover:text-red-700" title="Delete Service" onclick="return confirm('Are you sure you want to delete this service? This action cannot be undone.');">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
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
                                            <a href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                <span class="sr-only">Previous</span>
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <a href="?page=<?php echo $i; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?php echo $i === $page ? 'text-pink-500 bg-pink-50' : 'text-gray-700 hover:bg-gray-50'; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <a href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
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
