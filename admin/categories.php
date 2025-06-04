<?php
session_start();
include '../config/db.php';
include '../includes/functions.php';

// Redirect if not admin
if (!isAdmin()) {
    redirect('../index.php');
}

$errors = [];
$success = false;
$edit_mode = false;
$category_id = 0;
$category_name = '';
$category_slug = '';
$category_icon = '';
$category_description = '';

// Handle category actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    // Edit category
    if ($action == 'edit' && isset($_GET['id']) && is_numeric($_GET['id'])) {
        $category_id = (int)$_GET['id'];
        $sql = "SELECT * FROM categories WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $category = $result->fetch_assoc();
            $category_name = $category['name'];
            $category_slug = $category['slug'];
            $category_icon = $category['icon'];
            $category_description = $category['description'];
            $edit_mode = true;
        }
    }
    
    // Delete category
    if ($action == 'delete' && isset($_GET['id']) && is_numeric($_GET['id'])) {
        $category_id = (int)$_GET['id'];
        
        // Check if category has products
        $sql = "SELECT COUNT(*) as count FROM products WHERE category = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            $errors[] = "Không thể xóa danh mục này vì có sản phẩm thuộc danh mục.";
        } else {
            $sql = "DELETE FROM categories WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $category_id);
            
            if ($stmt->execute()) {
                $success = true;
            } else {
                $errors[] = "Đã xảy ra lỗi khi xóa danh mục.";
            }
        }
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $category_name = sanitize($_POST['name']);
    $category_slug = sanitize($_POST['slug']);
    $category_icon = sanitize($_POST['icon']);
    $category_description = sanitize($_POST['description']);
    
    // Validate input
    if (empty($category_name)) {
        $errors[] = "Tên danh mục không được để trống";
    }
    
    if (empty($category_slug)) {
        $errors[] = "Slug không được để trống";
    } else {
        // Make sure slug is URL-friendly
        $category_slug = strtolower(str_replace(' ', '-', $category_slug));
    }
    
    if (empty($category_icon)) {
        $errors[] = "Icon không được để trống";
    }
    
    // If no errors, save category
    if (empty($errors)) {
        if ($edit_mode) {
            // Update existing category
            $sql = "UPDATE categories SET name = ?, slug = ?, icon = ?, description = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $category_name, $category_slug, $category_icon, $category_description, $category_id);
        } else {
            // Insert new category
            $sql = "INSERT INTO categories (name, slug, icon, description) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $category_name, $category_slug, $category_icon, $category_description);
        }
        
        if ($stmt->execute()) {
            $success = true;
            // Reset form
            if (!$edit_mode) {
                $category_name = '';
                $category_slug = '';
                $category_icon = '';
                $category_description = '';
            }
        } else {
            $errors[] = "Đã xảy ra lỗi khi lưu danh mục.";
        }
    }
}

// Get all categories
$sql = "SELECT c.*, (SELECT COUNT(*) FROM products WHERE category = c.slug) as product_count FROM categories c ORDER BY c.name ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý danh mục - SecondLife Market</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Quản lý danh mục</h1>
                </div>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $edit_mode ? 'Danh mục đã được cập nhật thành công.' : 'Danh mục đã được thêm thành công.'; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Category Form -->
                    <div class="col-md-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold"><?php echo $edit_mode ? 'Chỉnh sửa danh mục' : 'Thêm danh mục mới'; ?></h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="<?php echo $edit_mode ? htmlspecialchars($_SERVER["PHP_SELF"] . "?action=edit&id=" . $category_id) : htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Tên danh mục</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($category_name); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="slug" class="form-label">Slug</label>
                                        <input type="text" class="form-control" id="slug" name="slug" value="<?php echo htmlspecialchars($category_slug); ?>" required>
                                        <div class="form-text">Định danh URL-friendly, ví dụ: "dien-tu"</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="icon" class="form-label">Icon (Font Awesome)</label>
                                        <input type="text" class="form-control" id="icon" name="icon" value="<?php echo htmlspecialchars($category_icon); ?>" required>
                                        <div class="form-text">Ví dụ: "fas fa-laptop"</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Mô tả</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($category_description); ?></textarea>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <?php if ($edit_mode): ?>
                                            <a href="categories.php" class="btn btn-outline-secondary">Hủy</a>
                                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                                        <?php else: ?>
                                            <button type="reset" class="btn btn-outline-secondary">Làm mới</button>
                                            <button type="submit" class="btn btn-primary">Thêm mới</button>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Categories Table -->
                    <div class="col-md-8">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold">Danh sách danh mục</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Tên</th>
                                                <th>Slug</th>
                                                <th>Icon</th>
                                                <th>Số sản phẩm</th>
                                                <th>Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($result->num_rows > 0): ?>
                                                <?php while($category = $result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo $category['id']; ?></td>
                                                        <td><?php echo $category['name']; ?></td>
                                                        <td><?php echo $category['slug']; ?></td>
                                                        <td><i class="<?php echo $category['icon']; ?>"></i> <?php echo $category['icon']; ?></td>
                                                        <td><?php echo $category['product_count']; ?></td>
                                                        <td>
                                                            <a href="categories.php?action=edit&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="categories.php?action=delete&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">Không có danh mục nào</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/script.js"></script>
    
    <script>
        // Auto-generate slug from name
        document.getElementById('name').addEventListener('input', function() {
            const name = this.value;
            const slug = name.toLowerCase()
                .replace(/[^\w\s-]/g, '') // Remove special characters
                .replace(/\s+/g, '-') // Replace spaces with hyphens
                .replace(/-+/g, '-'); // Replace multiple hyphens with a single hyphen
            
            document.getElementById('slug').value = slug;
        });
    </script>
</body>
</html>
