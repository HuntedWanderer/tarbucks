<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TARBUCK</title>
    <link rel="icon" href="/images/logo.webp">
  
    <link rel="stylesheet" href="/css/ss.css">
    <style>
    .main-head-content {
    gap: 30px;
    display: flex;
    align-items: center;
}
        .logo {
            width: 100px;
            height: 100px;
            margin-right: 10px;
            border-radius: 50px;
            display: flex;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: lightgreen;
            padding: 5px 20px;
            position: relative;
        }
        .main-nav {
            font-family: "Roboto", sans-serif;
            display: block;
            text-decoration: none;
            color: black;
            gap: 50px;
            text-align: right;
        }
        #title {
            font-size: 30px;
            font-weight: bold;
            text-shadow: 2px 2px 2px violet;
        }
    
        .login-btnN {
            background-color: #2ecc71;
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            border: 2px solid #27ae60;
            margin-left: auto;
        }

        .login-btnN:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        .login-btnN:active {
            transform: translateY(0);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
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