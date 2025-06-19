<?php
/**
 * User Login Page
 * 
 * Allows customers to log in. Provides a link to sign up.
 */

// Core files needed for pre-header logic.
// session.php must come first for session_start().
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php'; // Provides $conn
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/includes/utilities.php'; // For sanitize() and redirect()
require_once __DIR__ . '/includes/auth.php'; // For isLoggedIn()

$pageTitle = "User Login";
$errors = [];
$email = '';

// Sanitize the redirect parameter if it exists
$redirect_url = 'index.php'; // Default redirect URL
if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
    // Basic validation: ensure it's a relative path or a known safe domain if allowing absolute
    // For now, let's assume it's a relative path within the site
    $redirect_url = sanitize($_GET['redirect']); 
}

// If user is already logged in as a customer, redirect them.
// This logic MUST run before any HTML output.
if (isLoggedIn() && getSession('user_role') === 'customer') {
    redirect($redirect_url);
    exit; // Terminate script execution after redirect
}

// Handle POST request for login attempt.
// This logic also MUST run before any HTML output if a redirect occurs.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errors[] = 'Email and password are required.';
    } else {
        $user = User::authenticate($conn, $email, $password, 'customer');
        if ($user) {
            setUserSession($user);
            // Sanitize user's first name before displaying in flash message
            $firstName = getSession('user_first_name');
            $welcomeName = $firstName ? htmlspecialchars($firstName) : 'User';
            setFlashMessage('login_success', 'Welcome back, ' . $welcomeName . '!');
            redirect($redirect_url);
            exit; // Terminate script execution after redirect
        } else {
            $errors[] = 'Invalid user credentials.';
        }
    }
}

// Now that all potential redirects are handled, include the header.
require_once __DIR__ . '/includes/header.php';

// The rest of the HTML page follows...
?>
<div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
        <h2 class="text-2xl font-bold text-center mb-8 text-pink-600">User Login</h2>
        <?php if ($errors): ?>
            <div class="mb-4 text-red-600">
                <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
            </div>
        <?php endif; ?>
        <form method="POST" autocomplete="off">
            <div class="mb-4">
                <label class="block mb-2 font-semibold">Email</label>
                <input type="email" name="email" class="w-full border rounded px-3 py-2" required value="<?= htmlspecialchars($email) ?>">
            </div>
            <div class="mb-6">
                <label class="block mb-2 font-semibold">Password</label>
                <input type="password" name="password" class="w-full border rounded px-3 py-2" required>
            </div>
            <button type="submit" class="w-full py-3 bg-pink-500 text-white rounded font-semibold text-lg hover:bg-pink-600 transition">Login as User</button>
        </form>
        <div class="mt-6 text-center">
            <span>Don't have an account?</span>
            <a href="register.php" class="text-pink-500 hover:underline ml-2">Sign Up</a>
        </div>
        <div class="mt-4 text-center">
            <a href="login.php" class="text-pink-500 hover:underline">Back to Role Selection</a>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>

    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no errors, attempt to log in
    if (empty($errors)) {
        $user = $userModel->loginUser($username, $password);
        
        if ($user) {
            // Set user session
            setUserSession($user);
            
            // Set remember me cookie if requested
            if ($rememberMe) {
                $token = generateRandomToken();
                $expiry = time() + (30 * 24 * 60 * 60); // 30 days
                
                // Save token in database
                $userModel->saveRememberMeToken($user['id'], $token, $expiry);
                
                // Set cookie
                setcookie('remember_me', $token, $expiry, '/');
            }
            
            // Set success message and redirect
            setFlashMessage('success', 'Login successful! Welcome back, ' . $user['first_name'] . '.');
            
            // Redirect based on role or to the requested page
            if ($user['role'] === 'admin') {
                redirect('admin/dashboard.php');
            } elseif ($user['role'] === 'employee') {
                redirect('employee/dashboard.php');
            } else {
                redirect($redirect);
            }
        } else {
            $errors[] = "Invalid username/email or password";
        }
    }
}
?>

<section class="py-12 bg-gray-100">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-pink-500 text-white py-4 px-6">
                <h2 class="text-2xl font-bold">Login to Your Account</h2>
                <p class="text-sm">Access your Salon Kuz account to book appointments and more</p>
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
                
                <?php if (isset($_GET['registered']) && $_GET['registered'] === 'true'): ?>
                    <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-3 rounded mb-4">
                        Registration successful! You can now log in with your credentials.
                    </div>
                <?php endif; ?>
                
                <form action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" method="POST">
                    <div class="mb-4">
                        <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username or Email</label>
                        <input type="text" id="username" name="username" value="<?php echo $username; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                        <input type="password" id="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    </div>
                    
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember_me" class="form-checkbox h-4 w-4 text-pink-500">
                            <span class="ml-2 text-gray-700 text-sm">Remember me</span>
                        </label>
                    </div>
                    
                    <div class="flex items-center justify-between mb-4">
                        <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                            Login
                        </button>
                        <a href="forgot-password.php" class="inline-block align-baseline font-bold text-sm text-pink-500 hover:text-pink-600">
                            Forgot Password?
                        </a>
                    </div>
                    
                    <p class="text-gray-600 text-sm text-center">
                        Don't have an account? <a href="register.php" class="text-pink-500 hover:text-pink-600 font-bold">Register now</a>
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
