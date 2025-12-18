<?php
ob_start();
require 'main_page.php';


$title = 'TARBUCK';
include 'basic_searching.php';

include 'main_content.php';

include 'test_product.php';
?>

<?php
include 'foot.php';
ob_end_flush(); 
?>
