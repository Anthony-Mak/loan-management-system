<?php
// Start session
session_start();

// Connect to database
$conn = new mysqli("localhost:8000", "root", "friend", "employee_loans");

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    // Query database
    $sql = "SELECT id, username, password_hash FROM users WHERE username = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password (using password_hash)
        if (password_verify($password, $user['password_hash'])) {
            // Password is correct, create session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['logged_in'] = true;
            
            header("Location: dashboard.php");
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "User not found";
    }
}
?>