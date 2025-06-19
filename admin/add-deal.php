<?php
/**
 * Add Deal
 * 
 * This page allows administrators to add a new deal or discount.
 */

// Set page title
$pageTitle = "Add New Deal";

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

// Create instances
$dealModel = new Deal($conn);
$serviceModel = new Service($conn);

// Get all services for dropdown
$services = $serviceModel->getAllServices();

// Pre-select service if provided in URL
$preSelectedServiceId = isset($_GET['service_id']) ? intval($_GET['service_id']) : null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $serviceId = intval($_POST['service_id']);
    $discountPercentage = floatval($_POST['discount_percentage']);
    $startDate = sanitizeInput($_POST['start_date']);
    $endDate = sanitizeInput($_POST['end_date']);
    
    // Validate inputs
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Deal name is required.';
    }
    
    if (empty($description)) {
        $errors[] = 'Description is required.';
    }
    
    if ($serviceId <= 0) {
        $errors[] = 'Please select a service.';
    }
    
    if ($discountPercentage <= 0 || $discountPercentage > 100) {
        $errors[] = 'Discount percentage must be between 1 and 100.';
    }
    
    if (empty($startDate)) {
        $errors[] = 'Start date is required.';
    }
    
    if (empty($endDate)) {
        $errors[] = 'End date is required.';
    }
    
    if (!empty($startDate) && !empty($endDate) && strtotime($startDate) > strtotime($endDate)) {
        $errors[] = 'End date must be after start date.';
    }
    
    // Check if service already has an active deal
    if ($serviceId > 0) {
        $existingDeal = $dealModel->getActiveDealForService($serviceId);
        
        if ($existingDeal) {
            $errors[] = 'This service already has an active deal. Please edit the existing deal or select a different service.';
        }
    }
    
    // If no errors, create the deal
    if (empty($errors)) {
        $dealId = $dealModel->createDeal(
            $name,
            $description,
            $serviceId,
            $discountPercentage,
            $startDate,
            $endDate
        );
        
        if ($dealId) {
            setFlashMessage('success', 'Deal created successfully.');
            redirect('deals.php');
        } else {
            $errors[] = 'Failed to create deal. Please try again.';
        }
    }
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
                    <h1 class="text-2xl font-bold">Add New Deal</h1>
                    <a href="deals.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Deals
                    </a>
                </div>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gray-800 text-white py-4 px-6">
                        <h2 class="text-xl font-bold">Deal Information</h2>
                    </div>
                    
                    <div class="p-6">
                        <!-- Error Messages -->
                        <?php if (isset($errors) && !empty($errors)): ?>
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                                <ul class="list-disc pl-5">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Add Deal Form -->
                        <form action="add-deal.php<?php echo $preSelectedServiceId ? '?service_id=' . $preSelectedServiceId : ''; ?>" method="POST">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <!-- Deal Name -->
                                <div class="md:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Deal Name *</label>
                                    <input type="text" name="name" id="name" value="<?php echo isset($name) ? $name : ''; ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required>
                                    <p class="text-xs text-gray-500 mt-1">Example: "Summer Special", "Holiday Discount", etc.</p>
                                </div>
                                
                                <!-- Description -->
                                <div class="md:col-span-2">
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                                    <textarea name="description" id="description" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required><?php echo isset($description) ? $description : ''; ?></textarea>
                                    <p class="text-xs text-gray-500 mt-1">Describe the deal and any special conditions.</p>
                                </div>
                                
                                <!-- Service -->
                                <div class="md:col-span-2">
                                    <label for="service_id" class="block text-sm font-medium text-gray-700 mb-1">Service *</label>
                                    <select name="service_id" id="service_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required>
                                        <option value="">Select Service</option>
                                        <?php foreach ($services as $service): ?>
                                            <?php 
                                            // Check if service already has an active deal
                                            $existingDeal = $dealModel->getActiveDealForService($service['id']);
                                            $disabled = $existingDeal ? 'disabled' : '';
                                            $selected = (isset($serviceId) && $serviceId === $service['id']) || ($preSelectedServiceId === $service['id']);
                                            ?>
                                            <option value="<?php echo $service['id']; ?>" <?php echo $selected ? 'selected' : ''; ?> <?php echo $disabled; ?>>
                                                <?php echo $service['name']; ?> (<?php echo formatPrice($service['price']); ?>) 
                                                <?php echo $existingDeal ? '- Already has a deal' : ''; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">Services that already have active deals are disabled.</p>
                                </div>
                                
                                <!-- Discount Percentage -->
                                <div>
                                    <label for="discount_percentage" class="block text-sm font-medium text-gray-700 mb-1">Discount Percentage *</label>
                                    <div class="relative">
                                        <input type="number" name="discount_percentage" id="discount_percentage" min="1" max="100" value="<?php echo isset($discountPercentage) ? $discountPercentage : '10'; ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50 pr-12" required>
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <span class="text-gray-500">%</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Service Price Preview -->
                                <div id="price-preview" class="hidden">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Price Preview</label>
                                    <div class="bg-gray-50 rounded-md p-3 border border-gray-200">
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-600">Original Price:</span>
                                            <span id="original-price" class="font-semibold"></span>
                                        </div>
                                        <div class="flex items-center justify-between mt-1">
                                            <span class="text-gray-600">Discount:</span>
                                            <span id="discount-amount" class="font-semibold text-red-500"></span>
                                        </div>
                                        <div class="flex items-center justify-between mt-1 border-t border-gray-200 pt-1">
                                            <span class="text-gray-700 font-medium">Final Price:</span>
                                            <span id="final-price" class="font-bold text-green-600"></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Start Date -->
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                                    <input type="date" name="start_date" id="start_date" value="<?php echo isset($startDate) ? $startDate : date('Y-m-d'); ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required>
                                </div>
                                
                                <!-- End Date -->
                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                                    <input type="date" name="end_date" id="end_date" value="<?php echo isset($endDate) ? $endDate : date('Y-m-d', strtotime('+30 days')); ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required>
                                </div>
                            </div>
                            
                            <!-- Submit Buttons -->
                            <div class="flex justify-between">
                                <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-6 rounded transition duration-300">
                                    <i class="fas fa-plus mr-2"></i> Create Deal
                                </button>
                                
                                <a href="deals.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded transition duration-300">
                                    <i class="fas fa-times mr-2"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceSelect = document.getElementById('service_id');
    const discountInput = document.getElementById('discount_percentage');
    const pricePreview = document.getElementById('price-preview');
    const originalPrice = document.getElementById('original-price');
    const discountAmount = document.getElementById('discount-amount');
    const finalPrice = document.getElementById('final-price');
    
    // Service prices data
    const servicePrices = <?php echo json_encode(array_column($services, 'price', 'id')); ?>;
    
    // Update price preview
    function updatePricePreview() {
        const serviceId = serviceSelect.value;
        const discount = parseFloat(discountInput.value) || 0;
        
        if (serviceId && servicePrices[serviceId]) {
            const price = parseFloat(servicePrices[serviceId]);
            const discountValue = (price * discount / 100).toFixed(2);
            const finalPriceValue = (price - discountValue).toFixed(2);
            
            originalPrice.textContent = '<?php echo CURRENCY_SYMBOL; ?>' + price.toFixed(2);
            discountAmount.textContent = '-<?php echo CURRENCY_SYMBOL; ?>' + discountValue;
            finalPrice.textContent = '<?php echo CURRENCY_SYMBOL; ?>' + finalPriceValue;
            
            pricePreview.classList.remove('hidden');
        } else {
            pricePreview.classList.add('hidden');
        }
    }
    
    // Event listeners
    serviceSelect.addEventListener('change', updatePricePreview);
    discountInput.addEventListener('input', updatePricePreview);
    
    // Initialize price preview
    updatePricePreview();
});
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>
