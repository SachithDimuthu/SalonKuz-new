<?php
/**
 * Booking Page
 * 
 * This page allows users to book appointments for services.
 */

// Set page title
$pageTitle = "Book Appointment";

// Include header
require_once 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('error', 'You must be logged in to book an appointment.');
    redirect('login.php?redirect=' . urlencode('booking.php' . (isset($_GET['service_id']) ? '?service_id=' . $_GET['service_id'] : '')));
}

// Include models
require_once 'models/Service.php';
require_once 'models/User.php';
require_once 'models/Booking.php';
require_once 'models/Deal.php';

// Create instances
$serviceModel = new Service($conn);
$userModel = new User($conn);
$bookingModel = new Booking($conn);
$dealModel = new Deal($conn);

// Get current user
$userId = $_SESSION['user_id'];
$user = $userModel->getUserById($userId);

// Initialize variables
$errors = [];
$success = false;
$selectedService = null;
$selectedDeal = null;
$selectedEmployee = null;
$selectedDate = date('Y-m-d', strtotime('+1 day'));
$selectedTime = '';
$employees = [];
$availableTimes = [];

// Handle service ID from URL
if (isset($_GET['service_id'])) {
    $serviceId = intval($_GET['service_id']);
    $selectedService = $serviceModel->getServiceById($serviceId);
    
    if ($selectedService) {
        // Get employees who can perform this service
        $employees = $userModel->getEmployeesByServiceId($serviceId);
        
        // Check if service has an active deal
        $activeDeal = $dealModel->getActiveDealForService($serviceId);
        if ($activeDeal) {
            $selectedDeal = $activeDeal;
        }
    }
}

// Handle deal ID from URL
if (isset($_GET['deal_id']) && !$selectedService) {
    $dealId = intval($_GET['deal_id']);
    $selectedDeal = $dealModel->getDealById($dealId);
    
    if ($selectedDeal && !empty($selectedDeal['services'])) {
        // Pre-select the first service in the deal
        $selectedService = $selectedDeal['services'][0];
        $serviceId = $selectedService['id'];
        
        // Get employees who can perform this service
        $employees = $userModel->getEmployeesByServiceId($serviceId);
    }
}

// Handle form submission for service selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_service'])) {
    $serviceId = intval($_POST['service_id']);
    $selectedService = $serviceModel->getServiceById($serviceId);
    
    if ($selectedService) {
        // Get employees who can perform this service
        $employees = $userModel->getEmployeesByServiceId($serviceId);
        
        // Check if service has an active deal
        $activeDeal = $dealModel->getActiveDealForService($serviceId);
        if ($activeDeal) {
            $selectedDeal = $activeDeal;
        }
        
        // If deal was selected from URL, keep it
        if (isset($_POST['deal_id']) && !empty($_POST['deal_id'])) {
            $dealId = intval($_POST['deal_id']);
            $selectedDeal = $dealModel->getDealById($dealId);
        }
    } else {
        $errors[] = "Selected service not found.";
    }
}

// Handle form submission for employee and date selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_datetime'])) {
    $serviceId = intval($_POST['service_id']);
    $selectedService = $serviceModel->getServiceById($serviceId);
    
    if ($selectedService) {
        // Get deal if applicable
        if (isset($_POST['deal_id']) && !empty($_POST['deal_id'])) {
            $dealId = intval($_POST['deal_id']);
            $selectedDeal = $dealModel->getDealById($dealId);
        }
        
        // Get selected employee
        $employeeId = intval($_POST['employee_id']);
        $selectedEmployee = $userModel->getUserById($employeeId);
        
        if ($selectedEmployee) {
            // Get selected date
            $selectedDate = sanitize($_POST['booking_date']);
            
            // Validate date (must be in the future)
            $currentDate = date('Y-m-d');
            if ($selectedDate < $currentDate) {
                $errors[] = "Booking date must be in the future.";
            } else {
                // Get available time slots for the selected employee, service, and date
                $availableTimes = $bookingModel->getAvailableTimeSlots($employeeId, $serviceId, $selectedDate);
                
                if (empty($availableTimes)) {
                    $errors[] = "No available time slots for the selected date. Please choose another date.";
                }
            }
        } else {
            $errors[] = "Selected employee not found.";
        }
        
        // Get employees who can perform this service (for the form)
        $employees = $userModel->getEmployeesByServiceId($serviceId);
    } else {
        $errors[] = "Selected service not found.";
    }
}

// Handle form submission for booking confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    $serviceId = intval($_POST['service_id']);
    $employeeId = intval($_POST['employee_id']);
    $bookingDate = sanitize($_POST['booking_date']);
    $startTime = sanitize($_POST['start_time']);
    $notes = sanitize($_POST['notes'] ?? '');
    $dealId = isset($_POST['deal_id']) && !empty($_POST['deal_id']) ? intval($_POST['deal_id']) : null;
    
    // Validate inputs
    if (empty($serviceId)) {
        $errors[] = "Service is required.";
    }
    
    if (empty($employeeId)) {
        $errors[] = "Employee is required.";
    }
    
    if (empty($bookingDate)) {
        $errors[] = "Booking date is required.";
    }
    
    if (empty($startTime)) {
        $errors[] = "Start time is required.";
    }
    
    // If no errors, create the booking
    if (empty($errors)) {
        $bookingData = [
            'user_id' => $userId,
            'service_id' => $serviceId,
            'employee_id' => $employeeId,
            'booking_date' => $bookingDate,
            'start_time' => $startTime,
            'notes' => $notes,
            'deal_id' => $dealId,
            'status' => 'pending'
        ];
        
        $bookingId = $bookingModel->createBooking($bookingData);
        
        if ($bookingId) {
            $success = true;
            setFlashMessage('success', 'Booking created successfully! Your appointment is pending confirmation.');
            redirect('booking-confirmation.php?id=' . $bookingId);
        } else {
            $errors[] = "Failed to create booking. Please try again.";
        }
    }
}

// Get all services for the service selection form
$allServices = $serviceModel->getAllServices();
?>

<section class="py-12 bg-gray-100">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold mb-2 salon-primary">Book an Appointment</h1>
            <p class="text-gray-700">Schedule your beauty treatment at Salon Kuz</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded mb-6 max-w-3xl mx-auto">
                <ul class="list-disc pl-4">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <!-- Booking Steps -->
        <div class="max-w-3xl mx-auto mb-8">
            <div class="flex justify-between">
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 bg-pink-500 text-white rounded-full flex items-center justify-center font-bold">1</div>
                    <span class="text-sm mt-2 text-gray-700">Select Service</span>
                </div>
                <div class="flex-1 border-t-2 border-gray-300 self-center"></div>
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 <?php echo $selectedService ? 'bg-pink-500 text-white' : 'bg-gray-300 text-gray-600'; ?> rounded-full flex items-center justify-center font-bold">2</div>
                    <span class="text-sm mt-2 text-gray-700">Choose Date & Time</span>
                </div>
                <div class="flex-1 border-t-2 border-gray-300 self-center"></div>
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 <?php echo $selectedTime ? 'bg-pink-500 text-white' : 'bg-gray-300 text-gray-600'; ?> rounded-full flex items-center justify-center font-bold">3</div>
                    <span class="text-sm mt-2 text-gray-700">Confirm Booking</span>
                </div>
            </div>
        </div>
        
        <!-- Step 1: Service Selection -->
        <?php if (!$selectedService || (isset($_POST['select_service']) && !empty($errors))): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden max-w-3xl mx-auto">
                <div class="bg-pink-500 text-white py-4 px-6">
                    <h2 class="text-xl font-bold">Step 1: Select a Service</h2>
                </div>
                <div class="p-6">
                    <form action="booking.php" method="POST">
                        <div class="mb-4">
                            <label for="service_id" class="block text-gray-700 text-sm font-bold mb-2">Choose a Service *</label>
                            <select id="service_id" name="service_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                <option value="">-- Select a Service --</option>
                                <?php foreach ($allServices as $service): ?>
                                    <option value="<?php echo $service['id']; ?>" <?php echo (isset($selectedService) && $selectedService['id'] == $service['id']) ? 'selected' : ''; ?>>
                                        <?php echo $service['name']; ?> (<?php echo formatPrice($service['price']); ?> - <?php echo $service['duration']; ?> min)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <?php if (isset($selectedDeal)): ?>
                            <div class="mb-6 bg-pink-50 border border-pink-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <div class="bg-pink-500 text-white text-sm font-bold py-1 px-2 rounded-full inline-block mr-2">
                                        <?php echo $selectedDeal['discount_percentage']; ?>% OFF
                                    </div>
                                    <h3 class="text-lg font-semibold text-pink-700"><?php echo $selectedDeal['title']; ?></h3>
                                </div>
                                <p class="text-sm text-gray-700 mt-2"><?php echo $selectedDeal['description']; ?></p>
                                <input type="hidden" name="deal_id" value="<?php echo $selectedDeal['id']; ?>">
                            </div>
                        <?php endif; ?>
                        
                        <div>
                            <input type="hidden" name="select_service" value="1">
                            <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                                Continue to Date & Time
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        
        <!-- Step 2: Date & Employee Selection -->
        <?php elseif (!$selectedTime || (isset($_POST['select_datetime']) && !empty($errors))): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden max-w-3xl mx-auto">
                <div class="bg-pink-500 text-white py-4 px-6">
                    <h2 class="text-xl font-bold">Step 2: Choose Date & Employee</h2>
                </div>
                <div class="p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Selected Service</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <?php if ($selectedService['image']): ?>
                                    <img src="assets/images/services/<?php echo $selectedService['image']; ?>" alt="<?php echo $selectedService['name']; ?>" class="w-16 h-16 object-cover rounded-lg mr-4">
                                <?php else: ?>
                                    <div class="w-16 h-16 bg-gray-300 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-spa text-gray-400 text-2xl"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h4 class="font-semibold"><?php echo $selectedService['name']; ?></h4>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <span class="mr-3"><i class="far fa-clock mr-1"></i> <?php echo $selectedService['duration']; ?> min</span>
                                        <?php if ($selectedDeal): ?>
                                            <span class="text-pink-500 font-semibold"><?php echo formatPrice(calculateDiscountedPrice($selectedService['price'], $selectedDeal['discount_percentage'])); ?></span>
                                            <span class="text-gray-500 line-through ml-1"><?php echo formatPrice($selectedService['price']); ?></span>
                                        <?php else: ?>
                                            <span><?php echo formatPrice($selectedService['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php if ($selectedDeal): ?>
                                <div class="mt-2 pt-2 border-t border-gray-200">
                                    <div class="flex items-center">
                                        <div class="bg-pink-500 text-white text-xs font-bold py-1 px-2 rounded-full inline-block mr-2">
                                            <?php echo $selectedDeal['discount_percentage']; ?>% OFF
                                        </div>
                                        <span class="text-sm text-pink-700"><?php echo $selectedDeal['title']; ?></span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mt-2 text-right">
                            <a href="booking.php" class="text-pink-500 hover:text-pink-600 text-sm">
                                <i class="fas fa-edit mr-1"></i> Change Service
                            </a>
                        </div>
                    </div>
                    
                    <form action="booking.php" method="POST">
                        <div class="mb-4">
                            <label for="employee_id" class="block text-gray-700 text-sm font-bold mb-2">Select Employee *</label>
                            <select id="employee_id" name="employee_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                <option value="">-- Select an Employee --</option>
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?php echo $employee['id']; ?>" <?php echo (isset($selectedEmployee) && $selectedEmployee['id'] == $employee['id']) ? 'selected' : ''; ?>>
                                        <?php echo $employee['first_name'] . ' ' . $employee['last_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-6">
                            <label for="booking_date" class="block text-gray-700 text-sm font-bold mb-2">Select Date *</label>
                            <input type="date" id="booking_date" name="booking_date" value="<?php echo $selectedDate; ?>" min="<?php echo date('Y-m-d'); ?>" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        </div>
                        
                        <div>
                            <input type="hidden" name="service_id" value="<?php echo $selectedService['id']; ?>">
                            <?php if ($selectedDeal): ?>
                                <input type="hidden" name="deal_id" value="<?php echo $selectedDeal['id']; ?>">
                            <?php endif; ?>
                            <input type="hidden" name="select_datetime" value="1">
                            <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                                Check Available Times
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
        <!-- Step 2.5: Time Selection -->
        <?php elseif (!empty($availableTimes)): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden max-w-3xl mx-auto">
                <div class="bg-pink-500 text-white py-4 px-6">
                    <h2 class="text-xl font-bold">Step 2: Choose Available Time</h2>
                </div>
                <div class="p-6">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Booking Details</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Service:</p>
                                    <p class="font-semibold"><?php echo $selectedService['name']; ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Employee:</p>
                                    <p class="font-semibold"><?php echo $selectedEmployee['first_name'] . ' ' . $selectedEmployee['last_name']; ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Date:</p>
                                    <p class="font-semibold"><?php echo formatDate($selectedDate); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Price:</p>
                                    <p class="font-semibold">
                                        <?php if ($selectedDeal): ?>
                                            <span class="text-pink-500"><?php echo formatPrice(calculateDiscountedPrice($selectedService['price'], $selectedDeal['discount_percentage'])); ?></span>
                                            <span class="text-gray-500 line-through text-sm ml-1"><?php echo formatPrice($selectedService['price']); ?></span>
                                        <?php else: ?>
                                            <?php echo formatPrice($selectedService['price']); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 text-right">
                            <a href="booking.php" class="text-pink-500 hover:text-pink-600 text-sm mr-3">
                                <i class="fas fa-edit mr-1"></i> Change Service
                            </a>
                            <a href="javascript:history.back()" class="text-pink-500 hover:text-pink-600 text-sm">
                                <i class="fas fa-edit mr-1"></i> Change Date/Employee
                            </a>
                        </div>
                    </div>
                    
                    <form action="booking.php" method="POST">
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Select Available Time *</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <?php foreach ($availableTimes as $time): ?>
                                    <label class="bg-gray-100 hover:bg-pink-50 border border-gray-300 rounded-lg p-3 cursor-pointer transition duration-300 flex items-center justify-center">
                                        <input type="radio" name="start_time" value="<?php echo $time; ?>" class="mr-2" required>
                                        <span><?php echo formatTime($time); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label for="notes" class="block text-gray-700 text-sm font-bold mb-2">Special Requests (Optional)</label>
                            <textarea id="notes" name="notes" rows="3" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                        </div>
                        
                        <div>
                            <input type="hidden" name="service_id" value="<?php echo $selectedService['id']; ?>">
                            <input type="hidden" name="employee_id" value="<?php echo $selectedEmployee['id']; ?>">
                            <input type="hidden" name="booking_date" value="<?php echo $selectedDate; ?>">
                            <?php if ($selectedDeal): ?>
                                <input type="hidden" name="deal_id" value="<?php echo $selectedDeal['id']; ?>">
                            <?php endif; ?>
                            <input type="hidden" name="confirm_booking" value="1">
                            <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                                Confirm Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
require_once 'includes/footer.php';
?>
