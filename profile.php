<?php
// profile.php
require_once "config.php";
session_start();

// เริ่มต้นตะกร้าสินค้าถ้ายังไม่มี
if(!isset($_SESSION["cart"])){
    $_SESSION["cart"] = array();
}

// จัดการการเพิ่มสินค้าลงตะกร้า
if(isset($_POST["add_to_cart"]) && isset($_POST["product_id"])){
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        // หากยังไม่ได้ล็อกอิน ให้แสดงข้อความและไม่สามารถเพิ่มสินค้าได้
        header("location: login.php");
        exit;
    }

    $product_id = $_POST["product_id"];
    if(isset($_SESSION["cart"][$product_id])){
        $_SESSION["cart"][$product_id]++;
    } else {
        $_SESSION["cart"][$product_id] = 1;
    }
}

// ดึงข้อมูลสินค้าทั้งหมด
$sql = "SELECT p.*, c.name as category_name, 
        (SELECT image_url FROM product_images WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE p.status = 'active'
        ORDER BY p.product_id DESC";
$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<?php
// ตรวจสอบระดับผู้ใช้จากตัวแปร session
$level = isset($_SESSION["level"]) ? $_SESSION["level"] : "user";
?>

<!-- โค้ดเนปบาร์ของคุณต่อไปนี้ -->
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
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i>
                        <?php echo isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : "Guest"; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <?php if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true): ?>
                            <li><a class="dropdown-item" href="login.php">เข้าสู่ระบบ</a></li>
                            <li><a class="dropdown-item" href="register.php">ลงทะเบียน</a></li>
                        <?php else: ?>
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <?php if ($level === "admin"): ?>
                                <li><a class="dropdown-item" href="back_office.php">Back Office</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">ออกจากระบบ</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<!-- End Navbar -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>