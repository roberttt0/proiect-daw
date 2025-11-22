<?php
date_default_timezone_set('Europe/Bucharest');
session_start();

$host = 'db';
$db   = 'appdb';
$user = 'appuser';
$pass = 'secret123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Eroare conexiune: " . $e->getMessage());
}

function redirect($url)
{
    header("Location: $url");
    exit();
}
