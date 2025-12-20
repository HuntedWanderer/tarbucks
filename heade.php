<style>
 * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f5f5f5;
    }

    /* =========================================
       2. HEADER CONTAINER (Green Theme)
       ========================================= */
    .header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        /* TARGET STYLE: Light Green Background */
        background-color: lightgreen; 
        padding: 10px 15px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        position: relative;
        z-index: 100;
        height: 80px; /* Fixed height for consistency */
    }

    /* =========================================
       3. LOGO (Circular)
       ========================================= */
    .logo {
        width: 60px;
        height: 60px;
        border-radius: 50%; /* Circular */
        object-fit: cover;
        flex-shrink: 0;
        margin-right: 15px;
        border: 2px solid white; /* Adds a nice pop on green */
    }

    /* =========================================
       4. NAVIGATION (Title + Links)
       ========================================= */
    .main-head-content {
        display: flex;
        align-items: center;
        flex-grow: 1;
        gap: 20px;
        overflow: hidden; /* Prevents overflow issues */
    }

    /* Title */
    .main-head-content h1 {
        font-size: 22px;
        margin: 0;
        color: #004d00; /* Dark Green Text */
        white-space: nowrap;
        font-weight: bold;
        text-shadow: 1px 1px 2px rgba(255,255,255,0.3);
    }

    /* Links */
    .main-head-content a {
        text-decoration: none;
        color: #333;
        font-weight: bold;
        font-size: 15px;
        transition: color 0.3s;
        white-space: nowrap;
        padding: 5px;
    }

    .main-head-content a:hover {
        color: white; /* Turn white on hover like target */
    }

    /* =========================================
       5. RIGHT SIDE (Cart + Profile)
       ========================================= */
    .header-right {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-left: 10px;
        flex-shrink: 0;
    }

    /* Cart Button (Styled to match your theme) */
    #viewcart-btn {
        background-color: #2e7d32; /* Dark Green Button */
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
        text-decoration: none;
        font-weight: bold;
        font-size: 13px;
        white-space: nowrap;
        border: 1px solid #1b5e20;
        transition: all 0.3s ease;
    }

    #viewcart-btn:hover {
        background-color: #1b5e20;
        transform: translateY(-2px);
    }

    /* Profile Picture */
    .photo-preview-small {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid white;
    }

    /* =========================================
       6. MOBILE RESPONSIVENESS (The Fix)
       ========================================= */
    @media (max-width: 768px) {
        
        .header {
            padding: 8px 10px; /* Compact padding */
            height: auto;
        }

        /* 1. HIDE THE TITLE (To save space) */
        .main-head-content h1 {
            display: none;
        }

        /* 2. MAKE NAV SCROLLABLE & COMPACT */
        .main-head-content {
            gap: 5px; /* Tight gap */
            overflow-x: auto; /* Scroll horizontally */
            padding-bottom: 0;
            
            /* Hide scrollbars */
            scrollbar-width: none; 
            -ms-overflow-style: none;
        }
        .main-head-content::-webkit-scrollbar { 
            display: none; 
        }

        /* 3. STYLE LINKS AS "PILLS" (Like the target) */
        .main-head-content a {
            font-size: 12px;
            padding: 5px 10px;
            background: rgba(255, 255, 255, 0.4); /* Semi-transparent pill */
            border-radius: 12px;
            color: #004d00;
        }

        /* 4. RESIZE ELEMENTS FOR MOBILE */
        .logo {
            width: 40px;
            height: 40px;
            margin-right: 8px;
        }

        /* Cart Button - Mobile Icon Only or Compact Text */
        #viewcart-btn {
            padding: 6px 10px;
            font-size: 11px;
        }

        .photo-preview-small {
            width: 35px;
            height: 35px;
        }
    }
</style>

<header class="header">
    <img class="logo" src="logo.webp" alt="Tarbuck Logo">

    <nav class="main-head-content">
        <a href="member.php">
            <h1 style="margin:0; font-size: 20px;">Tarbuck Coffee</h1>
        </a>
        <a href="member.php">PRODUCT</a>
        <a href="orderhistory.php">ORDER HISTORY</a>
        <a href="logout.php">LOGOUT</a>
    </nav>

    <div class="header-right">
        <a href="cart.php" id="viewcart-btn"> 
            🛒 Cart (<?= $cart_count ?? 0 ?>) 
        </a> 
        
        <a href="profileMember.php">
            <img src="view.php?image=<?= encode($user['photo']) ?>" 
                 alt="Profile" 
                 class="photo-preview-small">
        </a>
    </div>
</header>
<div id="info"><?= temp('info') ?></div>