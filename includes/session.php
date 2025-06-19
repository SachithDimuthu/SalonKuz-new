<?php
/**
 * Session Management File
 * 
 * This file handles session management for the Salon Kuz website.
 */

// Start session if not already started
// Set session lifetime to 24 hours (86400 seconds)
ini_set('session.gc_maxlifetime', 86400);
ini_set('session.cookie_lifetime', 86400);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Set a session variable
 * 
 * @param string $key The session key
 * @param mixed $value The session value
 * @return void
 */
if (!function_exists('setSession')) {
    function setSession($key, $value) {
    $_SESSION[$key] = $value;
    }
}

/**
 * Get a session variable
 * 
 * @param string $key The session key
 * @param mixed $default The default value to return if the key doesn't exist
 * @return mixed The session value or the default value
 */
if (!function_exists('getSession')) {
    function getSession($key, $default = null) {
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }
}

/**
 * Unset a session variable
 * 
 * @param string $key The session key
 * @return void
 */
if (!function_exists('unsetSession')) {
    function unsetSession($key) {
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
    }
}

/**
 * Clear all session variables
 * 
 * @return void
 */
if (!function_exists('clearSession')) {
    function clearSession() {
    session_unset();
    }
}

/**
 * Destroy the session
 * 
 * @return void
 */
if (!function_exists('destroySession')) {
    function destroySession() {
    session_destroy();
    }
}

/**
 * Set a flash message
 * 
 * @param string $key The flash message key
 * @param string $message The flash message
 * @param string $type The flash message type (success, error, info, warning)
 * @return void
 */
if (!function_exists('setFlashMessage')) {
    function setFlashMessage($key, $message, $type = 'info') {
    $_SESSION['flash_messages'][$key] = [
        'message' => $message,
        'type' => $type
    ];
    }
}

/**
 * Get a flash message
 * 
 * @param string $key The flash message key
 * @return array|null The flash message or null if it doesn't exist
 */
if (!function_exists('getFlashMessage')) {
    function getFlashMessage($key) {
    if (isset($_SESSION['flash_messages'][$key])) {
        $flashMessage = $_SESSION['flash_messages'][$key];
        unset($_SESSION['flash_messages'][$key]);
        return $flashMessage;
    }
    return null;
    }
}

/**
 * Check if a flash message exists
 * 
 * @param string $key The flash message key
 * @return bool True if the flash message exists, false otherwise
 */
if (!function_exists('hasFlashMessage')) {
    function hasFlashMessage($key) {
    return isset($_SESSION['flash_messages'][$key]);
    }
}

/**
 * Display all flash messages
 * 
 * @return string The HTML for all flash messages
 */
if (!function_exists('displayFlashMessages')) {
    function displayFlashMessages() {
    $output = '';
    if (isset($_SESSION['flash_messages']) && !empty($_SESSION['flash_messages'])) {
        foreach ($_SESSION['flash_messages'] as $key => $flashMessage) {
            $type = $flashMessage['type'];
            $message = $flashMessage['message'];
            
            $bgColor = 'bg-blue-100';
            $textColor = 'text-blue-700';
            $borderColor = 'border-blue-400';
            
            if ($type === 'success') {
                $bgColor = 'bg-green-100';
                $textColor = 'text-green-700';
                $borderColor = 'border-green-400';
            } elseif ($type === 'error') {
                $bgColor = 'bg-red-100';
                $textColor = 'text-red-700';
                $borderColor = 'border-red-400';
            } elseif ($type === 'warning') {
                $bgColor = 'bg-yellow-100';
                $textColor = 'text-yellow-700';
                $borderColor = 'border-yellow-400';
            }
            
            $output .= '<div class="' . $bgColor . ' border ' . $borderColor . ' ' . $textColor . ' px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">' . $message . '</span>
                        </div>';
            
            unset($_SESSION['flash_messages'][$key]);
        }
    }
    return $output;
    }
}

/**
 * Set user session after successful login
 * 
 * @param array $user The user data
 * @return void
 */
if (!function_exists('setUserSession')) {
    function setUserSession($user) {
        // Ensure $user is an array, if it's an object, convert it or access properties accordingly
        if (is_object($user)) {
            $user = (array) $user; // Basic object to array conversion
        }

        if (is_array($user)) {
            setSession('user_id', $user['id'] ?? ($user['user_id'] ?? null));
            setSession('user_username', $user['username'] ?? null);
            setSession('user_email', $user['email'] ?? null);
            
            // Handle different ways name might be provided
            $fullName = $user['name'] ?? null;
            $firstName = $user['first_name'] ?? null;
            $lastName = $user['last_name'] ?? null;

            if ($firstName) {
                setSession('user_first_name', $firstName);
                setSession('user_last_name', $lastName);
            } elseif ($fullName && strpos($fullName, ' ') !== false) {
                // If 'name' contains a space and 'last_name' isn't set, try to split it
                list($fName, $lName) = explode(' ', $fullName, 2);
                setSession('user_first_name', $fName);
                setSession('user_last_name', $lName ?? null);
            } elseif ($fullName) {
                 setSession('user_first_name', $fullName); // Assume 'name' is just the first name if no space
                 setSession('user_last_name', null); // No last name provided in this case
            } else {
                setSession('user_first_name', null); // Default to null if no name info
                setSession('user_last_name', null);
            }
            
            setSession('user_role', $user['role'] ?? null);
            setSession('user_profile_image', $user['profile_image'] ?? null);
            setSession('logged_in', true);
        } else {
            // If $user is not an array or suitable object, ensure logged_in is false
            setSession('logged_in', false);
            // Optionally log an error or set a flash message here for debugging
        }
    }
}


/**
 * Clear user session after logout
 * 
 * @return void
 */
if (!function_exists('clearUserSession')) {
    function clearUserSession() {
    unsetSession('user_id');
    unsetSession('user_username');
    unsetSession('user_email');
    unsetSession('user_first_name');
    unsetSession('user_last_name');
    unsetSession('user_role');
    unsetSession('user_profile_image');
    unsetSession('logged_in');
    }
}
?>
