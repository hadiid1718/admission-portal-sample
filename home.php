<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quaid-i-Azam University - Admission Portal</title>
    <link rel="stylesheet" href="./assets//css/home.css">

</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                🎓 Quaid-i-Azam University
            </div>
            <ul class="nav-links">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            <div class="login-buttons">
                <a href="/adv-web-project/university_admission/student/student_login.php" rel="noopener" class="btn-nav btn-student">Student Login</a>
                <a href="/adv-web-project/university_admission/admin/admin_login.php" rel="noopener" class="btn-nav btn-admin">Admin Login</a>
            </div>
        </div>
    </nav>

    <!-- Hero Slider -->
    <section class="hero-slider" id="home">
        <div class="slide slide1 active">
            <div class="slide-content">
                <h1>Welcome to Quaid-i-Azam University</h1>
                <p>Pakistan's Premier Institution of Higher Learning and Research Excellence</p>
                <a href="./student/student_register.php" class="slide-btn">Apply for Admission</a>
            </div>
        </div>

        <div class="slide slide2">
            <div class="slide-content">
                <h1>Shape Your Future</h1>
                <p>Join Pakistan's top-ranked university and unlock endless possibilities</p>
                <a href="#about" class="slide-btn">Learn More</a>
            </div>
        </div>

        <div class="slide slide3">
            <div class="slide-content">
                <h1>Excellence in Education</h1>
                <p>Transforming lives through quality education and innovative research</p>
                <a href="#contact" class="slide-btn">Contact Us</a>
            </div>
        </div>

        <button class="slider-arrow prev" onclick="changeSlide(-1)">❮</button>
        <button class="slider-arrow next" onclick="changeSlide(1)">❯</button>

        <div class="slider-nav">
            <span class="slider-dot active" onclick="currentSlide(0)"></span>
            <span class="slider-dot" onclick="currentSlide(1)"></span>
            <span class="slider-dot" onclick="currentSlide(2)"></span>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">50+</div>
                <div class="stat-label">Years of Excellence</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">15,000+</div>
                <div class="stat-label">Students Enrolled</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">500+</div>
                <div class="stat-label">Expert Faculty</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">45+</div>
                <div class="stat-label">Programs Offered</div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section" id="about">
        <div class="container">
            <h2 class="section-title">About Quaid-i-Azam University</h2>
            <p class="section-subtitle">Established in 1967, QAU stands as a beacon of academic excellence and research innovation in Pakistan</p>

            <div class="about-content">
                <div class="about-card">
                    <div class="about-icon">🎯</div>
                    <h3>Our Mission</h3>
                    <p>To provide world-class education, foster research excellence, and contribute to national development through knowledge and innovation.</p>
                </div>

                <div class="about-card">
                    <div class="about-icon">👁️</div>
                    <h3>Our Vision</h3>
                    <p>To be recognized as a leading research university in Asia, known for academic excellence and groundbreaking discoveries.</p>
                </div>

                <div class="about-card">
                    <div class="about-icon">⭐</div>
                    <h3>Our Values</h3>
                    <p>Academic integrity, research excellence, diversity, innovation, and commitment to serving society through education.</p>
                </div>
            </div>

            <div class="about-text">
                <h2>Why Choose QAU?</h2>
                <p>Quaid-i-Azam University is Pakistan's premier public research university, offering undergraduate and postgraduate programs across diverse disciplines including Natural Sciences, Social Sciences, and Biological Sciences.</p>
                <p>Located in Islamabad, our picturesque 1,700-acre campus provides state-of-the-art facilities, world-class faculty, and a vibrant academic environment that nurtures critical thinking and innovation.</p>
                <p>Join thousands of successful alumni who have made significant contributions in academia, industry, government, and civil society both nationally and internationally.</p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact-section" id="contact">
        <div class="container">
            <h2 class="section-title">Get In Touch</h2>
            <p class="section-subtitle">Have questions? We're here to help you with your admission inquiries</p>

            <div class="contact-container">
                <div class="contact-info">
                    <h3>Contact Information</h3>
                    
                    <div class="info-item">
                        <div class="info-icon">📍</div>
                        <div class="info-text">
                            <h4>Address</h4>
                            <p>Quaid-i-Azam University<br>
                            University Road, Islamabad<br>
                            Pakistan - 45320</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"></div>
                        <div class="info-text">
                            <h4>Phone</h4>
                            <p>+92-51-90642100<br>
                            +92-51-90642101</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"></div>
                        <div class="info-text">
                            <h4>Email</h4>
                            <p>admissions@qau.edu.pk<br>
                            info@qau.edu.pk</p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"></div>
                        <div class="info-text">
                            <h4>Office Hours</h4>
                            <p>Monday - Friday: 9:00 AM - 5:00 PM<br>
                            Saturday: 9:00 AM - 1:00 PM<br>
                            Sunday: Closed</p>
                        </div>
                    </div>
                </div>

                <div class="contact-form">
                    <h3>Send us a Message</h3>
                    <?php include './config/config.php'; ?>
                    
                    <form method="POST" action="./handler/contact_handler.php">
                        <div class="form-group">
                            <label>Your Name *</label>
                            <input type="text" name="name" required>
                        </div>

                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone">
                        </div>

                        <div class="form-group">
                            <label>Subject *</label>
                            <input type="text" name="subject" required>
                        </div>

                        <div class="form-group">
                            <label>Message *</label>
                            <textarea name="message" required></textarea>
                        </div>

                        <button type="submit" name="submit_contact" class="submit-btn">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 Quaid-i-Azam University. All Rights Reserved.</p>
        <p>Empowering minds, Transforming futures</p>
        <div class="footer-links">
            <a href="student_login.php">Student Portal</a>
            <a href="admin_login.php">Admin Portal</a>
            <a href="#about">About Us</a>
            <a href="#contact">Contact</a>
        </div>
    </footer>

    <script>
        // Slider functionality
        let currentSlideIndex = 0;
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.slider-dot');

        function showSlide(index) {
            if (index >= slides.length) currentSlideIndex = 0;
            if (index < 0) currentSlideIndex = slides.length - 1;

            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));

            slides[currentSlideIndex].classList.add('active');
            dots[currentSlideIndex].classList.add('active');
        }

        function changeSlide(direction) {
            currentSlideIndex += direction;
            showSlide(currentSlideIndex);
        }

        function currentSlide(index) {
            currentSlideIndex = index;
            showSlide(currentSlideIndex);
        }

        // Auto slide every 5 seconds
        setInterval(() => {
            currentSlideIndex++;
            showSlide(currentSlideIndex);
        }, 5000);

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>