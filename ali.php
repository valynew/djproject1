<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

$host = "localhost";
$user = "root";
$password = "";
$dbname = "dj_db"; // Make sure this DB exists

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Extra GET parameter to switch between tables
$table = isset($_GET['table']) ? $_GET['table'] : 'user';

// GET: Fetch all DJs from djform
if ($method === 'GET' && $table === 'djform') {
    $sql = "SELECT * FROM djform";
    $result = $conn->query($sql);

    $djs = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $djs[] = $row;
        }
        echo json_encode($djs); // Return plain array
    } else {
        echo json_encode([]);
    }
}
// GET: Fetch all users from user table (default)
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
// POST: Insert a user
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->djname) && !empty($data->email) && !empty($data->phonenumber)) {
        $stmt = $conn->prepare("INSERT INTO user (djname, email, phonenumber) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $data->djname, $data->email, $data->phonenumber);

        if ($stmt->execute()) {
            echo json_encode(["status" => true, "message" => "User inserted successfully"]);
        } else {
            echo json_encode(["status" => false, "message" => "Insert failed: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => false, "message" => "Missing required fields: djname, email, phonenumber"]);
    }
}
// PUT: Update a user
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->id) && !empty($data->djname) && !empty($data->email) && !empty($data->phonenumber)) {
        $stmt = $conn->prepare("UPDATE user SET djname = ?, email = ?, phonenumber = ? WHERE id = ?");
        $stmt->bind_param("sssi", $data->djname, $data->email, $data->phonenumber, $data->id);

        if ($stmt->execute()) {
            echo json_encode(["status" => true, "message" => "User updated successfully"]);
        } else {
            echo json_encode(["status" => false, "message" => "Update failed: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => false, "message" => "Missing required fields: id, djname, email, phonenumber"]);
    }
}
// DELETE: Remove a user
elseif ($method === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"));

    if (!empty($data->id)) {
        $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
        $stmt->bind_param("i", $data->id);

        if ($stmt->execute()) {
            echo json_encode(["status" => true, "message" => "User deleted successfully"]);
        } else {
            echo json_encode(["status" => false, "message" => "Delete failed: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => false, "message" => "Missing required field: id"]);
    }
} else {
    echo json_encode(["status" => false, "message" => "Unsupported request method"]);
}

$conn->close();
?>



   
 

 