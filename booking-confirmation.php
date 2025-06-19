<?php
/**
 * Booking Confirmation Page
 * 
 * This page displays the confirmation details after a successful booking.
 */

// Set page title
$pageTitle = "Booking Confirmation";

// Include header
require_once 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'You must be logged in to view booking details.');
    redirect('login.php?redirect=' . urlencode('booking-confirmation.php' . (isset($_GET['id']) ? '?id=' . $_GET['id'] : '')));
}

// Include models
require_once 'models/Booking.php';
require_once 'models/Service.php';
require_once 'models/User.php';
require_once 'models/Deal.php';

// Create instances
$bookingModel = new Booking($conn);
$serviceModel = new Service($conn);
$userModel = new User($conn);
$dealModel = new Deal($conn);

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

// Check if booking exists and belongs to the current user
if (!$booking || $booking['user_id'] != $userId) {
    setFlashMessage('error', 'Booking not found or you do not have permission to view it.');
    redirect('profile.php');
}

// Get service details
$service = $serviceModel->getServiceById($booking['service_id']);

// Get employee details
$employee = $userModel->getUserById($booking['employee_id']);

// Get deal details if applicable
$deal = null;
if (!empty($booking['deal_id'])) {
    $deal = $dealModel->getDealById($booking['deal_id']);
}

// Calculate price
$price = $service['price'];
if ($deal) {
    $price = calculateDiscountedPrice($price, $deal['discount_percentage']);
}
?>

<section class="py-12 bg-gray-100">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="bg-pink-500 text-white py-4 px-6">
                    <h2 class="text-xl font-bold">Booking Confirmation</h2>
                </div>
                
                <div class="p-6">
                    <div class="mb-8 text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check text-green-500 text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Thank You for Your Booking!</h3>
                        <p class="text-gray-600">Your appointment has been successfully scheduled.</p>
                        <p class="text-gray-600 text-sm mt-1">Booking Reference: <span class="font-semibold">#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></span></p>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <h4 class="text-lg font-semibold mb-4 border-b pb-2">Booking Details</h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Service:</p>
                                <p class="font-semibold"><?php echo $service['name']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Date:</p>
                                <p class="font-semibold"><?php echo formatDate($booking['booking_date']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Time:</p>
                                <p class="font-semibold"><?php echo formatTime($booking['start_time']); ?> - <?php echo formatTime($booking['end_time']); ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Duration:</p>
                                <p class="font-semibold"><?php echo $service['duration']; ?> minutes</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Stylist/Therapist:</p>
                                <p class="font-semibold"><?php echo $employee['first_name'] . ' ' . $employee['last_name']; ?></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Status:</p>
                                <p class="font-semibold">
                                    <?php
                                    $statusClass = '';
                                    switch ($booking['status']) {
                                        case 'confirmed':
                                            $statusClass = 'text-green-500';
                                            break;
                                        case 'pending':
                                            $statusClass = 'text-yellow-500';
                                            break;
                                        case 'cancelled':
                                            $statusClass = 'text-red-500';
                                            break;
                                        case 'completed':
                                            $statusClass = 'text-blue-500';
                                            break;
                                    }
                                    ?>
                                    <span class="<?php echo $statusClass; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        
                        <?php if ($deal): ?>
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex items-center">
                                    <div class="bg-pink-500 text-white text-xs font-bold py-1 px-2 rounded-full inline-block mr-2">
                                        <?php echo $deal['discount_percentage']; ?>% OFF
                                    </div>
                                    <span class="text-sm text-pink-700"><?php echo $deal['title']; ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($booking['notes'])): ?>
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <p class="text-sm text-gray-600">Special Requests:</p>
                                <p class="text-gray-700"><?php echo nl2br($booking['notes']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <h4 class="text-lg font-semibold mb-4 border-b pb-2">Payment Details</h4>
                        
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-700"><?php echo $service['name']; ?></span>
                            <span class="text-gray-700"><?php echo formatPrice($service['price']); ?></span>
                        </div>
                        
                        <?php if ($deal): ?>
                            <div class="flex justify-between items-center mb-2 text-pink-500">
                                <span>Discount (<?php echo $deal['title']; ?>)</span>
                                <span>-<?php echo formatPrice($service['price'] - $price); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="flex justify-between items-center font-bold text-lg mt-4 pt-4 border-t border-gray-200">
                            <span>Total</span>
                            <span><?php echo formatPrice($price); ?></span>
                        </div>
                        
                        <div class="mt-4 text-sm text-gray-600">
                            <p>Payment will be collected at the salon after your service.</p>
                        </div>
                    </div>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <h4 class="font-semibold text-yellow-700 mb-2">Important Information</h4>
                        <ul class="list-disc pl-5 text-sm text-yellow-700 space-y-1">
                            <li>Please arrive 10 minutes before your appointment time.</li>
                            <li>If you need to cancel or reschedule, please do so at least 24 hours in advance.</li>
                            <li>Your booking is currently <?php echo strtolower($booking['status']); ?>. You will receive an email when it is confirmed.</li>
                        </ul>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row justify-between items-center">
                        <a href="profile.php#bookings" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition duration-300 mb-3 sm:mb-0">
                            View All Bookings
                        </a>
                        
                        <?php if ($booking['status'] === 'pending' || $booking['status'] === 'confirmed'): ?>
                            <a href="cancel-booking.php?id=<?php echo $booking['id']; ?>" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition duration-300" onclick="return confirm('Are you sure you want to cancel this booking?');">
                                Cancel Booking
                            </a>
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
