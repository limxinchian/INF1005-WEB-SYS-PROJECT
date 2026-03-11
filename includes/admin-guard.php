<?php
    require_once __DIR__ . '/../config/session.php';

    if(!isAdmin()){
        header("Location: /index.php");
        exit();
    }
?>