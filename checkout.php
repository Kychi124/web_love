<?php
// checkout.php
require_once "config.php";
session_start();

// ตรวจสอบการล็อกอิน
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// ตรวจสอบว่ามีสินค้าในตะกร้า
if(empty($_SESSION["cart"])){
    header("location: cart.php");
    exit;
}

// ดึงข้อมูลที่อยู่ของผู้ใช้
$user_id = $_SESSION["user_id"];
$address_sql = "SELECT * FROM user_addresses WHERE user_id = ? AND is_default = 1";
$stmt = mysqli_prepare($conn, $address_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$address_result = mysqli_stmt_get_result($stmt);
$default_address = mysqli_fetch_assoc($address_result);

// ดึงข้อมูลสินค้าในตะกร้า
$cart_items = array();
$total = 0;
$shipping_fee = 50; // ค่าจัดส่งคงที่

if(!empty($_SESSION["cart"])){
    $product_ids = array_keys($_SESSION["cart"]);
    $sql = "SELECT * FROM products WHERE product_id IN (" . implode(",", $product_ids) . ")";
    $result = mysqli_query($conn, $sql);
    
    while($row = mysqli_fetch_assoc($result)){
        $cart_items[] = $row;
        $total += $row["price"] * $_SESSION["cart"][$row["product_id"]];
    }
}

// จัดการการสั่งซื้อ
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // เริ่ม transaction
    mysqli_begin_transaction($conn);
    
    try {
        // สร้างคำสั่งซื้อใหม่
        $order_sql = "INSERT INTO orders (user_id, total_amount, shipping_fee, shipping_address_id, status) 
                     VALUES (?, ?, ?, ?, 'pending')";
        $stmt = mysqli_prepare($conn, $order_sql);
        mysqli_stmt_bind_param($stmt, "iddi", $user_id, $total, $shipping_fee, $default_address['address_id']);
        mysqli_stmt_execute($stmt);
        $order_id = mysqli_insert_id($conn);

        // บันทึกรายการสินค้า
        foreach($cart_items as $item){
            $quantity = $_SESSION["cart"][$item["product_id"]];
            $subtotal = $item["price"] * $quantity;
            
            $order_item_sql = "INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) 
                              VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $order_item_sql);
            mysqli_stmt_bind_param($stmt, "iiidi", $order_id, $item["product_id"], $quantity, $item["price"], $subtotal);
            mysqli_stmt_execute($stmt);

            // อัพเดทสต็อกสินค้า
            $update_stock_sql = "UPDATE inventory SET quantity = quantity - ? WHERE product_id = ?";
            $stmt = mysqli_prepare($conn, $update_stock_sql);
            mysqli_stmt_bind_param($stmt, "ii", $quantity, $item["product_id"]);
            mysqli_stmt_execute($stmt);
        }

        // บันทึกข้อมูลการชำระเงิน
        $payment_sql = "INSERT INTO payments (order_id, amount, payment_method, status) 
                       VALUES (?, ?, ?, 'pending')";
        $stmt = mysqli_prepare($conn, $payment_sql);
        $payment_method = $_POST["payment_method"];
        $total_with_shipping = $total + $shipping_fee;
        mysqli_stmt_bind_param($stmt, "ids", $order_id, $total_with_shipping, $payment_method);
        mysqli_stmt_execute($stmt);

        // Commit transaction
        mysqli_commit($conn);
        
        // ล้างตะกร้า
        $_SESSION["cart"] = array();
        
        // ไปยังหน้าแสดงผลการสั่งซื้อ
        header("location: order_complete.php?order_id=" . $order_id);
        exit;
        
    } catch (Exception $e) {
        // Rollback หากเกิดข้อผิดพลาด
        mysqli_rollback($conn);
        $error_message = "เกิดข้อผิดพลาดในการสั่งซื้อ กรุณาลองใหม่อีกครั้ง";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>ชำระเงิน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>ชำระเงิน</h2>
        
        <?php if(isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">ที่อยู่จัดส่ง</h5>
                    </div>
                    <div class="card-body">
                        <?php if($default_address): ?>
                            <p>
                                <?php echo htmlspecialchars($default_address["address_line1"]); ?><br>
                                <?php if($default_address["address_line2"]): ?>
                                    <?php echo htmlspecialchars($default_address["address_line2"]); ?><br>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($default_address["city"]); ?><br>
                                <?php echo htmlspecialchars($default_address["postal_code"]); ?>
                            </p>
                            <a href="address.php" class="btn btn-outline-primary btn-sm">เปลี่ยนที่อยู่</a>
                        <?php else: ?>
                            <p>กรุณาเพิ่มที่อยู่จัดส่ง</p>
                            <a href="address.php" class="btn btn-primary">เพิ่มที่อยู่</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">วิธีการชำระเงิน</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" 
                                       id="cod" value="cod" checked>
                                <label class="form-check-label" for="cod">
                                    เก็บเงินปลายทาง
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" 
                                       id="bank_transfer" value="bank_transfer">
                                <label class="form-check-label" for="bank_transfer">
                                    โอนเงินผ่านธนาคาร
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" 
                                    <?php echo (!$default_address ? 'disabled' : ''); ?>>
                                ยืนยันการสั่งซื้อ
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">สรุปคำสั่งซื้อ</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach($cart_items as $item): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>
                                    <?php echo htmlspecialchars($item["name"]); ?> 
                                    x <?php echo $_SESSION["cart"][$item["product_id"]]; ?>
                                </span>
                                <span>
                                    ฿<?php echo number_format($item["price"] * $_SESSION["cart"][$item["product_id"]], 2); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>ราคารวม</span>
                            <span>฿<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>ค่าจัดส่ง</span>
                            <span>฿<?php echo number_format($shipping_fee, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <strong>ยอดรวมทั้งหมด</strong>
                            <strong>฿<?php echo number_format($total + $shipping_fee, 2); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>