<?php
require 'includes/functions.php';

session_start();

if(!isset($_SESSION['loggedin'])){
    header('Location: index.php');
    exit();
}

if(preg_match("/^[0-9]+$/", $_GET['pid'])){
    if(addDownvote($_GET['pid'], $_SESSION['email'])){
        if(updateVotes($_GET['pid'])){
            if(isset($_COOKIE['cookie'])){
                for($i = 0; $i < 5; $i++){
                    if($_COOKIE['cookie'][$i] == $_GET['pid']){ 
                        setcookie('cookie['.$i.']', null, time() - 3600);
                        header('Location: index.php');
                        exit(); 
                    }
                }
            }
        }
    }
}

header('Location: index.php');
exit(); 


