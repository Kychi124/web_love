<?php
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

    mysqli_stmt_bind_param($stmt, "isssssssi", $user_id, $address_type, $address_line1, $address_line2, $city, $state, $postal_code, $country, $is_default);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION["success"] = "เพิ่มที่อยู่เรียบร้อยแล้ว!";
        header("location: address.php");
        exit;
    } else {
        $_SESSION["error"] = "เกิดข้อผิดพลาดในการเพิ่มที่อยู่!";
    }
}

// ดึงข้อมูลที่อยู่ที่บันทึกไว้สำหรับผู้ใช้
$user_id = $_SESSION["user_id"]; // สมมติว่า user_id ถูกบันทึกไว้ใน session
$sql_addresses = "SELECT * FROM user_addresses WHERE user_id = ?";
$stmt_addresses = mysqli_prepare($conn, $sql_addresses);
mysqli_stmt_bind_param($stmt_addresses, "i", $user_id);
mysqli_stmt_execute($stmt_addresses);
$result_addresses = mysqli_stmt_get_result($stmt_addresses);

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มที่อยู่</title>
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
    <h2 class="text-center">ที่อยู่ที่บันทึกไว้</h2>

    <!-- แสดงข้อความแจ้งเตือน และลบค่าทันที -->
    <?php if (isset($_SESSION["success"])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION["success"]; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION["success"]); // ลบค่า session หลังแสดง ?>
    <?php endif; ?>

    <?php if (isset($_SESSION["error"])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION["error"]; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION["error"]); // ลบค่า session หลังแสดง ?>
    <?php endif; ?>

    <div class="text-end mb-4">
        <a href="add_address.php" class="btn btn-success">เพิ่มที่อยู่</a>
        <a href="profile.php" class="btn btn-danger">กลับ</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ประเภทที่อยู่</th>
                <th>ที่อยู่</th>
                <th>เมือง</th>
                <th>รัฐ/จังหวัด</th>
                <th>รหัสไปรษณีย์</th>
                <th>ประเทศ</th>
                <th>การดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($address = mysqli_fetch_assoc($result_addresses)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($address['address_type']); ?></td>
                    <td><?php echo htmlspecialchars($address['address_line1']); ?> <?php echo htmlspecialchars($address['address_line2']); ?></td>
                    <td><?php echo htmlspecialchars($address['city']); ?></td>
                    <td><?php echo htmlspecialchars($address['state']); ?></td>
                    <td><?php echo htmlspecialchars($address['postal_code']); ?></td>
                    <td><?php echo htmlspecialchars($address['country']); ?></td>
                    <td>
                        <a href="address_edit.php?address_id=<?php echo $address['address_id']; ?>" class="btn btn-warning btn-sm">แก้ไข</a>
                        <a href="address_delete.php?address_id=<?php echo $address['address_id']; ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบที่อยู่นี้?');">
                           ลบ
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
