<?php
// Allow cross-origin requests and JSON response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

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

// ---------------------------
// POST: Insert Data
// ---------------------------
if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->djname) && !empty($data->email) && !empty($data->phonenumber)) {
        $djname = $conn->real_escape_string($data->djname);
        $email = $conn->real_escape_string($data->email);
        $phonenumber = $conn->real_escape_string($data->phonenumber);

        $sql = "INSERT INTO user (djname, email, phonenumber) VALUES ('$djname', '$email', '$phonenumber')";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => true, "message" => "User inserted successfully"]);
        } else {
            echo json_encode(["status" => false, "message" => "Insert failed: " . $conn->error]);
        }
    } else {
        echo json_encode(["status" => false, "message" => "Missing one or more required fields: djname, email, phonenumber"]);
    }
}

// ---------------------------
// GET: Fetch Data
// ---------------------------
elseif ($method === 'GET') {
    $sql = "SELECT id, djname, email, phonenumber FROM user";
    $result = $conn->query($sql);

    $users = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode(["status" => true, "data" => $users]);
    } else {
        echo json_encode(["status" => false, "message" => "No users found"]);
    }
}

// ---------------------------
// Unsupported Method
// ---------------------------
else {
    echo json_encode(["status" => false, "message" => "Unsupported request method"]);
}

$conn->close();
?>
