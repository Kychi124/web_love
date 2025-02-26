<?php
require_once "config.php";
session_start();

// 🛑 ป้องกันการเข้าถึงเฉพาะ Admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_level"] !== "admin") {
    header("location: index.php");
    exit;
}

// 🛑 รับค่า category_id ที่ต้องแก้ไข
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("location: categorie.php");
    exit;
}

$category_id = $_GET["id"];

// ✅ ดึงข้อมูลประเภทสินค้า
$sql = "SELECT * FROM categories WHERE category_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $category_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$category = mysqli_fetch_assoc($result);

if (!$category) {
    echo "<script>alert('ไม่พบข้อมูลประเภทสินค้า'); window.location.href='categorie.php';</script>";
    exit;
}

// ✅ อัปเดตข้อมูลประเภทสินค้า
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST["category_name"]);
    $description = mysqli_real_escape_string($conn, $_POST["category_description"]);

    $sql_update = "UPDATE categories SET category_name=?, category_description=? WHERE category_id=?";
    $stmt_update = mysqli_prepare($conn, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "ssi", $name, $description, $category_id);

    if (mysqli_stmt_execute($stmt_update)) {
        $_SESSION["success_message"] = "อัปเดตประเภทสินค้าเรียบร้อยแล้ว!";
        header("location: categorie.php");
        exit;
    } else {
        $_SESSION["error_message"] = "เกิดข้อผิดพลาด: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขประเภทสินค้า</title>
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
    <h2 class="text-center m-3">แก้ไขประเภทสินค้า</h2>
    <form action="categorie_edit.php?id=<?php echo $category_id; ?>" method="post" class="m-3">
        <div class="col-md-10 mb-3 mx-auto">
            <label class="form-label">ชื่อประเภทสินค้า*</label>
            <input type="text" name="category_name" class="form-control" value="<?php echo htmlspecialchars($category['category_name']); ?>" required>
        </div>

        <div class="col-md-10 mx-auto">
            <label class="form-label">คำอธิบาย*</label>
            <textarea name="category_description" class="form-control" required><?php echo htmlspecialchars($category['category_description']); ?></textarea>
        </div>

        <div class="col-12 text-center mt-5">
            <button class="btn btn-success btn-lg" type="submit">บันทึกการแก้ไข</button>
            <a href="categorie.php" class="btn btn-danger btn-lg">ยกเลิก</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
