<?php
// cart.php
require_once "config.php";
session_start();

// ตรวจสอบการล็อกอิน
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// อัพเดทจำนวนสินค้า
if(isset($_POST["update_cart"])){
    foreach($_POST["quantity"] as $product_id => $quantity){
        if($quantity > 0){
            $_SESSION["cart"][$product_id] = $quantity;
        } else {
            unset($_SESSION["cart"][$product_id]);
        }
    }
}

// ลบสินค้าออกจากตะกร้า
if(isset($_POST["remove_item"])){
    $product_id = $_POST["remove_item"];
    unset($_SESSION["cart"][$product_id]);
}

// ดึงข้อมูลสินค้าในตะกร้า
$cart_items = array();
$total = 0;

if(!empty($_SESSION["cart"])){
    $product_ids = array_keys($_SESSION["cart"]);
    $sql = "SELECT * FROM products WHERE product_id IN (" . implode(",", $product_ids) . ")";
    $result = mysqli_query($conn, $sql);
    
    while($row = mysqli_fetch_assoc($result)){
        $cart_items[] = $row;
        $total += $row["price"] * $_SESSION["cart"][$row["product_id"]];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>ตะกร้าสินค้า</title>
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
                        <a class="nav-link active" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            ตะกร้าสินค้า
                            <span class="badge bg-danger">
                                <?php echo array_sum($_SESSION["cart"]); ?>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>ตะกร้าสินค้า</h2>
        <?php if(empty($cart_items)): ?>
            <div class="alert alert-info">
                ไม่มีสินค้าในตะกร้า <a href="index.php">เลือกซื้อสินค้า</a>
            </div>
        <?php else: ?>
            <form method="post">
                <table class="table">
                    <thead>
                        <tr>
                            <th>สินค้า</th>
                            <th>ราคา</th>
                            <th>จำนวน</th>
                            <th>รวม</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item["name"]); ?></td>
                            <td>฿<?php echo number_format($item["price"], 2); ?></td>
                            <td>
                                <input type="number" name="quantity[<?php echo $item['product_id']; ?>]" 
                                    value="<?php echo $_SESSION['cart'][$item['product_id']]; ?>" 
                                    min="1" class="form-control" style="width: 80px">
                            </td>
                            <td>
                                ฿<?php echo number_format($item["price"] * $_SESSION["cart"][$item["product_id"]], 2); ?>
                            </td>
                            <td>
                                <button type="submit" name="remove_item" value="<?php echo $item['product_id']; ?>" 
                                    class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>รวมทั้งหมด:</strong></td>
                            <td><strong>฿<?php echo number_format($total, 2); ?></strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="d-flex justify-content-between">
                    <button type="submit" name="update_cart" class="btn btn-primary">
                        <i class="fas fa-sync"></i> อัพเดทตะกร้า
                    </button>
                    <a href="checkout.php" class="btn btn-success">
                        <i class="fas fa-shopping-cart"></i> ชำระเงิน
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>