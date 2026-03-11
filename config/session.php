<?php
    if(session_status() == PHP_SESSION_NONE){
        session_start();
    }

    function isLoggedIn(){
        return isset($_SESSION['user_id']);
    }

    function isMember(){
        return isset($_SESSION['role']) && in_array($_SESSION["role"], ["member","admin"]);
    }

    function isAdmin(){
        return isset($_SESSION['role']) && $_SESSION["role"] === "admin";
    }

    function currentUserId(){
        return $_SESSION['user_id'] ?? null;
    }
?>