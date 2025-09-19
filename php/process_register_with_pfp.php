<?php
session_start();
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "manga";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

$user = $_POST['username'] ?? '';
$pass = $_POST['password'] ?? '';

// Validate inputs
if (empty($user) || empty($pass)) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
    exit();
}

// Check if username already exists BEFORE doing anything else
$check_stmt = $conn->prepare("SELECT username FROM users WHERE username = ? LIMIT 1");
$check_stmt->bind_param("s", $user);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'The username already exists. Please choose a different username.']);
    $check_stmt->close();
    $conn->close();
    exit();
}

$check_stmt->close();

// Check password strength using the same function from original code
function is_password_pwned($password) {
    $hashed_password = sha1($password);
    $prefix = substr($hashed_password, 0, 5);
    $suffix = substr($hashed_password, 5);
    $url = "https://api.pwnedpasswords.com/range/" . $prefix;
    $response = @file_get_contents($url);

    if ($response === false) {
        return false;
    }
    
    $lines = explode("\n", $response);
    foreach ($lines as $line) {
        if (empty($line)) continue;
        $parts = explode(":", $line);
        if (count($parts) >= 2 && strcasecmp(trim($parts[0]), $suffix) === 0) {
            return true;
        }
    }
    return false;
}

if (is_password_pwned($pass)) {
    echo json_encode(['success' => false, 'message' => 'The password you entered was found in a list of stolen passwords. Choose a different password.']);
    exit();
}

// Hash password
$hashed_password = password_hash($pass, PASSWORD_DEFAULT);

// Handle profile picture upload
$pfp_path = null;
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/profiles/';
    
    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $file_extension = 'jpg'; // Since we're converting to JPEG
    $filename = uniqid('profile_') . '.' . $file_extension;
    $target_path = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
        $pfp_path = 'uploads/profiles/' . $filename;
    }
}

// Insert user into database
$stmt = $conn->prepare("INSERT INTO users (username, password, pfp) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $user, $hashed_password, $pfp_path);

if ($stmt->execute()) {
    // Get the user ID
    $user_id = $conn->insert_id;
    
    // Set session variables
    $_SESSION['registration_success'] = true;
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = $user;
    $_SESSION['user_id'] = $user_id;
    
    echo json_encode(['success' => true, 'message' => 'Registration completed successfully!']);
    
    $stmt->close();
    $conn->close();
    exit();
} else {
    // This should now never happen for duplicate usernames since we check earlier
    echo json_encode(['success' => false, 'message' => 'Error during registration: ' . $stmt->error]);
    
    // Clean up uploaded file on database error
    if ($pfp_path && file_exists('../' . $pfp_path)) {
        unlink('../' . $pfp_path);
    }
    
    $stmt->close();
    $conn->close();
    exit();
}
?>