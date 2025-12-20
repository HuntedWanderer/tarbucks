<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TARBUCK</title>
    <link rel="icon" href="logo.webp">
  
    <link rel="stylesheet" href="/css/ss.css">
    <style>



.header {
    display: flex;
    align-items: center;
    justify-content: space-between; 
    background-color: lightgreen;
    padding: 10px 15px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    position: relative;
    z-index: 100;
}


.logo {
    width: 60px;        
    height: 60px;
    border-radius: 50%; 
    object-fit: cover;
    flex-shrink: 0;    
    margin-right: 15px; 
}


.main-head-content {
    display: flex;
    align-items: center;
    flex-grow: 1; 
    gap: 20px;    
}

#title {
    font-family: 'Roboto', sans-serif;
    font-size: 22px;
    font-weight: bold;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    margin: 0;
    line-height: 1;
    white-space: nowrap; 
}


.main-nav {
    text-decoration: none;
    color: #333;
    font-weight: bold;
    font-size: 14px;
    font-family: "Roboto", sans-serif;
    transition: color 0.3s;
}

.main-nav:hover {
    color: white;
}


.login-btnN {
    background-color: #2ecc71;
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: bold;
    font-size: 13px;
    white-space: nowrap;
    border: 2px solid #27ae60;
    transition: all 0.3s ease;
    margin-left: 10px; 
}

.login-btnN:hover {
    background-color: #27ae60;
    transform: translateY(-2px);
}


@media (max-width: 768px) {
    

    .main-nav {
        display: none; 
    }

    
    #title {
        font-size: 18px; 
    }
    
    
    .logo {
        width: 50px;
        height: 50px;
        margin-right: 10px;
    }

    
    .login-btnN {
        padding: 6px 12px;
        font-size: 12px;
    }
}
     
    </style>
</head>
<body>

<header class="header">
    <img class="logo" src="logo.webp" alt="Tarbuck Coffee Logo">
    <div class="main-head-content">
        <h1 id="title">Tarbuck Coffee </h1>
        <a href="index.php" class="main-nav">
            <span>ABOUT US</span>
        </a>
        <a href="mainpage_menu.php" class="main-nav">
            <span>MENU</span>
        </a>
        <a href="findStore.php" class="main-nav">
            <span>FIND STORE</span>
        </a>
    </div>

    <a href="head.php" class="login-btnN">LOGIN HERE</a>
</header>
</body>
</html>