

<?php

include 'header.php';
require 'lib/SimpleImage.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Truth Matters - TARBUCK</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lora:wght@400;500&display=swap" rel="stylesheet">
    <style>

        body, h1, p {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body.cyc-body {
            font-family: 'Lora', serif; 
            background-color: #f7f7f7;
            color: #333;
            line-height: 1.8;
            padding: 20px;
        }

      
        .cyc-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

     
        .cyc-header {
            font-family: 'Playfair Display', serif; 
            font-size: 2.8rem;
            color: #006241; 
            text-align: center;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .cyc-paragraph {
            font-family: 'Lora', serif; 
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 20px;
            line-height: 1.8;
        }

        .cyc-divider {
            border: none;
            height: 2px;
            background-color: #006241;
            margin: 20px 0;
        }

        .cyc-image {
            display: block;
            margin: 20px auto;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border: 5px solid #006241; 
            max-width: 100%;
        }
    </style>
</head>
<body class="cyc-body">

<div class="cyc-container">
    <img src="view.php?image=tarbuckphoto.png" alt="Truth Matters Image" class="cyc-image">
</div>  

<div class="cyc-container">
    <h1 class="cyc-header">TRUTH MATTERS</h1>
    
    <p class="cyc-paragraph"><strong>False information has caused violence, vandalism, and even assaults on our employees in some stores. This behavior is unacceptable and must stop.</strong></p>
    <p class="cyc-paragraph"><strong>We are committed to setting the record straight and ensuring the truth is heard.</strong></p>
    <hr class="cyc-divider">
    <p class="cyc-paragraph"><strong>TARBUCK Malaysia is fully owned by a publicly listed company in Malaysia. For over25 years we have been dedicated to serving local communities by collaborating with grassroots organizations, government entities, and NGOs through targeted programs thatempower Malaysians</strong>.</p>
    <p class="cyc-paragraph"><strong>With a workforce of over 5,000 Malaysians, including individuals with disabilities, TARBUCK operates a network of 400 stores nationwide, creating opportunities and making a positive impact in every community we serve.</strong></p>
</div>

</body>

<?php
include 'foot.php';
?>
</html>