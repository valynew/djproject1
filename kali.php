<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => false,
        "message" => "Only POST method allowed",
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['djname']) || !isset($data['password']) || !isset($data['action'])) {
    echo json_encode(["status" => false, "message" => "Missing djname, password, or action"]);
    exit;
}

$djname = $data['djname'];
$password = $data['password'];
$action = $data['action'];

$conn = new mysqli("localhost", "root", "", "usersdb");

if ($conn->connect_error) {
    echo json_encode(["status" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM credential WHERE djname = ?");
$stmt->bind_param("s", $djname);
$stmt->execute();
$result = $stmt->get_result();

if ($action === 'login') {
    // 👇 REPLACED LOGIN BLOCK STARTS HERE
    $stmt = $conn->prepare("SELECT * FROM credential WHERE djname = ?");
    $stmt->bind_param("s", $djname);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            echo json_encode([
                "status" => true,
                "message" => "Login successful",
                "user" => [
                    "id" => $row['id'],
                    "djname" => $row['djname']
                ]
            ]);
        } else {
            echo json_encode(["status" => false, "message" => "Invalid password"]);
        }
    } else {
        echo json_encode(["status" => false, "message" => "User not found"]);
    }
    // 👆 REPLACED LOGIN BLOCK ENDS HERE

} elseif ($action === 'register') {
    if ($result->num_rows > 0) {
        echo json_encode(["status" => false, "message" => "DJ name already exists"]);
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insertStmt = $conn->prepare("INSERT INTO credential (djname, password) VALUES (?, ?)");
        $insertStmt->bind_param("ss", $djname, $hashedPassword);

        if ($insertStmt->execute()) {
            echo json_encode([
                "status" => true,
                "message" => "User registered successfully",
                "user" => [
                    "id" => $insertStmt->insert_id,
                    "djname" => $djname
                ]
            ]);
        } else {
            echo json_encode(["status" => false, "message" => "Registration failed"]);
        }
        $insertStmt->close();
    }

} else {
    echo json_encode(["status" => false, "message" => "Invalid action"]);
}

$stmt->close();
$conn->close();
?>
