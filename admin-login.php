<?php
// admin-login.php - Admin login page for Salon Kuz

// Core files needed for pre-header logic.
// session.php must come first for session_start().
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php'; // Provides $conn
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/includes/utilities.php'; // For sanitize() and redirect()
require_once __DIR__ . '/includes/auth.php';     // For isLoggedIn()

$pageTitle = "Admin Login";
$errors = [];
$email = '';

// Sanitize the redirect parameter if it exists
$redirect_url = 'admin/deals.php'; // Default admin redirect URL
if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
    $redirect_url = sanitize($_GET['redirect']);
}

// If admin is already logged in, redirect them.
// This logic MUST run before any HTML output.
if (isLoggedIn() && getSession('user_role') === 'admin') {
    redirect($redirect_url);
    exit; // Terminate script execution after redirect
}

// Handle POST request for admin login attempt.
// This logic also MUST run before any HTML output if a redirect occurs.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errors[] = 'Email and password are required.';
    } else {
        $user = User::authenticate($conn, $email, $password, 'admin');
        if ($user) {
            setUserSession($user);
            $firstName = getSession('user_first_name');
            $welcomeName = $firstName ? htmlspecialchars($firstName) : 'Admin';
            setFlashMessage('login_success', 'Welcome back, ' . $welcomeName . '!');
            redirect($redirect_url);
            exit; // Terminate script execution after redirect
        } else {
            $errors[] = 'Invalid admin credentials.';
        }
    }
}

// Now that all potential redirects are handled, include the header.
require_once __DIR__ . '/includes/header.php';

// The rest of the HTML page follows...
?>
<div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
        <h2 class="text-2xl font-bold text-center mb-8 text-pink-600">Admin Login</h2>
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
            <button type="submit" class="w-full py-3 bg-pink-500 text-white rounded font-semibold text-lg hover:bg-pink-600 transition">Login as Admin</button>
        </form>
        <div class="mt-6 text-center">
            <a href="login.php" class="text-pink-500 hover:underline">Back to Role Selection</a>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
