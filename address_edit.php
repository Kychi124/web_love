<?php
require_once "config.php";
session_start();

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// ตรวจสอบว่าได้รับ address_id หรือไม่
if (!isset($_GET["address_id"]) || empty($_GET["address_id"])) {
    header("location: address.php");
    exit;
}

$address_id = $_GET["address_id"];
$user_id = $_SESSION["user_id"];

// ดึงข้อมูลที่อยู่จากฐานข้อมูล
$sql = "SELECT * FROM user_addresses WHERE address_id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $address_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$address = mysqli_fetch_assoc($result);

if (!$address) {
    $_SESSION["error"] = "ไม่พบที่อยู่นี้!";
    header("location: address.php");
    exit;
}

// อัปเดตข้อมูลที่อยู่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $address_type = $_POST["address_type"];
    $address_line1 = $_POST["address_line1"];
    $address_line2 = $_POST["address_line2"];
    $city = $_POST["city"];
    $state = $_POST["state"];
    $postal_code = $_POST["postal_code"];
    $country = $_POST["country"];
    $is_default = isset($_POST["is_default"]) ? 1 : 0;

    $sql = "UPDATE user_addresses SET address_type = ?, address_line1 = ?, address_line2 = ?, city = ?, state = ?, postal_code = ?, country = ?, is_default = ? WHERE address_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssssiii", $address_type, $address_line1, $address_line2, $city, $state, $postal_code, $country, $is_default, $address_id, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION["success"] = "แก้ไขที่อยู่เรียบร้อยแล้ว!";
        header("location: address.php");
        exit;
    } else {
        $_SESSION["error"] = "เกิดข้อผิดพลาดในการแก้ไขที่อยู่!";
    }
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขที่อยู่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2 class="text-center">แก้ไขที่อยู่</h2>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">ประเภทที่อยู่</label>
            <select name="address_type" class="form-control" required>
                <option value="billing" <?= ($address['address_type'] == 'billing') ? 'selected' : ''; ?>>ที่อยู่สำหรับการเรียกเก็บเงิน</option>
                <option value="shipping" <?= ($address['address_type'] == 'shipping') ? 'selected' : ''; ?>>ที่อยู่สำหรับการจัดส่ง</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">ที่อยู่บรรทัดที่ 1</label>
            <input type="text" name="address_line1" class="form-control" value="<?= htmlspecialchars($address['address_line1']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">ที่อยู่บรรทัดที่ 2</label>
            <input type="text" name="address_line2" class="form-control" value="<?= htmlspecialchars($address['address_line2']); ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">เมือง</label>
            <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($address['city']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">รัฐ/จังหวัด</label>
            <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($address['state']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">รหัสไปรษณีย์</label>
            <input type="text" name="postal_code" class="form-control" value="<?= htmlspecialchars($address['postal_code']); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">ประเทศ</label>
            <input type="text" name="country" class="form-control" value="<?= htmlspecialchars($address['country']); ?>" required>
        </div>

        <div class="form-check">
            <input type="checkbox" name="is_default" class="form-check-input" id="is_default" <?= ($address['is_default'] == 1) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="is_default">ตั้งเป็นที่อยู่เริ่มต้น</label>
        </div>

        <div class="text-center mt-3">
            <button type="submit" class="btn btn-success">บันทึกการเปลี่ยนแปลง</button>
            <a href="address.php" class="btn btn-secondary">ยกเลิก</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
