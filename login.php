<?php 
include 'header.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        
        if ($user['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        echo "<div class='alert alert-danger'>Invalid credentials.</div>";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-4">
        <div class="text-center mb-4">
            <h2 class="text-primary fw-bold">RunConnect</h2>
        </div>
        <div class="card shadow">
            <div class="card-header bg-white text-center">
                <h4>Login</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <div class="text-center mt-3">
                    <small>Don't have an account? <a href="registration.php">Create one</a></small>
                    <span class="mx-2">|</span>
                    <small><a href="admin_login.php">Admin Login</a></small>
                </div>
            </div>
        </div>
    </div>
</div>
</body></html>
