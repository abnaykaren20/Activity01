
<?php
require_once 'config.php';
require_once 'includes/functions.php';

require_login();

$edit = null;

if (isset($_GET['edit'])) {

    $stmt = $pdo->prepare(
        "SELECT * FROM products WHERE id = ?"
    );

    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'save') {

        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $quantity = filter_var(
            $_POST['quantity'] ?? null,
            FILTER_VALIDATE_INT
        );

        $price = filter_var(
            $_POST['price'] ?? null,
            FILTER_VALIDATE_FLOAT
        );

        if (
            $name === '' ||
            $quantity === false ||
            $quantity === null ||
            $quantity < 0 ||
            $price === false ||
            $price === null ||
            $price < 0
        ) {

            flash('Please check your product details and try again.');

        } elseif ($id > 0) {

            $stmt = $pdo->prepare("
                UPDATE products
                SET name = ?,
                    description = ?,
                    quantity = ?,
                    price = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $name,
                $description,
                $quantity,
                $price,
                $id
            ]);

            flash('Product information has been updated successfully!');

        } else {

            $stmt = $pdo->prepare("
                INSERT INTO products
                (name, description, quantity, price, created_by)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $name,
                $description,
                $quantity,
                $price,
                $_SESSION['user_id']
            ]);

            flash('Your new product has been added successfully!');
        }

        header('Location: products.php');
        exit;
    }

    if ($action === 'delete') {

        $id = (int) ($_POST['id'] ?? 0);

        $stmt = $pdo->prepare(
            "DELETE FROM products WHERE id = ?"
        );

        $stmt->execute([$id]);

        flash('The product has been removed from your inventory.');

        header('Location: products.php');
        exit;
    }
}

$products = $pdo->query("
    SELECT *
    FROM products
    ORDER BY updated_at DESC
")->fetchAll();

$pageTitle = 'Products';

require 'includes/header.php';
?>

<div class="page-heading">

    <div>

        <div class="eyebrow">
            INVENTORY
        </div>

        <h1>Products</h1>

        <p class="muted">
            Manage your products in one place.
            You can add new items, update details,
            or remove products from your inventory.
        </p>

    </div>

</div>

<div class="product-layout">

    <section class="panel form-panel">

        <h2>
            <?= $edit ? 'Update Product' : 'Add New Product' ?>
        </h2>

        <form method="POST">

            <input
                type="hidden"
                name="action"
                value="save"
            >

            <input
                type="hidden"
                name="id"
                value="<?= e($edit['id'] ?? 0) ?>"
            >

            <!-- PRODUCT NAME -->
            <label for="name">
                Product Name
            </label>

            <input
                type="text"
                id="name"
                name="name"
                placeholder="e.g. Wireless Mouse"
                value="<?= e($edit['name'] ?? '') ?>"
                required
            >

            <!-- PRODUCT DESCRIPTION -->
            <label for="description">
                Description
            </label>

            <textarea
                id="description"
                name="description"
                rows="3"
                placeholder="Write a short description about the product..."
            ><?= e($edit['description'] ?? '') ?></textarea>

            <!-- QUANTITY AND PRICE -->
            <div class="two-col">

                <div>

                    <label for="quantity">
                        Quantity
                    </label>

                    <input
                        type="number"
                        id="quantity"
                        name="quantity"
                        min="0"
                        step="1"
                        value="<?= e($edit['quantity'] ?? '0') ?>"
                        required
                    >

                </div>

                <div>

                    <label for="price">
                        Price (₱)
                    </label>

                    <input
                        type="number"
                        id="price"
                        name="price"
                        min="0"
                        step="0.01"
                        value="<?= e($edit['price'] ?? '0.00') ?>"
                        required
                    >
                </div>
            </div>

            <!-- SUBMIT BUTTON -->
            <button class="btn full" type="submit">
                <?= $edit ? 'Save Changes' : 'Add Product' ?>
            </button>

            <!-- CANCEL EDITING -->
            <?php if ($edit): ?>

                <a
                    href="products.php"
                    class="cancel-link"
                >
                    Cancel editing
                </a>
            <?php endif; ?>
        </form>
    </section>

    <!-- PRODUCT LIST-->
    <section class="panel">

        <div class="panel-head">

            <h2>Product List</h2>

            <span class="muted">
                <?= count($products) ?> product(s) available
            </span>

        </div>

        <?php if (empty($products)): ?>

            <p class="empty">
                Your inventory is currently empty.
                Add your first product to get started!
            </p>

        <?php else: ?>

            <div class="table-wrap">

                <table>

                    <thead>

                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($products as $product): ?>

                            <tr>

                                <!-- PRODUCT INFORMATION -->
                                <td>

                                    <strong>
                                        <?= e($product['name']) ?>
                                    </strong>

                                    <small>
                                        <?= e($product['description'] ?? '') ?>
                                    </small>

                                </td>

                                <!-- AVAILABLE QUANTITY -->
                                <td>

                                    <span class="<?= $product['quantity'] <= 5 ? 'low' : '' ?>">

                                        <?= (int) $product['quantity'] ?>

                                    </span>

                                </td>

                                <!-- PRODUCT PRICE -->
                                <td>

                                    ₱<?= number_format(
                                        (float) $product['price'],
                                        2
                                    ) ?>

                                </td>

                                <!-- EDIT AND DELETE ACTIONS -->
                                <td class="actions">

                                    <a href="products.php?edit=<?= (int) $product['id'] ?>">
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this product?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="delete"
                                        >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int) $product['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="link-danger"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require 'includes/footer.php'; ?>
