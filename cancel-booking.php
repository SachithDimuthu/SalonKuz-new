<?php
/**
 * Cancel Booking
 * 
 * This script handles the cancellation of a booking.
 */

// Include header
require_once 'includes/session.php';
require_once 'config/database.php';
require_once 'includes/utilities.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'You must be logged in to cancel a booking.');
    redirect('login.php?redirect=' . urlencode('profile.php'));
}

// Include models
require_once 'models/Booking.php';

// Create booking instance
$bookingModel = new Booking($conn);

// Get current user
$userId = $_SESSION['user_id'];

// Get booking ID from URL
$bookingId = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$bookingId) {
    setFlashMessage('error', 'Invalid booking ID.');
    redirect('profile.php');
}

// Get booking details
$booking = $bookingModel->getBookingById($bookingId);

// Check if booking exists and belongs to the current user or if user is admin
if (!$booking || ($booking['user_id'] != $userId && !isAdmin())) {
    setFlashMessage('error', 'Booking not found or you do not have permission to cancel it.');
    redirect('profile.php');
}

// Check if booking can be cancelled (only pending or confirmed bookings can be cancelled)
if ($booking['status'] !== 'pending' && $booking['status'] !== 'confirmed') {
    setFlashMessage('error', 'This booking cannot be cancelled as it is already ' . $booking['status'] . '.');
    redirect('profile.php');
}

// Cancel the booking
$cancelled = $bookingModel->updateBookingStatus($bookingId, 'cancelled');

if ($cancelled) {
    setFlashMessage('success', 'Booking cancelled successfully.');
} else {
    setFlashMessage('error', 'Failed to cancel booking. Please try again.');
}

// Redirect back to profile or admin dashboard based on user role
if (isAdmin()) {
    redirect('admin/bookings.php');
} else {
    redirect('profile.php#bookings');
}
?>
