<?php
require 'includes/functions.php';

session_start();

if(!isset($_SESSION['loggedin'])){
    header('Location: index.php');
    exit();
}

//change to preg_match
if(filterID($_GET['id'])){
    if(deleteProduct($_GET['id'], $_SESSION['email'])){
        if(isset($_COOKIE['cookie'])){
            for($i = 0; $i < 5; $i++){
                if($_COOKIE['cookie'][$i] == $_GET['id']){
                    setcookie('cookie['.$i.']', null, time() - 3600);
                    header('Location: index.php');
                    exit(); 
                }
            }
        } 
    }
}

header('Location: index.php');
exit(); 