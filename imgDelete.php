<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["imageURL"])){
    header("location: login.php");
    exit;
}


if(isset($_SESSION["imageURL"])){
    $imageURL= $_SESSION["imageURL"];
    // exit;
    if (file_exists($imageURL)) 
        {
        unlink($imageURL);
        echo "File Successfully Delete."; 
        unset($_SESSION["imageURL"]);
        header("location: admin.php");
            exit();
        }
        else
        {
        echo "File does not exists"; 
        }
}


?>