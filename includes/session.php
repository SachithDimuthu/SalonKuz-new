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
 * Set a flash message in the session.
 *
 * @param string $type The type of message (e.g., 'success', 'error', 'info').
 * @param string $message The message content.
 * @return void
 */
if (!function_exists('setFlashMessage')) {
    function setFlashMessage($type, $message) {
        $_SESSION['flash_messages'][] = [
            'type' => $type,
            'message' => $message
        ];
    }
}

if (!function_exists('getFlashMessage')) { // Changed from displayFlashMessages to getFlashMessage for consistency with plan
    /**
     * Get and display all flash messages.
     * Once displayed, messages are cleared from the session.
     *
     * @return void
     */
    function getFlashMessage() { // Changed from displayFlashMessages to getFlashMessage
        if (isset($_SESSION['flash_messages']) && !empty($_SESSION['flash_messages'])) {
            $output = '';
            foreach ($_SESSION['flash_messages'] as $flashMessage) {
                $alertType = '';
                switch ($flashMessage['type']) {
                    case 'success':
                        $alertType = 'bg-green-100 border-green-400 text-green-700';
                        break;
                    case 'error':
                        $alertType = 'bg-red-100 border-red-400 text-red-700';
                        break;
                    case 'info':
                        $alertType = 'bg-blue-100 border-blue-400 text-blue-700';
                        break;
                    case 'warning':
                        $alertType = 'bg-yellow-100 border-yellow-400 text-yellow-700';
                        break;
                    default:
                        $alertType = 'bg-gray-100 border-gray-400 text-gray-700';
                        break;
                }
                $output .= '<div class="' . $alertType . ' border px-4 py-3 rounded relative mb-4" role="alert">';
                $output .= '<span class="block sm:inline">' . htmlspecialchars($flashMessage['message'], ENT_QUOTES, 'UTF-8') . '</span>';
                // Optional: Add a close button
                // $output .= '<span class="absolute top-0 bottom-0 right-0 px-4 py-3">';
                // $output .= '<svg class="fill-current h-6 w-6 text-'.$flashMessage['type'].'-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>';
                // $output .= '</span>';
                $output .= '</div>';
            }
            unset($_SESSION['flash_messages']); // Clear messages after displaying
            echo $output; // Echo the messages
        }
    }
}

// Potentially other session related functions might be here already or can be added later.

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
