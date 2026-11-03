
<?php
require_once 'config.php';
require_once 'includes/functions.php';

//  USER LOGIM=N
require_login();

$totalProducts = (int) $pdo->query(
    "SELECT COUNT(*) FROM products"
)->fetchColumn();

$totalUnits = (int) $pdo->query(
    "SELECT COALESCE(SUM(quantity), 0) FROM products"
)->fetchColumn();

$inventoryValue = (float) $pdo->query(
    "SELECT COALESCE(SUM(quantity * price), 0) FROM products"
)->fetchColumn();

$lowStock = (int) $pdo->query(
    "SELECT COUNT(*) FROM products WHERE quantity <= 5"
)->fetchColumn();

$recent = $pdo->query("
    SELECT
        p.*,
        u.username AS creator
    FROM products p
    LEFT JOIN users u ON p.created_by = u.id
    ORDER BY p.updated_at DESC
    LIMIT 6
")->fetchAll();

// Set the page title
$pageTitle = 'Dashboard';

require 'includes/header.php';
?>

<!-- Dashboard Heading -->
<div class="page-heading">
    <div>
        <div class="eyebrow">OVERVIEW</div>
        <h1>Dashboard</h1>
        <p class="muted">
            Welcome back, <?= e($_SESSION['username']) ?>!
            Here's a quick look at your inventory.
        </p>
    </div>

    <a href="products.php" class="btn">
        + Add Product
    </a>
</div>

<!-- INVENTORY SUMMARY -->
<div class="stats">

    <div class="stat">
        <span>Total Products</span>
        <strong><?= $totalProducts ?></strong>
        <small>Products in your inventory</small>
    </div>

    <div class="stat">
        <span>Total Units</span>
        <strong><?= $totalUnits ?></strong>
        <small>Available items in stock</small>
    </div>

    <div class="stat">
        <span>Inventory Value</span>
        <strong>
            ₱<?= number_format($inventoryValue, 2) ?>
        </strong>
        <small>Total value of your inventory</small>
    </div>

    <div class="stat">
        <span>Low Stock</span>
        <strong><?= $lowStock ?></strong>
        <small>Products with 5 units or fewer</small>
    </div>

</div>

<!-- UPDATE PRODUCTS -->
<section class="panel">

    <div class="panel-head">
        <h2>Recently Updated Products</h2>

        <a href="products.php">
            View All →
        </a>
    </div>

    <?php if (empty($recent)): ?>

        <p class="empty">
            No products have been added yet.
            Start by adding your first product!
        </p>

    <?php else: ?>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($recent as $product): ?>

                        <tr>
                            <td>
                                <strong>
                                    <?= e($product['name']) ?>
                                </strong>

                                <small>
                                    <?= e($product['description'] ?? '') ?>
                                </small>
                            </td>
                            <td>
                                <?= (int) $product['quantity'] ?>
                            </td>
                            <td>
                                ₱<?= number_format(
                                    (float) $product['price'],
                                    2
                                ) ?>
                            </td>
                            <td>
                                <?= e($product['updated_at']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require 'includes/footer.php'; ?>
