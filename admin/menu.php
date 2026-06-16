<?php
$pageTitle = 'Menu Items';
$activePage = 'menu';
require_once __DIR__ . '/../config/db.php';
requireLogin();
$conn = dbConnect();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add' || $action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $fields = [
            'category_id' => intval($_POST['category_id']),
            'name' => sanitize($_POST['name']),
            'description' => sanitize($_POST['description']),
            'long_description' => sanitize($_POST['long_description']),
            'price' => floatval($_POST['price']),
            'image' => sanitize($_POST['image']),
            'ingredients' => sanitize($_POST['ingredients']),
            'allergens' => sanitize($_POST['allergens']),
            'preparation_time' => intval($_POST['preparation_time']),
            'calories' => intval($_POST['calories']) ?: null,
            'is_available' => isset($_POST['is_available']) ? 1 : 0,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_spicy' => isset($_POST['is_spicy']) ? 1 : 0,
            'is_vegetarian' => isset($_POST['is_vegetarian']) ? 1 : 0,
            'is_gluten_free' => isset($_POST['is_gluten_free']) ? 1 : 0,
            'sort_order' => intval($_POST['sort_order']),
        ];
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE menu_items SET category_id=?,name=?,description=?,long_description=?,price=?,image=?,ingredients=?,allergens=?,preparation_time=?,calories=?,is_available=?,is_featured=?,is_spicy=?,is_vegetarian=?,is_gluten_free=?,sort_order=? WHERE id=?");
            $stmt->bind_param("isssdsissiiiiiiii", $fields['category_id'], $fields['name'], $fields['description'], $fields['long_description'], $fields['price'], $fields['image'], $fields['ingredients'], $fields['allergens'], $fields['preparation_time'], $fields['calories'], $fields['is_available'], $fields['is_featured'], $fields['is_spicy'], $fields['is_vegetarian'], $fields['is_gluten_free'], $fields['sort_order'], $id);
            $stmt->execute();
            setFlash('success', 'Menu item updated');
        } else {
            $stmt = $conn->prepare("INSERT INTO menu_items (category_id,name,description,long_description,price,image,ingredients,allergens,preparation_time,calories,is_available,is_featured,is_spicy,is_vegetarian,is_gluten_free,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("isssdsissiiiiiii", $fields['category_id'], $fields['name'], $fields['description'], $fields['long_description'], $fields['price'], $fields['image'], $fields['ingredients'], $fields['allergens'], $fields['preparation_time'], $fields['calories'], $fields['is_available'], $fields['is_featured'], $fields['is_spicy'], $fields['is_vegetarian'], $fields['is_gluten_free'], $fields['sort_order']);
            $stmt->execute();
            setFlash('success', 'Menu item added');
        }
    } elseif ($action === 'delete') {
        $conn->query("DELETE FROM menu_items WHERE id = " . intval($_POST['id']));
        setFlash('success', 'Menu item deleted');
    } elseif ($action === 'toggle_avail') {
        $conn->query("UPDATE menu_items SET is_available = NOT is_available WHERE id = " . intval($_POST['id']));
        setFlash('success', 'Availability toggled');
    } elseif ($action === 'toggle_featured') {
        $conn->query("UPDATE menu_items SET is_featured = NOT is_featured WHERE id = " . intval($_POST['id']));
        setFlash('success', 'Featured toggled');
    }
    redirect('/admin/menu.php');
}

$editItem = null;
if (isset($_GET['edit'])) {
    $editItem = $conn->query("SELECT * FROM menu_items WHERE id = " . intval($_GET['edit']))->fetch_assoc();
}

$filterCat = intval($_GET['category'] ?? 0);
$search = sanitize($_GET['search'] ?? '');

$sql = "SELECT m.*, c.name as category_name FROM menu_items m LEFT JOIN categories c ON m.category_id = c.id WHERE 1=1";
if ($filterCat) $sql .= " AND m.category_id = $filterCat";
if ($search) $sql .= " AND (m.name LIKE '%$search%' OR m.description LIKE '%$search%')";
$sql .= " ORDER BY m.is_featured DESC, m.sort_order, m.name";
$items = $conn->query($sql);
$categories = $conn->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");

include 'header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <form class="d-flex gap-2 flex-wrap" method="GET">
        <input type="text" name="search" value="<?= $search ?>" class="form-control rounded-pill" placeholder="Search items..." style="width:200px;">
        <select name="category" class="form-select rounded-pill" style="width:150px;" onchange="this.form.submit()">
            <option value="0">All Categories</option>
            <?php $categories->data_seek(0); while($c = $categories->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>" <?= $filterCat == $c['id'] ? 'selected' : '' ?>><?= $c['name'] ?></option>
            <?php endwhile; ?>
        </select>
        <?php if($search || $filterCat): ?><a href="menu.php" class="btn btn-outline-secondary rounded-pill">Clear</a><?php endif; ?>
    </form>
    <button class="btn btn-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#itemModal" onclick="resetItemForm()">
        <i class="bi bi-plus-lg"></i> Add Item
    </button>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Item</th><th>Category</th><th>Price</th><th>Flags</th><th>Status</th><th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="../<?= $item['image'] ?>" class="rounded" style="width:48px;height:48px;object-fit:cover;">
                            <div>
                                <div class="fw-medium"><?= $item['name'] ?></div>
                                <div class="small text-muted"><?= substr($item['description'], 0, 40) ?>...</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-light text-dark border"><?= $item['category_name'] ?></span></td>
                    <td class="fw-bold text-danger">₦<?= number_format($item['price'], 2) ?></td>
                    <td>
                        <?php if($item['is_spicy']): ?><span class="badge bg-danger" title="Spicy"><i class="bi bi-fire"></i></span><?php endif; ?>
                        <?php if($item['is_vegetarian']): ?><span class="badge bg-success" title="Vegetarian"><i class="bi bi-leaf"></i></span><?php endif; ?>
                        <?php if($item['is_gluten_free']): ?><span class="badge bg-info" title="Gluten Free"><i class="bi bi-wheat"></i></span><?php endif; ?>
                        <?php if($item['is_featured']): ?><span class="badge bg-warning text-dark" title="Featured"><i class="bi bi-star-fill"></i></span><?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="toggle_avail">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn btn-sm <?= $item['is_available'] ? 'btn-success' : 'btn-secondary' ?> rounded-pill btn-sm"><?= $item['is_available'] ? 'Available' : 'Unavailable' ?></button>
                        </form>
                    </td>
                    <td class="text-end">
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="toggle_featured">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn btn-sm <?= $item['is_featured'] ? 'btn-warning' : 'btn-outline-warning' ?> rounded-pill btn-sm" title="Toggle Featured"><i class="bi bi-star<?= $item['is_featured'] ? '-fill' : '' ?>"></i></button>
                        </form>
                        <button class="btn btn-sm btn-outline-primary rounded-pill btn-sm" data-bs-toggle="modal" data-bs-target="#itemModal" onclick='fillItemForm(<?= json_encode($item) ?>)'><i class="bi bi-pencil"></i></button>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this item?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill btn-sm"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="itemModalTitle">Add Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="itemFormAction" value="add">
                    <input type="hidden" name="id" id="itemId" value="0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Name *</label>
                            <input type="text" name="name" id="itemName" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Category *</label>
                            <select name="category_id" id="itemCategory" class="form-select rounded-3" required>
                                <?php $categories->data_seek(0); while($c = $categories->fetch_assoc()): ?>
                                <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Price *</label>
                            <input type="number" step="0.01" name="price" id="itemPrice" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Prep Time (min)</label>
                            <input type="number" name="preparation_time" id="itemPrep" class="form-control rounded-3" value="15">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Calories</label>
                            <input type="number" name="calories" id="itemCals" class="form-control rounded-3">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Short Description</label>
                            <input type="text" name="description" id="itemDesc" class="form-control rounded-3">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Long Description</label>
                            <textarea name="long_description" id="itemLongDesc" class="form-control rounded-3" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium">Image Path</label>
                            <input type="text" name="image" id="itemImage" class="form-control rounded-3" placeholder="images/dish-name.jpg">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Ingredients (comma-separated)</label>
                            <input type="text" name="ingredients" id="itemIngs" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Allergens (comma-separated)</label>
                            <input type="text" name="allergens" id="itemAlls" class="form-control rounded-3">
                        </div>
                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-3">
                                <label class="form-check-label"><input type="checkbox" name="is_available" id="itemAvail" class="form-check-input" checked> Available</label>
                                <label class="form-check-label"><input type="checkbox" name="is_featured" id="itemFeat" class="form-check-input"> Featured</label>
                                <label class="form-check-label"><input type="checkbox" name="is_spicy" id="itemSpicy" class="form-check-input"> Spicy</label>
                                <label class="form-check-label"><input type="checkbox" name="is_vegetarian" id="itemVeg" class="form-check-input"> Vegetarian</label>
                                <label class="form-check-label"><input type="checkbox" name="is_gluten_free" id="itemGF" class="form-check-input"> Gluten Free</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill">Save Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetItemForm() {
    document.getElementById('itemModalTitle').textContent = 'Add Menu Item';
    document.getElementById('itemFormAction').value = 'add';
    document.getElementById('itemId').value = '0';
    ['Name','Category','Price','Prep','Cals','Desc','LongDesc','Image','Ings','Alls'].forEach(f => document.getElementById('item'+f).value = '');
    document.getElementById('itemPrep').value = '15';
    document.getElementById('itemAvail').checked = true;
    ['Feat','Spicy','Veg','GF'].forEach(f => document.getElementById('item'+f).checked = false);
}
function fillItemForm(item) {
    document.getElementById('itemModalTitle').textContent = 'Edit Menu Item';
    document.getElementById('itemFormAction').value = 'edit';
    document.getElementById('itemId').value = item.id;
    document.getElementById('itemName').value = item.name;
    document.getElementById('itemCategory').value = item.category_id;
    document.getElementById('itemPrice').value = item.price;
    document.getElementById('itemPrep').value = item.preparation_time;
    document.getElementById('itemCals').value = item.calories || '';
    document.getElementById('itemDesc').value = item.description;
    document.getElementById('itemLongDesc').value = item.long_description;
    document.getElementById('itemImage').value = item.image;
    document.getElementById('itemIngs').value = item.ingredients;
    document.getElementById('itemAlls').value = item.allergens;
    document.getElementById('itemAvail').checked = item.is_available == 1;
    document.getElementById('itemFeat').checked = item.is_featured == 1;
    document.getElementById('itemSpicy').checked = item.is_spicy == 1;
    document.getElementById('itemVeg').checked = item.is_vegetarian == 1;
    document.getElementById('itemGF').checked = item.is_gluten_free == 1;
}
</script>
<?php include 'footer.php'; ?>
