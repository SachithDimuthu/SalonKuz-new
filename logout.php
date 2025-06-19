<?php
/**
 * Logout Page
 * 
 * This script handles user logout by destroying the session and redirecting to the homepage.
 */

// Include session management
require_once 'includes/session.php';

// Delete remember me cookie if exists
if (isset($_COOKIE['remember_me'])) {
    // Include database connection and User model to remove token from database
    require_once 'config/database.php';
    require_once 'models/User.php';
    
    $userModel = new User($conn);
    
    // Get current user ID
    $userId = $_SESSION['user_id'] ?? null;
    
    // Remove token from database if user ID is available
    if ($userId) {
        $userModel->removeRememberMeToken($userId, $_COOKIE['remember_me']);
    }
    
    // Delete cookie
    setcookie('remember_me', '', time() - 3600, '/');
}

// Log the user out
clearUserSession();

// Set success message
setFlashMessage('success', 'You have been logged out successfully.');

// Redirect to homepage
header('Location: index.php');
exit;
?>
