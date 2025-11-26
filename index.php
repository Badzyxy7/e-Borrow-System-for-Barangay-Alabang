<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-Borrow System | Barangay Alabang</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: {
              50: '#eff6ff',
              100: '#dbeafe',
              500: '#3b82f6',
              600: '#2563eb',
              700: '#1d4ed8',
              800: '#1e40af',
              900: '#1e3a8a'
            }
          },
          animation: {
            'float': 'float 6s ease-in-out infinite',
            'fade-in-up': 'fadeInUp 0.8s ease-out',
          },
          keyframes: {
            float: {
              '0%, 100%': { transform: 'translateY(0px)' },
              '50%': { transform: 'translateY(-20px)' }
            },
            fadeInUp: {
              '0%': { 
                opacity: '0',
                transform: 'translateY(30px)'
              },
              '100%': {
                opacity: '1', 
                transform: 'translateY(0px)'
              }
            }
          }
        }
      }
    }
  </script>
  <style>
    .glass-effect {
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
    }
    
    .hero-gradient {
      background: linear-gradient(135deg, rgba(15, 23, 42, 0.85) 0%, rgba(30, 64, 175, 0.75) 100%);
    }
    
    .scroll-smooth {
      scroll-behavior: smooth;
    }
    
    .animate-on-scroll {
      opacity: 0;
      transform: translateY(30px);
      transition: all 0.8s ease-out;
    }
    
    .animate-on-scroll.visible {
      opacity: 1;
      transform: translateY(0);
    }

    /* Better mobile menu transition */
    #mobile-menu {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease-out;
    }

    #mobile-menu.active {
      max-height: 400px;
    }

    /* Ensure proper touch targets on mobile */
    @media (max-width: 768px) {
      a, button {
        min-height: 44px;
        min-width: 44px;
      }
    }
  </style>
</head>
<body class="font-sans scroll-smooth">

  <!-- Navbar -->
  <nav class="glass-effect bg-white/90 fixed w-full top-0 z-50 border-b border-white/20 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Left: Logo + Text -->
        <div class="flex items-center space-x-2 sm:space-x-4">
          <div class="w-9 h-9 sm:w-10 sm:h-10 bg-gradient-to-br from-blue-600 to-blue-800 rounded-xl flex items-center justify-center flex-shrink-0">
            <i data-feather="home" class="w-4 h-4 sm:w-5 sm:h-5 text-white"></i>
          </div>
          <div>
            <h1 class="text-base sm:text-xl font-bold bg-gradient-to-r from-blue-900 to-blue-700 bg-clip-text text-transparent">Barangay Alabang</h1>
            <p class="text-xs sm:text-sm text-gray-600">E-Borrow System</p>
          </div>
        </div>

        <!-- Desktop Menu -->
        <div class="hidden md:flex items-center space-x-6 lg:space-x-8">
          <a href="#home" class="text-gray-700 hover:text-blue-700 font-medium transition-colors duration-300">Home</a>
          <a href="#services" class="text-gray-700 hover:text-blue-700 font-medium transition-colors duration-300">Services</a>
          <a href="#equipment" class="text-gray-700 hover:text-blue-700 font-medium transition-colors duration-300">Equipment</a>
          <a href="#contact" class="text-gray-700 hover:text-blue-700 font-medium transition-colors duration-300">Contact</a>
          <a href="login.php" class="bg-gradient-to-r from-blue-900 to-blue-700 text-white px-4 lg:px-6 py-2 lg:py-3 rounded-xl hover:from-blue-800 hover:to-blue-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 font-medium flex items-center gap-2 text-sm lg:text-base">
            <i data-feather="log-in" class="w-4 h-4"></i>
            Request an item
          </a>
        </div>

        <!-- Mobile menu button -->
        <div class="md:hidden">
          <button id="menu-btn" class="p-2 rounded-lg text-gray-600 hover:text-blue-600 hover:bg-blue-50 focus:outline-none transition-colors">
            <i data-feather="menu" class="w-6 h-6" id="menu-icon"></i>
            <i data-feather="x" class="w-6 h-6 hidden" id="close-icon"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="md:hidden bg-white/95 backdrop-blur-lg border-t border-white/20">
      <div class="flex flex-col space-y-1 py-4 px-4 text-gray-800 font-medium">
        <a href="#home" class="hover:text-blue-700 hover:bg-blue-50 transition px-4 py-3 rounded-lg">Home</a>
        <a href="#services" class="hover:text-blue-700 hover:bg-blue-50 transition px-4 py-3 rounded-lg">Services</a>
        <a href="#equipment" class="hover:text-blue-700 hover:bg-blue-50 transition px-4 py-3 rounded-lg">Equipment</a>
        <a href="#contact" class="hover:text-blue-700 hover:bg-blue-50 transition px-4 py-3 rounded-lg">Contact</a>
        <a href="login.php" class="bg-gradient-to-r from-blue-900 to-blue-700 text-white px-6 py-3 rounded-xl hover:from-blue-800 hover:to-blue-600 transition-all duration-300 shadow-lg flex items-center justify-center gap-2 mt-2">
          <i data-feather="log-in" class="w-4 h-4"></i>
          Request an item
        </a>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section id="home" class="relative min-h-screen flex items-center overflow-hidden pt-16">
    <!-- Background with Overlay -->
    <div class="absolute inset-0">
      <img src="photos/niggapic.jpg" alt="Barangay Hall" class="w-full h-full object-cover">
      <div class="absolute inset-0 hero-gradient"></div>
    </div>

    <!-- Floating Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-1/4 left-1/4 w-24 h-24 sm:w-32 sm:h-32 bg-blue-400/10 rounded-full animate-float"></div>
      <div class="absolute top-3/4 right-1/4 w-16 h-16 sm:w-24 sm:h-24 bg-green-400/10 rounded-full animate-float" style="animation-delay: -2s;"></div>
      <div class="absolute top-1/2 right-1/3 w-12 h-12 sm:w-16 sm:h-16 bg-blue-300/10 rounded-full animate-float" style="animation-delay: -4s;"></div>
    </div>

    <!-- Content Wrapper -->
    <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-12 xl:px-16 flex flex-col md:flex-row items-center justify-between gap-8 md:gap-12 py-12 md:py-0">
      <!-- LEFT SIDE (Text + Buttons) -->
      <div class="md:w-1/2 text-white text-center md:text-left animate-fade-in-up space-y-4 sm:space-y-6">
        <h2 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-bold leading-tight">
          E-Borrow System
          <br>
          <span class="bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent">
            for Barangay Alabang
          </span>
        </h2>
        <p class="text-sm sm:text-base md:text-lg lg:text-xl text-blue-100 max-w-lg mx-auto md:mx-0 leading-relaxed px-4 md:px-0">
          Borrow equipment for your community needs with ease — tables, chairs, sound systems, and more!
        </p>

        <div class="flex flex-col sm:flex-row justify-center md:justify-start gap-4 sm:gap-6 mt-6 sm:mt-8 px-4 md:px-0">
          <a href="login" class="group bg-gradient-to-r from-blue-600 to-blue-700 px-6 sm:px-8 py-3 sm:py-4 rounded-xl text-white font-semibold hover:from-blue-700 hover:to-blue-800 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center justify-center gap-3 text-sm sm:text-base">
            <i data-feather="package" class="w-4 h-4 sm:w-5 sm:h-5 group-hover:rotate-12 transition-transform duration-300"></i>
            Browse Equipment
          </a>
          <a href="#services" class="group glass-effect bg-white/10 border-2 border-white/30 px-6 sm:px-8 py-3 sm:py-4 rounded-xl font-semibold hover:bg-white/20 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center justify-center gap-3 text-sm sm:text-base">
            <i data-feather="info" class="w-4 h-4 sm:w-5 sm:h-5 group-hover:scale-110 transition-transform duration-300"></i>
            Learn More
          </a>
        </div>
      </div>

      <!-- RIGHT SIDE (Photo Carousel - visible on larger tablets and desktop) -->
      <div class="hidden lg:flex md:w-1/2 justify-center animate-fade-in-up">
        <div class="relative w-72 lg:w-96 xl:w-[28rem] h-96 rounded-3xl shadow-2xl border-4 border-white/20 overflow-hidden">
          <!-- Image 1 -->
          <img src="photos/niggapic.jpg" alt="Community Equipment 1" class="carousel-image absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 opacity-100">
          <!-- Image 2 -->
          <img src="photos/thesispic.jpg" alt="Community Equipment 2" class="carousel-image absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 opacity-0">
          <!-- Image 3 -->
          <img src="photos/wth.jpg" alt="Community Equipment 3" class="carousel-image absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 opacity-0">
          
          <!-- Carousel Indicators -->
          <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex gap-2 z-10">
            <div class="carousel-dot w-2 h-2 rounded-full bg-white transition-all duration-300"></div>
            <div class="carousel-dot w-2 h-2 rounded-full bg-white/50 transition-all duration-300"></div>
            <div class="carousel-dot w-2 h-2 rounded-full bg-white/50 transition-all duration-300"></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Services Section -->
  <section id="services" class="py-16 sm:py-20 lg:py-24 bg-gradient-to-br from-gray-50 to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12 sm:mb-16 animate-on-scroll">
        <h3 class="text-3xl sm:text-4xl lg:text-5xl font-bold bg-gradient-to-r from-blue-900 to-blue-700 bg-clip-text text-transparent mb-4 sm:mb-6">Our Services</h3>
        <p class="text-base sm:text-lg lg:text-xl text-gray-600 max-w-3xl mx-auto px-4">Discover how our E-Borrow System makes community equipment accessible to everyone</p>
      </div>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
        <div class="group animate-on-scroll glass-effect bg-white/70 backdrop-blur-sm p-6 sm:p-8 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 border border-white/20 hover:-translate-y-2">
          <div class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-4 sm:mb-6 group-hover:scale-110 transition-transform duration-300">
            <i data-feather="package" class="w-7 h-7 sm:w-8 sm:h-8 text-white"></i>
          </div>
          <h4 class="text-xl sm:text-2xl font-semibold mb-3 sm:mb-4 text-gray-800">Equipment Lending</h4>
          <p class="text-sm sm:text-base text-gray-600 leading-relaxed">Borrow community equipment like tables, chairs, and sound systems for your events with our streamlined process.</p>
        </div>
        
        <div class="group animate-on-scroll glass-effect bg-white/70 backdrop-blur-sm p-6 sm:p-8 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 border border-white/20 hover:-translate-y-2" style="animation-delay: 0.2s;">
          <div class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mb-4 sm:mb-6 group-hover:scale-110 transition-transform duration-300">
            <i data-feather="smartphone" class="w-7 h-7 sm:w-8 sm:h-8 text-white"></i>
          </div>
          <h4 class="text-xl sm:text-2xl font-semibold mb-3 sm:mb-4 text-gray-800">Easy Requests</h4>
          <p class="text-sm sm:text-base text-gray-600 leading-relaxed">Submit borrow requests online and track their approval status anytime with our user-friendly interface.</p>
        </div>
        
        <div class="group animate-on-scroll glass-effect bg-white/70 backdrop-blur-sm p-6 sm:p-8 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 border border-white/20 hover:-translate-y-2 sm:col-span-2 lg:col-span-1" style="animation-delay: 0.4s;">
          <div class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mb-4 sm:mb-6 group-hover:scale-110 transition-transform duration-300">
            <i data-feather="users" class="w-7 h-7 sm:w-8 sm:h-8 text-white"></i>
          </div>
          <h4 class="text-xl sm:text-2xl font-semibold mb-3 sm:mb-4 text-gray-800">Community Support</h4>
          <p class="text-sm sm:text-base text-gray-600 leading-relaxed">Ensuring fair access to barangay-owned equipment for all residents and community organizations.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Equipment Section -->
  <section id="equipment" class="py-16 sm:py-20 lg:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12 sm:mb-16 animate-on-scroll">
        <h3 class="text-3xl sm:text-4xl lg:text-5xl font-bold bg-gradient-to-r from-blue-900 to-blue-700 bg-clip-text text-transparent mb-4 sm:mb-6">Available Equipment</h3>
        <p class="text-base sm:text-lg lg:text-xl text-gray-600 max-w-3xl mx-auto px-4">Browse our collection of high-quality equipment available for community use</p>
      </div>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 mb-8 sm:mb-12">
        <?php
        // Include database connection
        include "db.php";
        
        // Query to get available equipment
        $sql = "SELECT id, name, description, image, quantity, status, `condition`, available 
                FROM equipment 
                WHERE status='available' AND available > 0 
                LIMIT 3";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $delay = 0;
            while ($row = $result->fetch_assoc()) {
                // Determine image path with fallback
                $imagePath = 'photos/' . $row['image'];
                if (empty($row['image']) || !file_exists($imagePath)) {
                    $imagePath = 'photos/placeholder.png';
                }
                
                // Truncate description if too long
                $description = $row['description'];
                $short_desc = strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                
                echo '<div class="group animate-on-scroll glass-effect bg-gradient-to-br from-white to-gray-50 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 border border-gray-100 hover:-translate-y-2 overflow-hidden" style="animation-delay: ' . $delay . 's;">';
                echo '<div class="h-48 sm:h-56 bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden relative">';
                echo '<img src="' . htmlspecialchars($imagePath) . '" alt="' . htmlspecialchars($row['name']) . '" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">';
                echo '<div class="absolute top-3 right-3 sm:top-4 sm:right-4 bg-black/70 text-white px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium">';
                echo htmlspecialchars($row['available']) . ' Available';
                echo '</div>';
                echo '</div>';
                echo '<div class="p-6 sm:p-8">';
                echo '<h4 class="font-bold text-xl sm:text-2xl mb-2 sm:mb-3 text-gray-800">' . htmlspecialchars($row['name']) . '</h4>';
                echo '<p class="text-sm sm:text-base text-gray-600 leading-relaxed mb-3 sm:mb-4">' . htmlspecialchars($short_desc) . '</p>';
                
                if (!empty($row['condition'])) {
                    echo '<div class="flex items-center text-xs sm:text-sm text-blue-600 mb-2">';
                    echo '<i data-feather="info" class="w-3 h-3 sm:w-4 sm:h-4 mr-2"></i>';
                    echo 'Condition: ' . htmlspecialchars($row['condition']);
                    echo '</div>';
                }
                
                echo '<div class="flex items-center text-xs sm:text-sm text-green-600">';
                echo '<i data-feather="check-circle" class="w-3 h-3 sm:w-4 sm:h-4 mr-2"></i>';
                echo 'Available for booking';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                
                $delay += 0.2;
            }
        } else {
            // Fallback to sample cards if no database connection or no equipment
            echo '<div class="group animate-on-scroll glass-effect bg-gradient-to-br from-white to-gray-50 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 border border-gray-100 hover:-translate-y-2 overflow-hidden">';
            echo '<div class="h-48 sm:h-56 bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden relative">';
            echo '<img src="photos/placeholder.png" alt="Equipment" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">';
            echo '<div class="absolute top-3 right-3 sm:top-4 sm:right-4 bg-black/70 text-white px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium">Available Soon</div>';
            echo '</div>';
            echo '<div class="p-6 sm:p-8">';
            echo '<h4 class="font-bold text-xl sm:text-2xl mb-2 sm:mb-3 text-gray-800">Equipment Coming Soon</h4>';
            echo '<p class="text-sm sm:text-base text-gray-600 leading-relaxed mb-3 sm:mb-4">Please check back later for available equipment.</p>';
            echo '</div>';
            echo '</div>';
        }
        
        // Close database connection if it exists
        if (isset($conn)) {
            $conn->close();
        }
        ?>
      </div>
      
      <div class="text-center animate-on-scroll">
        <a href="login.php" class="group inline-flex items-center gap-3 bg-gradient-to-r from-blue-900 to-blue-700 text-white px-8 sm:px-10 py-3 sm:py-4 rounded-xl hover:from-blue-800 hover:to-blue-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 font-semibold text-base sm:text-lg">
          <i data-feather="eye" class="w-4 h-4 sm:w-5 sm:h-5 group-hover:scale-110 transition-transform duration-300"></i>
          View More Equipment
          <i data-feather="arrow-right" class="w-4 h-4 sm:w-5 sm:h-5 group-hover:translate-x-1 transition-transform duration-300"></i>
        </a>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact" class="py-16 sm:py-20 lg:py-24 bg-gradient-to-br from-gray-50 to-blue-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center animate-on-scroll">
        <h3 class="text-3xl sm:text-4xl lg:text-5xl font-bold bg-gradient-to-r from-blue-900 to-blue-700 bg-clip-text text-transparent mb-6 sm:mb-8">Contact Us</h3>
        <p class="text-base sm:text-lg lg:text-xl text-gray-600 mb-8 sm:mb-12 max-w-2xl mx-auto px-4">For inquiries and assistance, reach us through any of the following channels</p>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 mb-6 sm:mb-8">
          <div class="glass-effect bg-white/70 backdrop-blur-sm p-6 sm:p-8 rounded-3xl shadow-xl border border-white/20">
            <div class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-4 sm:mb-6 mx-auto">
              <i data-feather="map-pin" class="w-7 h-7 sm:w-8 sm:h-8 text-white"></i>
            </div>
            <h4 class="font-bold text-lg sm:text-xl mb-2 sm:mb-3 text-gray-800">Visit Us</h4>
            <p class="text-sm sm:text-base text-gray-600">Barangay Alabang Hall</p>
          </div>
          
          <div class="glass-effect bg-white/70 backdrop-blur-sm p-6 sm:p-8 rounded-3xl shadow-xl border border-white/20">
            <div class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mb-4 sm:mb-6 mx-auto">
              <i data-feather="mail" class="w-7 h-7 sm:w-8 sm:h-8 text-white"></i>
            </div>
            <h4 class="font-bold text-lg sm:text-xl mb-2 sm:mb-3 text-gray-800">Email Us</h4>
            <a href="mailto:barangay@alabang.gov.ph" class="text-sm sm:text-base text-blue-700 hover:text-blue-800 transition-colors duration-300 break-all">barangay@alabang.gov.ph</a>
          </div>
          
          <div class="glass-effect bg-white/70 backdrop-blur-sm p-6 sm:p-8 rounded-3xl shadow-xl border border-white/20 sm:col-span-2 lg:col-span-1">
            <div class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mb-4 sm:mb-6 mx-auto">
              <i data-feather="phone" class="w-7 h-7 sm:w-8 sm:h-8 text-white"></i>
            </div>
            <h4 class="font-bold text-lg sm:text-xl mb-2 sm:mb-3 text-gray-800">Call Us</h4>
            <p class="text-sm sm:text-base text-gray-600">(02) 1234-5678</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-gradient-to-r from-blue-900 to-blue-800 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
      <div class="flex flex-col md:flex-row justify-between items-center gap-6 md:gap-0">
        <div class="flex items-center space-x-3 sm:space-x-4">
          <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-400 to-blue-500 rounded-xl flex items-center justify-center flex-shrink-0">
            <i data-feather="home" class="w-5 h-5 sm:w-6 sm:h-6 text-white"></i>
          </div>
          <div>
            <h1 class="text-lg sm:text-xl font-bold">Barangay Alabang</h1>
            <p class="text-xs sm:text-sm text-blue-200">E-Borrow System</p>
          </div>
        </div>
        <div class="text-center md:text-right">
          <p class="text-sm sm:text-base text-blue-200">&copy; 2025 Barangay Alabang | E-Borrow System</p>
          <p class="text-xs sm:text-sm text-blue-300 mt-1">Empowering communities through accessible equipment sharing</p>
        </div>
      </div>
    </div>
  </footer>

  <script>
    // Initialize Feather icons
    feather.replace();
    
    // Mobile menu toggle with smooth animation
    const menuBtn = document.getElementById('menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const menuIcon = document.getElementById('menu-icon');
    const closeIcon = document.getElementById('close-icon');
    
    menuBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('active');
      menuIcon.classList.toggle('hidden');
      closeIcon.classList.toggle('hidden');
    });

    // Close mobile menu when clicking on a link
    const mobileLinks = mobileMenu.querySelectorAll('a');
    mobileLinks.forEach(link => {
      link.addEventListener('click', () => {
        mobileMenu.classList.remove('active');
        menuIcon.classList.remove('hidden');
        closeIcon.classList.add('hidden');
      });
    });
    
    // Smooth scrolling for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          const offset = 64; // Account for fixed navbar height
          const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
          window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
          });
        }
      });
    });
    
    // Animate elements on scroll
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
        }
      });
    }, observerOptions);
    
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
      observer.observe(el);
    });
    
    // Add navbar background on scroll with smooth transition
    let lastScroll = 0;
    window.addEventListener('scroll', () => {
      const nav = document.querySelector('nav');
      const currentScroll = window.pageYOffset;
      
      if (currentScroll > 50) {
        nav.classList.add('shadow-xl');
      } else {
        nav.classList.remove('shadow-xl');
      }
      
      lastScroll = currentScroll;
    });

    // Prevent scroll when mobile menu is open
    const body = document.body;
    const lockScroll = () => body.style.overflow = 'hidden';
    const unlockScroll = () => body.style.overflow = '';
    
    // Re-initialize feather icons after any dynamic content changes
    setTimeout(() => {
      feather.replace();
    }, 100);

    // Image Carousel Auto-Change
    const carouselImages = document.querySelectorAll('.carousel-image');
    const carouselDots = document.querySelectorAll('.carousel-dot');
    let currentImageIndex = 0;

    function changeImage() {
      // Hide current image
      carouselImages[currentImageIndex].style.opacity = '0';
      carouselDots[currentImageIndex].classList.remove('bg-white');
      carouselDots[currentImageIndex].classList.add('bg-white/50');
      
      // Move to next image
      currentImageIndex = (currentImageIndex + 1) % carouselImages.length;
      
      // Show next image
      carouselImages[currentImageIndex].style.opacity = '1';
      carouselDots[currentImageIndex].classList.remove('bg-white/50');
      carouselDots[currentImageIndex].classList.add('bg-white');
    }

    // Change image every 2 seconds
    setInterval(changeImage, 2000);
  </script>

</body>
</html>