<?php
/**
 * Edit Employee Services
 * 
 * This page allows administrators to assign services to employees.
 */

// Set page title
$pageTitle = "Edit Employee Services";

// Include header
require_once '../includes/header.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'You do not have permission to access the admin dashboard.');
    redirect('../login.php');
}

// Include models
require_once '../models/User.php';
require_once '../models/Service.php';

// Create instances
$userModel = new User($conn);
$serviceModel = new Service($conn);

// Get employee ID from URL
$employeeId = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$employeeId) {
    setFlashMessage('error', 'Invalid employee ID.');
    redirect('users.php');
}

// Get employee details
$employee = $userModel->getUserById($employeeId);

if (!$employee) {
    setFlashMessage('error', 'Employee not found.');
    redirect('users.php');
} elseif ($employee['role'] !== 'employee') {
    setFlashMessage('error', 'User is not an employee.');
    redirect('user-details.php?id=' . $employeeId);
}

// Get all services
$allServices = $serviceModel->getAllServices();

// Get services assigned to employee
$assignedServices = $userModel->getEmployeeServices($employeeId);
$assignedServiceIds = array_column($assignedServices, 'id');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get selected services
    $selectedServiceIds = isset($_POST['services']) ? $_POST['services'] : [];
    
    // Update employee services
    $success = $userModel->updateEmployeeServices($employeeId, $selectedServiceIds);
    
    if ($success) {
        setFlashMessage('success', 'Employee services updated successfully.');
        redirect('user-details.php?id=' . $employeeId);
    } else {
        setFlashMessage('error', 'Failed to update employee services.');
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
                    <h1 class="text-2xl font-bold">Edit Employee Services</h1>
                    <a href="user-details.php?id=<?php echo $employeeId; ?>" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Details
                    </a>
                </div>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-gray-800 text-white py-4 px-6">
                        <h2 class="text-xl font-bold">Employee Information</h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="w-16 h-16 bg-gray-200 rounded-full overflow-hidden mr-4">
                                <?php if (!empty($employee['profile_image'])): ?>
                                    <img src="../uploads/profiles/<?php echo $employee['profile_image']; ?>" alt="<?php echo $employee['first_name']; ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-user text-gray-400 text-2xl"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold"><?php echo $employee['first_name'] . ' ' . $employee['last_name']; ?></h3>
                                <p class="text-gray-600"><?php echo $employee['position']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gray-800 text-white py-4 px-6">
                        <h2 class="text-xl font-bold">Assign Services</h2>
                    </div>
                    
                    <div class="p-6">
                        <p class="text-gray-600 mb-6">Select the services that this employee can perform. This will determine which services customers can book with this employee.</p>
                        
                        <form action="edit-employee-services.php?id=<?php echo $employeeId; ?>" method="POST">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                                <?php if (empty($allServices)): ?>
                                    <div class="md:col-span-3 text-center py-8">
                                        <div class="text-gray-400 mb-3">
                                            <i class="fas fa-spa text-5xl"></i>
                                        </div>
                                        <h3 class="text-xl font-semibold text-gray-700 mb-1">No Services Available</h3>
                                        <p class="text-gray-500">There are no services to assign to this employee.</p>
                                        <a href="services.php" class="mt-4 inline-block bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                                            <i class="fas fa-plus mr-2"></i> Add Services
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($allServices as $service): ?>
                                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                            <div class="flex items-start">
                                                <div class="flex items-center h-5 mt-1">
                                                    <input type="checkbox" name="services[]" id="service_<?php echo $service['id']; ?>" value="<?php echo $service['id']; ?>" class="h-4 w-4 text-pink-600 focus:ring-pink-500 border-gray-300 rounded" <?php echo in_array($service['id'], $assignedServiceIds) ? 'checked' : ''; ?>>
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="service_<?php echo $service['id']; ?>" class="font-semibold text-gray-700 cursor-pointer">
                                                        <?php echo $service['name']; ?>
                                                    </label>
                                                    <p class="text-gray-500 text-xs">
                                                        <?php echo $service['duration']; ?> min | <?php echo formatPrice($service['price']); ?>
                                                    </p>
                                                    <p class="text-gray-600 text-xs mt-1">
                                                        <?php echo truncateText($service['description'], 100); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex justify-between">
                                <div>
                                    <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-6 rounded transition duration-300">
                                        <i class="fas fa-save mr-2"></i> Save Changes
                                    </button>
                                    
                                    <button type="button" id="select-all" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded transition duration-300 ml-2">
                                        <i class="fas fa-check-square mr-2"></i> Select All
                                    </button>
                                    
                                    <button type="button" id="deselect-all" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded transition duration-300 ml-2">
                                        <i class="fas fa-square mr-2"></i> Deselect All
                                    </button>
                                </div>
                                
                                <a href="user-details.php?id=<?php echo $employeeId; ?>" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded transition duration-300">
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
    // Select All button
    document.getElementById('select-all').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('input[name="services[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
    });
    
    // Deselect All button
    document.getElementById('deselect-all').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('input[name="services[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
    });
});
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>
