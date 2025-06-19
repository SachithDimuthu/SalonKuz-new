<?php
/**
 * Registration Page
 * 
 * This page allows new users to register for an account.
 */

// Set page title
$pageTitle = "Register";

// Include header
require_once 'includes/header.php';

// Include models
require_once 'models/User.php';
require_once 'includes/auth.php'; // For sanitize, redirect, setFlashMessage

// Create user instance
$userModel = new User($conn);

// Initialize variables
$name = '';
$email = '';
$phone = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    
    // Validate form data
    if (empty($name)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } elseif ($userModel->emailExists($email)) {
        $errors[] = "Email already exists";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    // First name and Last name validation removed, combined into 'name'
    
    // Phone number can be optional, or keep validation if strictly required
    // For now, let's assume it's optional as per User model's register method default
    // If (empty($phone)) { $errors[] = "Phone number is required"; }
    
    // If no errors, register the user
    if (empty($errors)) {
        // Call the updated register method from User model
        $registered = $userModel->register($name, $email, $password, $phone, 'customer');
        
        // The register method now returns true on success, or an error message string on failure
        if ($registered === true) {
            // Set success message and redirect to user login page
            setFlashMessage('success', 'Registration successful! You can now log in.');
            redirect('user-login.php'); // Corrected redirect destination
        } else {
            // If $registered is not true, it's an error message string from the model
            $errors[] = $registered; // Display the specific error from the model
        }
    } // Closes: if (empty($errors))
} // Closes: if ($_SERVER['REQUEST_METHOD'] === 'POST')
?>

<section class="py-12 bg-gray-100">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-pink-500 text-white py-4 px-6">
                <h2 class="text-2xl font-bold">Create an Account</h2>
                <p class="text-sm">Join Salon Kuz to book appointments and access exclusive deals</p>
            </div>
            
            <div class="py-6 px-6">
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc pl-4">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form action="register.php" method="POST">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="name" id="name" value="<?= htmlspecialchars($name ?? '') ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-pink-500 focus:border-pink-500 sm:text-sm">
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email *</label>
                        <input type="email" id="email" name="email" value="<?php echo $email; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="phone" class="block text-gray-700 text-sm font-bold mb-2">Phone Number *</label>
                        <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($phone ?? '') ?>" pattern="[0-9]{7,15}" title="Please enter a valid phone number (7-15 digits)" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-pink-500 focus:border-pink-500 sm:text-sm"> 
                        <small class="text-xs text-gray-500">Optional</small>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password *</label>
                        <input type="password" id="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <p class="text-gray-600 text-xs mt-1">Password must be at least 6 characters long</p>
                    </div>
                    
                    <div class="mb-6">
                        <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    
                    <div class="flex items-center justify-between mb-4">
                        <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                            Register
                        </button>
                        <a href="login.php" class="inline-block align-baseline font-bold text-sm text-pink-500 hover:text-pink-600">
                            Already have an account?
                        </a>
                    </div>
                    
                    <p class="text-gray-600 text-xs">
                        By registering, you agree to our <a href="terms.php" class="text-pink-500 hover:text-pink-600">Terms of Service</a> and <a href="privacy.php" class="text-pink-500 hover:text-pink-600">Privacy Policy</a>.
                    </p>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
require_once 'includes/footer.php';
?>
