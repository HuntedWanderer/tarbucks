<?php
include '_base.php';

if (is_post()) {
    $user_id  = req('user_id');  
    $password = req('password'); 

    $_err = [];

    if (empty($user_id)) {
        $_err['user_id'] = 'Please enter your User id';
    }

    if (empty($password)) {
        $_err['password'] = 'Please enter your password';
    }

    if (empty($_err)) {
        // 新增锁定检查逻辑
        $stm = $_db->prepare('SELECT *, 
                            IF(locked_until > NOW(), 1, 0) as is_locked 
                            FROM member 
                            WHERE user_id = ?');
        $stm->execute([$user_id]);
        $user = $stm->fetch();

        if ($user) {
            // 检查锁定
            if ($user['is_locked']) {
                $unlock_time = date("H:i", strtotime($user['locked_until']));
                temp('info', "Account locked. Please try again after $unlock_time");
                redirect('head.php');
                exit;
            }


            if (password_verify($password, $user['password'])) {
                // 登录成功时重置
                $_SESSION['user_id'] = $user['user_id']; 
                $_SESSION['user'] = $user;
                $_db->prepare('UPDATE member 
                               SET failed_attempts = 0, 
                                   locked_until = NULL 
                               WHERE user_id = ?')
                    ->execute([$user_id]);

                temp('info', 'Login Successful');
                $target = ($user['role'] === 'Admin') ? 'try.php' : 'member.php';
                login($user, $target);
                exit;
            } else {
                // 密码错误
                $new_attempts = $user['failed_attempts'] + 1;
                $remaining = 5 - $new_attempts;

                $update_data = [
                    ':attempts' => $new_attempts,
                    ':user_id' => $user_id
                ];

                $set_clause = 'failed_attempts = :attempts';
                
                if ($new_attempts >= 5) {
                    $update_data[':locked_until'] = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                    $set_clause .= ', locked_until = :locked_until';
                    $message = "Too many failed attempts. Account locked for 15 minutes.";
                } else {
                    $message = "User ID or password incorrect. Remaining attempts: $remaining";
                }

               
                $_db->prepare("UPDATE member 
                             SET $set_clause 
                             WHERE user_id = :user_id")
                    ->execute($update_data);

                temp('info', $message);
            }
        } else {
            temp('info', 'User ID or password incorrect');
        }

        redirect('head.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Tarbuck Coffee' ?></title>
    <link rel="stylesheet" href="zz.css">
    <link rel="stylesheet" href="/css/style.css">
    <style>

        
      
    </style>
</head>
<body>
    <div id="info"><?= (temp('info')) ?></div>
   <?php include 'header.php'?>
   
        </div>
    </header>


    <div class="main-content">
        <div class="login-container">
            <h2>Login</h2>
            <form method="POST">
                <div class="form-group">
                    <input type="text" 
                           name="user_id" 
                           placeholder="User ID"
                           value="<?= encode(post('user_id')) ?>"
                           required
                           autofocus>
                    <?php if (isset($_err['user_id'])): ?>
                        <div class="error"><?= $_err['user_id'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <input type="password" 
                           name="password" 
                           placeholder="Password"
                           required>
                
                    <?php if (temp('info') && strpos(temp('info'), 'Remaining attempts') !== false): ?>
                        <div class="error"><?= temp('info') ?></div>
                    <?php endif; ?>
                </div>
                <button type="submit" class="submit-btn">Login</button>
            </form>
        </div>
        
        <div class="login-links" style="justify-content: center; gap: 20px;">
         <a href="createAcc.php">Create Account</a>
    <a href="forgot_password.php">Forgot Password?</a>
    </form>
</div>
        </div>
    </div>



</body>
</html>