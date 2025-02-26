<?php
require_once "config.php";
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 🛑 ป้องกันการเข้าถึง
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if (!isset($_SESSION["user_level"]) || $_SESSION["user_level"] !== "admin") {
    header("location: index.php");
    exit;
}

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("location: product.php");
    exit;
}

$product_id = $_GET["id"];

// ✅ ดึงข้อมูลสินค้า
$sql = "SELECT * FROM products WHERE product_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo "<script>alert('ไม่พบข้อมูลสินค้า'); window.location.href='product.php';</script>";
    exit;
}

// ✅ ดึงรูปภาพสินค้า
$images = [];
$sql_images = "SELECT * FROM product_images WHERE product_id = ?";
$stmt_images = mysqli_prepare($conn, $sql_images);
mysqli_stmt_bind_param($stmt_images, "i", $product_id);
mysqli_stmt_execute($stmt_images);
$result_images = mysqli_stmt_get_result($stmt_images);
while ($row = mysqli_fetch_assoc($result_images)) {
    $images[] = $row;
}

// ✅ ฟังก์ชันดึงข้อมูล Category Options
function generateOption($table, $id_field, $name_field, $selected_id) {
    global $conn;
    $options = "";
    $sql = "SELECT * FROM $table";
    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $selected = ($row[$id_field] == $selected_id) ? "selected" : "";
        $options .= "<option value='{$row[$id_field]}' $selected>{$row[$name_field]}</option>";
    }
    return $options;
}

// ✅ อัปเดตสินค้า
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST["product_name"]);
    $description = mysqli_real_escape_string($conn, $_POST["product_description"]);
    $category_id = $_POST["category_id"];
    $price = $_POST["price"];
    $cost_price = $_POST["cost_price"];
    $status = $_POST["status"];

    // 🛠️ UPDATE สินค้า
    $sql_update = "UPDATE products SET product_name=?, product_description=?, category_id=?, price=?, cost_price=?, status=? WHERE product_id=?";
    $stmt_update = mysqli_prepare($conn, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "ssiddsi", $name, $description, $category_id, $price, $cost_price, $status, $product_id);

    if (mysqli_stmt_execute($stmt_update)) {
        // 🖼️ อัปโหลดรูปใหม่ (ถ้ามี)
        if (!empty($_FILES["product_images"]["name"][0])) {
            foreach ($_FILES["product_images"]["tmp_name"] as $key => $tmp_name) {
                if ($_FILES["product_images"]["error"][$key] == 0) {
                    $image_name = uniqid() . "_" . basename($_FILES["product_images"]["name"][$key]);
                    $target_path = "uploads/" . $image_name;

                    if (move_uploaded_file($tmp_name, $target_path)) {
                        // บันทึกรูปภาพใหม่
                        mysqli_query($conn, "INSERT INTO product_images (product_id, image_url) VALUES ('$product_id', '$image_name')");
                    }
                }
            }
        }

        header("Location: product.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขสินค้า - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <ul class="navbar-nav me-auto"></ul>
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
    <h2 class="text-center m-3">แก้ไขสินค้า</h2>
    <form action="product_edit.php?id=<?php echo $product_id; ?>" method="post" enctype="multipart/form-data" class="row g-3">
        
        <div class="col-md-6">
            <label class="form-label">ชื่อสินค้า*</label>
            <input type="text" name="product_name" class="form-control" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">รายละเอียดสินค้า</label>
            <textarea name="product_description" class="form-control"><?php echo htmlspecialchars($product['product_description']); ?></textarea>
        </div>

        <div class="col-md-6">
            <label class="form-label">ประเภทสินค้า*</label>
            <select name="category_id" class="form-select">
                <?php echo generateOption("categories", "category_id", "category_name", $product["category_id"]); ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">สถานะสินค้า*</label>
            <select name="status" class="form-select">
                <option value="available" <?php echo ($product["status"] == "available") ? "selected" : ""; ?>>มีสินค้า</option>
                <option value="out_of_stock" <?php echo ($product["status"] == "out_of_stock") ? "selected" : ""; ?>>หมด</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">ราคาขาย*</label>
            <div class="input-group has-validation"> 
                <span class="input-group-text">฿</span>
                <input type="number" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>
                <span class="input-group-text">.00</span>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label">ราคาต้นทุน*</label>
            <div class="input-group has-validation">
                <span class="input-group-text">฿</span>
                <input type="number" name="cost_price" class="form-control" value="<?php echo $product['cost_price']; ?>" required>
                <span class="input-group-text">.00</span>
            </div>
        </div>

        <!-- 🔹 อัปโหลดรูปภาพใหม่ -->
        <div class="col-md-6">
            <label class="form-label">อัปโหลดรูปภาพใหม่</label>
            <input type="file" id="product_images" name="product_images[]" class="form-control" multiple accept="image/*" onchange="previewNewImages()">
        </div>

        <!-- 🔹 พรีวิวรูปภาพใหม่ -->
        <div class="col-md-12">
            <label class="form-label">พรีวิวรูปใหม่</label>
            <div id="newImagePreview" class="d-flex flex-wrap"></div>
        </div>

        <!-- 🔹 แสดงรูปภาพเก่าที่มีอยู่แล้ว -->
        <div class="col-md-12">
            <label class="form-label">รูปภาพปัจจุบัน</label>
            <div class="d-flex flex-wrap">
                <?php foreach ($images as $img) : ?>
                    <div class="position-relative me-2">
                        <img src="uploads/<?php echo $img['image_url']; ?>" class="img-thumbnail" style="width: 100px; height: 100px;">
                        <a href="delete_image.php?image_id=<?php echo $img['image_id']; ?>&product_id=<?php echo $product_id; ?>" class="btn btn-danger btn-sm position-absolute" style="top: 5px; right: 5px;">&times;</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="text-center mt-4">
            <button class="btn btn-primary btn-lg" type="submit">บันทึก</button>
            <a href="product.php" class="btn btn-danger btn-lg">ยกเลิก</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function previewNewImages() {
        let input = document.getElementById("product_images");
        let previewContainer = document.getElementById("newImagePreview");

        previewContainer.innerHTML = ""; // เคลียร์พรีวิวก่อนแสดงใหม่

        if (input.files) {
            Array.from(input.files).forEach(file => {
                if (file.type.startsWith("image/")) { // ตรวจสอบว่าเป็นไฟล์รูปภาพ
                    let reader = new FileReader();
                    reader.onload = function (e) {
                        let imgContainer = document.createElement("div");
                        imgContainer.className = "position-relative me-2";

                        let img = document.createElement("img");
                        img.src = e.target.result;
                        img.className = "img-thumbnail";
                        img.style = "width: 100px; height: 100px; object-fit: cover; margin: 5px;";

                        // 🔹 ปุ่มลบรูป
                        let removeBtn = document.createElement("button");
                        removeBtn.className = "btn btn-danger btn-sm position-absolute";
                        removeBtn.style = "top: 5px; right: 5px;";
                        removeBtn.innerHTML = "&times;";
                        removeBtn.onclick = function () {
                            removeNewImage(file);
                        };

                        imgContainer.appendChild(img);
                        imgContainer.appendChild(removeBtn);
                        previewContainer.appendChild(imgContainer);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    }

    function removeNewImage(file) {
        let input = document.getElementById("product_images");
        let dt = new DataTransfer();
        Array.from(input.files).forEach(f => {
            if (f !== file) {
                dt.items.add(f);
            }
        });
        input.files = dt.files;
        previewNewImages();
    }
</script>
<script>
    document.getElementById("productForm").addEventListener("submit", function(event) {
        let productName = document.querySelector('input[name="product_name"]').value.trim();
        let category = document.querySelector('select[name="category_id"]').value;
        let price = parseFloat(document.querySelector('input[name="price"]').value);
        let costPrice = parseFloat(document.querySelector('input[name="cost_price"]').value);
        let status = document.querySelector('select[name="status"]').value;

        // 📌 เช็คว่ากรอกครบทุกช่องหรือไม่
        if (!productName || !category || isNaN(price) || isNaN(costPrice) || !status) {
            event.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'กรุณากรอกข้อมูลให้ครบ!',
                text: 'โปรดตรวจสอบว่าคุณกรอกข้อมูลครบทุกช่อง',
                confirmButtonColor: '#d33',
                confirmButtonText: 'ตกลง'
            });
            return;
        }

        // 📌 เช็คราคาขายห้ามต่ำกว่าต้นทุน
        if (price < costPrice) {
            event.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'ราคาขายต้องมากกว่าราคาต้นทุน!',
                text: 'กรุณากรอกราคาขายให้ถูกต้อง',
                confirmButtonColor: '#d33',
                confirmButtonText: 'ตกลง'
            });
            return;
        }

        if (successMessage) {
            Swal.fire({
                icon: 'success',
                title: 'แก้ไขสำเร็จ!',
                text: successMessage,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'ตกลง'
            });
            sessionStorage.removeItem("success_message"); // ลบค่าหลังแสดงแล้ว
        }
    });
</script>

</body>
</html>
