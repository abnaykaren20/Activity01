
<?php
require_once 'config.php';

// Default administrator account
$username = 'admin';
$password = 'Admin123!';

// Check if an administrator already exists
$count = (int) $pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE role = 'admin'
")->fetchColumn();

if ($count > 0) {
    exit("
        <h2>Administrator Already Exists!</h2>
        <p>An administrator account is already registered.</p>
        <p>Please proceed to the login page.</p>
        <p><a href='login.php'>Go to Login</a></p>
    ");
}

// Check if username already exists
$stmt = $pdo->prepare("
    SELECT user_id, role
    FROM users
    WHERE username = ?
");
$stmt->execute([$username]);

$existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existingUser) {
    exit("
        <h2>Username Already Taken!</h2>
        <p>The username 'admin' is already registered.</p>
        <p>Choose another username or check your existing account.</p>
        <p><a href='login.php'>Go to Login</a></p>
    ");
}

// Hash administrator password
$hashedPassword = password_hash(
    $password,
    PASSWORD_DEFAULT
);

// Insert administrator account
try {

    $stmt = $pdo->prepare("
        INSERT INTO users (username, password, status, role)
        VALUES (?, ?, 'approved', 'admin')
    ");

    $stmt->execute([
        $username,
        $hashedPassword
    ]);

    echo "
        <h2>Admin Account Created Successfully!</h2>

        <p>Your administrator account is ready.</p>

        <p><strong>Username:</strong> admin</p>
        <p><strong>Password:</strong> Admin123!</p>

        <p>
            <a href='login.php'>Proceed to Login</a>
        </p>

        <p style='color:red;'>
            IMPORTANT: Delete setup_admin.php after setup.
        </p>
    ";

} catch (PDOException $e) {

    echo "Database Error: " . htmlspecialchars($e->getMessage());

}
?>
