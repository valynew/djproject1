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

if (!isset($data['djname']) || !isset($data['password'])) {
    echo json_encode(["status" => false, "message" => "Missing djname or password"]);
    exit;
}

$djname = $data['djname'];
$password = $data['password'];

$conn = new mysqli("localhost", "root", "", "usersdb");

if ($conn->connect_error) {
    echo json_encode(["status" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM credential WHERE djname = ?");
$stmt->bind_param("s", $djname);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        echo json_encode([
            "status" => true,
            "message" => "Login successful",
            "user" => [
                "id" => $user['id'],
                "djname" => $user['djname']
            ]
        ]);
    } else {
        echo json_encode(["status" => false, "message" => "Incorrect password"]);
    }
} else {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO credential (djname, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $djname, $hashedPassword);

    if ($stmt->execute()) {
        echo json_encode([
            "status" => true,
            "message" => "User registered successfully",
            "user" => [
                "id" => $stmt->insert_id,
                "djname" => $djname
            ]
        ]);
    } else {
        echo json_encode(["status" => false, "message" => "Registration failed"]);
    }
}

$stmt->close();
$conn->close();
?>
