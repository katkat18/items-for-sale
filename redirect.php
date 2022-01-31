<?php
require 'includes/functions.php';

if(count($_POST) > 0){
    if($_GET['from'] == 'login'){
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        //need to check username to prevent mySQL injection
        if(validateEmail($email)){
            $found = findUser($email, $password);

            if($found){
                session_start();
                $_SESSION['loggedin'] = true; 
                $_SESSION['email'] = $email;
                header('Location: index.php');
                exit(); 
            }
        }

        setcookie('error_message', 'Oop! Login not found!');
        header('Location: index.php');
        exit(); 

    }elseif($_GET['from'] == 'signup'){
        //check signup
        if(validateSignup($_POST) && saveUser($_POST)){
            session_start();
            $_SESSION['loggedin'] = true;
            $_SESSION['email'] = trim($_POST['email']);
            header('Location: index.php');
            exit();
        }

        setcookie('error_message', 'Sorry! Something went wrong with signing you up. Please try again.');
        header('Location: index.php');
        exit();
    }
}

header('Location: index.php');
exit();

?>