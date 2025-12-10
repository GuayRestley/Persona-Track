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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }

        :root {
            --pink-light: #ffc2c2;
            --white: #ffffff;
            --red: #d12525;
            --purple: #9881f2;
            --pink-medium: #ff9999;
        }

        /* Header */
        header {
            background: linear-gradient(135deg, var(--red) 0%, var(--purple) 100%);
            color: white;
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--red);
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
            font-weight: 500;
        }

        .nav-links a:hover {
            color: var(--pink-light);
        }

        .btn {
            padding: 0.6rem 1.5rem;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: white;
            color: var(--red);
        }

        .btn-primary:hover {
            background: var(--pink-light);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid white;
            color: white;
        }

        .btn-outline:hover {
            background: white;
            color: var(--red);
        }

        /* Hero Section */
        .hero {
            margin-top: 80px;
            background: linear-gradient(135deg, var(--pink-light) 0%, var(--pink-medium) 50%, var(--purple) 100%);
            padding: 6rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 3rem;
            color: white;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .hero .subtitle {
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
            font-weight: 500;
        }

        .hero p {
            font-size: 1.2rem;
            color: white;
            margin-bottom: 2rem;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-large {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
        }

        /* Info Banner */
        .info-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }

        .info-banner h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .info-banner p {
            font-size: 1.1rem;
            max-width: 800px;
            margin: 0 auto;
        }

        /* About Section */
        .about {
            padding: 5rem 2rem;
            background: white;
            max-width: 1200px;
            margin: 0 auto;
        }

        .about h2 {
            text-align: center;
            font-size: 2.5rem;
            color: var(--red);
            margin-bottom: 2rem;
        }

        .about-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            font-size: 1.1rem;
            line-height: 1.8;
            color: #555;
        }

        /* Features Section */
        .features {
            padding: 5rem 2rem;
            background: linear-gradient(to bottom, #f8f9fa, #fff);
        }

        .features h2 {
            text-align: center;
            font-size: 2.5rem;
            color: var(--red);
            margin-bottom: 3rem;
        }

        .features-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--red), var(--purple));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }

        .feature-card h3 {
            color: var(--red);
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .feature-card p {
            color: #666;
            line-height: 1.6;
        }

        /* Benefits Section */
        .benefits {
            padding: 5rem 2rem;
            background: white;
        }

        .benefits h2 {
            text-align: center;
            font-size: 2.5rem;
            color: var(--red);
            margin-bottom: 3rem;
        }

        .benefits-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .benefit-item {
            display: flex;
            gap: 1.5rem;
            padding: 2rem;
            background: #f8f9fa;
            border-radius: 15px;
            transition: all 0.3s;
        }

        .benefit-item:hover {
            background: var(--pink-light);
            transform: translateX(10px);
        }

        .benefit-icon {
            font-size: 3rem;
            flex-shrink: 0;
        }

        .benefit-content h3 {
            color: var(--red);
            margin-bottom: 0.5rem;
        }

        .benefit-content p {
            color: #666;
        }

        /* How It Works Section */
        .how-it-works {
            padding: 5rem 2rem;
            background: linear-gradient(135deg, var(--pink-light) 0%, var(--pink-medium) 50%, var(--purple) 100%);
        }

        .how-it-works h2 {
            text-align: center;
            font-size: 2.5rem;
            color: white;
            margin-bottom: 3rem;
        }

        .steps-container {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .step-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            position: relative;
        }

        .step-number {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--red), var(--purple));
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0 auto 1rem;
        }

        .step-card h3 {
            color: var(--red);
            margin-bottom: 1rem;
        }

        .step-card p {
            color: #666;
        }

        /* Statistics Section */
        .statistics {
            padding: 5rem 2rem;
            background: white;
        }

        .statistics h2 {
            text-align: center;
            font-size: 2.5rem;
            color: var(--red);
            margin-bottom: 3rem;
        }

        .stats-grid {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }

        .stat-box {
            text-align: center;
            padding: 2rem;
            background: linear-gradient(135deg, var(--pink-light), var(--pink-medium));
            border-radius: 15px;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            color: var(--red);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.1rem;
            color: #555;
        }

        /* CTA Section */
        .cta-section {
            padding: 5rem 2rem;
            background: linear-gradient(135deg, var(--red) 0%, var(--purple) 100%);
            color: white;
            text-align: center;
        }

        .cta-section h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .cta-section p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Footer */
        footer {
            background: linear-gradient(135deg, var(--red), var(--purple));
            color: white;
            padding: 3rem 2rem 1rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.5rem;
        }

        .footer-section a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-section a:hover {
            color: var(--pink-light);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.2);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .hero .subtitle {
                font-size: 1.2rem;
            }

            .features-grid,
            .benefits-grid,
            .steps-container,
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
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

            <div class="hero-buttons">
                <a href="login.php" class="btn btn-primary btn-large">Get Started</a>
            </div>
        </nav>
    </header>

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