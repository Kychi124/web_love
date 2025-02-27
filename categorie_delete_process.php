<?php
require_once "config.php";
session_start();

// 🛑 ป้องกันการเข้าถึงเฉพาะ Admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_level"] !== "admin") {
    header("location: index.php");
    exit;
}

// 🛑 รับค่า category_id ที่ต้องลบ
if (!isset($_GET["id"]) || empty($_GET["id"])) {
    header("location: categorie.php");
    exit;
}

$category_id = $_GET["id"];

// ✅ ตรวจสอบว่ามีสินค้าผูกกับประเภทนี้หรือไม่
$sql_check = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "i", $category_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
$row_check = mysqli_fetch_assoc($result_check);

if ($row_check["count"] > 0) {
    $_SESSION["error_message"] = "ไม่สามารถลบประเภทนี้ได้ เนื่องจากมีสินค้าผูกอยู่!";
    header("location: categorie.php");
    exit;
}

// ✅ ลบประเภทสินค้า
$sql_delete = "DELETE FROM categories WHERE category_id = ?";
$stmt_delete = mysqli_prepare($conn, $sql_delete);
mysqli_stmt_bind_param($stmt_delete, "i", $category_id);

if (mysqli_stmt_execute($stmt_delete)) {
    $_SESSION["success_message"] = "ลบประเภทสินค้าเรียบร้อยแล้ว!";
} else {
    $_SESSION["error_message"] = "เกิดข้อผิดพลาด: " . mysqli_error($conn);
}

header("location: categorie.php");
exit;
?>