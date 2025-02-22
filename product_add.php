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

// 🛠️ ดึงหมวดหมู่สินค้ามาแสดง
function generateOption($table, $valueColumn, $textColumn) {
    global $conn;
    $options = '<option value="">เลือกหมวดหมู่</option>';
    $query = "SELECT $valueColumn, $textColumn FROM $table";
    $result = mysqli_query($conn, $query);

    while ($row = mysqli_fetch_assoc($result)) {
        $options .= '<option value="'.$row[$valueColumn].'">'.$row[$textColumn].'</option>';
    }
    return $options;
}

// ✅ เพิ่มสินค้า
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST["product_name"]);
    $description = mysqli_real_escape_string($conn, $_POST["product_description"]);
    $category_id = $_POST["category_id"];
    $price = $_POST["price"];
    $cost_price = $_POST["cost_price"];
    $status = $_POST["status"];

    // 🛠️ INSERT สินค้าเข้า DB
    $sql = "INSERT INTO products (product_name, product_description, category_id, price, cost_price, status) 
            VALUES ('$name', '$description', '$category_id', '$price', '$cost_price', '$status')";

    if (mysqli_query($conn, $sql)) {
        $product_id = mysqli_insert_id($conn); // ดึง ID ของสินค้าที่เพิ่มล่าสุด

        // 🖼️ อัปโหลดรูปภาพ
        if (!empty($_FILES["product_images"]["name"][0])) {
            foreach ($_FILES["product_images"]["tmp_name"] as $key => $tmp_name) {
                $image_name = time()."_".basename($_FILES["product_images"]["name"][$key]);
                $target_path = "uploads/".$image_name;

                if (move_uploaded_file($tmp_name, $target_path)) {
                    mysqli_query($conn, "INSERT INTO product_images (product_id, image_url) VALUES ('$product_id', '$image_name')");
                }
            }
        }
        echo "<script>alert('เพิ่มสินค้าสำเร็จ!'); window.location.href='product.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
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
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">ออกจากระบบ</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container mt-5 mb-5 p-5 bg-light rounded">
    <h2 class="text-center m-3">เพิ่มสินค้า</h2>
    <form id="productForm" class="needs-validation row g-3" action="product_add.php" method="post" enctype="multipart/form-data" novalidate>
        <div class="col-md-6">
            <label class="form-label">ชื่อสินค้า</label>
            <input type="text" name="product_name" class="form-control" required>
            <div class="valid-feedback"></div>
            <div class="invalid-feedback">กรุณากรอกชื่อสินค้า</div>
        </div>

        <div class="col-md-6">
            <label class="form-label">รายละเอียดสินค้า</label>
            <textarea name="product_description" class="form-control" required></textarea>
            <div class="valid-feedback"></div>
            <div class="invalid-feedback">กรุณากรอกรายละเอียดสินค้า</div>
        </div>

        <div class="col-md-6">
            <label class="form-label">ประเภทสินค้า</label>
            <select name="category_id" class="form-select" required>
                <?php echo generateOption("categories", "category_id", "category_name"); ?>
            </select>
            <div class="valid-feedback"></div>
            <div class="invalid-feedback">กรุณาเลือกประเภทสินค้า</div>
        </div>

        <div class="col-md-6">
            <label class="form-label">สถานะสินค้า</label>
            <select name="status" class="form-select" required>
                <option value="">เลือกสถานะ</option>
                <option value="available">มีสินค้า</option>
                <option value="out_of_stock">หมด</option>
            </select>
            <div class="valid-feedback"></div>
            <div class="invalid-feedback">กรุณาเลือกสถานะสินค้า</div>
        </div>

        <div class="col-md-6">
            <label class="form-label">ราคาขาย</label>
            <div class="input-group has-validation">
                <span class="input-group-text">฿</span>
                <input type="number" name="price" class="form-control" required>
                <span class="input-group-text">.00</span>
                <div class="valid-feedback"></div>
                <div class="invalid-feedback">กรุณากรอกราคาขาย</div>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label">ราคาต้นทุน</label>
            <div class="input-group has-validation">
                <span class="input-group-text">฿</span>
                <input type="number" name="cost_price" class="form-control" required>
                <span class="input-group-text">.00</span>
                <div class="valid-feedback"></div>
                <div class="invalid-feedback">กรุณากรอกราคาต้นทุน</div>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label">อัปโหลดรูปภาพ</label>
            <input type="file" name="product_images[]" class="form-control" multiple required>
            <div class="valid-feedback"></div>
            <div class="invalid-feedback">กรุณาอัปโหลดรูปภาพสินค้า</div>
        </div>

        <div class="col-12 text-center mt-4">
            <button class="btn btn-success btn-lg" type="submit">เพิ่มสินค้า</button>
            <a href="product.php" class="btn btn-danger btn-lg">ยกเลิก</a>
        </div>
    </form>
</div>
    <script>
        (function () {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');

            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
