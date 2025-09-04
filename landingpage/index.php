<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-Borrow System | Barangay Alabang</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans">

  <!-- Navbar -->
  <nav class="flex justify-between items-center p-4 bg-white shadow fixed w-full top-0 z-20">
    <div>
      <h1 class="text-xl font-bold text-blue-900">Barangay Alabang</h1>
      <p class="text-sm text-gray-500">E-Borrow System</p>
    </div>
    <div class="space-x-6">
      <a href="#home" class="hover:text-blue-700">Home</a>
      <a href="#services" class="hover:text-blue-700">Services</a>
      <a href="#equipment" class="hover:text-blue-700">Equipment</a>
      <a href="#contact" class="hover:text-blue-700">Contact</a>
      <a href="login.php" class="bg-blue-900 text-white px-4 py-2 rounded hover:bg-blue-700">Request Borrow</a>
    </div>
  </nav>

  <!-- Hero Section -->
  <section id="home" class="relative h-screen flex items-center justify-center text-center text-white">
    <!-- Background with Overlay -->
    <div class="absolute inset-0">
<img src="../photos/niggapic.jpg" alt="Barangay Hall" class="w-full h-full object-cover">
      <div class="absolute inset-0 bg-blue-900 opacity-70"></div>
    </div>
    <!-- Content -->
    <div class="relative z-10 max-w-2xl px-4">
      <h2 class="text-4xl md:text-5xl font-bold">E-Borrow System<br><span class="text-green-400">for Barangay Alabang</span></h2>
      <p class="mt-4 text-lg">Borrow equipment for your community needs with ease — tables, chairs, sound systems, and more!</p>
      <div class="mt-6 flex flex-col md:flex-row justify-center gap-4">
        <a href="#equipment" class="bg-blue-700 px-6 py-3 rounded text-white font-semibold hover:bg-blue-600">Browse Equipment</a>
        <a href="#services" class="border border-white px-6 py-3 rounded font-semibold hover:bg-white hover:text-blue-900">Learn More</a>
      </div>
    </div>
  </section>

  <!-- Services Section -->
  <section id="services" class="py-20 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 text-center">
      <h3 class="text-3xl font-bold text-blue-900 mb-12">Our Services</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white p-6 rounded-lg shadow">
          <h4 class="text-xl font-semibold mb-2">Equipment Lending</h4>
          <p>Borrow community equipment like tables, chairs, and sound systems for your events.</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
          <h4 class="text-xl font-semibold mb-2">Easy Requests</h4>
          <p>Submit borrow requests online and track their approval status anytime.</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
          <h4 class="text-xl font-semibold mb-2">Community Support</h4>
          <p>Ensuring fair access to barangay-owned equipment for all residents.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Equipment Section -->
  <section id="equipment" class="py-20">
    <div class="max-w-6xl mx-auto px-4 text-center">
      <h3 class="text-3xl font-bold text-blue-900 mb-12">Available Equipment</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white rounded-lg shadow p-6">
          <h4 class="font-semibold text-lg mb-2">Portable Projector</h4>
          <p>HD projector with HDMI cable</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <h4 class="font-semibold text-lg mb-2">PA System</h4>
          <p>Portable PA system, mic included</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <h4 class="font-semibold text-lg mb-2">Folding Table</h4>
          <p>1.8m folding table for community events</p>
        </div>
      </div>
      <a href="browse_equipment.php" class="mt-8 inline-block bg-blue-900 text-white px-6 py-3 rounded hover:bg-blue-700">View More</a>
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact" class="py-20 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 text-center">
      <h3 class="text-3xl font-bold text-blue-900 mb-8">Contact Us</h3>
      <p class="mb-6">For inquiries and assistance, reach us at:</p>
      <p class="font-semibold">Barangay Alabang Hall</p>
      <p>Email: <a href="mailto:barangay@alabang.gov.ph" class="text-blue-700 hover:underline">barangay@alabang.gov.ph</a></p>
      <p>Phone: (02) 1234-5678</p>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-blue-900 text-white text-center py-4">
    <p>&copy; <?php echo date("Y"); ?> Barangay Alabang | E-Borrow System</p>
  </footer>

</body>
</html>
