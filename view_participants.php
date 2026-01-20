<?php 
include 'header.php'; 
if (!isAdmin()) header("Location: login.php");

$event_id = $_GET['event_id'] ?? 0;

// Handle Status Update
if (isset($_POST['update_status'])) {
    $reg_id = $_POST['reg_id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE registrations SET status = ? WHERE id = ?");
    $stmt->execute([$status, $reg_id]);
}

// Fetch Event Info
$evtStmt = $pdo->prepare("SELECT title FROM events WHERE id = ?");
$evtStmt->execute([$event_id]);
$event = $evtStmt->fetch();

// Fetch Participants
$sql = "SELECT u.name, u.email, u.gender, TIMESTAMPDIFF(YEAR, u.birthdate, CURDATE()) as age, 
        r.id as reg_id, r.status, r.registration_date, r.payment_proof, r.race_category 
        FROM registrations r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.event_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$event_id]);
$participants = $stmt->fetchAll();
?>

<h3>Participants for: <?= htmlspecialchars($event['title']) ?></h3>
<a href="admin_dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>

<table class="table table-hover">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Gender</th>
            <th>Age</th>
            <th>Category</th>
            <th>Registered At</th>
            <th>Proof of Payment</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($participants as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= htmlspecialchars($p['email']) ?></td>
            <td><?= htmlspecialchars($p['gender']) ?></td>
            <td><?= htmlspecialchars($p['age']) ?></td>
            <td><?= htmlspecialchars($p['race_category']) ?></td>
            <td><?= $p['registration_date'] ?></td>
            <td>
                <?php if (!empty($p['payment_proof'])): ?>
                    <a href="<?= htmlspecialchars($p['payment_proof']) ?>" target="_blank" class="btn btn-sm btn-outline-info">View Image</a>
                <?php else: ?>
                    <span class="text-muted">Not Uploaded</span>
                <?php endif; ?>
            </td>
            <td>
                <form method="POST" class="d-flex">
                    <input type="hidden" name="reg_id" value="<?= $p['reg_id'] ?>">
                    <select name="status" class="form-select form-select-sm me-2">
                        <option value="Pending" <?= $p['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="Confirmed" <?= $p['status'] == 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="Cancelled" <?= $p['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                    <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</body></html>
