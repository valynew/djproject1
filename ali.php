<?php
// Allow cross-origin requests and JSON response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

// Database credentials
$host = "localhost";
$user = "root";
$password = "";
$dbname = "dj_db";

// Create DB connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(["status" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Get POST data from JSON input
$data = json_decode(file_get_contents("php://input"));

// Check if all required fields are present
if (!empty($data->djname) && !empty($data->email) && !empty($data->phonenumber)) {
    $djname = $conn->real_escape_string($data->djname);
    $email = $conn->real_escape_string($data->email);
    $phonenumber = $conn->real_escape_string($data->phonenumber);

    $sql = "INSERT INTO user (djname, email, phonenumber) VALUES ('$djname', '$email', '$phonenumber')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => true, "message" => " User inserted successfully"]);
    } else {
        echo json_encode(["status" => false, "message" => "Insert failed: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => false, "message" => " Missing one or more required fields: djname, email, phonenumber"]);
}


$conn->close();
?>
