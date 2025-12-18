<style>
    /* Header Styles */
    .header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 30px;
        background-color: #fff;
        border-bottom: 1px solid #ddd;
        height: 80px;
    }
    .logo { height: 60px; width: auto; }

    /* Navigation */
    .main-head-content {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-grow: 1;
        justify-content: center;
    }
    .main-head-content a {
        text-decoration: none;
        color: #333;
        font-weight: bold;
    }
    .main-head-content a:hover { color: #028a31; }

    /* Right Side (Cart + Profile) */
    .header-right {
        display: flex;
        align-items: center;
        gap: 15px;
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
    }
    .photo-preview-small {
        width: 45px;
        height: 45px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #028a31;
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
        <a href="head.php">LOGOUT</a>
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