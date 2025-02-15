<?php
// register.php
require_once "config.php";
session_start();

$email = $password = $confirm_password = "";
$email_err = $password_err = $confirm_password_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // ตรวจสอบอีเมล
    if(empty(trim($_POST["email"]))){
        $email_err = "กรุณากรอกอีเมล";
    } else {
        $sql = "SELECT user_id FROM users WHERE email = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = trim($_POST["email"]);
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $email_err = "อีเมลนี้ถูกใช้งานแล้ว";
                } else{
                    $email = trim($_POST["email"]);
                }
            } else{
                echo "มีบางอย่างผิดพลาด กรุณาลองใหม่อีกครั้ง";
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // ตรวจสอบรหัสผ่าน
    if(empty(trim($_POST["password"]))){
        $password_err = "กรุณากรอกรหัสผ่าน";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // ตรวจสอบการยืนยันรหัสผ่าน
    if(empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "กรุณายืนยันรหัสผ่าน";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "รหัสผ่านไม่ตรงกัน";
        }
    }
    
    // ตรวจสอบข้อผิดพลาดก่อนบันทึกลงฐานข้อมูล
    if(empty($email_err) && empty($password_err) && empty($confirm_password_err)){
        $sql = "INSERT INTO users (email, password_hash) VALUES (?, ?)";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "ss", $param_email, $param_password);
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            
            if(mysqli_stmt_execute($stmt)){
                header("location: index.php");
                exit();
            } else{
                echo "มีบางอย่างผิดพลาด กรุณาลองใหม่อีกครั้ง";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>สมัครสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="mt-5">สมัครสมาชิก</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group mb-3">
                        <label>อีเมล</label>
                        <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                        <span class="invalid-feedback"><?php echo $email_err; ?></span>
                    </div>    
                    <div class="form-group mb-3">
                        <label>รหัสผ่าน</label>
                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>
                    <div class="form-group mb-3">
                        <label>ยืนยันรหัสผ่าน</label>
                        <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                        <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                    </div>
                    <div class="form-group mb-3">
                        <input type="submit" class="btn btn-primary" value="สมัครสมาชิก">
                    </div>
                    <p>มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบที่นี่</a></p>
                </form>
            </div>
        </div>
    </div>    
</body>
</html>
