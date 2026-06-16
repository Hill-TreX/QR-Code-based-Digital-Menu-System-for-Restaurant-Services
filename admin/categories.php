<?php
$pageTitle = 'Categories';
$activePage = 'categories';
require_once __DIR__ . '/../config/db.php';
requireLogin();
$conn = dbConnect();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add' || $action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $name = sanitize($_POST['name']);
        $description = sanitize($_POST['description']);
        $image = sanitize($_POST['image']);
        $sort_order = intval($_POST['sort_order'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE categories SET name=?, description=?, image=?, sort_order=? WHERE id=?");
            $stmt->bind_param("sssii", $name, $description, $image, $sort_order, $id);
            $stmt->execute();
            setFlash('success', 'Category updated successfully');
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, description, image, sort_order) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $name, $description, $image, $sort_order);
            $stmt->execute();
            setFlash('success', 'Category added successfully');
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        $conn->query("DELETE FROM categories WHERE id = $id");
        setFlash('success', 'Category deleted');
    } elseif ($action === 'toggle') {
        $id = intval($_POST['id']);
        $conn->query("UPDATE categories SET is_active = NOT is_active WHERE id = $id");
        setFlash('success', 'Status toggled');
    }
    redirect('/admin/categories.php');
}

// Get edit data
$editCat = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $editCat = $conn->query("SELECT * FROM categories WHERE id = $editId")->fetch_assoc();
}

$categories = $conn->query("SELECT c.*, COUNT(m.id) as item_count FROM categories c LEFT JOIN menu_items m ON c.id = m.category_id GROUP BY c.id ORDER BY c.sort_order, c.name");
include 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <button class="btn btn-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="resetForm()">
        <i class="bi bi-plus-lg"></i> Add Category
    </button>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Category</th>
                    <th>Items</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="../<?= $cat['image'] ?>" class="rounded" style="width:40px;height:40px;object-fit:cover;">
                            <div>
                                <div class="fw-medium"><?= $cat['name'] ?></div>
                                <div class="small text-muted"><?= substr($cat['description'], 0, 50) ?>...</div>
                            </div>
                        </div>
                    </td>
                    <td><?= $cat['item_count'] ?></td>
                    <td><?= $cat['sort_order'] ?></td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                            <button type="submit" class="btn btn-sm <?= $cat['is_active'] ? 'btn-success' : 'btn-secondary' ?> rounded-pill">
                                <?= $cat['is_active'] ? 'Active' : 'Inactive' ?>
                            </button>
                        </form>
                    </td>
                    <td class="text-end">
                        <a href="?edit=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick='fillForm(<?= json_encode($cat) ?>)'>
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this category?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="catId" value="0">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Name *</label>
                        <input type="text" name="name" id="catName" class="form-control rounded-3" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Description</label>
                        <textarea name="description" id="catDesc" class="form-control rounded-3" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Image Path</label>
                        <input type="text" name="image" id="catImage" class="form-control rounded-3" placeholder="images/category-name.jpg">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Sort Order</label>
                        <input type="number" name="sort_order" id="catOrder" class="form-control rounded-3" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('modalTitle').textContent = 'Add Category';
    document.getElementById('formAction').value = 'add';
    document.getElementById('catId').value = '0';
    document.getElementById('catName').value = '';
    document.getElementById('catDesc').value = '';
    document.getElementById('catImage').value = '';
    document.getElementById('catOrder').value = '0';
}
function fillForm(cat) {
    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('catId').value = cat.id;
    document.getElementById('catName').value = cat.name;
    document.getElementById('catDesc').value = cat.description;
    document.getElementById('catImage').value = cat.image;
    document.getElementById('catOrder').value = cat.sort_order;
}
</script>
<?php include 'footer.php'; ?>
