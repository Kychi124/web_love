<?php
// login.php
require_once "config.php";
session_start();

// ถ้าผู้ใช้ล็อกอินอยู่แล้ว ให้ส่งไปที่หน้า index.php
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

$email = $password = "";
$email_err = $password_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["email"]))) {
        $email_err = "กรุณากรอกอีเมล";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "กรุณากรอกรหัสผ่าน";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($email_err) && empty($password_err)) {
        // ดึง user_id, email, password_hash และ level จากฐานข้อมูล
        $sql = "SELECT user_id, email, password_hash, level FROM users WHERE email = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = $email;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $email, $hashed_password, $level);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // สร้างเซสชัน
                            session_start();
                            
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $id;
                            $_SESSION["email"] = $email;
                            $_SESSION["level"] = $level; // ✅ กำหนดค่า level

                            // ถ้าเป็น admin ให้ไปหน้า back_office.php
                            if ($level === "admin") {
                                header("location: back_office.php");
                            } else {
                                header("location: index.php");
                            }
                            exit;
                        } else {
                            $login_err = "อีเมลหรือรหัสผ่านไม่ถูกต้อง";
                        }
                    }
                } else {
                    $login_err = "อีเมลหรือรหัสผ่านไม่ถูกต้อง";
                }
            } else {
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
    <title>เข้าสู่ระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="mt-5">เข้าสู่ระบบ</h2>
                <?php 
                if(!empty($login_err)){
                    echo '<div class="alert alert-danger">' . $login_err . '</div>';
                }        
                ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group mb-3">
                        <label>อีเมล</label>
                        <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                        <span class="invalid-feedback"><?php echo $email_err; ?></span>
                    </div>    
                    <div class="form-group mb-3">
                        <label>รหัสผ่าน</label>
                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>
                    <div class="form-group mb-3">
                        <input type="submit" class="btn btn-primary" value="เข้าสู่ระบบ">
                    </div>
                    <p>ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิกที่นี่</a></p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>