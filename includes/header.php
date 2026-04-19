<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="description" content="<?php echo $meta_description ?? 'SoleMate - Premium Shoes for Every Step'; ?>">
    <meta name="keywords" content="shoes, sneakers, boots, footwear, online store">
    <meta name="author" content="SoleMate">
    <meta name="robots" content="index, follow">
    
    <title><?php echo $page_title ?? 'SoleMate'; ?> - Premium Shoes</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/responsive.css">
    <?php
    $theme = getSiteSetting('template_theme', 'light');
    if ($theme !== 'light') {
        echo '<link rel="stylesheet" href="/assets/css/' . $theme . '.css">';
    }
    ?>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Leaflet Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
    <div class="site-wrapper">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="container">
                <div class="top-bar-content">
                    <div class="contact-info">
                        <i class="fas fa-phone"></i> <span><?php echo getSiteSetting('contact_phone', '1-800-555-SHOE'); ?></span>
                        <i class="fas fa-envelope"></i> <span><?php echo getSiteSetting('contact_email', 'support@solemate.com'); ?></span>
                    </div>
                    <div class="user-links">
                        <?php if (isLoggedIn()): ?>
                            <a href="/user/dashboard.php"><i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?></a>
                            <a href="/pages/dynamic/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            <?php if (isAdmin()): ?>
                                <a href="/admin/" class="admin-link"><i class="fas fa-cog"></i> Admin</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="/pages/dynamic/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                            <a href="/pages/dynamic/register.php"><i class="fas fa-user-plus"></i> Register</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Header -->
        <header class="main-header">
            <div class="container">
                <div class="header-content">
                    <div class="logo">
                        <a href="/">
                            <img src="/assets/images/logo.png" alt="SoleMate">
                            <span class="logo-text">SoleMate</span>
                        </a>
                    </div>
                    
                    <!-- Mobile Menu Toggle -->
                    <button class="mobile-menu-toggle" id="mobileMenuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <!-- Navigation -->
                    <nav class="main-nav" id="mainNav">
                        <ul>
                            <li><a href="/pages/dynamic/index.php">Home</a></li>
                            <li><a href="/pages/dynamic/products.php">Shop</a></li>
                            <li><a href="/pages/dynamic/products.php?category=men">Men</a></li>
                            <li><a href="/pages/dynamic/products.php?category=women">Women</a></li>
                            <li><a href="/pages/dynamic/products.php?category=kids">Kids</a></li>
                            <li><a href="/pages/static/about.php">About</a></li>
                            <li><a href="/pages/static/contact.php">Contact</a></li>
                        </ul>
                    </nav>
                    
                    <!-- Header Icons -->
                    <div class="header-icons">
                        <a href="/pages/dynamic/search.php" class="search-icon">
                            <i class="fas fa-search"></i>
                        </a>
                        <a href="/user/wishlist.php" class="wishlist-icon">
                            <i class="far fa-heart"></i>
                            <span class="badge" id="wishlistCount">0</span>
                        </a>
                        <a href="/pages/dynamic/cart.php" class="cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="badge" id="cartCount"><?php echo getCartCount(); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Mobile Menu -->
        <div class="mobile-menu" id="mobileMenu">
            <ul>
                <li><a href="/pages/dynamic/index.php">Home</a></li>
                <li><a href="/pages/dynamic/products.php">Shop All</a></li>
                <li><a href="/pages/dynamic/products.php?category=men">Men</a></li>
                <li><a href="/pages/dynamic/products.php?category=women">Women</a></li>
                <li><a href="/pages/dynamic/products.php?category=kids">Kids</a></li>
                <li><a href="/pages/dynamic/products.php?category=athletic">Athletic</a></li>
                <li><a href="/pages/dynamic/products.php?category=boots">Boots</a></li>
                <li><a href="/pages/static/about.php">About</a></li>
                <li><a href="/pages/static/contact.php">Contact</a></li>
                <li><a href="/pages/static/size-guide.php">Size Guide</a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <main class="main-content">
