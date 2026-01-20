<?php 
include 'header.php'; 

// Redirect if already logged in as admin
if (isAdmin()) {
    header("Location: admin_dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Strict check for admin role
        if ($user['role'] === 'admin') {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            header("Location: admin_dashboard.php");
            exit;
        } else {
            echo "<div class='alert alert-danger'>Access Denied. This area is for administrators only.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Invalid credentials.</div>";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-4">
        <div class="text-center mb-4">
            <h2 class="text-primary fw-bold">RunConnect Admin</h2>
        </div>
        <div class="card shadow">
            <div class="card-header bg-dark text-white text-center">
                <h4>Admin Login</h4>
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
                    <button type="submit" class="btn btn-dark w-100">Login to Dashboard</button>
                </form>
                <div class="text-center mt-3">
                    <small><a href="login.php">Back to User Login</a></small>
                    <span class="mx-2">|</span>
                    <small><a href="admin_register.php">Register New Admin</a></small>
                </div>
            </div>
        </div>
    </div>
</div>
</body></html>