<?php
/**
 * Services Page
 * 
 * This page displays all services offered by Salon Kuz and allows users to view details and book appointments.
 */

// Set page title
$pageTitle = "Services";

// Include header
require_once 'includes/header.php';

// Include models
require_once 'models/Service.php';
require_once 'models/Deal.php';

// Create instances
$serviceModel = new Service($conn);
$dealModel = new Deal($conn);

// Get service categories
$categories = $serviceModel->getServiceCategories();

// Handle service ID parameter for single service view
$serviceId = isset($_GET['id']) ? intval($_GET['id']) : null;

// Handle category filter
$categoryFilter = isset($_GET['category']) ? sanitize($_GET['category']) : null;

// Handle search
$searchTerm = isset($_GET['search']) ? sanitize($_GET['search']) : null;

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 8;
$offset = ($page - 1) * $limit;

// Get services based on filters
if ($serviceId) {
    // Get single service
    $service = $serviceModel->getServiceById($serviceId);
    
    // Get active deal for this service if any
    $activeDeal = $dealModel->getActiveDealForService($serviceId);
} else {
    // Get services based on filters
    if ($searchTerm) {
        $services = $serviceModel->searchServices($searchTerm, $categoryFilter);
        $totalServices = count($services);
        $services = array_slice($services, $offset, $limit);
    } else {
        $services = $serviceModel->getAllServices($categoryFilter, $limit, $offset);
        $totalServices = $serviceModel->countServices($categoryFilter);
    }
    
    // Calculate total pages
    $totalPages = ceil($totalServices / $limit);
}
?>

<?php if ($serviceId && $service): ?>
    <!-- Single Service View -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row">
                <div class="md:w-1/2 mb-8 md:mb-0 md:pr-8">
                    <?php if ($service['image']): ?>
                        <img src="assets/images/services/<?php echo $service['image']; ?>" alt="<?php echo $service['name']; ?>" class="w-full h-auto rounded-lg shadow-lg">
                    <?php else: ?>
                        <div class="w-full h-64 bg-gray-300 flex items-center justify-center rounded-lg">
                            <i class="fas fa-spa text-gray-400 text-5xl"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="md:w-1/2">
                    <h1 class="text-3xl font-bold mb-2 salon-primary"><?php echo $service['name']; ?></h1>
                    <div class="flex items-center mb-4">
                        <span class="bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm mr-2"><?php echo $service['category']; ?></span>
                        <span class="text-gray-600"><i class="far fa-clock mr-1"></i> <?php echo $service['duration']; ?> minutes</span>
                    </div>
                    
                    <?php if ($activeDeal): ?>
                        <div class="bg-pink-100 border border-pink-300 text-pink-700 px-4 py-3 rounded mb-4">
                            <div class="flex items-center">
                                <i class="fas fa-tag mr-2"></i>
                                <div>
                                    <p class="font-bold"><?php echo $activeDeal['title']; ?> - <?php echo $activeDeal['discount_percentage']; ?>% OFF</p>
                                    <p class="text-sm">Valid until <?php echo formatDate($activeDeal['end_date']); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <span class="text-2xl font-bold text-pink-500"><?php echo formatPrice(calculateDiscountedPrice($service['price'], $activeDeal['discount_percentage'])); ?></span>
                            <span class="text-gray-500 line-through ml-2"><?php echo formatPrice($service['price']); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="mb-4">
                            <span class="text-2xl font-bold text-pink-500"><?php echo formatPrice($service['price']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold mb-2">Description</h3>
                        <p class="text-gray-700"><?php echo nl2br($service['description']); ?></p>
                    </div>
                    
                    <?php if (isLoggedIn()): ?>
                        <a href="booking.php?service_id=<?php echo $service['id']; ?>" class="inline-block bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-6 rounded transition duration-300">Book Now</a>
                    <?php else: ?>
                        <a href="login.php?redirect=services.php?id=<?php echo $service['id']; ?>" class="inline-block bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-6 rounded transition duration-300">Login to Book</a>
                    <?php endif; ?>
                    
                    <a href="services.php" class="inline-block ml-4 text-pink-500 hover:text-pink-600 font-semibold">Back to Services</a>
                </div>
            </div>
        </div>
    </section>
<?php else: ?>
    <!-- Services List View -->
    <section class="py-12 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h1 class="text-3xl font-bold mb-2 salon-primary">Our Services</h1>
                <p class="text-gray-700">Discover our comprehensive range of beauty services</p>
            </div>
            
            <!-- Search and Filter -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <form action="services.php" method="GET" class="flex flex-col md:flex-row md:items-end space-y-4 md:space-y-0 md:space-x-4">
                    <div class="flex-grow">
                        <label for="search" class="block text-gray-700 text-sm font-bold mb-2">Search Services</label>
                        <input type="text" id="search" name="search" value="<?php echo $searchTerm; ?>" placeholder="Search by name or description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div>
                        <label for="category" class="block text-gray-700 text-sm font-bold mb-2">Filter by Category</label>
                        <select id="category" name="category" class="shadow border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category; ?>" <?php echo ($categoryFilter === $category) ? 'selected' : ''; ?>><?php echo $category; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                            <i class="fas fa-search mr-1"></i> Search
                        </button>
                    </div>
                    <?php if ($searchTerm || $categoryFilter): ?>
                        <div>
                            <a href="services.php" class="inline-block text-pink-500 hover:text-pink-600 font-semibold">Clear Filters</a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
            
            <?php if (empty($services)): ?>
                <div class="text-center py-8">
                    <p class="text-gray-700">No services found. Please try a different search or category.</p>
                </div>
            <?php else: ?>
                <!-- Services Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <?php foreach ($services as $service): ?>
                        <?php 
                        // Check if service has an active deal
                        $hasActiveDeal = $dealModel->serviceHasActiveDeals($service['id']);
                        $activeDeal = $hasActiveDeal ? $dealModel->getActiveDealForService($service['id']) : null;
                        ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden transition duration-300 hover:shadow-xl">
                            <?php if ($service['image']): ?>
                                <img src="assets/images/services/<?php echo $service['image']; ?>" alt="<?php echo $service['name']; ?>" class="w-full h-48 object-cover">
                            <?php else: ?>
                                <div class="w-full h-48 bg-gray-300 flex items-center justify-center">
                                    <i class="fas fa-spa text-gray-400 text-4xl"></i>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($hasActiveDeal): ?>
                                <div class="absolute top-2 right-2 bg-pink-500 text-white text-xs font-bold py-1 px-2 rounded-full">
                                    <?php echo $activeDeal['discount_percentage']; ?>% OFF
                                </div>
                            <?php endif; ?>
                            
                            <div class="p-6">
                                <div class="flex items-center mb-2">
                                    <h3 class="text-xl font-semibold"><?php echo $service['name']; ?></h3>
                                    <span class="ml-auto bg-gray-200 text-gray-700 px-2 py-1 rounded-full text-xs"><?php echo $service['category']; ?></span>
                                </div>
                                <p class="text-gray-600 mb-4 text-sm"><?php echo substr($service['description'], 0, 100) . '...'; ?></p>
                                <div class="flex justify-between items-center mb-4">
                                    <?php if ($hasActiveDeal): ?>
                                        <div>
                                            <span class="text-pink-500 font-bold"><?php echo formatPrice(calculateDiscountedPrice($service['price'], $activeDeal['discount_percentage'])); ?></span>
                                            <span class="text-gray-500 line-through text-sm ml-1"><?php echo formatPrice($service['price']); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-pink-500 font-bold"><?php echo formatPrice($service['price']); ?></span>
                                    <?php endif; ?>
                                    <span class="text-gray-500 text-sm"><i class="far fa-clock mr-1"></i> <?php echo $service['duration']; ?> min</span>
                                </div>
                                <div class="flex space-x-2">
                                    <a href="services.php?id=<?php echo $service['id']; ?>" class="flex-1 text-center bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded transition duration-300">View Details</a>
                                    <?php if (isLoggedIn()): ?>
                                        <a href="booking.php?service_id=<?php echo $service['id']; ?>" class="flex-1 text-center bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded transition duration-300">Book</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="mt-8">
                        <?php 
                        $queryParams = [];
                        if ($categoryFilter) $queryParams[] = "category=" . urlencode($categoryFilter);
                        if ($searchTerm) $queryParams[] = "search=" . urlencode($searchTerm);
                        $queryString = !empty($queryParams) ? '&' . implode('&', $queryParams) : '';
                        
                        echo generatePagination($page, $totalPages, "services.php?" . $queryString);
                        ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<?php
// Include footer
require_once 'includes/footer.php';
?>
