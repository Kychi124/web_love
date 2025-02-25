<?php
session_start();
require_once "config.php";

// ตรวจสอบว่ามีค่า address_id หรือไม่
if (isset($_GET["address_id"])) {
    $address_id = $_GET["address_id"];

    // ลบที่อยู่จากฐานข้อมูล
    $sql = "DELETE FROM user_addresses WHERE address_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $address_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION["success"] = "ลบที่อยู่เรียบร้อยแล้ว!";
    } else {
        $_SESSION["error"] = "เกิดข้อผิดพลาดในการลบที่อยู่!";
    }

    header("location: address.php");
    exit;
} else {
    $_SESSION["error"] = "ไม่พบที่อยู่ที่ต้องการลบ!";
    header("location: address.php");
    exit;
}
