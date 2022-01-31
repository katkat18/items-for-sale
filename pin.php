<?php
require 'includes/functions.php';

session_start();

if(!isset($_SESSION['loggedin'])){
    header('Location: index.php');
    exit();
}

if(filterID($_GET['pid']) && filterID($_GET['owner'])){
    addPin($_GET['pid'], $_GET['owner'], $_SESSION['email']);
}

header('Location: index.php');
exit();

