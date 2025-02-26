<?php
require_once "config.php";
session_start();

// 🛑 ป้องกันการเข้าถึงเฉพาะ Admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_level"] !== "admin") {
    header("location: index.php");
    exit;
}

// 🛑 รับค่า category_id ที่ต้องลบ
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("location: categorie.php");
    exit;
}

$category_id = $_GET["id"];

// ✅ ดึงข้อมูลประเภทสินค้าจากฐานข้อมูล
$sql = "SELECT * FROM categories WHERE category_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $category_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header("location: categorie.php");
    exit;
}

$category = mysqli_fetch_assoc($result);

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันการลบประเภทสินค้า - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php
// ✅ ดึงระดับผู้ใช้จาก session
$level = $_SESSION["user_level"];
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="back_office.php">Admin Panel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto"></ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i>
                        <?php echo isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) : "Guest"; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="profile.php">โปรไฟล์</a></li>
                        <li><a class="dropdown-item" href="index.php">หน้าแรก</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">ออกจากระบบ</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-sm mt-5 mb-5 p-4 bg-light rounded w-50">
    <h2 class="text-center">ยืนยันการลบประเภทสินค้า</h2>
    <div class="col-md-10 mb-3 mx-auto">
        <label class="form-label">ชื่อประเภทสินค้า:</label>
        <input type="text" class="form-control" value="<?php echo htmlspecialchars($category['category_name']); ?>" readonly>
    </div>
    <div class="col-md-10 mb-3 mx-auto">
        <label class="form-label">คำอธิบาย:</label>
        <textarea class="form-control" readonly><?php echo htmlspecialchars($category['category_description']); ?></textarea>
    </div>
    
    <form action="categorie_delete_process.php?id=<?php echo $category_id; ?>" method="post">
        <div class="text-center">
            <button type="submit" class="btn btn-danger">ยืนยันการลบ</button>
            <a href="categorie.php" class="btn btn-secondary">ยกเลิก</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
