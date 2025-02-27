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

$sql_script = "SELECT products.*,categories.category_name
                FROM products
                JOIN categories ON products.category_id = categories.category_id";
$result = mysqli_query($conn,$sql_script) or die(mysqli_error($conn));
if(mysqli_num_rows($result)> 0){
    $row = mysqli_fetch_assoc($result);
}else{
    $row = null;
}
$totalrows_result = mysqli_num_rows($result);

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
                        <li><a class="dropdown-item" href="orders_show.php">คำสั่งซื้อ</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">ออกจากระบบ</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="d-flex justify-content-between align-items-center px-5 my-4 fs-5">
    <div>
        <a href="back_office.php" class="text-black">back office</a>
        >
        <a href="product.php" class="text-black">product management</a>
    </div>
    <button 
        class="btn btn-success p-3 mx-4"
        onclick="window.location.href='product_add.php'">
        Add Product
    </button>
</div>
    <div class="p-4 m-4 bg-light rounded">
        <table class="table table-hover">
        <thead class="table-dark">
            <tr>
            <th>ลำดับ</th>
            <th>ชื่อสินค้า</th>
            <th>ประเภทสินค้า</th>
            <th>คำอธิบายสินค้า</th>
            <th>ราคาขาย</th>
            <th>ราคาต้นทุน</th>
            <th>สถานะของสินค้า</th>
            <th>จัดการ</th>
            </tr>
        </thead>
<?php 
    if($row){
        do{
?>
        <tbody>
            <tr>
            <td><?php echo $row['product_id'];?></td>
            <td><?php echo $row['product_name'];?></td>
            <td><?php echo $row['category_name'];?></td>
            <td><?php echo $row['product_description'];?></td>
            <td><?php echo $row['price'];?></td>
            <td><?php echo $row['cost_price'];?></td>
            <td><?php echo $row['status'];?></td>
            <td>
                <button class="btn btn-warning" onclick="window.location.href='product_edit.php?id=<?php echo $row['product_id'];?>'">แก้ไข</button>
                <button class="btn btn-danger" onclick="window.location.href='product_delete.php?id=<?php echo $row['product_id'];?>'">ลบ</button>
            </td>
            </tr>
<?php 
        }while($row = mysqli_fetch_assoc($result));
    }else{
        echo "<tr><td colspan ='8'>ไม่มีข้อมูลสินค้า</td></tr>";
    }
    ?>
        </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>