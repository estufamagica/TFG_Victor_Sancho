<?php



if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = $_POST['email'];
    $password = $_POST['password'];
    $login = '';

    $API->setUsername($email);
    $API->setPassword($password);
    @$file = $API->login($email, $password);
    if ($file) { //login correcte
        $result = json_decode($file, true);
        if ($result['STATUS'] === 'OK') {
            $_SESSION['email'] = $email;
            $_SESSION['passwordl'] = $password;
            $login = 'Login correcte';
            //echo("<script>location.href = '/JsonDummy/json/index.php?';</script>");
            //require __DIR__ .'\../../index.php';
            header('Location: index.php');

        }else{ $login = 'Login incorrecte';}
    }else{ $login = 'Login incorrecte';} //login incorrecte




}

require_once __DIR__ .'/../View/v_login.php';