<?php
/**
 * Deals Page
 * 
 * This page displays all deals and special offers from Salon Kuz.
 */

// Set page title
$pageTitle = "Deals & Offers";

// Include header
require_once 'includes/header.php';

// Include models
require_once 'models/Deal.php';
require_once 'models/Service.php';

// Create instances
$dealModel = new Deal($conn);
$serviceModel = new Service($conn);

// Handle deal ID parameter for single deal view
$dealId = isset($_GET['id']) ? intval($_GET['id']) : null;

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

// Get deals
if ($dealId) {
    // Get single deal
    $deal = $dealModel->getDealById($dealId);
} else {
    // Get all active deals
    $activeOnly = isset($_GET['active']) && $_GET['active'] == '1';
    $deals = $dealModel->getAllDeals($activeOnly, $limit, $offset);
    $totalDeals = $dealModel->countDeals($activeOnly);
    
    // Calculate total pages
    $totalPages = ceil($totalDeals / $limit);
}
?>

<?php if ($dealId && $deal): ?>
    <!-- Single Deal View -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row">
                <div class="md:w-1/2 mb-8 md:mb-0 md:pr-8">
                    <?php if ($deal['image']): ?>
                        <img src="assets/images/deals/<?php echo $deal['image']; ?>" alt="<?php echo $deal['title']; ?>" class="w-full h-auto rounded-lg shadow-lg">
                    <?php else: ?>
                        <div class="w-full h-64 bg-pink-100 flex items-center justify-center rounded-lg">
                            <i class="fas fa-gift text-pink-300 text-5xl"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-6 bg-pink-50 border border-pink-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold mb-2 text-pink-700">Deal Details</h3>
                        <ul class="space-y-2">
                            <li class="flex items-center">
                                <i class="fas fa-percentage text-pink-500 mr-2"></i>
                                <span class="text-gray-700">Discount: <strong><?php echo $deal['discount_percentage']; ?>% OFF</strong></span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-calendar-alt text-pink-500 mr-2"></i>
                                <span class="text-gray-700">Valid from: <strong><?php echo formatDate($deal['start_date']); ?></strong> to <strong><?php echo formatDate($deal['end_date']); ?></strong></span>
                            </li>
                            <?php
                            $currentDate = date('Y-m-d');
                            $isActive = ($currentDate >= $deal['start_date'] && $currentDate <= $deal['end_date']);
                            ?>
                            <li class="flex items-center">
                                <i class="fas fa-circle text-<?php echo $isActive ? 'green' : 'red'; ?>-500 mr-2 text-xs"></i>
                                <span class="text-gray-700">Status: <strong class="text-<?php echo $isActive ? 'green' : 'red'; ?>-500"><?php echo $isActive ? 'Active' : 'Expired'; ?></strong></span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="md:w-1/2">
                    <div class="bg-pink-500 text-white text-sm font-bold py-1 px-3 rounded-full inline-block mb-2">
                        <?php echo $deal['discount_percentage']; ?>% OFF
                    </div>
                    <h1 class="text-3xl font-bold mb-4 salon-primary"><?php echo $deal['title']; ?></h1>
                    
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold mb-2">Description</h3>
                        <p class="text-gray-700"><?php echo nl2br($deal['description']); ?></p>
                    </div>
                    
                    <?php if (!empty($deal['services'])): ?>
                        <div class="mb-6">
                            <h3 class="text-xl font-semibold mb-2">Applicable Services</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php foreach ($deal['services'] as $service): ?>
                                    <div class="bg-gray-50 border border-gray-200 rounded p-4">
                                        <h4 class="font-semibold"><?php echo $service['name']; ?></h4>
                                        <div class="flex justify-between items-center mt-2">
                                            <div>
                                                <span class="text-pink-500 font-bold"><?php echo formatPrice(calculateDiscountedPrice($service['price'], $deal['discount_percentage'])); ?></span>
                                                <span class="text-gray-500 line-through text-sm ml-1"><?php echo formatPrice($service['price']); ?></span>
                                            </div>
                                            <a href="services.php?id=<?php echo $service['id']; ?>" class="text-pink-500 hover:text-pink-600">View</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($isActive): ?>
                        <?php if (isLoggedIn()): ?>
                            <a href="booking.php?deal_id=<?php echo $deal['id']; ?>" class="inline-block bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-6 rounded transition duration-300">Book with This Deal</a>
                        <?php else: ?>
                            <a href="login.php?redirect=deals.php?id=<?php echo $deal['id']; ?>" class="inline-block bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-6 rounded transition duration-300">Login to Book</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded mb-4">
                            This deal has expired. Check out our other active deals.
                        </div>
                    <?php endif; ?>
                    
                    <a href="deals.php" class="inline-block ml-4 text-pink-500 hover:text-pink-600 font-semibold">Back to Deals</a>
                </div>
            </div>
        </div>
    </section>
<?php else: ?>
    <!-- Deals List View -->
    <section class="py-12 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h1 class="text-3xl font-bold mb-2 salon-primary">Special Deals & Offers</h1>
                <p class="text-gray-700">Take advantage of our limited-time offers and save on your favorite beauty services</p>
            </div>
            
            <!-- Filter -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-8">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Filter Deals</h3>
                    <div>
                        <a href="deals.php?active=1" class="<?php echo (isset($_GET['active']) && $_GET['active'] == '1') ? 'bg-pink-500 text-white' : 'bg-gray-200 text-gray-700'; ?> hover:bg-pink-500 hover:text-white font-bold py-2 px-4 rounded transition duration-300 mr-2">Active Deals</a>
                        <a href="deals.php" class="<?php echo (!isset($_GET['active'])) ? 'bg-pink-500 text-white' : 'bg-gray-200 text-gray-700'; ?> hover:bg-pink-500 hover:text-white font-bold py-2 px-4 rounded transition duration-300">All Deals</a>
                    </div>
                </div>
            </div>
            
            <?php if (empty($deals)): ?>
                <div class="text-center py-8">
                    <p class="text-gray-700">No deals found. Please check back later for new offers.</p>
                </div>
            <?php else: ?>
                <!-- Deals Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($deals as $deal): ?>
                        <?php
                        $currentDate = date('Y-m-d');
                        $isActive = ($currentDate >= $deal['start_date'] && $currentDate <= $deal['end_date']);
                        ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden transition duration-300 hover:shadow-xl <?php echo !$isActive ? 'opacity-75' : ''; ?>">
                            <?php if ($deal['image']): ?>
                                <img src="assets/images/deals/<?php echo $deal['image']; ?>" alt="<?php echo $deal['title']; ?>" class="w-full h-48 object-cover">
                            <?php else: ?>
                                <div class="w-full h-48 bg-pink-100 flex items-center justify-center">
                                    <i class="fas fa-gift text-pink-300 text-4xl"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="p-6">
                                <div class="flex items-center mb-2">
                                    <div class="bg-pink-500 text-white text-sm font-bold py-1 px-2 rounded-full inline-block">
                                        <?php echo $deal['discount_percentage']; ?>% OFF
                                    </div>
                                    <?php if ($isActive): ?>
                                        <span class="ml-auto bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">Active</span>
                                    <?php else: ?>
                                        <span class="ml-auto bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs">Expired</span>
                                    <?php endif; ?>
                                </div>
                                <h3 class="text-xl font-semibold mb-2"><?php echo $deal['title']; ?></h3>
                                <p class="text-gray-600 mb-4 text-sm"><?php echo substr($deal['description'], 0, 100) . '...'; ?></p>
                                <p class="text-gray-500 text-sm mb-4">
                                    Valid from <?php echo formatDate($deal['start_date']); ?> to <?php echo formatDate($deal['end_date']); ?>
                                </p>
                                
                                <?php if (!empty($deal['services'])): ?>
                                    <div class="mb-4">
                                        <p class="text-sm text-gray-600 mb-1">Applicable for:</p>
                                        <div class="flex flex-wrap">
                                            <?php 
                                            $serviceCount = count($deal['services']);
                                            $displayCount = min(3, $serviceCount);
                                            for ($i = 0; $i < $displayCount; $i++): 
                                            ?>
                                                <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded mr-1 mb-1"><?php echo $deal['services'][$i]['name']; ?></span>
                                            <?php endfor; ?>
                                            
                                            <?php if ($serviceCount > 3): ?>
                                                <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">+<?php echo $serviceCount - 3; ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="flex space-x-2">
                                    <a href="deals.php?id=<?php echo $deal['id']; ?>" class="flex-1 text-center bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded transition duration-300">View Details</a>
                                    <?php if ($isActive && isLoggedIn()): ?>
                                        <a href="booking.php?deal_id=<?php echo $deal['id']; ?>" class="flex-1 text-center bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded transition duration-300">Book Now</a>
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
                        if (isset($_GET['active'])) $queryParams[] = "active=" . urlencode($_GET['active']);
                        $queryString = !empty($queryParams) ? '&' . implode('&', $queryParams) : '';
                        
                        echo generatePagination($page, $totalPages, "deals.php?" . $queryString);
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
