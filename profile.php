<?php 
include 'header.php'; 
if (!isLoggedIn()) header("Location: login.php");

$user_id = $_SESSION['user_id'];

// Handle Profile Update
if (isset($_POST['update_profile'])) {
    $new_email = $_POST['email'];
    $new_pass = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    if ($new_pass) {
        $stmt = $pdo->prepare("UPDATE users SET email = ?, password = ? WHERE id = ?");
        $stmt->execute([$new_email, $new_pass, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$new_email, $user_id]);
    }
    echo "<div class='alert alert-success'>Profile updated!</div>";
}

// Fetch User Details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch Registered Events
$regStmt = $pdo->prepare("
    SELECT e.title, e.event_date, e.category, r.status, rr.rank, rr.finish_time 
    FROM registrations r 
    JOIN events e ON r.event_id = e.id 
    JOIN users u ON r.user_id = u.id
    LEFT JOIN race_results rr ON r.event_id = rr.event_id AND (r.participant_name = rr.participant_name OR u.name = rr.participant_name)
    WHERE r.user_id = ?
    ORDER BY e.event_date DESC
");
$regStmt->execute([$user_id]);
$all_events = $regStmt->fetchAll();

?>

<div class="row">
    <!-- Settings Column -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">Settings</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>New Password (leave blank to keep)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-warning w-100">Update Settings</button>
                </form>
            </div>
        </div>
    </div>

    <!-- My Events Column -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">My Registered Events</div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Time</th>
                            <th>Rank</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($all_events as $ev): ?>
                        <tr>
                            <td><?= htmlspecialchars($ev['title']) ?></td>
                            <td><?= date('M d, Y', strtotime($ev['event_date'])) ?></td>
                            <td><?= htmlspecialchars($ev['category']) ?></td>
                            <td><?= htmlspecialchars($ev['finish_time'] ?? '-') ?></td>
                            <td>
                                <?= $ev['rank'] ? "<span class='badge bg-warning text-dark'>#{$ev['rank']}</span>" : "-" ?>
                            </td>
                            <td>
                                <?php 
                                    $badge = $ev['status'] == 'Confirmed' ? 'success' : 'secondary';
                                    echo "<span class='badge bg-$badge'>{$ev['status']}</span>";
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if(empty($all_events)) echo "<p class='text-center text-muted'>You haven't joined any events yet.</p>"; ?>
            </div>
        </div>

        <div class="d-grid gap-2">
            <a href="race_results.php" class="btn btn-outline-primary btn-lg">
                🏆 View Overall Race Results
            </a>
        </div>
    </div>
</div>
</body></html>
