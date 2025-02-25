<?php
require_once "config.php";
session_start();

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// ตรวจสอบค่า id_account ใน SESSION
if (!isset($_SESSION["user_id"])) {
    die("เกิดข้อผิดพลาด: ไม่พบค่า user_id ใน SESSION กรุณาล็อกอินใหม่");
}

$user_id = $_SESSION["user_id"];

// ดึงข้อมูลโปรไฟล์จากฐานข้อมูล
$sql = "SELECT email, first_name, last_name, phone, user_images FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("เกิดข้อผิดพลาด SQL: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// ตรวจสอบการอัปเดตข้อมูล
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $phone = $_POST["phone"];

    // กำหนดเส้นทางสำหรับอัปโหลดภาพ
    $target_dir = "web_love/images_account/";
    $imageFileType = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));

    // ตั้งชื่อไฟล์ใหม่โดยใช้วันที่และเวลา
    $new_image_name = date("Ymd_His") . '.' . $imageFileType;
    $target_file = $target_dir . $new_image_name;
    $uploadOk = 1;

    // ตรวจสอบว่าเป็นไฟล์รูปภาพ
    if (isset($_POST["submit"])) {
        $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            $_SESSION["error"] = "ไฟล์ไม่ใช่รูปภาพ.";
            $uploadOk = 0;
        }
    }

    // ตรวจสอบว่ามีไฟล์อยู่แล้ว
    if (file_exists($target_file)) {
        $_SESSION["error"] = "ไฟล์นี้มีอยู่แล้ว.";
        $uploadOk = 0;
    }

    // ตรวจสอบขนาดไฟล์
    if ($_FILES["profile_image"]["size"] > 500000) {
        $_SESSION["error"] = "ขออภัย ไฟล์ของคุณใหญ่เกินไป.";
        $uploadOk = 0;
    }

    // อนุญาตเฉพาะรูปแบบไฟล์ที่กำหนด
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        $_SESSION["error"] = "ขออภัย อนุญาตเฉพาะไฟล์ JPG, JPEG, PNG & GIF เท่านั้น.";
        $uploadOk = 0;
    }

    // ตรวจสอบว่าทุกอย่างถูกต้องหรือไม่
    if ($uploadOk == 0) {
        $_SESSION["error"] = "ขออภัย ไฟล์ของคุณไม่ถูกอัปโหลด.";
    } else {
        // หากทุกอย่างถูกต้อง ให้ลองอัปโหลดไฟล์
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            // อัปเดตข้อมูลในฐานข้อมูล
            $sql = "UPDATE users SET first_name=?, last_name=?, phone=?, user_images=? WHERE user_id=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssi", $first_name, $last_name, $phone, $new_image_name, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION["success"] = "อัปเดตโปรไฟล์เรียบร้อยแล้ว!";
                header("location: profile.php");
                exit;
            } else {
                $_SESSION["error"] = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล!";
            }
        } else {
            $_SESSION["error"] = "ขออภัย เกิดข้อผิดพลาดในการอัปโหลดไฟล์.";
        }
    }
}



?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขโปรไฟล์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
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
            </ul>

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
    <div class="text-end mb-4"> <!-- ใช้ text-end เพื่อจัดแนวไปทางขวา -->
    <a href="index.php" class="btn btn-danger" color="red" >กลับสู่หน้าแรก</a>
    </div>

    <h2 class="text-center">แก้ไขโปรไฟล์</h2>

<?php if (isset($_SESSION["success"])): ?>
    <div class="alert alert-success"><?php echo $_SESSION["success"]; unset($_SESSION["success"]); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION["error"])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION["error"]; unset($_SESSION["error"]); ?></div>
<?php endif; ?>

<form action="profile.php" method="post" enctype="multipart/form-data">
    <div class="mb-3 text-center">
        <?php if (!empty($user['user_images'])): ?>
            <img src="web_love/images_account/<?php echo htmlspecialchars($user['user_images']); ?>" 
            alt="Profile Image" class="rounded-circle" width="150" height="150">
        <?php else: ?>
            <i class="bi bi-person-circle" style="font-size: 150px;"></i>
        <?php endif; ?>
        <br>
        <input type="file" name="profile_image" class="form-control mt-2">
    </div>

    <div class="mb-3">
        <label class="form-label">ชื่อ</label>
        <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">นามสกุล</label>
        <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">เบอร์โทร</label>
        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
    </div>


        <div class="text-center">
            <button type="submit" name="submit" class="btn btn-success">บันทึก</button>
            <a href="address.php" class="btn btn-primary">แก้ไขที่อยู่</a>
            <a href="profile_pass_edit.php" class="btn btn-warning">เปลี่ยนรหัสผ่าน</a>
        </div>

    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
