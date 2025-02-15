<?php
// order_complete.php
require_once "config.php";
session_start();

// ตรวจสอบการล็อกอิน
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// ตรวจสอบ order_id
if(!isset($_GET["order_id"])){
    header("location: index.php");
    exit;
}

$order_id = $_GET["order_id"];
$user_id = $_SESSION["user_id"];

// ดึงข้อมูลคำสั่งซื้อ
$order_sql = "SELECT o.*, p.status as payment_status, p.payment_method
              FROM orders o 
              LEFT JOIN payments p ON o.order_id = p.order_id
              WHERE o.order_id = ? AND o.user_id = ?";
$stmt = mysqli_prepare($conn, $order_sql);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($order_result);

// ถ้าไม่พบคำสั่งซื้อหรือไม่ใช่ของผู้ใช้นี้
if(!$order){
    header("location: index.php");
    exit;
}

// ดึงรายการสินค้าในคำสั่งซื้อ
$items_sql = "SELECT oi.*, p.name, p.sku 
              FROM order_items oi
              JOIN products p ON oi.product_id = p.product_id
              WHERE oi.order_id = ?";
$stmt = mysqli_prepare($conn, $items_sql);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$items_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>สั่งซื้อสำเร็จ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        <h2 class="mt-3">สั่งซื้อสำเร็จ</h2>
                        <p class="lead">ขอบคุณสำหรับการสั่งซื้อ</p>
                        <p>หมายเลขคำสั่งซื้อ: #<?php echo str_pad($order_id, 8, "0", STR_PAD_LEFT); ?></p>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">รายละเอียดคำสั่งซื้อ</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>สถานะคำสั่งซื้อ:</strong>
                                <span class="badge bg-info"><?php echo htmlspecialchars($order["status"]); ?></span>
                            </div>
                            <div class="col-md-6">
                                <strong>สถานะการชำระเงิน:</strong>
                                <span class="badge bg-warning"><?php echo htmlspecialchars($order["payment_status"]); ?></span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>สินค้า</th>
                                        <th>จำนวน</th>
                                        <th>ราคา</th>
                                        <th>รวม</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item["name"]); ?></td>
                                        <td><?php echo htmlspecialchars($item["quantity"]); ?></td>
                                        <td><?php echo number_format($item["price"], 2); ?> ฿</td>
                                        <td><?php echo number_format($item["price"] * $item["quantity"], 2); ?> ฿</td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="text-center mt-4">
                            <a href="index.php" class="btn btn-primary">กลับไปที่หน้าหลัก</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
