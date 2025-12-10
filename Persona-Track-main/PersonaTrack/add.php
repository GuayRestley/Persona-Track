<?php
// homepage.php - Public Homepage
session_start();

// Redirect logged-in users to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PersonaTrack - Barangay Profiling System</title>
    <link rel="stylesheet" href="CSS/homepage.css">

</head>
<body>

    <!-- Header -->
    <header>
        <nav>
            <div class="logo">
                <div class="logo-icon">🏘️</div>
                <div>
                    <div style="font-size: 1.5rem;">PersonaTrack</div>
                    <div style="font-size: 0.8rem; opacity: 0.9;">Barangay Profiling System</div>
                </div>
            </div>

            <ul class="nav-links">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#features">Services</a></li>
                <li><a href="#benefits">Benefits</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>

            <div style="display: flex; gap: 1rem;">
                <a href="login.php" class="btn btn-outline">Login</a>

                <!-- ❌ removed public sign-up
                     ✔ replaced with a safe call-to-action -->
                <a href="#contact" class="btn btn-primary">Contact Us</a>
            </div>

        </nav>
    </header>

    <!-- (YOUR HERO SECTION — unchanged) -->
    <!-- (YOUR INFO BANNER — unchanged) -->
    <!-- (YOUR ABOUT SECTION — unchanged) -->
    <!-- (YOUR FEATURES SECTION — unchanged) -->
    <!-- (YOUR BENEFITS SECTION — unchanged) -->
    <!-- (YOUR HOW IT WORKS SECTION — unchanged) -->
    <!-- (YOUR STATISTICS SECTION — unchanged) -->

    <!-- Fix CTA section -->
    <section class="cta-section">
        <h2>Ready to Get Started?</h2>
        <p>Barangay officials may request an official PersonaTrack account from your Barangay Administration Office.</p>
        <div class="cta-buttons">
            <!-- Removed "registration.php" because public registration is not allowed -->
            <a href="login.php" class="btn btn-primary btn-large">Official Login</a>
            <a href="#contact" class="btn btn-outline btn-large">Contact Barangay Office</a>
        </div>
    </section>

    <!-- Footer (unchanged except removed unnecessary links) -->
    <footer id="contact">
        <div class="footer-content">

            <div class="footer-section">
                <h3>PersonaTrack</h3>
                <p>Efficient, Secure, and Transparent Barangay Profiling System</p>
                <p style="margin-top: 1rem; font-size: 0.9rem;">Building stronger communities through technology.</p>
            </div>

            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#features">Services</a></li>
                    <li><a href="#benefits">Benefits</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </div>

            <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>Welcome to PersonaTrack</h1>
            <p class="subtitle">The Official Barangay Resident Profiling and Records System</p>
            <p>Managing resident data securely, efficiently, and transparently for better community services.</p>
            <div class="hero-buttons">
                <a href="login.php" class="btn btn-primary btn-large">Get Started</a>
                <a href="#features" class="btn btn-outline btn-large">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Info Banner -->
    <section class="info-banner">
        <h2>🏘️ Empowering Barangay Communities Through Digital Innovation</h2>
        <p>PersonaTrack streamlines resident management, improves service delivery, and promotes transparency in barangay operations. Join hundreds of barangays already using our system!</p>
    </section>

    <!-- About Section -->
    <section class="about" id="about">
        <h2>About PersonaTrack</h2>
        <div class="about-content">
            <p style="margin-bottom: 1.5rem;">
                <strong>PersonaTrack</strong> is a comprehensive barangay profiling system designed to help local government units maintain accurate, organized, and accessible resident records.
            </p>
            <p style="margin-bottom: 1.5rem;">
                Our system ensures fast, secure, and organized data management, enabling barangay officials to deliver better community services, generate accurate reports, and maintain transparency in all operations.
            </p>
            <p>
                With PersonaTrack, we're building stronger, more connected communities through technology.
            </p>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <h2>Our Services</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">📋</div>
                <h3>Resident Records</h3>
                <p>Comprehensive profiling of all barangay residents with complete demographic and contact information.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🏠</div>
                <h3>Household Management</h3>
                <p>Organized household data by purok, street, and zone for efficient community mapping.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Reports & Analytics</h3>
                <p>Generate population reports by age, gender, residency status, and other demographic factors.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3>Secure Access</h3>
                <p>Role-based access control ensuring data privacy and security for all residents.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📝</div>
                <h3>Activity Logging</h3>
                <p>Complete transparency with detailed activity logs of all system operations.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📄</div>
                <h3>Document Processing</h3>
                <p>Streamlined processing of barangay clearances, certifications, and official documents.</p>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits" id="benefits">
        <h2>Why Choose PersonaTrack?</h2>
        <div class="benefits-grid">
            <div class="benefit-item">
                <div class="benefit-icon">⚡</div>
                <div class="benefit-content">
                    <h3>Fast & Efficient</h3>
                    <p>Reduce processing time for barangay documents from hours to minutes with automated workflows.</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">🔐</div>
                <div class="benefit-content">
                    <h3>Secure & Private</h3>
                    <p>Bank-level security with encrypted data storage and role-based access controls.</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">📱</div>
                <div class="benefit-content">
                    <h3>Accessible Anywhere</h3>
                    <p>Access the system from any device - desktop, tablet, or smartphone with internet connection.</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">💰</div>
                <div class="benefit-content">
                    <h3>Cost-Effective</h3>
                    <p>Eliminate paper-based systems and reduce operational costs with digital record-keeping.</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">📈</div>
                <div class="benefit-content">
                    <h3>Data-Driven Insights</h3>
                    <p>Make better decisions with real-time statistics and comprehensive population analytics.</p>
                </div>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">✅</div>
                <div class="benefit-content">
                    <h3>Easy to Use</h3>
                    <p>Intuitive interface designed for barangay officials with minimal training required.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works">
        <h2>How It Works</h2>
        <div class="steps-container">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3>Register Account</h3>
                <p>Barangay officials create secure accounts with role-based permissions.</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <h3>Add Residents</h3>
                <p>Input resident information including demographics, contact details, and address.</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <h3>Manage Records</h3>
                <p>Update, search, and organize resident data with powerful filtering tools.</p>
            </div>
            <div class="step-card">
                <div class="step-number">4</div>
                <h3>Generate Reports</h3>
                <p>Create comprehensive reports and analytics for better decision-making.</p>
            </div>
        </div>
    </section>

    <!-- Statistics -->
    <section class="statistics">
        <h2>Trusted by Communities</h2>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-number">50+</div>
                <div class="stat-label">Barangays</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">100K+</div>
                <div class="stat-label">Residents Profiled</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">500+</div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">99.9%</div>
                <div class="stat-label">Uptime</div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <h2>Ready to Get Started?</h2>
        <p>Join PersonaTrack today and transform how your barangay manages resident information. Get started in minutes!</p>
        <div class="cta-buttons">
            <a href="registration.php" class="btn btn-primary btn-large">Create Account</a>
            <a href="login.php" class="btn btn-outline btn-large">Sign In</a>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
        <div class="footer-content">
            <div class="footer-section">
                <h3>PersonaTrack</h3>
                <p>Efficient, Secure, and Transparent Barangay Profiling System</p>
                <p style="margin-top: 1rem; font-size: 0.9rem;">Building stronger communities through technology.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About Us</a></li>
                    <li><a href="#features">Services</a></li>
                    <li><a href="#benefits">Benefits</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Information</h3>
                <ul>
                    <li>📍 Barangay Hall, San Juan</li>
                    <li>📞 Contact: (074) XXX-XXXX</li>
                    <li>✉️ Email: info@personatrack.ph</li>
                    <li>🌐 Web: www.personatrack.ph</li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Legal</h3>
                <ul>
                    <li><a href="#privacy">Data Privacy Notice</a></li>
                    <li><a href="#terms">Terms of Service</a></li>
                    <li><a href="#policy">Privacy Policy</a></li>
                    <li><a href="#support">Support</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 PersonaTrack - Barangay Profiling System. All Rights Reserved.</p>
            <p style="margin-top: 0.5rem; font-size: 0.9rem;">Developed by: Ayeo-eo, Cedric | Guaym, Restley | Macaraeg, Jake Russell</p>
        </div>
    </footer>

</body>
</html>
