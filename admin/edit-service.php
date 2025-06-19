<?php
/**
 * Edit Service
 * 
 * This page allows administrators to edit an existing service.
 */

// Set page title
$pageTitle = "Edit Service";

// Include header
require_once '../includes/header.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'You do not have permission to access the admin dashboard.');
    redirect('../login.php');
}

// Include models
require_once '../models/Service.php';

// Create instances
$serviceModel = new Service($conn);

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

// Get all categories for dropdown
$categories = $serviceModel->getAllCategories();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    $price = floatval($_POST['price']);
    $duration = intval($_POST['duration']);
    $category = sanitizeInput($_POST['category']);
    $newCategory = isset($_POST['new_category']) ? sanitizeInput($_POST['new_category']) : '';
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Use new category if provided
    if (!empty($newCategory)) {
        $category = $newCategory;
    }
    
    // Validate inputs
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Service name is required.';
    }
    
    if (empty($description)) {
        $errors[] = 'Description is required.';
    }
    
    if ($price <= 0) {
        $errors[] = 'Price must be greater than zero.';
    }
    
    if ($duration <= 0) {
        $errors[] = 'Duration must be greater than zero.';
    }
    
    if (empty($category) && empty($newCategory)) {
        $errors[] = 'Category is required.';
    }
    
    // Handle image upload
    $imageName = $service['image']; // Keep existing image by default
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $errors[] = 'Invalid image format. Please upload a JPEG, PNG, or GIF file.';
        } elseif ($_FILES['image']['size'] > $maxSize) {
            $errors[] = 'Image size exceeds the maximum limit of 2MB.';
        } else {
            // Generate a unique filename
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = 'service_' . time() . '_' . uniqid() . '.' . $extension;
            $uploadPath = '../uploads/services/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            // Move uploaded file
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath . $imageName)) {
                $errors[] = 'Failed to upload image. Please try again.';
                $imageName = $service['image']; // Keep existing image if upload fails
            } else {
                // Delete old image if exists and new one was uploaded successfully
                if ($service['image'] && file_exists($uploadPath . $service['image'])) {
                    unlink($uploadPath . $service['image']);
                }
            }
        }
    }
    
    // Handle image deletion
    if (isset($_POST['delete_image']) && $_POST['delete_image'] === '1') {
        $uploadPath = '../uploads/services/';
        
        // Delete old image if exists
        if ($service['image'] && file_exists($uploadPath . $service['image'])) {
            unlink($uploadPath . $service['image']);
        }
        
        $imageName = null;
    }
    
    // If no errors, update the service
    if (empty($errors)) {
        $updated = $serviceModel->updateService(
            $serviceId,
            $name,
            $description,
            $price,
            $duration,
            $category,
            $imageName,
            $isActive
        );
        
        if ($updated) {
            setFlashMessage('success', 'Service updated successfully.');
            redirect('services.php');
        } else {
            $errors[] = 'Failed to update service. Please try again.';
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
                    <h1 class="text-2xl font-bold">Edit Service</h1>
                    <a href="services.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Services
                    </a>
                </div>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gray-800 text-white py-4 px-6">
                        <h2 class="text-xl font-bold">Service Information</h2>
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
                        
                        <!-- Edit Service Form -->
                        <form action="edit-service.php?id=<?php echo $serviceId; ?>" method="POST" enctype="multipart/form-data">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <!-- Service Name -->
                                <div class="md:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Service Name *</label>
                                    <input type="text" name="name" id="name" value="<?php echo isset($name) ? $name : $service['name']; ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required>
                                </div>
                                
                                <!-- Description -->
                                <div class="md:col-span-2">
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                                    <textarea name="description" id="description" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required><?php echo isset($description) ? $description : $service['description']; ?></textarea>
                                </div>
                                
                                <!-- Price -->
                                <div>
                                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price (<?php echo CURRENCY_SYMBOL; ?>) *</label>
                                    <input type="number" name="price" id="price" min="0" step="0.01" value="<?php echo isset($price) ? $price : $service['price']; ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required>
                                </div>
                                
                                <!-- Duration -->
                                <div>
                                    <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">Duration (minutes) *</label>
                                    <input type="number" name="duration" id="duration" min="5" step="5" value="<?php echo isset($duration) ? $duration : $service['duration']; ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required>
                                </div>
                                
                                <!-- Category -->
                                <div>
                                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                                    <select name="category" id="category" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" onchange="toggleNewCategory()">
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat; ?>" <?php echo (isset($category) ? $category === $cat : $service['category'] === $cat) ? 'selected' : ''; ?>>
                                                <?php echo $cat; ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <option value="new">+ Add New Category</option>
                                    </select>
                                </div>
                                
                                <!-- New Category (initially hidden) -->
                                <div id="new-category-field" class="hidden">
                                    <label for="new_category" class="block text-sm font-medium text-gray-700 mb-1">New Category Name *</label>
                                    <input type="text" name="new_category" id="new_category" value="<?php echo isset($newCategory) ? $newCategory : ''; ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50">
                                </div>
                                
                                <!-- Current Image -->
                                <?php if ($service['image']): ?>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Image</label>
                                    <div class="flex items-center space-x-4">
                                        <div class="w-24 h-24 bg-gray-100 rounded overflow-hidden">
                                            <img src="../uploads/services/<?php echo $service['image']; ?>" alt="<?php echo $service['name']; ?>" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <div class="flex items-center">
                                                <input type="checkbox" name="delete_image" id="delete_image" value="1" class="h-4 w-4 text-pink-600 focus:ring-pink-500 border-gray-300 rounded">
                                                <label for="delete_image" class="ml-2 block text-sm text-gray-700">
                                                    Delete current image
                                                </label>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">Check this to remove the current image without replacing it.</p>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Service Image -->
                                <div class="md:col-span-2">
                                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">
                                        <?php echo $service['image'] ? 'Replace Image' : 'Service Image'; ?>
                                    </label>
                                    <input type="file" name="image" id="image" accept="image/jpeg, image/png, image/gif" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100">
                                    <p class="text-xs text-gray-500 mt-1">Max size: 2MB. Formats: JPEG, PNG, GIF</p>
                                </div>
                                
                                <!-- Status -->
                                <div class="md:col-span-2">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="is_active" id="is_active" class="h-4 w-4 text-pink-600 focus:ring-pink-500 border-gray-300 rounded" <?php echo (isset($isActive) ? $isActive : $service['is_active']) ? 'checked' : ''; ?>>
                                        <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                            Active (service will be visible to customers)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Submit Buttons -->
                            <div class="flex justify-between">
                                <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-6 rounded transition duration-300">
                                    <i class="fas fa-save mr-2"></i> Update Service
                                </button>
                                
                                <a href="services.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded transition duration-300">
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
function toggleNewCategory() {
    const categorySelect = document.getElementById('category');
    const newCategoryField = document.getElementById('new-category-field');
    const newCategoryInput = document.getElementById('new_category');
    
    if (categorySelect.value === 'new') {
        newCategoryField.classList.remove('hidden');
        newCategoryInput.setAttribute('required', 'required');
    } else {
        newCategoryField.classList.add('hidden');
        newCategoryInput.removeAttribute('required');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleNewCategory();
    
    // Handle delete image checkbox
    const deleteImageCheckbox = document.getElementById('delete_image');
    const imageInput = document.getElementById('image');
    
    if (deleteImageCheckbox) {
        deleteImageCheckbox.addEventListener('change', function() {
            if (this.checked) {
                imageInput.disabled = true;
                imageInput.classList.add('opacity-50');
            } else {
                imageInput.disabled = false;
                imageInput.classList.remove('opacity-50');
            }
        });
    }
});
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>
