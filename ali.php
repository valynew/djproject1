<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$conn = new mysqli("localhost", "root", "", "dj_db");

if ($conn->connect_error) {
    echo json_encode(["status" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

// --- LOGIN / REGISTER (via POST + mode) ---
if ($method === 'POST' && isset($data->mode)) {
    $mode = strtolower($data->mode);

    if (!empty($data->djname) && !empty($data->password)) {
        $djname = $conn->real_escape_string($data->djname);
        $password = $data->password;

        if ($mode === 'register') {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $check = $conn->query("SELECT id FROM user WHERE djname = '$djname'");
            if ($check->num_rows > 0) {
                echo json_encode(["status" => false, "message" => "DJ name already exists"]);
                exit();
            }

            $sql = "INSERT INTO user (djname, password) VALUES ('$djname', '$hashedPassword')";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(["status" => true, "message" => "Registration successful"]);
            } else {
                echo json_encode(["status" => false, "message" => "Registration failed: " . $conn->error]);
            }

        } elseif ($mode === 'login') {
            $sql = "SELECT * FROM user WHERE djname = '$djname'";
            $result = $conn->query($sql);

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $token = bin2hex(random_bytes(16));
                    echo json_encode([
                        "status" => true,
                        "message" => "Login successful",
                        "token" => $token
                    ]);
                } else {
                    echo json_encode(["status" => false, "message" => "Invalid password"]);
                }
            } else {
                echo json_encode(["status" => false, "message" => "DJ name not found"]);
            }

        } else {
            echo json_encode(["status" => false, "message" => "Unknown mode: $mode"]);
        }

    } else {
        echo json_encode(["status" => false, "message" => "Missing DJ name or password"]);
    }
}

// --- CREATE USER RECORD (email & phone) ---
elseif ($method === 'POST') {
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

// --- UPDATE USER ---
elseif ($method === 'PUT') {
    if (!empty($data->id) && !empty($data->djname) && !empty($data->email) && !empty($data->phonenumber)) {
        $id = $conn->real_escape_string($data->id);
        $djname = $conn->real_escape_string($data->djname);
        $email = $conn->real_escape_string($data->email);
        $phonenumber = $conn->real_escape_string($data->phonenumber);

        $sql = "UPDATE user SET djname = '$djname', email = '$email', phonenumber = '$phonenumber' WHERE id = $id";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => true, "message" => "User updated successfully"]);
        } else {
            echo json_encode(["status" => false, "message" => "Update failed: " . $conn->error]);
        }
    } else {
        echo json_encode(["status" => false, "message" => "Missing required fields"]);
    }
}

// --- DELETE USER ---
elseif ($method === 'DELETE') {
    if (!empty($data->id)) {
        $id = $conn->real_escape_string($data->id);
        $sql = "DELETE FROM user WHERE id = $id";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => true, "message" => "User deleted successfully"]);
        } else {
            echo json_encode(["status" => false, "message" => "Delete failed: " . $conn->error]);
        }
    } else {
        echo json_encode(["status" => false, "message" => "Missing required field: id"]);
    }
}

// --- GET USERS ---
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

// --- Unsupported ---
else {
    echo json_encode(["status" => false, "message" => "Unsupported request method"]);
}

$conn->close();
?>
