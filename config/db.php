<?php

global $conn;

function connect()
{
    global $conn;
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "laboratory";
    if (!isset($conn)) {
        $conn = new mysqli($servername, $username, $password, $database);
    }
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}


?>