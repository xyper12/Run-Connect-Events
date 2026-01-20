<?php 
include 'header.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $gender = $_POST['gender'];
    $birthdate = $_POST['birthdate'];
    $role = 'admin';

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, gender, birthdate, role) VALUES (?, ?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$name, $email, $password, $gender, $birthdate, $role]);
        echo "<div class='alert alert-success'>Admin Account Created! <a href='admin_login.php'>Login here</a></div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Email already exists.</div>";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="text-center mb-4">
            <h2 class="text-primary fw-bold">RunConnect Admin</h2>
        </div>
        <div class="card shadow">
            <div class="card-header bg-dark text-white text-center"><h4>Admin Registration</h4></div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3"><label>Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                    <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
                    <div class="mb-3">
                        <label>Gender</label>
                        <select name="gender" class="form-control" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3"><label>Birthdate</label><input type="date" name="birthdate" class="form-control" required></div>
                    <button type="submit" class="btn btn-dark w-100">Register as Admin</button>
                </form>
                <div class="text-center mt-3">
                    <small>Already have an account? <a href="admin_login.php">Login here</a></small>
                </div>
            </div>
        </div>
    </div>
</div>
</body></html>