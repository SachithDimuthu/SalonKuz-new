<?php
/**
 * Update Booking Status
 * 
 * This script handles updating the status of a booking by an admin.
 */

// Include header
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/utilities.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('error', 'You do not have permission to access the admin dashboard.');
    redirect('../login.php');
}

// Include models
require_once '../models/Booking.php';
require_once '../models/User.php';

// Create instances
$bookingModel = new Booking($conn);
$userModel = new User($conn);

// Get booking ID and new status from URL
$bookingId = isset($_GET['id']) ? intval($_GET['id']) : null;
$newStatus = isset($_GET['status']) ? $_GET['status'] : null;

// Validate inputs
if (!$bookingId || !$newStatus || !in_array($newStatus, ['pending', 'confirmed', 'cancelled', 'completed'])) {
    setFlashMessage('error', 'Invalid booking ID or status.');
    redirect('bookings.php');
}

// Get booking details
$booking = $bookingModel->getBookingById($bookingId);

if (!$booking) {
    setFlashMessage('error', 'Booking not found.');
    redirect('bookings.php');
}

// Update booking status
$updated = $bookingModel->updateBookingStatus($bookingId, $newStatus);

if ($updated) {
    // Get customer details for notification
    $customer = $userModel->getUserById($booking['user_id']);
    
    // Set success message
    setFlashMessage('success', 'Booking status updated successfully to ' . ucfirst($newStatus) . '.');
    
    // TODO: Send email notification to customer about status change
    // This would be implemented in a future version with email functionality
    
} else {
    setFlashMessage('error', 'Failed to update booking status.');
}

// Redirect back to the appropriate page
$referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

if (strpos($referrer, 'booking-details.php') !== false) {
    redirect('booking-details.php?id=' . $bookingId);
} else {
    redirect('bookings.php');
}
?>
