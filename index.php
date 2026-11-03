
<?php
require_once 'config.php';
require_once 'includes/functions.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        "SELECT * FROM users WHERE username = ?"
    );

    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {

        if ($user['status'] !== 'approved') {

            $error = 'Your account is still waiting for admin approval.';

        } else {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect the user to the dashboard
            header('Location: dashboard.php');
            exit;
        }

    } else {

        $error = 'The username or password you entered is incorrect.';

    }
}

// SET PAGE TITLE
$pageTitle = 'Sign In';

require 'includes/header.php';
?>

<!-- LOGIN SECTION -->
<section class="auth-wrap">
    <div class="auth-card">
        <div class="eyebrow">
            WELCOME BACK
        </div>
        <h1>Sign in to your account</h1>
        <p class="muted">
            Welcome back! Please enter your login details
            to access your inventory dashboard.
        </p>

        <!-- DISPLAY ERROR -->
        <?php if (!empty($error)): ?>

            <div class="error">
                <?= e($error) ?>
            </div>

        <?php endif; ?>

        <!-- LOGIN FORM -->
        <form method="POST" action="">
            <label for="username">
                Username
            </label>

            <input
                type="text"
                id="username"
                name="username"
                placeholder="Enter your username"
                autocomplete="username"
                required
                value="<?= e($_POST['username'] ?? '') ?>"
            >

            <label for="password">
                Password
            </label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                autocomplete="current-password"
                required
            >

            <button type="submit" class="btn full">
                Sign In
            </button>

        </form>

        <!-- LINK -->
        <p class="auth-bottom">
            Don't have an account yet?
            <a href="register.php">Create an account</a>
        </p>

    </div>

</section>

<?php require 'includes/footer.php'; ?>
