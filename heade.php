<style>
    
    /* =========================================

       1. GLOBAL & RESET

       ========================================= */

    * {

        box-sizing: border-box; /* Fixes padding issues */

        margin: 0;

        padding: 0;

    }



    body {

        font-family: 'Segoe UI', sans-serif;

        background-color: #f5f5f5;

        overflow-x: hidden; /* Prevents side-scrolling on mobile */

    }



    /* =========================================

       2. HEADER CONTAINER

       ========================================= */

    .header {

        display: flex;

        align-items: center;

        justify-content: space-between;

        padding: 10px 30px;

        background-color: #fff;

        border-bottom: 1px solid #ddd;

        /* FLEX WRAP: Crucial! Allows content to create a 2nd row on mobile */

        flex-wrap: wrap; 

        position: relative;

        z-index: 100;

    }



    /* =========================================

       3. LOGO

       ========================================= */

    .logo {

        height: 60px;

        width: auto;

        /* Ensures logo is always visible */

        flex-shrink: 0; 

    }



    /* =========================================

       4. MIDDLE NAVIGATION (Title + Links)

       ========================================= */

    .main-head-content {

        display: flex;

        align-items: center;

        gap: 25px;

        flex-grow: 1;

        justify-content: center;

    }



    .main-head-content a {

        text-decoration: none;

        color: #333;

        font-weight: bold;

        font-size: 15px;

        white-space: nowrap; /* Prevents text from breaking */

        transition: color 0.3s;

        /* Padding makes links easier to tap */

        padding: 5px; 

    }



    .main-head-content a:hover {

        color: #028a31;

    }



    /* Specific style for the Title inside the nav */

    .main-head-content h1 {

        margin: 0;

        font-size: 22px;

        color: #028a31; /* Make the brand name green */

    }



    /* =========================================

       5. RIGHT SIDE (Cart + Profile)

       ========================================= */

    .header-right {

        display: flex;

        align-items: center;

        gap: 15px;

        flex-shrink: 0; /* Prevents this section from collapsing */

    }



    #viewcart-btn {

        background-color: #028a31;

        color: white;

        padding: 8px 15px;

        border-radius: 20px;

        text-decoration: none;

        font-weight: bold;

        font-size: 14px;

        white-space: nowrap;

        transition: background-color 0.3s;

        display: flex;

        align-items: center;

        gap: 5px;

    }



    #viewcart-btn:hover {

        background-color: #026b26;

    }



    .photo-preview-small {

        width: 45px;

        height: 45px;

        object-fit: cover;

        border-radius: 50%;

        border: 2px solid #028a31;

        transition: transform 0.2s;

    }

    

    .photo-preview-small:active {

        transform: scale(0.95); /* Click effect */

    }



    /* =========================================

       6. MOBILE RESPONSIVENESS (The Magic Part)

       ========================================= */

    @media (max-width: 850px) {

        .header {

            padding: 10px 15px;

            gap: 10px; /* Space between the rows */

        }



        /* ROW 1: Logo (Left) and Header Right (Right) */

        .logo {

            height: 45px; /* Smaller logo */

            order: 1;     /* Force to be first */

        }

        

        .header-right {

            order: 2;     /* Force to be second (same line as logo) */

            margin-left: auto; /* Pushes it to the far right */

        }



        /* ROW 2: Navigation (Drops down) */

        .main-head-content {

            order: 3;     /* Force to be third (new line) */

            width: 100%;  /* Take full width */

            

            /* SCROLLABLE NAV: Swipe left/right to see all links */

            overflow-x: auto; 

            justify-content: flex-start; /* Align start so scrolling works */

            padding-bottom: 5px; /* Space for scrollbar */

            border-top: 1px solid #f0f0f0; /* faint line to separate */

            padding-top: 10px;

        }



        /* Hide scrollbar for cleaner look */

        .main-head-content::-webkit-scrollbar {

            display: none; 

        }



        /* Adjust Title size on mobile */

        .main-head-content h1 {

            font-size: 18px;

            margin-right: 10px;

        }

        

        /* Make links look like buttons or pills on mobile */

        .main-head-content a:not(:first-child) {

            background-color: #f0f0f0;

            padding: 5px 12px;

            border-radius: 15px;

            font-size: 13px;

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