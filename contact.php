<?php
$pageTitle = "Contact Us";
require_once 'includes/header.php';

$message_sent = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (empty($name)) $errors[] = 'Name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (empty($subject)) $errors[] = 'Subject is required.';
    if (empty($message)) $errors[] = 'Message is required.';

    if (empty($errors)) {
        // In a real application, you would send an email here.
        // For this placeholder, we'll just simulate success.
        // Example: mail('your-email@example.com', "Contact Form: $subject", $message, "From: $email");
        $message_sent = true;
        // You might want to set a flash message here instead of direct output
    }
}

?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-pink-600">Contact Us</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold mb-4 text-pink-500">Get in Touch</h2>
            <p class="text-gray-700 mb-4">We'd love to hear from you! Whether you have a question about our services, want to book an appointment, or just want to say hello, feel free to reach out using the form below or through our contact details.</p>
            
            <div class="space-y-3">
                <p class="text-gray-700"><i class="fas fa-map-marker-alt mr-2 text-pink-500"></i>123 Beauty Lane, Glamour City, ST 12345</p>
                <p class="text-gray-700"><i class="fas fa-phone mr-2 text-pink-500"></i>(123) 456-7890</p>
                <p class="text-gray-700"><i class="fas fa-envelope mr-2 text-pink-500"></i>contact@salonkuz.com</p>
            </div>
        </div>

        <div class="bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold mb-4 text-pink-500">Send Us a Message</h2>
            <?php if ($message_sent): ?>
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-md">
                    Thank you for your message! We'll get back to you soon.
                </div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-md">
                    <p class="font-semibold">Please correct the following errors:</p>
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!$message_sent): ?>
            <form action="contact.php" method="POST">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 font-semibold mb-2">Full Name</label>
                    <input type="text" id="name" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-semibold mb-2">Email Address</label>
                    <input type="email" id="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="subject" class="block text-gray-700 font-semibold mb-2">Subject</label>
                    <input type="text" id="subject" name="subject" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="message" class="block text-gray-700 font-semibold mb-2">Message</label>
                    <textarea id="message" name="message" rows="5" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                </div>
                <div>
                    <button type="submit" class="w-full bg-pink-500 text-white font-semibold px-4 py-3 rounded-md hover:bg-pink-600 transition duration-300">Send Message</button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
