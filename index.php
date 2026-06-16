<?php require_once __DIR__ . '/config/db.php'; $conn = dbConnect(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gourmet Bites - Digital Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-shop"></i> Gourmet Bites</a>
            <div class="d-flex align-items-center gap-2">
                <button id="themeToggle" class="btn btn-outline-light btn-sm rounded-circle d-none d-sm-inline-flex" title="Toggle Dark Mode">
                    <i class="bi bi-moon"></i>
                </button>
                <a href="pages/track.php" class="btn btn-outline-light btn-sm rounded-pill"><i class="bi bi-map"></i> Track Order</a>
            </div>
        </div>
    </nav>

    <!-- Hero Banner -->
    <div class="hero-banner position-relative">
        <img src="images/hero-banner.jpg" alt="Restaurant" class="w-100" style="height: 250px; object-fit: cover;">
        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.4);">
            <div class="text-center text-white px-3">
                <h1 class="display-5 fw-bold">Our Menu</h1>
                <p class="lead">Fresh flavors, crafted with passion</p>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="input-group input-group-lg shadow-sm rounded-pill overflow-hidden">
                    <span class="input-group-text bg-white border-0 ps-4"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control border-0" placeholder="Search menu items...">
                </div>
            </div>
        </div>
    </div>

    <!-- Category Filter -->
    <div class="container mt-4">
        <div class="d-flex gap-2 overflow-auto pb-2" id="categoryFilters">
            <button class="btn btn-danger rounded-pill px-4 active" data-category="0" onclick="filterCategory(0, this)">All</button>
            <?php
            $cats = $conn->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");
            while ($cat = $cats->fetch_assoc()):
            ?>
            <button class="btn btn-outline-danger rounded-pill px-4" data-category="<?= $cat['id'] ?>" onclick="filterCategory(<?= $cat['id'] ?>, this)">
                <?= htmlspecialchars($cat['name']) ?>
            </button>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Menu Items Grid -->
    <div class="container mt-4 mb-5">
        <div id="menuGrid" class="row g-4">
            <?php
            $items = $conn->query("SELECT m.*, c.name as category_name FROM menu_items m LEFT JOIN categories c ON m.category_id = c.id WHERE m.is_available = 1 ORDER BY m.is_featured DESC, m.sort_order, m.name");
            while ($item = $items->fetch_assoc()):
            ?>
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3 menu-item" data-category="<?= $item['category_id'] ?>" data-name="<?= strtolower(htmlspecialchars($item['name'])) ?>">
                <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden menu-card" onclick="window.location.href='pages/item.php?id=<?= $item['id'] ?>'" style="cursor:pointer">
                    <div class="position-relative">
                        <img src="<?= $item['image'] ?>" class="card-img-top" alt="<?= $item['name'] ?>" style="height: 200px; object-fit: cover;">
                        <?php if($item['is_featured']): ?>
                        <span class="position-absolute top-0 start-0 m-2 badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Featured</span>
                        <?php endif; ?>
                        <?php if(!$item['is_available']): ?>
                        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50">
                            <span class="badge bg-secondary">Unavailable</span>
                        </div>
                        <?php endif; ?>
                        <div class="position-absolute bottom-0 end-0 m-2">
                            <?php if($item['is_spicy']): ?><span class="badge bg-danger"><i class="bi bi-fire"></i></span><?php endif; ?>
                            <?php if($item['is_vegetarian']): ?><span class="badge bg-success"><i class="bi bi-leaf"></i></span><?php endif; ?>
                            <?php if($item['is_gluten_free']): ?><span class="badge bg-info"><i class="bi bi-wheat"></i></span><?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h5 class="card-title mb-0"><?= $item['name'] ?></h5>
                            <span class="fw-bold text-danger fs-5">$<?= number_format($item['price'], 2) ?></span>
                        </div>
                        <p class="card-text text-muted small mb-2"><?= $item['description'] ?></p>
                        <div class="d-flex gap-3 text-muted small">
                            <?php if($item['preparation_time'] > 0): ?>
                            <span><i class="bi bi-clock"></i> <?= $item['preparation_time'] ?> min</span>
                            <?php endif; ?>
                            <?php if($item['calories']): ?>
                            <span><i class="bi bi-fire"></i> <?= $item['calories'] ?> cal</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 pb-3 d-flex justify-content-between align-items-center">
                        <span class="badge bg-light text-dark border"><?= $item['category_name'] ?></span>
                        <button class="btn btn-danger btn-sm rounded-pill px-3"
                            onclick="event.stopPropagation(); addToCart(<?= $item['id'] ?>, '<?= addslashes(htmlspecialchars($item['name'])) ?>', <?= $item['price'] ?>, '<?= htmlspecialchars($item['image']) ?>')">
                            <i class="bi bi-plus-lg"></i> Add
                        </button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <div id="noResults" class="text-center py-5 d-none">
            <i class="bi bi-search display-1 text-muted"></i>
            <p class="text-muted mt-3">No items found. Try a different search.</p>
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

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container text-center">
            <p class="mb-1 fw-bold"><i class="bi bi-shop"></i> Gourmet Bites Restaurant</p>
            <p class="small text-white-50 mb-0">123 Culinary Avenue, Food District | +1 (555) 123-4567</p>
            <a href="admin/login.php" class="text-white-50 small text-decoration-none d-block mt-2">Admin</a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>
