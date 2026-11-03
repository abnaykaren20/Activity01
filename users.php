
<?php
require_once 'config.php';
require_once 'includes/functions.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = (int) ($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    // Approve a pending user account
    if ($action === 'approve') {

        $stmt = $pdo->prepare("
            UPDATE users
            SET status = 'approved'
            WHERE id = ? AND role = 'user' AND status = 'pending'
        ");

        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            flash('The user account has been approved successfully.');
        } else {
            flash('Unable to approve this account. Please check its status.');
        }

    } elseif ($action === 'reject') {

        $stmt = $pdo->prepare("
            DELETE FROM users
            WHERE id = ? AND role = 'user' AND status = 'pending'
        ");

        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            flash('The user registration has been removed.');
        } else {
            flash('Unable to remove this registration. Please check the account.');
        }
    }

    header('Location: users.php');
    exit;
}

$users = $pdo->query("
    SELECT id, username, status, role, created_at
    FROM users
    ORDER BY created_at DESC
")->fetchAll();

$pageTitle = 'Users';

require 'includes/header.php';
?>

<div class="page-heading">
    <div>
        <div class="eyebrow">ADMINISTRATION</div>

        <h1>User Management</h1>

        <p class="muted">
            Manage registered accounts and review new registration requests.
            You can approve pending users or reject their applications.
        </p>
    </div>
</div>

<section class="panel">

    <div class="panel-head">
        <h2>Registered Users</h2>
        <p class="muted">
            Review the accounts registered in your system.
        </p>
    </div>

    <div class="table-wrap">

        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Registration Date</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                <?php if (empty($users)): ?>

                    <tr>
                        <td colspan="5">
                            No registered users found.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($users as $user): ?>

                        <tr>
                            <td>
                                <strong>
                                    <?= e($user['username']) ?>
                                </strong>
                            </td>

                            <td>
                                <?= e(ucfirst($user['role'])) ?>
                            </td>

                            <td>
                                <span class="status <?= e($user['status']) ?>">
                                    <?= e(ucfirst($user['status'])) ?>
                                </span>
                            </td>

                            <td>
                                <?= e($user['created_at']) ?>
                            </td>

                            <td>

                                <?php if (
                                    $user['role'] === 'user' &&
                                    $user['status'] === 'pending'
                                ): ?>

                                    <div class="actions">

                                        <!-- Approve User -->
                                        <form method="POST" action="users.php">
                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $user['id'] ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="approve"
                                            >

                                            <button
                                                type="submit"
                                                class="mini-btn"
                                            >
                                                Approve
                                            </button>
                                        </form>

                                        <!-- Reject User -->
                                        <form
                                            method="POST"
                                            action="users.php"
                                            onsubmit="return confirm('Are you sure you want to remove this registration?');"
                                        >
                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= (int) $user['id'] ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="reject"
                                            >

                                            <button
                                                type="submit"
                                                class="link-danger"
                                            >
                                                Reject
                                            </button>
                                        </form>

                                    </div>

                                <?php else: ?>

                                    <span class="muted">No action available</span>

                                <?php endif; ?>

                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

            </tbody>
        </table>

    </div>
</section>

<?php require 'includes/footer.php'; ?>
