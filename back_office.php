<?php
// back_office.php
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

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office - Admin</title>
    <link rel="stylesheet" href="back_style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<body>

<?php
// ✅ ดึงระดับผู้ใช้จาก session
$level = $_SESSION["user_level"];
?>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="back_office.php">Admin Panel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                   
                </li>
            </ul>
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
<!-- End Navbar -->
<!-- From Uiverse.io by SteveBloX --> 
<div class="flxe gap-5 mx-10 my-10">
    <!-- การ์ดที่ 1 -->
    <div class="card bg-gray-300/60 border border-white shadow-lg backdrop-blur-md rounded-lg text-center cursor-pointer transition-all duration-500 ease-in-out flex items-center justify-center select-none font-bold text-black hover:scale-105 active:scale-95 w-[270px] h-[160px]" onclick="window.location.href='product.php'">
        เพิ่มสินค้า
    </div>

    <!-- การ์ดที่ 2 -->
    <div class="card bg-gray-300/60 border border-white shadow-lg backdrop-blur-md rounded-lg text-center cursor-pointer transition-all duration-500 ease-in-out flex items-center justify-center select-none font-bold text-black hover:scale-105 active:scale-95 w-[270px] h-[160px]" onclick="window.location.href='categorie.php'">
        เพิ่มประเภทสินค้า
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
</body>
</html>
