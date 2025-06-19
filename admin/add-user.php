<?php
/**
 * Add User
 * 
 * This page allows administrators to add a new user.
 */

// Set page title
$pageTitle = "Add New User";

// Include header
require_once '../includes/header.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'You do not have permission to access the admin dashboard.');
    redirect('../login.php');
}

// Include models
require_once '../models/User.php';

// Create instances
$userModel = new User($conn);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $firstName = sanitizeInput($_POST['first_name']);
    $lastName = sanitizeInput($_POST['last_name']);
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $password = $_POST['password']; // Don't sanitize password as it will be hashed
    $confirmPassword = $_POST['confirm_password'];
    $role = sanitizeInput($_POST['role']);
    $position = isset($_POST['position']) ? sanitizeInput($_POST['position']) : null;
    
    // Validate inputs
    $errors = [];
    
    if (empty($firstName)) {
        $errors[] = 'First name is required.';
    }
    
    if (empty($lastName)) {
        $errors[] = 'Last name is required.';
    }
    
    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif ($userModel->usernameExists($username)) {
        $errors[] = 'Username already exists. Please choose a different one.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif ($userModel->emailExists($email)) {
        $errors[] = 'Email already exists. Please use a different one.';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }
    
    if (empty($role) || !in_array($role, ['admin', 'employee', 'customer'])) {
        $errors[] = 'Please select a valid role.';
    }
    
    // If role is employee, position is required
    if ($role === 'employee' && empty($position)) {
        $errors[] = 'Position is required for employees.';
    }
    
    // If no errors, create the user
    if (empty($errors)) {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Create user
        $userId = $userModel->createUser(
            $username,
            $hashedPassword,
            $email,
            $firstName,
            $lastName,
            $phone,
            $role,
            $position
        );
        
        if ($userId) {
            setFlashMessage('success', 'User created successfully.');
            redirect('users.php');
        } else {
            $errors[] = 'Failed to create user. Please try again.';
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
                    <h1 class="text-2xl font-bold">Add New User</h1>
                    <a href="users.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                        <i class="fas fa-arrow-left mr-2"></i> Back to Users
                    </a>
                </div>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gray-800 text-white py-4 px-6">
                        <h2 class="text-xl font-bold">User Information</h2>
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
                        
                        <!-- Add User Form -->
                        <form action="add-user.php" method="POST">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <!-- Personal Information -->
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                                    <input type="text" name="first_name" id="first_name" value="<?php echo isset($firstName) ? $firstName : ''; ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required>
                                </div>
                                
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                                    <input type="text" name="last_name" id="last_name" value="<?php echo isset($lastName) ? $lastName : ''; ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required>
                                </div>
                                
                                <div>
                                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username *</label>
                                    <input type="text" name="username" id="username" value="<?php echo isset($username) ? $username : ''; ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required>
                                </div>
                                
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                    <input type="email" name="email" id="email" value="<?php echo isset($email) ? $email : ''; ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required>
                                </div>
                                
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                    <input type="tel" name="phone" id="phone" value="<?php echo isset($phone) ? $phone : ''; ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50">
                                </div>
                                
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                                    <select name="role" id="role" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required onchange="togglePositionField()">
                                        <option value="">Select Role</option>
                                        <option value="admin" <?php echo (isset($role) && $role === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                        <option value="employee" <?php echo (isset($role) && $role === 'employee') ? 'selected' : ''; ?>>Employee</option>
                                        <option value="customer" <?php echo (isset($role) && $role === 'customer') ? 'selected' : ''; ?>>Customer</option>
                                    </select>
                                </div>
                                
                                <div id="position-field" class="<?php echo (isset($role) && $role === 'employee') ? '' : 'hidden'; ?>">
                                    <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Position *</label>
                                    <input type="text" name="position" id="position" value="<?php echo isset($position) ? $position : ''; ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" <?php echo (isset($role) && $role === 'employee') ? 'required' : ''; ?>>
                                    <p class="text-xs text-gray-500 mt-1">e.g., Hair Stylist, Nail Technician, Massage Therapist</p>
                                </div>
                                
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                                    <input type="password" name="password" id="password" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required minlength="8">
                                    <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                                </div>
                                
                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
                                    <input type="password" name="confirm_password" id="confirm_password" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required minlength="8">
                                </div>
                            </div>
                            
                            <!-- Submit Buttons -->
                            <div class="flex justify-between">
                                <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-6 rounded transition duration-300">
                                    <i class="fas fa-user-plus mr-2"></i> Create User
                                </button>
                                
                                <a href="users.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded transition duration-300">
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
function togglePositionField() {
    const roleSelect = document.getElementById('role');
    const positionField = document.getElementById('position-field');
    const positionInput = document.getElementById('position');
    
    if (roleSelect.value === 'employee') {
        positionField.classList.remove('hidden');
        positionInput.setAttribute('required', 'required');
    } else {
        positionField.classList.add('hidden');
        positionInput.removeAttribute('required');
    }
}
</script>

<?php
// Include footer
require_once '../includes/footer.php';
?>
