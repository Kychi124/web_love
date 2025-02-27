<?php
require_once "config.php";
session_start();

// 🛑 ป้องกันการเข้าถึงถ้าไม่ได้ล็อกอิน
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// 🛑 จำกัดการเข้าถึงเฉพาะ Admin เท่านั้น
if (!isset($_SESSION["user_level"]) || $_SESSION["user_level"] !== "admin") {
    header("location: index.php");
    exit;
}

// ✅ เพิ่มประเภทสินค้า
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST["category_name"]);
    $description = mysqli_real_escape_string($conn, $_POST["category_description"]);

    // 🛠️ INSERT ข้อมูลเข้า DB
    $sql = "INSERT INTO categories (category_name, category_description) 
            VALUES ('$name', '$description')";

    if (mysqli_query($conn, $sql)) {
        $_SESSION["success_message"] = "เพิ่มประเภทสินค้าเรียบร้อยแล้ว!";
    } else {
        $_SESSION["error_message"] = "เกิดข้อผิดพลาด: " . mysqli_error($conn);
    }

    // ปิดการเชื่อมต่อฐานข้อมูล
    mysqli_close($conn);

    // 🔄 Redirect ไปหน้า categorie.php
    header("location: categorie.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                        <li><a class="dropdown-item" href="orders_show.php">คำสั่งซื้อ</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">ออกจากระบบ</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-sm mt-5 mb-5 p-4 bg-light rounded w-50">
    <h2 class="text-center m-3">เพิ่มประเภทสินค้า</h2>
    <form id="categoryForm" class="m-3" action="categorie_add.php" method="post" enctype="multipart/form-data" novalidate>
        <div class="col-md-10 mb-3 mx-auto">
            <label class="form-label">ชื่อประเภทสินค้า*</label>
            <input type="text" name="category_name" class="form-control" required>
        </div>

        <div class="col-md-10 mx-auto">
            <label class="form-label">คำอธิบาย*</label>
            <textarea name="category_description" class="form-control" required></textarea>
        </div>

        <div class="col-12 text-center mt-5">
            <button class="btn btn-success btn-lg" type="submit">เพิ่มประเภทสินค้า</button>
            <a href="categorie.php" class="btn btn-danger btn-lg">ยกเลิก</a>
        </div>
    </form>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // ดึงค่าจาก sessionStorage
        let successMessage = sessionStorage.getItem("success_message");
        let errorMessage = sessionStorage.getItem("error_message");

        if (successMessage) {
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: successMessage,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'ตกลง'
            });
            sessionStorage.removeItem("success_message"); // ลบค่าหลังแสดงแล้ว
        }

        if (errorMessage) {
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด!',
                text: errorMessage,
                confirmButtonColor: '#d33',
                confirmButtonText: 'ตกลง'
            });
            sessionStorage.removeItem("error_message"); // ลบค่าหลังแสดงแล้ว
        }
    });
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
