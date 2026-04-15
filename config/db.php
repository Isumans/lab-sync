<?php

function connect() {
    static $conn = null;

    if ($conn !== null) {
        return $conn;
    }

    $servername = getenv('LABSYNC_DB_HOST') ?: 'localhost';
    $username = getenv('LABSYNC_DB_USER') ?: 'root';
    $password = getenv('LABSYNC_DB_PASS') ?: '';
    $database = getenv('LABSYNC_DB_NAME') ?: 'laboratory';

    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @new mysqli($servername, $username, $password, $database);

    if ($conn->connect_error) {
        http_response_code(500);
        die(
            "Database connection failed. " .
            "Please verify MySQL is running and database '" . htmlspecialchars($database, ENT_QUOTES, 'UTF-8') .
            "' exists. MySQL error: " . htmlspecialchars($conn->connect_error, ENT_QUOTES, 'UTF-8')
        );
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}

// function connect()
// {
//     global $conn;
//     $servername = "localhost";
//     $username = "root";
//     $password = "";
//     $database = "laboratory";
//     if (!isset($conn)) {
//         $conn = new mysqli($servername, $username, $password, $database);
//     }
//     if ($conn->connect_error) {
//         die("Connection failed: " . $conn->connect_error);
//     }
// }


// function iud($q)
// {
//     connect();
//     global $conn;
//     ;
//     if($conn->query($q)===true){
//         return True;
//     }else{
//         return False;
//     }

// }

// function search($q){
//    connect();
//    global $conn;
//    $result = $conn->query($q);
   

//    if ($result) {
//        return $result->fetch_all(MYSQLI_ASSOC);  // Return the result set
//    } else {
//        return null;     // Return null if the query fails
//    }

// }

?>