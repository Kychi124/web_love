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

// 🛠️ ดึงข้อมูลสินค้า
$product_id = $_GET['id'];
$query = "SELECT * FROM products WHERE product_id = '$product_id'";
$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);

// ✅ แก้ไขสินค้า
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST["product_name"]);
    $description = mysqli_real_escape_string($conn, $_POST["product_description"]);
    $category_id = $_POST["category_id"];
    $price = $_POST["price"];
    $cost_price = $_POST["cost_price"];
    $status = $_POST["status"];

    // 🛠️ UPDATE สินค้าใน DB
    $sql = "UPDATE products SET product_name='$name', product_description='$description', category_id='$category_id', price='$price', cost_price='$cost_price', status='$status' WHERE product_id='$product_id'";

    if (mysqli_query($conn, $sql)) {
        // 🖼️ อัปโหลดรูปภาพ (หลายรูป)
        if (!empty($_FILES["product_images"]["name"][0])) {
            foreach ($_FILES["product_images"]["tmp_name"] as $key => $tmp_name) {
                if ($_FILES["product_images"]["error"][$key] == 0) { // ตรวจสอบว่าไม่มีข้อผิดพลาด
                    $image_name = uniqid() . "_" . basename($_FILES["product_images"]["name"][$key]);
                    $target_path = "uploads/" . $image_name;

                    if (move_uploaded_file($tmp_name, $target_path)) {
                        // บันทึกรูปภาพลงฐานข้อมูล
                        mysqli_query($conn, "INSERT INTO product_images (product_id, image_url) VALUES ('$product_id', '$image_name')");
                    }
                }
            }
        }
        echo "<script>alert('แก้ไขสินค้าสำเร็จ!'); window.location.href='product.php';</script>";
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
    <title>Product Edit - Admin</title>
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
    <form id="productForm" class="needs-validation row g-3" action="product_edit.php?id=<?php echo $product_id; ?>" method="post" enctype="multipart/form-data" novalidate>
        <div class="col-md-6">
            <label class="form-label">ชื่อสินค้า*</label>
            <input type="text" name="product_name" class="form-control" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
            <div class="valid-feedback"></div>
            <div class="invalid-feedback">กรุณากรอกชื่อสินค้า</div>
        </div>

        <div class="col-md-6">
            <label class="form-label">รายละเอียดสินค้า</label>
            <textarea name="product_description" class="form-control" required><?php echo htmlspecialchars($product['product_description']); ?></textarea>
            <div class="valid-feedback"></div>
            <div class="invalid-feedback">กรุณากรอกรายละเอียดสินค้า</div>
        </div>

        <div class="col-md-6">
            <label class="form-label">ประเภทสินค้า*</label>
            <select name="category_id" class="form-select" required>
                <?php echo generateOption("categories", "category_id", "category_name", $product['category_id']); ?>
            </select>
            <div class="valid-feedback"></div>
            <div class="invalid-feedback">กรุณาเลือกประเภทสินค้า</div>
        </div>

        <div class="col-md-6">
            <label class="form-label">สถานะสินค้า*</label>
            <select name="status" class="form-select" required>
                <option value="available" <?php echo $product['status'] == 'available' ? 'selected' : ''; ?>>มีสินค้า</option>
                <option value="out_of_stock" <?php echo $product['status'] == 'out_of_stock' ? 'selected' : ''; ?>>หมด</option>
            </select>
            <div class="valid-feedback"></div>
            <div class="invalid-feedback">กรุณาเลือกสถานะสินค้า</div>
        </div>

        <div class="col-md-6">
            <label class="form-label">ราคาขาย*</label>
            <div class="input-group has-validation">
                <span class="input-group-text">฿</span>
                <input type="number" name="price" class="form-control" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                <span class="input-group-text">.00</span>
                <div class="valid-feedback"></div>
                <div class="invalid-feedback">กรุณากรอกราคาขาย</div>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label">ราคาต้นทุน*</label>
            <div class="input-group has-validation">
                <span class="input-group-text">฿</span>
                <input type="number" name="cost_price" class="form-control" value="<?php echo htmlspecialchars($product['cost_price']); ?>" required>
                <span class="input-group-text">.00</span>
                <div class="valid-feedback"></div>
                <div class="invalid-feedback">กรุณากรอกราคาต้นทุน</div>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label">อัปโหลดรูปภาพสินค้า*</label>
            <input type="file" name="product_images[]" id="product_images" class="form-control" multiple accept="image/*" onchange="addImages()">
            <div class="valid-feedback"></div>
            <div class="invalid-feedback">กรุณาอัปโหลดรูปภาพสินค้า</div>
        </div>

        <!-- 🔹 แสดงตัวอย่างรูป -->
        <div class="col-md-12">
            <label class="form-label">ตัวอย่างรูปภาพ</label>
            <div id="imagePreview" class="d-flex flex-wrap gap-2"></div>
        </div>

        <div class="col-12 text-center mt-4">
            <button class="btn btn-success btn-lg" type="submit">แก้ไขสินค้า</button>
            <a href="product.php" class="btn btn-danger btn-lg">ยกเลิก</a>
        </div>
    </form>
</div>

<script>
// เพิ่มโค้ด JavaScript ที่เหมือนเดิมเพื่อจัดการการแสดงตัวอย่างภาพและการลบภาพ
let selectedImages = [];

function addImages() {
    let input = document.getElementById("product_images");
    let files = Array.from(input.files);
    selectedImages = selectedImages.concat(files);
    updateFileInput();
    previewImages();
}

function previewImages() {
    let previewContainer = document.getElementById("imagePreview");
    previewContainer.innerHTML = "";

    selectedImages.forEach((file, index) => {
        let reader = new FileReader();
        reader.onload = function (e) {
            let imgContainer = document.createElement("div");
            imgContainer.className = "position-relative";

            let img = document.createElement("img");
            img.src = e.target.result;
            img.className = "img-thumbnail";
            img.style = "width: 150px; height: 150px; object-fit: cover; margin: 5px;";

            let removeBtn = document.createElement("button");
            removeBtn.className = "btn btn-danger btn-sm position-absolute";
            removeBtn.style = "top: 5px; right: 5px;";
            removeBtn.innerHTML = "&times;";
            removeBtn.onclick = function () {
                removeImage(index);
            };

            imgContainer.appendChild(img);
            imgContainer.appendChild(removeBtn);
            previewContainer.appendChild(imgContainer);
        };

        reader.readAsDataURL(file);
    });
}

function removeImage(index) {
    selectedImages.splice(index, 1);
    updateFileInput();
    previewImages();
}

function updateFileInput() {
    let dt = new DataTransfer();
    selectedImages.forEach(file => dt.items.add(file));
    document.getElementById("product_images").files = dt.files;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
