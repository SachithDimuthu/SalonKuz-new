    </main>
    
    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-semibold mb-4">Salon Kuz</h3>
                    <p class="text-gray-300">Experience the ultimate beauty transformation at Salon Kuz. Our team of professionals is dedicated to making you look and feel your best.</p>
                </div>
                <div>
                    <h3 class="text-xl font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-300 hover:text-pink-400 transition duration-300">Home</a></li>
                        <li><a href="services.php" class="text-gray-300 hover:text-pink-400 transition duration-300">Services</a></li>
                        <li><a href="deals.php" class="text-gray-300 hover:text-pink-400 transition duration-300">Deals</a></li>
                        <li><a href="about.php" class="text-gray-300 hover:text-pink-400 transition duration-300">About Us</a></li>
                        <li><a href="contact.php" class="text-gray-300 hover:text-pink-400 transition duration-300">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-semibold mb-4">Services</h3>
                    <ul class="space-y-2">
                        <li><a href="services.php?category=hair" class="text-gray-300 hover:text-pink-400 transition duration-300">Hair Styling</a></li>
                        <li><a href="services.php?category=facial" class="text-gray-300 hover:text-pink-400 transition duration-300">Facial Treatments</a></li>
                        <li><a href="services.php?category=makeup" class="text-gray-300 hover:text-pink-400 transition duration-300">Makeup</a></li>
                        <li><a href="services.php?category=nails" class="text-gray-300 hover:text-pink-400 transition duration-300">Nail Care</a></li>
                        <li><a href="services.php?category=massage" class="text-gray-300 hover:text-pink-400 transition duration-300">Massage</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-semibold mb-4">Contact Us</h3>
                    <ul class="space-y-2">
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt mt-1 mr-2 text-pink-400"></i>
                            <span class="text-gray-300">123 Beauty Street, Colombo, Sri Lanka</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-phone mt-1 mr-2 text-pink-400"></i>
                            <span class="text-gray-300">+94 11 234 5678</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-envelope mt-1 mr-2 text-pink-400"></i>
                            <span class="text-gray-300">info@salonkuz.com</span>
                        </li>
                    </ul>
                    <div class="mt-4 flex space-x-4">
                        <a href="#" class="text-gray-300 hover:text-pink-400 transition duration-300">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-pink-400 transition duration-300">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-pink-400 transition duration-300">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-pink-400 transition duration-300">
                            <i class="fab fa-pinterest"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-6 text-center">
                <p class="text-gray-300">&copy; <?php echo date('Y'); ?> Salon Kuz. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>
