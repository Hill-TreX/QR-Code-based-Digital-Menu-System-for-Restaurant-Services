<?php
require_once __DIR__ . '/../config/db.php';
$conn = dbConnect();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) { header('Location: ../index.php'); exit; }

$item = $conn->query("SELECT m.*, c.name as category_name FROM menu_items m LEFT JOIN categories c ON m.category_id = c.id WHERE m.id = $id")->fetch_assoc();
if (!$item) { header('Location: ../index.php'); exit; }

// Track view
$ip = $_SERVER['REMOTE_ADDR'];
$conn->query("INSERT INTO menu_views (menu_item_id, category_id, ip_address) VALUES ($id, {$item['category_id']}, '$ip')");
$conn->query("UPDATE menu_items SET view_count = view_count + 1 WHERE id = $id");

$ingredients = $item['ingredients'] ? array_map('trim', explode(',', $item['ingredients'])) : [];
$allergens = $item['allergens'] ? array_map('trim', explode(',', $item['allergens'])) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $item['name'] ?> - Gourmet Bites</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php"><i class="bi bi-arrow-left"></i> Back to Menu</a>
            <button id="themeToggle" class="btn btn-outline-light btn-sm rounded-circle"><i class="bi bi-moon"></i></button>
        </div>
    </nav>

    <!-- Hero Image -->
    <div class="position-relative">
        <img src="../<?= $item['image'] ?>" alt="<?= $item['name'] ?>" class="w-100" style="height: 300px; object-fit: cover;">
        <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(transparent 40%, rgba(0,0,0,0.7));"></div>
        <div class="position-absolute bottom-0 start-0 w-100 p-4 text-white">
            <div class="container">
                <div class="d-flex gap-2 mb-2">
                    <?php if($item['is_spicy']): ?><span class="badge bg-danger"><i class="bi bi-fire"></i> Spicy</span><?php endif; ?>
                    <?php if($item['is_vegetarian']): ?><span class="badge bg-success"><i class="bi bi-leaf"></i> Vegetarian</span><?php endif; ?>
                    <?php if($item['is_vegan']): ?><span class="badge bg-success"><i class="bi bi-seedling"></i> Vegan</span><?php endif; ?>
                    <?php if($item['is_gluten_free']): ?><span class="badge bg-info"><i class="bi bi-wheat"></i> Gluten Free</span><?php endif; ?>
                </div>
                <h1 class="fw-bold"><?= $item['name'] ?></h1>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <!-- Price & Meta -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <span class="fs-2 fw-bold text-danger">$<?= number_format($item['price'], 2) ?></span>
                    <?php if($item['calories']): ?><span class="text-muted ms-3"><i class="bi bi-fire"></i> <?= $item['calories'] ?> cal</span><?php endif; ?>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex gap-3 text-muted">
                        <?php if($item['preparation_time'] > 0): ?><span><i class="bi bi-clock"></i> <?= $item['preparation_time'] ?> min prep</span><?php endif; ?>
                        <span><i class="bi bi-eye"></i> <?= $item['view_count'] ?> views</span>
                    </div>
                    <?php if($item['is_available']): ?>
                    <button class="btn btn-danger btn-sm rounded-pill px-3"
                        onclick="addToCart(<?= $item['id'] ?>, '<?= addslashes(htmlspecialchars($item['name'])) ?>', <?= $item['price'] ?>, '../<?= htmlspecialchars($item['image']) ?>')">
                        <i class="bi bi-plus-lg"></i> Add
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Description -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <h5 class="fw-bold"><i class="bi bi-info-circle text-danger"></i> Description</h5>
                        <p class="text-muted mb-0"><?= nl2br($item['long_description'] ?: $item['description']) ?></p>
                    </div>
                </div>

                <!-- Ingredients -->
                <?php if(!empty($ingredients) && $ingredients[0]): ?>
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <h5 class="fw-bold"><i class="bi bi-basket text-danger"></i> Ingredients</h5>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <?php foreach($ingredients as $ing): ?>
                            <span class="badge bg-light text-dark border px-3 py-2"><?= $ing ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Allergens -->
                <?php if(!empty($allergens) && $allergens[0] && $allergens[0] !== 'None'): ?>
                <div class="card border-0 shadow-sm rounded-4 mb-4 border-warning">
                    <div class="card-body bg-warning bg-opacity-10">
                        <h5 class="fw-bold text-warning"><i class="bi bi-exclamation-triangle"></i> Allergen Information</h5>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <?php foreach($allergens as $alg): ?>
                            <span class="badge bg-warning text-dark px-3 py-2"><?= $alg ?></span>
                            <?php endforeach; ?>
                        </div>
                        <p class="small text-muted mt-3 mb-0">Please inform our staff if you have any allergies or dietary restrictions.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <h6 class="fw-bold">Category</h6>
                        <span class="badge bg-danger"><?= $item['category_name'] ?></span>
                        <hr>
                        <?php if(!$item['is_available']): ?>
                        <div class="alert alert-danger"><i class="bi bi-x-circle"></i> This item is currently unavailable</div>
                        <?php else: ?>
                        <div class="alert alert-success"><i class="bi bi-check-circle"></i> Available now</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Bubble -->
    <button class="cart-bubble" id="cartBubble" data-bs-toggle="offcanvas" data-bs-target="#cartOffcanvas" aria-label="View cart">
        <i class="bi bi-cart3"></i>
        <span class="cart-count d-none" id="cartCount">0</span>
    </button>

    <!-- Cart Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold"><i class="bi bi-cart3 text-danger"></i> Your Order</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column p-0">
            <div id="cartItems" class="flex-grow-1 p-3"></div>
            <div id="cartEmpty" class="flex-grow-1 d-flex flex-column align-items-center justify-content-center text-muted">
                <i class="bi bi-cart-x display-3"></i>
                <p class="mt-3">Your cart is empty</p>
            </div>
            <div id="cartFooter" class="border-top p-3 d-none">
                <div class="d-flex justify-content-between fw-bold mb-3">
                    <span>Total</span>
                    <span class="text-danger" id="cartTotal">$0.00</span>
                </div>
                <button class="btn btn-danger w-100 rounded-pill" onclick="placeOrder()">
                    <i class="bi bi-check-circle"></i> Place Order
                </button>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="small text-muted mb-0">Gourmet Bites Restaurant</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/app.js"></script>
</body>
</html>
