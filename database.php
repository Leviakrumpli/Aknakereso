<?php

$host = "mysql.caesar.elte.hu";
$dbname = "leviakrumpli";
$username = "leviakrumpli";
$password = "BA1hRF0JcLjQsfCX";

$mysqli = new mysqli($host, $username, $password, $dbname);

if($mysqli->connect_errno)
{
    die("Connection error: ". $mysqli->connect_error);
}

return $mysqli;