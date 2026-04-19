<?php
/**
 * SoleMate - Footer Template
 * Site footer with copyright, links, and scripts
 */

// Get site settings from database (if available)
$conn = getConnection();
$settings = [];
if ($conn) {
    $stmt = $conn->query("SELECT setting_key, setting_value FROM site_settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

$footer_text = $settings['footer_text'] ?? '© 2026 SoleMate. All rights reserved.';
$site_name = $settings['site_name'] ?? 'SoleMate';
?>

        </main> <!-- Close main-content from header.php -->

        <!-- ============================================ -->
        <!-- FOOTER SECTION -->
        <!-- ============================================ -->
        <footer class="main-footer">
            <div class="container">
                <div class="footer-grid">
                    <!-- Column 1: Company Info -->
                    <div class="footer-section">
                        <div class="footer-logo">
                            <span class="logo-text"><?php echo htmlspecialchars($site_name); ?></span>
                        </div>
                        <p class="footer-description">
                            Premium shoes for every step. Quality footwear from top brands at affordable prices. 
                            Your perfect fit starts here.
                        </p>
                        <div class="social-links">
                            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" aria-label="Pinterest"><i class="fab fa-pinterest-p"></i></a>
                            <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>

                    <!-- Column 2: Quick Links -->
                    <div class="footer-section">
                        <h4>Quick Links</h4>
                        <ul>
                            <li><a href="/pages/dynamic/products.php">Shop All</a></li>
                            <li><a href="/pages/static/about.php">About Us</a></li>
                            <li><a href="/pages/static/contact.php">Contact Us</a></li>
                            <li><a href="/pages/static/size-guide.php">Size Guide</a></li>
                            <li><a href="/user/dashboard.php">My Account</a></li>
                        </ul>
                    </div>

                    <!-- Column 3: Customer Service -->
                    <div class="footer-section">
                        <h4>Customer Service</h4>
                        <ul>
                            <li><a href="/pages/static/shipping.php">Shipping Information</a></li>
                            <li><a href="/pages/static/privacy.php">Privacy Policy</a></li>
                            <li><a href="/pages/static/returns.php">Returns & Exchanges</a></li>
                            <li><a href="/pages/static/faq.php">FAQ</a></li>
                            <li><a href="/pages/static/terms.php">Terms & Conditions</a></li>
                        </ul>
                    </div>

                    <!-- Column 4: Contact & Newsletter -->
                    <div class="footer-section">
                        <h4>Contact Us</h4>
                        <ul class="contact-info-list">
                            <li>
                                <i class="fas fa-map-marker-alt"></i>
                                <span>123 Main Street, Windsor, ON N9A 1A1</span>
                            </li>
                            <li>
                                <i class="fas fa-phone"></i>
                                <span><?php echo htmlspecialchars($settings['contact_phone'] ?? '1-800-555-SHOE'); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-envelope"></i>
                                <span><?php echo htmlspecialchars($settings['contact_email'] ?? 'support@solemate.com'); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-clock"></i>
                                <span>Mon-Fri: 9AM - 6PM EST</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Newsletter Signup -->
                <div class="footer-newsletter">
                    <div class="newsletter-wrapper">
                        <div class="newsletter-text">
                            <h4>Subscribe to Our Newsletter</h4>
                            <p>Get exclusive offers, new arrivals, and style tips straight to your inbox</p>
                        </div>
                        <form class="newsletter-form" method="POST" action="/subscribe.php" id="footerNewsletterForm">
                            <div class="newsletter-input-group">
                                <input type="email" name="email" placeholder="Enter your email address" required>
                                <button type="submit">Subscribe <i class="fas fa-paper-plane"></i></button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Footer Bottom -->
                <div class="footer-bottom">
                    <div class="footer-bottom-content">
                        <div class="copyright">
                            <p><?php echo htmlspecialchars($footer_text); ?></p>
                        </div>
                        <div class="payment-methods">
                            <i class="fab fa-cc-visa" title="Visa"></i>
                            <i class="fab fa-cc-mastercard" title="Mastercard"></i>
                            <i class="fab fa-cc-amex" title="American Express"></i>
                            <i class="fab fa-cc-paypal" title="PayPal"></i>
                            <i class="fab fa-cc-apple-pay" title="Apple Pay"></i>
                            <i class="fab fa-google-pay" title="Google Pay"></i>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <!-- ============================================ -->
        <!-- BACK TO TOP BUTTON -->
        <!-- ============================================ -->
        <button id="backToTop" class="back-to-top" aria-label="Back to top">
            <i class="fas fa-chevron-up"></i>
        </button>

        <!-- ============================================ -->
        <!-- SCRIPTS -->
        <!-- ============================================ -->
        <script src="/assets/js/main.js"></script>
        
        <script>
            // ============================================
            // Back to Top Button
            // ============================================
            const backToTopButton = document.getElementById('backToTop');
            
            if (backToTopButton) {
                window.addEventListener('scroll', function() {
                    if (window.pageYOffset > 300) {
                        backToTopButton.classList.add('show');
                    } else {
                        backToTopButton.classList.remove('show');
                    }
                });
                
                backToTopButton.addEventListener('click', function() {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }
            
            // ============================================
            // Newsletter Form Submission (AJAX)
            // ============================================
            const newsletterForm = document.getElementById('footerNewsletterForm');
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    
                    fetch('/api/newsletter-api.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Successfully subscribed!', 'success');
                            newsletterForm.reset();
                        } else {
                            showNotification(data.message || 'Subscription failed', 'error');
                        }
                    })
                    .catch(error => {
                        showNotification('An error occurred', 'error');
                    });
                });
            }
            
            // ============================================
            // Show Notification Function
            // ============================================
            function showNotification(message, type) {
                const notification = document.createElement('div');
                notification.className = `notification notification-${type}`;
                notification.innerHTML = `
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    <span>${message}</span>
                `;
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.classList.add('show');
                }, 100);
                
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 3000);
            }
            
            // ============================================
            // Update Cart Count (from session/localStorage)
            // ============================================
            function updateCartCount(count) {
                const cartBadge = document.getElementById('cartCount');
                if (cartBadge) {
                    cartBadge.textContent = count;
                    if (count === 0 || !count) {
                        cartBadge.style.display = 'none';
                    } else {
                        cartBadge.style.display = 'inline-block';
                    }
                }
            }
            
            // ============================================
            // Fetch current cart count on page load
            // ============================================
            function fetchCartCount() {
                fetch('/api/cart-api.php?action=getCount')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateCartCount(data.count);
                        }
                    })
                    .catch(error => console.error('Error fetching cart count:', error));
            }
            
            // Initialize cart count when page loads
            document.addEventListener('DOMContentLoaded', function() {
                fetchCartCount();
            });
        </script>

        <style>
            /* ============================================ */
            /* FOOTER STYLES */
            /* ============================================ */
            .main-footer {
                background: #0f172a;
                color: #94a3b8;
                padding-top: 60px;
                margin-top: 60px;
            }
            
            .footer-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 40px;
                padding-bottom: 50px;
                border-bottom: 1px solid #1e293b;
            }
            
            .footer-logo .logo-text {
                font-size: 24px;
                font-weight: bold;
                color: white;
                display: inline-block;
                margin-bottom: 15px;
            }
            
            .footer-description {
                margin-bottom: 20px;
                line-height: 1.6;
                color: #94a3b8;
            }
            
            .social-links {
                display: flex;
                gap: 15px;
            }
            
            .social-links a {
                width: 36px;
                height: 36px;
                background: #1e293b;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #94a3b8;
                transition: all 0.3s;
                text-decoration: none;
            }
            
            .social-links a:hover {
                background: #3b82f6;
                color: white;
                transform: translateY(-3px);
            }
            
            .footer-section h4 {
                color: white;
                font-size: 18px;
                margin-bottom: 20px;
                position: relative;
                padding-bottom: 10px;
            }
            
            .footer-section h4::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                width: 40px;
                height: 2px;
                background: #3b82f6;
            }
            
            .footer-section ul {
                list-style: none;
                padding: 0;
            }
            
            .footer-section ul li {
                margin-bottom: 12px;
            }
            
            .footer-section ul li a {
                color: #94a3b8;
                text-decoration: none;
                transition: color 0.3s;
            }
            
            .footer-section ul li a:hover {
                color: #3b82f6;
                padding-left: 5px;
            }
            
            .contact-info-list li {
                display: flex;
                gap: 12px;
                align-items: flex-start;
                margin-bottom: 15px;
            }
            
            .contact-info-list li i {
                color: #3b82f6;
                margin-top: 3px;
                width: 16px;
            }
            
            /* Newsletter Section */
            .footer-newsletter {
                padding: 40px 0;
                border-bottom: 1px solid #1e293b;
            }
            
            .newsletter-wrapper {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 30px;
            }
            
            .newsletter-text h4 {
                color: white;
                font-size: 20px;
                margin-bottom: 8px;
            }
            
            .newsletter-text p {
                color: #94a3b8;
                font-size: 14px;
            }
            
            .newsletter-input-group {
                display: flex;
                gap: 10px;
            }
            
            .newsletter-input-group input {
                padding: 12px 20px;
                width: 300px;
                border: 1px solid #334155;
                background: #1e293b;
                border-radius: 8px;
                color: white;
                outline: none;
                transition: border-color 0.3s;
            }
            
            .newsletter-input-group input:focus {
                border-color: #3b82f6;
            }
            
            .newsletter-input-group input::placeholder {
                color: #64748b;
            }
            
            .newsletter-input-group button {
                padding: 12px 24px;
                background: #3b82f6;
                border: none;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                cursor: pointer;
                transition: background 0.3s;
            }
            
            .newsletter-input-group button:hover {
                background: #2563eb;
            }
            
            /* Footer Bottom */
            .footer-bottom {
                padding: 25px 0;
            }
            
            .footer-bottom-content {
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 20px;
            }
            
            .copyright p {
                font-size: 14px;
                color: #64748b;
            }
            
            .payment-methods {
                display: flex;
                gap: 15px;
                font-size: 24px;
                color: #64748b;
            }
            
            .payment-methods i {
                transition: color 0.3s;
                cursor: pointer;
            }
            
            .payment-methods i:hover {
                color: #3b82f6;
            }
            
            /* Back to Top Button */
            .back-to-top {
                position: fixed;
                bottom: 30px;
                right: 30px;
                width: 50px;
                height: 50px;
                background: #3b82f6;
                color: white;
                border: none;
                border-radius: 50%;
                cursor: pointer;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s;
                z-index: 999;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }
            
            .back-to-top.show {
                opacity: 1;
                visibility: visible;
            }
            
            .back-to-top:hover {
                background: #2563eb;
                transform: translateY(-3px);
            }
            
            /* Notification Styles */
            .notification {
                position: fixed;
                bottom: 100px;
                right: 30px;
                padding: 12px 20px;
                border-radius: 8px;
                color: white;
                display: flex;
                align-items: center;
                gap: 10px;
                transform: translateX(400px);
                transition: transform 0.3s ease;
                z-index: 1000;
                font-size: 14px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }
            
            .notification.show {
                transform: translateX(0);
            }
            
            .notification-success {
                background: #22c55e;
            }
            
            .notification-error {
                background: #ef4444;
            }
            
            .notification-info {
                background: #3b82f6;
            }
            
            /* ============================================ */
            /* RESPONSIVE FOOTER */
            /* ============================================ */
            @media (max-width: 1024px) {
                .footer-grid {
                    grid-template-columns: repeat(2, 1fr);
                    gap: 30px;
                }
            }
            
            @media (max-width: 768px) {
                .footer-grid {
                    grid-template-columns: 1fr;
                    gap: 40px;
                }
                
                .newsletter-wrapper {
                    flex-direction: column;
                    text-align: center;
                }
                
                .newsletter-text {
                    text-align: center;
                }
                
                .newsletter-input-group {
                    flex-direction: column;
                    width: 100%;
                }
                
                .newsletter-input-group input {
                    width: 100%;
                }
                
                .footer-bottom-content {
                    flex-direction: column;
                    text-align: center;
                }
                
                .payment-methods {
                    justify-content: center;
                }
                
                .back-to-top {
                    bottom: 20px;
                    right: 20px;
                    width: 40px;
                    height: 40px;
                }
            }
            
            @media (max-width: 480px) {
                .main-footer {
                    margin-top: 40px;
                    padding-top: 40px;
                }
                
                .footer-section h4::after {
                    left: 50%;
                    transform: translateX(-50%);
                }
                
                .footer-section {
                    text-align: center;
                }
                
                .contact-info-list li {
                    justify-content: center;
                }
                
                .social-links {
                    justify-content: center;
                }
            }
        </style>
    </body>
    </html>
