<?php
/**
 * Utilities File
 * 
 * This file contains utility functions used throughout the Salon Kuz website.
 */

/**
 * Sanitize user input to prevent XSS attacks
 * 
 * @param string $data The input data to sanitize
 * @return string The sanitized data
 */
if (!function_exists('sanitize')) {
    function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
    }
}

/**
 * Redirect to a specific page
 * 
 * @param string $location The location to redirect to
 * @return void
 */
if (!function_exists('redirect')) {
    function redirect($location) {
    header("Location: $location");
    exit;
    }
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
    return isset($_SESSION['user_id']);
    }
}

/**
 * Check if user has a specific role
 * 
 * @param string $role The role to check for
 * @return bool True if user has the specified role, false otherwise
 */
if (!function_exists('hasRole')) {
    function hasRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    return $_SESSION['user_role'] === $role;
    }
}

/**
 * Check if user is an admin
 * 
 * @return bool True if user is an admin, false otherwise
 */
if (!function_exists('isAdmin')) {
    function isAdmin() {
    return hasRole('admin');
    }
}

/**
 * Check if user is an employee
 * 
 * @return bool True if user is an employee, false otherwise
 */
if (!function_exists('isEmployee')) {
    function isEmployee() {
    return hasRole('employee');
    }
}

/**
 * Check if user is a customer
 * 
 * @return bool True if user is a customer, false otherwise
 */
if (!function_exists('isCustomer')) {
    function isCustomer() {
    return hasRole('customer');
    }
}

/**
 * Generate a random string
 * 
 * @param int $length The length of the random string
 * @return string The random string
 */
if (!function_exists('generateRandomString')) {
    function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
    }
}

/**
 * Upload a file to the server
 * 
 * @param array $file The file to upload ($_FILES['file'])
 * @param string $destination The destination directory
 * @param array $allowedTypes The allowed file types
 * @return string|bool The filename if successful, false otherwise
 */
if (!function_exists('uploadFile')) {
    function uploadFile($file, $destination, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif']) {
    // Check if file was uploaded without errors
    if ($file['error'] === 0) {
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Check if file type is allowed
        if (in_array($fileExt, $allowedTypes)) {
            // Generate a unique filename
            $newFileName = generateRandomString() . '.' . $fileExt;
            $filePath = $destination . $newFileName;
            
            // Move the file to the destination directory
            if (move_uploaded_file($fileTmpName, $filePath)) {
                return $newFileName;
            }
        }
    }
    
    return false;
    }
}

/**
 * Format a date to a readable format
 * 
 * @param string $date The date to format
 * @param string $format The format to use
 * @return string The formatted date
 */
if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
    }
}

/**
 * Format a price to a readable format
 * 
 * @param float $price The price to format
 * @return string The formatted price
 */
if (!function_exists('formatPrice')) {
    function formatPrice($price) {
    return '$' . number_format($price, 2);
    }
}

/**
 * Format a time string to a readable format (e.g., HH:MM AM/PM).
 *
 * @param string $time The time string to format (HH:MM:SS or HH:MM).
 * @param string $format The desired output format string for date() function. Defaults to 'h:i A'.
 * @return string The formatted time string.
 */
if (!function_exists('formatTime')) {
    function formatTime($time, $format = 'h:i A') {
        if (empty($time)) {
            return ''; // Or return a default like 'N/A'
        }
        $timestamp = strtotime($time);
        if ($timestamp === false) {
            return $time; // Return original if strtotime fails
        }
        return date($format, $timestamp);
    }
}

/**
 * Calculate the discounted price
 * 
 * @param float $price The original price
 * @param float $discountPercentage The discount percentage
 * @return float The discounted price
 */
if (!function_exists('calculateDiscountedPrice')) {
    function calculateDiscountedPrice($price, $discountPercentage) {
    return $price - ($price * ($discountPercentage / 100));
    }
}

/**
 * Display a success message
 * 
 * @param string $message The message to display
 * @return string The HTML for the success message
 */
if (!function_exists('successMessage')) {
    function successMessage($message) {
    return '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</span>
            </div>';
    } // Closes successMessage function
} // Closes if (!function_exists('successMessage'))

/**
 * Display an error message
 * 
 * @param string $message The message to display
 * @return string The HTML for the error message
 */
if (!function_exists('errorMessage')) {
    function errorMessage($message) {
    return '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</span>
            </div>';
    } // Closes errorMessage function
} // Closes if (!function_exists('errorMessage'))

/**
 * Get the current page URL
 * 
 * @return string The current page URL
 */
if (!function_exists('getCurrentPageUrl')) {
    function getCurrentPageUrl() {
    $pageURL = 'http';
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
    }
}

/**
 * Generate pagination links
 * 
 * @param int $currentPage The current page
 * @param int $totalPages The total number of pages
 * @param string $url The base URL for pagination links
 * @return string The HTML for the pagination links
 */
if (!function_exists('generatePagination')) {
    function generatePagination($currentPage, $totalPages, $url) {
        $pagination = '<div class="flex justify-center mt-4">';
        $pagination .= '<nav class="inline-flex rounded-md shadow">';
        
        // Previous page link
        if ($currentPage > 1) {
            $pagination .= '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '?page=' . ($currentPage - 1) . '" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">Previous</a>';
        } else {
            $pagination .= '<span class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md cursor-not-allowed">Previous</span>';
        }
        
        // Page links
        for ($i = 1; $i <= $totalPages; $i++) {
            if ($i == $currentPage) {
                $pagination .= '<span class="px-3 py-2 text-sm font-medium text-white bg-indigo-600 border border-indigo-600">' . $i . '</span>';
            } else {
                $pagination .= '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '?page=' . $i . '" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">' . $i . '</a>';
            }
        }
        
        // Next page link
        if ($currentPage < $totalPages) {
            $pagination .= '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '?page=' . ($currentPage + 1) . '" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">Next</a>';
        } else {
            $pagination .= '<span class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md cursor-not-allowed">Next</span>';
        }
        
        $pagination .= '</nav>';
        $pagination .= '</div>';
        
        return $pagination;
    } // Closes generatePagination function
} // Closes if (!function_exists('generatePagination'))

if (!function_exists('truncateText')) {
    /**
     * Truncate text to a certain length and add ellipsis if needed.
     *
     * @param string $text The text to truncate.
     * @param int $maxLength The maximum length of the text.
     * @param string $suffix Suffix to append if text is truncated.
     * @return string The truncated text.
     */
    function truncateText($text, $maxLength, $suffix = '...') {
        if (mb_strlen($text) > $maxLength) {
            return mb_substr($text, 0, $maxLength - mb_strlen($suffix)) . $suffix;
        }
        return $text;
    }
}
?>
