<?php
require_once "config.php";
session_start();

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// กำหนดค่า user_id จากเซสชัน
$user_id = $_SESSION["user_id"]; // ตรวจสอบให้แน่ใจว่ามีการกำหนดค่า user_id ไว้

// ตรวจสอบการเพิ่มที่อยู่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $address_type = $_POST["address_type"];
    $address_line1 = $_POST["address_line1"];
    $address_line2 = $_POST["address_line2"];
    $city = $_POST["city"];
    $state = $_POST["state"];
    $postal_code = $_POST["postal_code"];
    $country = $_POST["country"];
    $is_default = isset($_POST["is_default"]) ? 1 : 0;

    // คำสั่ง SQL สำหรับเพิ่มที่อยู่
    $sql = "INSERT INTO user_addresses (user_id, address_type, address_line1, address_line2, city, state, postal_code, country, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("เกิดข้อผิดพลาด SQL: " . mysqli_error($conn));
    }

    // Bind parameter รวมถึง user_id
    mysqli_stmt_bind_param($stmt, "isssssssi", $user_id, $address_type, $address_line1, $address_line2, $city, $state, $postal_code, $country, $is_default);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION["success"] = "เพิ่มที่อยู่เรียบร้อยแล้ว!";
        header("location: address.php");
        exit;
    } else {
        $_SESSION["error"] = "เกิดข้อผิดพลาดในการเพิ่มที่อยู่!";
    }
}

// ดึงข้อมูลที่อยู่ทั้งหมดสำหรับผู้ใช้
$sql = "SELECT * FROM user_addresses WHERE user_id = ? ORDER BY address_id DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการที่อยู่</title>
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
                    <a class="nav-link" href="index.php">หน้าแรก</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">ตะกร้าสินค้า</a>
                </li>
            </ul>

        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="text-center">เพิ่มที่อยู่ใหม่</h2>

    <?php if (isset($_SESSION["success"])): ?>
        <div class="alert alert-success"><?php echo $_SESSION["success"]; unset($_SESSION["success"]); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION["error"])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION["error"]; unset($_SESSION["error"]); ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">ประเภทที่อยู่</label>
            <select name="address_type" class="form-control" required>
                <option value="billing">ที่อยู่สำหรับการเรียกเก็บเงิน</option>
                <option value="shipping">ที่อยู่สำหรับการจัดส่ง</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">ที่อยู่บรรทัดที่ 1</label>
            <input type="text" name="address_line1" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">ที่อยู่บรรทัดที่ 2</label>
            <input type="text" name="address_line2" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">เมือง</label>
            <input type="text" name="city" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">รัฐ/จังหวัด</label>
            <input type="text" name="state" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">รหัสไปรษณีย์</label>
            <input type="text" name="postal_code" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">ประเทศ</label>
            <input type="text" name="country" class="form-control" required>
        </div>

        <div class="form-check">
            <input type="checkbox" name="is_default" class="form-check-input" id="is_default">
            <label class="form-check-label" for="is_default">ตั้งเป็นที่อยู่เริ่มต้น</label>
        </div>

        <div class="text-center">
            <button type="submit" class="btn btn-success">เพิ่มที่อยู่</button>
            <a href="address.php" class="btn btn-primary">กลับ</a>
        </div>
    </form>


</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
