<?php
/**
 * Homepage
 * 
 * This is the main landing page for the Salon Kuz website.
 */

// Set page title
$pageTitle = "Home";

// Include header
require_once 'includes/header.php';
require_once 'includes/auth.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}


// Include models
require_once 'models/Service.php';
require_once 'models/Deal.php';

// Create instances
$serviceModel = new Service($conn);
$dealModel = new Deal($conn);

// Get popular services
$popularServices = $serviceModel->getPopularServices(4);

// Get active deals
$activeDeals = $dealModel->getAllDeals(true, 3);
?>

<!-- Hero Section -->
<section class="hero-section flex items-center justify-center">
    <div class="container mx-auto px-4 text-center text-white">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Welcome to Salon Kuz</h1>
        <p class="text-xl md:text-2xl mb-8">Experience the ultimate beauty transformation</p>
        <div class="flex justify-center space-x-4">
            <a href="services.php" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 px-6 rounded-full transition duration-300">Our Services</a>
            <a href="booking.php" class="bg-white hover:bg-gray-100 text-pink-500 font-bold py-3 px-6 rounded-full transition duration-300">Book Now</a>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 mb-8 md:mb-0 md:pr-8">
                <img src="assets/images/salon-about.jpg" alt="About Salon Kuz" class="rounded-lg shadow-lg w-full h-auto">
            </div>
            <div class="md:w-1/2">
                <h2 class="text-3xl font-bold mb-4 salon-primary">About Salon Kuz</h2>
                <p class="text-gray-700 mb-4">Welcome to Salon Kuz, where beauty meets excellence. Our team of skilled professionals is dedicated to providing you with the highest quality beauty services in a relaxing and luxurious environment.</p>
                <p class="text-gray-700 mb-6">From hair styling and coloring to facials, makeup, nail care, and massage, we offer a comprehensive range of services to enhance your natural beauty and help you look and feel your best.</p>
                <a href="about.php" class="bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded transition duration-300">Learn More</a>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="py-16 bg-gray-100">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold mb-2 salon-primary">Our Popular Services</h2>
            <p class="text-gray-700">Discover our most sought-after beauty treatments</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($popularServices as $service): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden transition duration-300 hover:shadow-xl">
                    <?php if ($service['image']): ?>
                        <img src="assets/images/services/<?php echo $service['image']; ?>" alt="<?php echo $service['name']; ?>" class="w-full h-48 object-cover">
                    <?php else: ?>
                        <div class="w-full h-48 bg-gray-300 flex items-center justify-center">
                            <i class="fas fa-spa text-gray-400 text-4xl"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2"><?php echo $service['name']; ?></h3>
                        <p class="text-gray-600 mb-4 text-sm"><?php echo substr($service['description'], 0, 100) . '...'; ?></p>
                        <div class="flex justify-between items-center">
                            <span class="text-pink-500 font-bold"><?php echo formatPrice($service['price']); ?></span>
                            <span class="text-gray-500 text-sm"><?php echo $service['duration']; ?> min</span>
                        </div>
                        <a href="services.php?id=<?php echo $service['id']; ?>" class="block text-center mt-4 bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded transition duration-300">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-8">
            <a href="services.php" class="inline-block bg-transparent hover:bg-pink-500 text-pink-500 hover:text-white border border-pink-500 hover:border-transparent font-bold py-2 px-4 rounded transition duration-300">View All Services</a>
        </div>
    </div>
</section>

<!-- Deals Section -->
<section class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold mb-2 salon-primary">Special Deals</h2>
            <p class="text-gray-700">Take advantage of our limited-time offers</p>
        </div>
        
        <?php if (empty($activeDeals)): ?>
            <div class="text-center">
                <p class="text-gray-700">No active deals at the moment. Check back soon!</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($activeDeals as $deal): ?>
                    <div class="bg-pink-50 rounded-lg shadow-md overflow-hidden transition duration-300 hover:shadow-xl border border-pink-200">
                        <?php if ($deal['image']): ?>
                            <img src="assets/images/deals/<?php echo $deal['image']; ?>" alt="<?php echo $deal['title']; ?>" class="w-full h-48 object-cover">
                        <?php else: ?>
                            <div class="w-full h-48 bg-pink-100 flex items-center justify-center">
                                <i class="fas fa-gift text-pink-300 text-4xl"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <div class="bg-pink-500 text-white text-sm font-bold py-1 px-2 rounded-full inline-block mb-2">
                                <?php echo $deal['discount_percentage']; ?>% OFF
                            </div>
                            <h3 class="text-xl font-semibold mb-2"><?php echo $deal['title']; ?></h3>
                            <p class="text-gray-600 mb-4 text-sm"><?php echo substr($deal['description'], 0, 100) . '...'; ?></p>
                            <p class="text-gray-500 text-sm mb-4">
                                Valid from <?php echo formatDate($deal['start_date']); ?> to <?php echo formatDate($deal['end_date']); ?>
                            </p>
                            <a href="deals.php?id=<?php echo $deal['id']; ?>" class="block text-center mt-4 bg-pink-500 hover:bg-pink-600 text-white font-bold py-2 px-4 rounded transition duration-300">View Deal</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-8">
                <a href="deals.php" class="inline-block bg-transparent hover:bg-pink-500 text-pink-500 hover:text-white border border-pink-500 hover:border-transparent font-bold py-2 px-4 rounded transition duration-300">View All Deals</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-16 bg-gray-100">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold mb-2 salon-primary">What Our Clients Say</h2>
            <p class="text-gray-700">Read testimonials from our satisfied customers</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center mr-4">
                        <span class="text-pink-500 font-bold">SM</span>
                    </div>
                    <div>
                        <h4 class="font-semibold">Sarah Mitchell</h4>
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-700">"I've been coming to Salon Kuz for over a year now, and I'm always impressed with the quality of service. The staff is professional, friendly, and skilled. Highly recommend their hair styling services!"</p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center mr-4">
                        <span class="text-pink-500 font-bold">JD</span>
                    </div>
                    <div>
                        <h4 class="font-semibold">John Davis</h4>
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-700">"The massage therapy at Salon Kuz is exceptional. I had a deep tissue massage that really helped with my back pain. The ambiance is relaxing, and the therapists are knowledgeable and attentive."</p>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center mr-4">
                        <span class="text-pink-500 font-bold">EW</span>
                    </div>
                    <div>
                        <h4 class="font-semibold">Emily Wilson</h4>
                        <div class="flex text-yellow-400">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
                <p class="text-gray-700">"I had my wedding makeup done at Salon Kuz, and I couldn't be happier with the results. The makeup artist listened to my preferences and created a look that was exactly what I wanted. My makeup stayed perfect all day!"</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="py-16 bg-pink-500 text-white">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4">Ready to Experience Salon Kuz?</h2>
        <p class="text-xl mb-8">Book your appointment today and treat yourself to our exceptional services</p>
        <a href="booking.php" class="bg-white hover:bg-gray-100 text-pink-500 font-bold py-3 px-6 rounded-full transition duration-300 inline-block">Book Now</a>
    </div>
</section>

<?php
// Include footer
require_once 'includes/footer.php';
?>
