<?php
require_once 'config.php';
require_once 'includes/functions.php';
if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($username) < 3) $error = 'Username must be at least 3 characters.';
    elseif (strlen($password) < 8) $error = 'Password must be at least 8 characters.';
    elseif ($password !== $confirm) $error = 'Passwords do not match.';
    else {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetch()) $error = 'That username is already taken.';
        else {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, status, role) VALUES (?, ?, 'pending', 'user')");
            $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
            flash('Registration submitted. Wait for an administrator to approve your account.');
            header('Location: index.php');
            exit;
        }
    }
}
$pageTitle = 'Register';
require 'includes/header.php';
?>
<section class="auth-wrap">
<div class="auth-card">
    <div class="eyebrow">GET STARTED</div>
    <h1>Create an account</h1>
    <p class="muted">Your account will need admin approval before sign in.</p>
    <?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
    <form method="POST">
        <label>Username</label>
        <input name="username" required minlength="3" placeholder="Choose a username" value="<?= e($_POST['username'] ?? '') ?>">
        <label>Password</label>
        <input type="password" name="password" required minlength="8" placeholder="At least 8 characters">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required placeholder="Enter password again">
        <button class="btn full" type="submit">Register</button>
    </form>
    <p class="auth-bottom">Already registered? <a href="index.php">Sign in</a></p>
</div>
</section>
<?php require 'includes/footer.php'; ?>
