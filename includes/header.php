<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/utilities.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Salon Kuz' : 'Salon Kuz - Beauty Salon'; ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        .salon-primary {
            color: #FF6B6B;
        }
        .salon-primary-bg {
            background-color: #FF6B6B;
        }
        .salon-secondary {
            color: #4ECDC4;
        }
        .salon-secondary-bg {
            background-color: #4ECDC4;
        }
        .hero-section {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/salon-hero.jpg');
            background-size: cover;
            background-position: center;
            height: 500px;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="index.php" class="text-2xl font-bold salon-primary">Salon Kuz</a>
                </div>
                <div class="hidden md:flex items-center space-x-6">
                    <a href="index.php" class="text-gray-700 hover:text-pink-500 transition duration-300">Home</a>
                    <a href="services.php" class="text-gray-700 hover:text-pink-500 transition duration-300">Services</a>
                    <a href="deals.php" class="text-gray-700 hover:text-pink-500 transition duration-300">Deals</a>
                    <a href="about.php" class="text-gray-700 hover:text-pink-500 transition duration-300">About Us</a>
                    <a href="contact.php" class="text-gray-700 hover:text-pink-500 transition duration-300">Contact</a>
                    
                    <?php if (isLoggedIn()): ?>
                        <div class="relative">
                            <button id="user-menu-button" class="flex items-center text-gray-700 hover:text-pink-500 transition duration-300">
                                <span class="mr-1"><?php echo getSession('user_first_name'); ?></span>
                                <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                                </svg>
                            </button>
                            <div id="user-menu-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden">
                                <?php if (isAdmin()): ?>
                                    <a href="admin/dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Admin Dashboard</a>
                                <?php elseif (isEmployee()): ?>
                                    <a href="employee/dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Employee Dashboard</a>
                                <?php else: ?>
                                    <a href="user/dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Dashboard</a>
                                <?php endif; ?>
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                                <a href="user/bookings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Bookings</a>
                                <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-700 hover:text-pink-500 transition duration-300">Login</a>
                        <a href="register.php" class="bg-pink-500 text-white px-4 py-2 rounded-md hover:bg-pink-600 transition duration-300">Register</a>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="text-gray-700 hover:text-pink-500 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Mobile menu -->
            <div id="mobile-menu" class="md:hidden hidden pb-4">
                <a href="index.php" class="block py-2 text-gray-700 hover:text-pink-500">Home</a>
                <a href="services.php" class="block py-2 text-gray-700 hover:text-pink-500">Services</a>
                <a href="deals.php" class="block py-2 text-gray-700 hover:text-pink-500">Deals</a>
                <a href="about.php" class="block py-2 text-gray-700 hover:text-pink-500">About Us</a>
                <a href="contact.php" class="block py-2 text-gray-700 hover:text-pink-500">Contact</a>
                
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="admin/dashboard.php" class="block py-2 text-gray-700 hover:text-pink-500">Admin Dashboard</a>
                    <?php elseif (isEmployee()): ?>
                        <a href="employee/dashboard.php" class="block py-2 text-gray-700 hover:text-pink-500">Employee Dashboard</a>
                    <?php else: ?>
                        <a href="user/dashboard.php" class="block py-2 text-gray-700 hover:text-pink-500">My Dashboard</a>
                    <?php endif; ?>
                    <a href="profile.php" class="block py-2 text-gray-700 hover:text-pink-500">Profile</a>
                    <a href="user/bookings.php" class="block py-2 text-gray-700 hover:text-pink-500">My Bookings</a>
                    <a href="logout.php" class="block py-2 text-gray-700 hover:text-pink-500">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="block py-2 text-gray-700 hover:text-pink-500">Login</a>
                    <a href="register.php" class="block py-2 text-gray-700 hover:text-pink-500">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <!-- Flash Messages -->
    <div class="container mx-auto px-4 mt-4">
        <?php echo displayFlashMessages(); ?>
    </div>
    
    <!-- Main Content -->
    <main class="flex-grow">

<script>
document.addEventListener('DOMContentLoaded', function () {
    // User dropdown menu
    const userMenuButton = document.getElementById('user-menu-button');
    const userMenuDropdown = document.getElementById('user-menu-dropdown');

    if (userMenuButton && userMenuDropdown) {
        userMenuButton.addEventListener('click', function (event) {
            event.stopPropagation(); // Prevent click from bubbling up to document
            userMenuDropdown.classList.toggle('hidden');
        });

        // Close dropdown if clicked outside
        document.addEventListener('click', function (event) {
            if (userMenuDropdown && !userMenuDropdown.classList.contains('hidden')) {
                if (!userMenuDropdown.contains(event.target) && !userMenuButton.contains(event.target)) {
                    userMenuDropdown.classList.add('hidden');
                }
            }
        });
    }

    // Mobile menu
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function () {
            mobileMenu.classList.toggle('hidden');
        });
    }
});
</script>
