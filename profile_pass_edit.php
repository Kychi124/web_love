<?php
// index.php
require_once "config.php";
session_start();

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// ตรวจสอบระดับผู้ใช้ (แก้ไข)
$level = isset($_SESSION["user_level"]) ? $_SESSION["user_level"] : "user";

// เริ่มต้นตะกร้าสินค้าถ้ายังไม่มี
if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = array();
}

// ดึงข้อมูลสินค้าทั้งหมด
$sql = "SELECT p.*, c.category_name, 
        (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE p.status = 'active'
        ORDER BY p.product_id DESC";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("SQL Query Failed: " . mysqli_error($conn));
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>ร้านค้าออนไลน์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">ร้านค้าออนไลน์</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">หน้าแรก</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        ตะกร้าสินค้า
                        <span class="badge bg-danger">
                            <?php echo array_sum($_SESSION["cart"]); ?>
                        </span>
                    </a>
                </li>
            </ul>

        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <?php if($row['primary_image']): ?>
                    <img src="<?php echo htmlspecialchars($row['primary_image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
                <?php else: ?>
                    <img src="placeholder.jpg" class="card-img-top" alt="No image">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                    <p class="card-text">
                        <small class="text-muted"><?php echo htmlspecialchars($row['category_name']); ?></small>
                    </p>
                    <p class="card-text">
                        <?php echo nl2br(htmlspecialchars(substr($row['description'], 0, 100))); ?>...
                    </p>
                    <h6 class="card-subtitle mb-2">฿<?php echo number_format($row['price'], 2); ?></h6>
                    <form method="post" class="mt-3">
                        <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                        <button type="submit" name="add_to_cart" class="btn btn-primary">
                            <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<div class="container mt-5">
    <div class="text-end mb-4"> <!-- ใช้ text-end เพื่อจัดแนวไปทางขวา -->
    <a href="profile.php" class="btn btn-danger" color="red" >กลับ</a>
    </div>

        <h2 class="text-center">เปลี่ยนรหัสผ่าน</h2>

<form action="profile.php" method="post">
    <div class="mb-3">
        <label class="form-label">รหัสผ่านเดิม</label>
        <input type="password" name="current_password" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">รหัสผ่านใหม่</label>
        <input type="password" name="new_password" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
        <input type="password" name="confirm_password" class="form-control" required>
    </div>

    <div class="text-center">
        <button type="submit" name="change_password" class="btn btn-success">บันทึก</button>
    </div>
</form>


</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>