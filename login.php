<?php
session_start();
require_once 'config/database.php';
require_once 'auth/auth.php';

$auth = new Auth($conn);

if ($auth->isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            
            if ($auth->login($username, $password)) {
                header('Location: index.php');
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } elseif ($_POST['action'] === 'register') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $email = $_POST['email'];
            
            if ($auth->register($username, $password, $email)) {
                $error = 'Đăng ký thành công!.';
            } else {
                $error = 'Đăng ký thất bại. Username hoặc email đã tồn tại.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Todo List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#login">Đăng nhập</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#register">Đăng ký</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                        <div class="alert alert-info"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="login">
                                <form method="POST">
                                    <input type="hidden" name="action" value="login">
                                    <div class="mb-3">
                                        <label class="form-label">Tài khoản</label>
                                        <input type="text" class="form-control" name="username" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Mật khẩu</label>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Đăng nhập</button>
                                </form>
                            </div>
                            <div class="tab-pane fade" id="register">
                                <form method="POST">
                                    <input type="hidden" name="action" value="register">
                                    <div class="mb-3">
                                        <label class="form-label">Tài khoản</label>
                                        <input type="text" class="form-control" name="username" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Mật khẩu</label>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Đăng ký</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 