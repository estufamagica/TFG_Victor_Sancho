<?php
require_once __DIR__ . '/src/Model/API/API_Wrapper.php';

session_start();
//set_include_path("M:/trabajos/Uni/Curso 5/Semestre 2/TFG/JsonDummy/json/src/Controller");
$request = $_GET['action'] ?? null;

 global $API;
 $API = new \App\Model\API\API_Wrapper('', '');


switch ($request){
    case 'homepage':
        require __DIR__ . '/src/Resources/r_homepage.php';
        break;
    case '':

        if(!isset($_SESSION['email'])){
            require __DIR__ . '/src/Resources/r_login.php';
        }else{

            require __DIR__ . '/src/Resources/r_homepage.php';
        }
        break;
    case 'login':
        require __DIR__ . '/src/Resources/r_login.php';
        break;
    case 'logout':
        require __DIR__.'/src/Resources/r_logout.php';

        break;
    default:
        http_response_code(404);
        require __DIR__ . '/src/Resources/r_404.php';
        break;
}
