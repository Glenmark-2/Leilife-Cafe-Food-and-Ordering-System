<?php
if(session_status()===PHP_SESSION_NONE) session_start();
if($_SERVER['REQUEST_METHOD']==='POST'){
    $data=json_decode(file_get_contents('php://input'),true);
    if(is_array($data)) $_SESSION['cart']=$data;
}
