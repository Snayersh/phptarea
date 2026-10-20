<?php

if (!isset($_GET['term'])) die('parametro no especificado');

session_start();
if ( !isset($_SESSION['id_usuario']) ) {
	die( "ACCESS DENIED");
}
require_once "db.php";
header('Content-Type: application/json; charset=utf-8');
$stmt = $pdo->prepare('SELECT nombre FROM cursos WHERE nombre LIKE :texto');
$stmt->execute(array( ':texto' => $_GET['term']."%"));
$retval = array();
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
  $retval[] = $row['nombre'];
}

echo(json_encode($retval, JSON_PRETTY_PRINT));
