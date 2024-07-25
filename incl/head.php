<?php
    session_start();
    if (!isset($_SESSION["iduser"])) {
        header("Location: login.php");
        exit();
    }else{
       $idUser = $_SESSION["iduser"];
    }
    include_once('incl/geral.php');
    ?>

 <!DOCTYPE html>
 <html lang="pt-br">

 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
     <meta name="description" content="<?=  $desc ? $h1 .' - '. $desc : $descricao ?>">
     <meta name="author" content="<?=$tagAutor?>">
     <meta name="keywords" content="<?=$keyWord ?  $keyWord : $keyWordPadrao ?>">
     <meta name="robots" content="index, follow">
     <title><?=$h1?></title>
     <link rel="shortcut icon" href="imagens/favicon.webp" type="image/x-icon">
     <!-- LINKS CSS -->
     <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" onload="this.rel='stylesheet'">
     <link rel="stylesheet" href="css/normalize.css">
     <link rel="stylesheet" href="css/geral.css">
     <link rel="stylesheet" href="css/style.css">
     