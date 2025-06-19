<?php
/**
 * Edit User
 * 
 * This page allows administrators to edit user information.
 */

// Set page title
$pageTitle = "Edit User";

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $firstName = sanitizeInput($_POST['first_name']);
    $lastName = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $role = sanitizeInput($_POST['role']);
    $position = isset($_POST['position']) ? sanitizeInput($_POST['position']) : null;
    $password = !empty($_POST['password']) ? $_POST['password'] : null; // Don't sanitize password as it will be hashed
    $confirmPassword = !empty($_POST['confirm_password']) ? $_POST['confirm_password'] : null;
    
    // Validate inputs
    $errors = [];
    
    if (empty($firstName)) {
        $errors[] = 'First name is required.';
    }
    
    if (empty($lastName)) {
        $errors[] = 'Last name is required.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif ($email !== $user['email'] && $userModel->emailExists($email)) {
        $errors[] = 'Email already exists. Please use a different one.';
    }
    
    if (!empty($password)) {
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }
    }
    
    if (empty($role) || !in_array($role, ['admin', 'employee', 'customer'])) {
        $errors[] = 'Please select a valid role.';
    }
    
    // If role is employee, position is required
    if ($role === 'employee' && empty($position)) {
        $errors[] = 'Position is required for employees.';
    }
    
    // Handle profile image upload
    $profileImage = $user['profile_image']; // Keep existing image by default
    
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($_FILES['profile_image']['type'], $allowedTypes)) {
            $errors[] = 'Invalid image format. Please upload a JPEG, PNG, or GIF file.';
        } elseif ($_FILES['profile_image']['size'] > $maxSize) {
            $errors[] = 'Image size exceeds the maximum limit of 2MB.';
        } else {
            // Generate a unique filename
            $extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $newFilename = 'profile_' . $userId . '_' . time() . '.' . $extension;
            $uploadPath = '../uploads/profiles/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath . $newFilename)) {
                // Delete old profile image if exists
                if (!empty($user['profile_image']) && file_exists($uploadPath . $user['profile_image'])) {
                    unlink($uploadPath . $user['profile_image']);
                }
                
                $profileImage = $newFilename;
            } else {
                $errors[] = 'Failed to upload profile image. Please try again.';
            }
        }
    }
    
    // If no errors, update the user
    if (empty($errors)) {
        // Hash password if provided
        $hashedPassword = null;
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        }
        
        // Update user
        $updated = $userModel->updateUser(
            $userId,
            $firstName,
            $lastName,
            $email,
            $phone,
            $role,
            $position,
            $profileImage,
            $hashedPassword
        );
        
        if ($updated) {
            setFlashMessage('success', 'User updated successfully.');
            redirect('user-details.php?id=' . $userId);
        } else {
            $errors[] = 'Failed to update user. Please try again.';
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
                    <h1 class="text-2xl font-bold">Edit User</h1>
                    <div class="flex space-x-2">
                        <a href="user-details.php?id=<?php echo $userId; ?>" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Details
                        </a>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gray-800 text-white py-4 px-6">
                        <h2 class="text-xl font-bold">Edit User: <?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h2>
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
                        
                        <!-- Edit User Form -->
                        <form action="edit-user.php?id=<?php echo $userId; ?>" method="POST" enctype="multipart/form-data">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <!-- Profile Image -->
                                <div class="md:col-span-2 flex flex-col items-center">
                                    <div class="w-32 h-32 bg-gray-200 rounded-full overflow-hidden mb-4">
                                        <?php if (!empty($user['profile_image'])): ?>
                                            <img src="../uploads/profiles/<?php echo $user['profile_image']; ?>" alt="<?php echo $user['first_name']; ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center">
                                                <i class="fas fa-user text-gray-400 text-5xl"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-4">
                                        <label for="profile_image" class="block text-sm font-medium text-gray-700 mb-1">Profile Image</label>
                                        <input type="file" name="profile_image" id="profile_image" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100">
                                        <p class="text-xs text-gray-500 mt-1">Max size: 2MB. Formats: JPEG, PNG, GIF</p>
                                    </div>
                                </div>
                                
                                <!-- Personal Information -->
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                                    <input type="text" name="first_name" id="first_name" value="<?php echo $user['first_name']; ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required>
                                </div>
                                
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                                    <input type="text" name="last_name" id="last_name" value="<?php echo $user['last_name']; ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required>
                                </div>
                                
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                    <input type="email" name="email" id="email" value="<?php echo $user['email']; ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required>
                                </div>
                                
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                    <input type="tel" name="phone" id="phone" value="<?php echo $user['phone']; ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50">
                                </div>
                                
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role *</label>
                                    <select name="role" id="role" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" required onchange="togglePositionField()">
                                        <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                        <option value="employee" <?php echo ($user['role'] === 'employee') ? 'selected' : ''; ?>>Employee</option>
                                        <option value="customer" <?php echo ($user['role'] === 'customer') ? 'selected' : ''; ?>>Customer</option>
                                    </select>
                                </div>
                                
                                <div id="position-field" class="<?php echo ($user['role'] === 'employee') ? '' : 'hidden'; ?>">
                                    <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Position *</label>
                                    <input type="text" name="position" id="position" value="<?php echo isset($user['position']) ? $user['position'] : ''; ?>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" <?php echo ($user['role'] === 'employee') ? 'required' : ''; ?>>
                                    <p class="text-xs text-gray-500 mt-1">e.g., Hair Stylist, Nail Technician, Massage Therapist</p>
                                </div>
                                
                                <div class="md:col-span-2 border-t border-gray-200 pt-4 mt-2">
                                    <h3 class="text-lg font-semibold mb-3">Change Password (Optional)</h3>
                                    <p class="text-sm text-gray-500 mb-4">Leave blank to keep the current password.</p>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                            <input type="password" name="password" id="password" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" minlength="8">
                                            <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                                        </div>
                                        
                                        <div>
                                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                            <input type="password" name="confirm_password" id="confirm_password" class="w-full rounded-md border-gray-300 shadow-sm focus:border-pink-500 focus:ring focus:ring-pink-500 focus:ring-opacity-50" minlength="8">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Submit Buttons -->
                            <div class="flex justify-between">
                                <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-6 rounded transition duration-300">
                                    <i class="fas fa-save mr-2"></i> Save Changes
                                </button>
                                
                                <a href="user-details.php?id=<?php echo $userId; ?>" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded transition duration-300">
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
