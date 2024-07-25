<?php 
// Verifica se o protocolo HTTPS está sendo usado
$protocolo = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";

// Obtém o nome do host (domínio)
$host = $_SERVER['HTTP_HOST'];

// Obtém o caminho do diretório do script atual
$caminho_script = $_SERVER['PHP_SELF'];

// Obtém apenas o diretório do script atual
$diretorio_atual = dirname($caminho_script);

// Concatena as partes para formar a URL base do diretório
$url = $protocolo . $host . $diretorio_atual . '/';
//Variaveis
$siteName = 'Global List' ;
include_once('vetMenu.php');
//METAS TAGS
$tagAutor = 'Mateus Oliveira de Jesus';
$descricao = $siteName .' - '. 'oferece uma solução eficiente para o cadastro, edição, exclusão e envio de informações de clientes. Gerencie seus dados com facilidade e segurança.';
$keyWordPadrao = 'cadastro de clientes, administração de clientes, gestão de clientes';

include('functions.php');?>