<?php
require_once "config.php";
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// ตรวจสอบค่า id_account ใน SESSION
if (!isset($_SESSION["user_id"])) {
    die("เกิดข้อผิดพลาด: ไม่พบค่า user_id ใน SESSION กรุณาล็อกอินใหม่");
}

$user_id = $_SESSION["user_id"];

// ดึงข้อมูลโปรไฟล์จากฐานข้อมูล
$sql = "SELECT email, first_name, last_name, phone, user_images FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("เกิดข้อผิดพลาด SQL: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// ตั้งค่ารูปโปรไฟล์เริ่มต้น
$user_image = !empty($user['user_images']) ? $user['user_images'] : 'default_profile.png';

// ตรวจสอบการอัปเดตข้อมูล
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $phone = $_POST["phone"];

    // อัปโหลดภาพใหม่
    if (!empty($_FILES["profile_image"]["name"])) {
        $target_dir = "uploads/"; // เปลี่ยนเป็นโฟลเดอร์ที่เก็บภาพจริง
        $imageFileType = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
        $new_image_name = uniqid() . '.' . $imageFileType;
        $target_file = $target_dir . $new_image_name;
        $uploadOk = 1;

        // ตรวจสอบว่าเป็นไฟล์รูปภาพ
        $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
        if ($check === false) {
            $_SESSION["error"] = "ไฟล์ที่อัปโหลดไม่ใช่รูปภาพ";
            $uploadOk = 0;
        }

        // ตรวจสอบขนาดไฟล์
        if ($_FILES["profile_image"]["size"] > 500000) {
            $_SESSION["error"] = "ไฟล์ใหญ่เกินไป (จำกัดที่ 500KB)";
            $uploadOk = 0;
        }

        // อนุญาตเฉพาะไฟล์รูปภาพ
        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            $_SESSION["error"] = "อนุญาตเฉพาะไฟล์ JPG, JPEG, PNG & GIF เท่านั้น";
            $uploadOk = 0;
        }

        // อัปโหลดไฟล์
        if ($uploadOk == 1 && move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $user_image = $new_image_name;
        }
    }

    // อัปเดตข้อมูลผู้ใช้
    $sql = "UPDATE users SET first_name=?, last_name=?, phone=?, user_images=? WHERE user_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssi", $first_name, $last_name, $phone, $user_image, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION["success"] = "อัปเดตโปรไฟล์เรียบร้อยแล้ว!";
        header("location: profile.php");
        exit;
    } else {
        $_SESSION["error"] = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล!";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขโปรไฟล์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="text-end mb-4">
        <a href="index.php" class="btn btn-danger">กลับสู่หน้าแรก</a>
    </div>

    <h2 class="text-center">แก้ไขโปรไฟล์</h2>

    <?php if (isset($_SESSION["success"])): ?>
        <div class="alert alert-success"><?php echo $_SESSION["success"]; unset($_SESSION["success"]); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION["error"])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION["error"]; unset($_SESSION["error"]); ?></div>
    <?php endif; ?>

    <form action="profile.php" method="post" enctype="multipart/form-data">
        <div class="mb-3 text-center">
            <img src="uploads/<?php echo htmlspecialchars($user_image); ?>" 
            alt="Profile Image" class="rounded-circle" width="150" height="150">
            <br>
            <input type="file" name="profile_image" class="form-control mt-2">
        </div>

        <div class="mb-3">
            <label class="form-label">ชื่อ</label>
            <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">นามสกุล</label>
            <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">เบอร์โทร</label>
            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
        </div>

        <div class="text-center">
            <button type="submit" name="submit" class="btn btn-success">บันทึก</button>
            <a href="address.php" class="btn btn-primary">แก้ไขที่อยู่</a>
            <a href="profile_pass_edit.php" class="btn btn-warning">เปลี่ยนรหัสผ่าน</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
