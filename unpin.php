<?php
require 'includes/functions.php';

session_start();

if(!isset($_SESSION['loggedin'])){
    header('Location: index.php');
    exit();
}

//change to preg_match
if(filterID($_GET['pin'])){
    removePin($_GET['pin'], $_SESSION['email']);
}

header('Location: index.php');
exit();