<?php
require_once "config.php";
session_start();

// ตรวจสอบสิทธิ์แอดมิน
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_level"] !== "admin") {
    header("location: login.php");
    exit;
}

// ตรวจสอบค่าพารามิเตอร์ id
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("location: product.php");
    exit;
}

$product_id = $_GET["id"];

// ลบรูปภาพสินค้าทั้งหมดที่เกี่ยวข้อง
$sql_images = "SELECT image_url FROM product_images WHERE product_id = ?";
$stmt_images = mysqli_prepare($conn, $sql_images);
mysqli_stmt_bind_param($stmt_images, "i", $product_id);
mysqli_stmt_execute($stmt_images);
$result_images = mysqli_stmt_get_result($stmt_images);

while ($row = mysqli_fetch_assoc($result_images)) {
    $image_path = "uploads/" . $row['image_url'];
    if (file_exists($image_path)) {
        unlink($image_path);
    }
}

// ลบรายการรูปภาพออกจากฐานข้อมูล
$sql_delete_images = "DELETE FROM product_images WHERE product_id = ?";
$stmt_delete_images = mysqli_prepare($conn, $sql_delete_images);
mysqli_stmt_bind_param($stmt_delete_images, "i", $product_id);
mysqli_stmt_execute($stmt_delete_images);

// ลบข้อมูลสินค้า
$sql_delete_product = "DELETE FROM products WHERE product_id = ?";
$stmt_delete_product = mysqli_prepare($conn, $sql_delete_product);
mysqli_stmt_bind_param($stmt_delete_product, "i", $product_id);

if (mysqli_stmt_execute($stmt_delete_product)) {
    echo "<script>alert('ลบสินค้าสำเร็จ!'); window.location.href='product.php';</script>";
} else {
    echo "<script>alert('เกิดข้อผิดพลาดในการลบสินค้า'); window.location.href='product.php';</script>";
}
?>
