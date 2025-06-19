<?php
/**
 * User Profile Page
 * 
 * This page allows users to view and update their profile information.
 */

// Set page title
$pageTitle = "My Profile";

// Include header
require_once 'includes/header.php';

// Include models
require_once 'models/User.php';
require_once 'models/Booking.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'You must be logged in to view this page.');
    redirect('login.php?redirect=' . urlencode('profile.php'));
}

// Create instances
$userModel = new User($conn);
$bookingModel = new Booking($conn);

// Get user data
$userId = $_SESSION['user_id'];
$user = $userModel->getUserById($userId);

// Get user's bookings
$bookings = $bookingModel->getBookingsByUserId($userId, 5);

// Initialize variables
$errors = [];
$success = false;

// Handle profile update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Get form data
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    
    // Validate form data
    if (empty($firstName)) {
        $errors[] = "First name is required";
    }
    
    if (empty($lastName)) {
        $errors[] = "Last name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } elseif ($email !== $user['email'] && $userModel->emailExists($email)) {
        $errors[] = "Email already exists";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }
    
    // Handle profile image upload
    $profileImage = $user['profile_image'];
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['size'] > 0) {
        $uploadResult = uploadFile($_FILES['profile_image'], 'assets/images/profiles/', ['jpg', 'jpeg', 'png']);
        
        if (isset($uploadResult['error'])) {
            $errors[] = $uploadResult['error'];
        } else {
            $profileImage = $uploadResult['filename'];
        }
    }
    
    // If no errors, update the profile
    if (empty($errors)) {
        $userData = [
            'id' => $userId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'profile_image' => $profileImage
        ];
        
        $updated = $userModel->updateUser($userData);
        
        if ($updated) {
            $success = true;
            // Update session data
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            // Refresh user data
            $user = $userModel->getUserById($userId);
            setFlashMessage('success', 'Profile updated successfully.');
        } else {
            $errors[] = "Failed to update profile. Please try again.";
        }
    }
}

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    // Get form data
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate form data
    if (empty($currentPassword)) {
        $errors[] = "Current password is required";
    } elseif (!$userModel->verifyPassword($userId, $currentPassword)) {
        $errors[] = "Current password is incorrect";
    }
    
    if (empty($newPassword)) {
        $errors[] = "New password is required";
    } elseif (strlen($newPassword) < 6) {
        $errors[] = "New password must be at least 6 characters";
    }
    
    if ($newPassword !== $confirmPassword) {
        $errors[] = "New passwords do not match";
    }
    
    // If no errors, change the password
    if (empty($errors)) {
        $changed = $userModel->changePassword($userId, $newPassword);
        
        if ($changed) {
            $success = true;
            setFlashMessage('success', 'Password changed successfully.');
        } else {
            $errors[] = "Failed to change password. Please try again.";
        }
    }
}
?>

<section class="py-12 bg-gray-100">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row">
            <!-- Sidebar -->
            <div class="md:w-1/4 mb-6 md:mb-0">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-pink-500 text-white py-4 px-6">
                        <h2 class="text-xl font-bold">My Account</h2>
                    </div>
                    <div class="py-4">
                        <ul class="divide-y divide-gray-200">
                            <li>
                                <a href="#profile" class="block px-6 py-3 hover:bg-pink-50 text-pink-500 font-semibold">
                                    <i class="fas fa-user mr-2"></i> Profile Information
                                </a>
                            </li>
                            <li>
                                <a href="#password" class="block px-6 py-3 hover:bg-pink-50 text-gray-700 hover:text-pink-500">
                                    <i class="fas fa-key mr-2"></i> Change Password
                                </a>
                            </li>
                            <li>
                                <a href="#bookings" class="block px-6 py-3 hover:bg-pink-50 text-gray-700 hover:text-pink-500">
                                    <i class="fas fa-calendar-alt mr-2"></i> My Bookings
                                </a>
                            </li>
                            <li>
                                <a href="logout.php" class="block px-6 py-3 hover:bg-pink-50 text-gray-700 hover:text-pink-500">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="md:w-3/4 md:pl-6">
                <!-- Profile Information -->
                <div id="profile" class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-pink-500 text-white py-4 px-6">
                        <h2 class="text-xl font-bold">Profile Information</h2>
                    </div>
                    <div class="p-6">
                        <?php if (!empty($errors) && isset($_POST['update_profile'])): ?>
                            <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded mb-4">
                                <ul class="list-disc pl-4">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success && isset($_POST['update_profile'])): ?>
                            <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded mb-4">
                                Profile updated successfully!
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex flex-col md:flex-row">
                            <div class="md:w-1/3 mb-6 md:mb-0 flex flex-col items-center">
                                <div class="w-32 h-32 rounded-full overflow-hidden mb-4">
                                    <?php if ($user['profile_image']): ?>
                                        <img src="assets/images/profiles/<?php echo $user['profile_image']; ?>" alt="Profile Image" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-gray-300 flex items-center justify-center">
                                            <i class="fas fa-user text-gray-400 text-4xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <p class="text-gray-700 font-semibold"><?php echo $user['username']; ?></p>
                                <p class="text-gray-500 text-sm"><?php echo ucfirst($user['role']); ?></p>
                            </div>
                            <div class="md:w-2/3">
                                <form action="profile.php" method="POST" enctype="multipart/form-data">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label for="first_name" class="block text-gray-700 text-sm font-bold mb-2">First Name</label>
                                            <input type="text" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                        </div>
                                        <div>
                                            <label for="last_name" class="block text-gray-700 text-sm font-bold mb-2">Last Name</label>
                                            <input type="text" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                                        <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" value="<?php echo $user['phone']; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                    </div>
                                    
                                    <div class="mb-6">
                                        <label for="profile_image" class="block text-gray-700 text-sm font-bold mb-2">Profile Image</label>
                                        <input type="file" id="profile_image" name="profile_image" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                        <p class="text-gray-600 text-xs mt-1">Allowed formats: JPG, JPEG, PNG. Max size: 2MB</p>
                                    </div>
                                    
                                    <div>
                                        <input type="hidden" name="update_profile" value="1">
                                        <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                                            Update Profile
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Change Password -->
                <div id="password" class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-pink-500 text-white py-4 px-6">
                        <h2 class="text-xl font-bold">Change Password</h2>
                    </div>
                    <div class="p-6">
                        <?php if (!empty($errors) && isset($_POST['change_password'])): ?>
                            <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded mb-4">
                                <ul class="list-disc pl-4">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success && isset($_POST['change_password'])): ?>
                            <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded mb-4">
                                Password changed successfully!
                            </div>
                        <?php endif; ?>
                        
                        <form action="profile.php#password" method="POST">
                            <div class="mb-4">
                                <label for="current_password" class="block text-gray-700 text-sm font-bold mb-2">Current Password</label>
                                <input type="password" id="current_password" name="current_password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="new_password" class="block text-gray-700 text-sm font-bold mb-2">New Password</label>
                                <input type="password" id="new_password" name="new_password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                <p class="text-gray-600 text-xs mt-1">Password must be at least 6 characters long</p>
                            </div>
                            
                            <div class="mb-6">
                                <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            </div>
                            
                            <div>
                                <input type="hidden" name="change_password" value="1">
                                <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                                    Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- My Bookings -->
                <div id="bookings" class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-pink-500 text-white py-4 px-6 flex justify-between items-center">
                        <h2 class="text-xl font-bold">My Recent Bookings</h2>
                        <a href="my-bookings.php" class="text-white text-sm hover:underline">View All</a>
                    </div>
                    <div class="p-6">
                        <?php if (empty($bookings)): ?>
                            <div class="text-center py-4">
                                <p class="text-gray-700">You don't have any bookings yet.</p>
                                <a href="services.php" class="inline-block mt-2 bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded transition duration-300">Book a Service</a>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead>
                                        <tr>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Service</th>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date & Time</th>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bookings as $booking): ?>
                                            <tr>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <div class="flex items-center">
                                                        <div>
                                                            <div class="text-sm font-semibold text-gray-900"><?php echo $booking['service_name']; ?></div>
                                                            <div class="text-xs text-gray-500">with <?php echo $booking['employee_name']; ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 px-4 border-b border-gray-200">
                                                    <div class="text-sm text-gray-900"><?php echo formatDate($booking['booking_date']); ?></div>
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
                                                    <a href="booking-details.php?id=<?php echo $booking['id']; ?>" class="text-pink-500 hover:text-pink-600 mr-3">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                                                        <a href="cancel-booking.php?id=<?php echo $booking['id']; ?>" class="text-red-500 hover:text-red-600" onclick="return confirm('Are you sure you want to cancel this booking?');">
                                                            <i class="fas fa-times"></i> Cancel
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
require_once 'includes/footer.php';
?>
