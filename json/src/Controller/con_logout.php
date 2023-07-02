<?php

if(isset($_SESSION['email'])) {
    session_destroy();
    echo "<script> localStorage.removeItem('email'); localStorage.removeItem('pass');</script>";

}
header('Location: index.php');
exit();
