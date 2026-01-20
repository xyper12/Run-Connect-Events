<?php 
include 'header.php'; 

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$event_id = $_GET['event_id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Fetch Event Details
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Event not found.</div></div>";
    exit;
}

// Fetch User Details for pre-filling
$uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$uStmt->execute([$user_id]);
$user = $uStmt->fetch();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $p_name = $_POST['participant_name'];
    $p_cat = $_POST['race_category'];
    $p_contact = $_POST['contact_info'];
    $p_payment = $_POST['payment_method'];
    $p_amount = $_POST['amount_paid'];
    $payment_proof = null;

    // Handle File Upload
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_ext = strtolower(pathinfo($_FILES["payment_proof"]["name"], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        
        if (in_array($file_ext, $allowed)) {
            $payment_proof = $target_dir . uniqid() . "." . $file_ext;
            move_uploaded_file($_FILES["payment_proof"]["tmp_name"], $payment_proof);
        }
    }

    if (!isset($error)) {
        try {
            $sql = "INSERT INTO registrations (user_id, event_id, participant_name, race_category, contact_info, payment_method, payment_proof, amount_paid) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $event_id, $p_name, $p_cat, $p_contact, $p_payment, $payment_proof, $p_amount]);
            
            echo "<div class='container mt-4'><div class='alert alert-success'>Successfully registered! <a href='profile.php'>View your events</a></div></div>";
            // Stop rendering the form
            include 'footer.php';
            exit;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $error = "You are already registered for this event.";
            } else {
                $error = "Registration Error: " . $e->getMessage();
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Register for: <?= htmlspecialchars($event['title']) ?></h4>
            </div>
            <div class="card-body">
                <?php if(isset($error)) echo "<div class='alert alert-warning'>$error</div>"; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <h5 class="mb-3 text-secondary">Participant Information</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="participant_name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact_info" class="form-control" placeholder="+1 234 567 890" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Race Category</label>
                        <input type="text" name="race_category" class="form-control" value="<?= htmlspecialchars($event['category']) ?>" required>
                        <div class="form-text">You can specify a sub-category if applicable (e.g., 5K, 10K, Elite).</div>
                    </div>

                    <h5 class="mb-3 mt-4 text-secondary">Payment Details</h5>
                    <div class="mb-4">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="">Select Payment Method</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="PayPal">PayPal</option>
                            <option value="GCash">GCash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="On-site Payment">On-site Payment</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount Paid</label>
                        <input type="number" name="amount_paid" class="form-control" step="0.01" placeholder="Enter amount" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Proof of Payment (Screenshot/Photo)</label>
                        <input type="file" name="payment_proof" class="form-control" accept="image/*,.pdf">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">Confirm Registration</button>
                        <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body></html>