<?php



// global $conn;

function connect() {
    static $conn = null;
    if ($conn === null) {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "laboratory";

        $conn = new mysqli($servername, $username, $password, $database);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    }
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