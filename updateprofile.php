<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => false, "message" => "Only POST method allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['oldname']) || !isset($data['newname']) || !isset($data['newpassword'])) {
    echo json_encode(["status" => false, "message" => "Missing fields"]);
    exit;
}

$oldname = $data['oldname'];
$newname = $data['newname'];
$newpassword = $data['newpassword'];

$conn = new mysqli("localhost", "root", "", "usersdb");

if ($conn->connect_error) {
    echo json_encode(["status" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$hashedPassword = password_hash($newpassword, PASSWORD_DEFAULT);

// Check if the old user exists
$check = $conn->prepare("SELECT * FROM credential WHERE djname = ?");
$check->bind_param("s", $oldname);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => false, "message" => "Old user not found"]);
    exit;
}

// Proceed to update
$sql = "UPDATE credential SET djname = ?, password = ? WHERE djname = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $newname, $hashedPassword, $oldname);

if ($stmt->execute()) {
    echo json_encode(["status" => true, "message" => "Profile updated successfully"]);
} else {
    echo json_encode(["status" => false, "message" => "Update failed"]);
}

$stmt->close();
$conn->close();
?>
