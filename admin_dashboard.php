<?php 
include 'header.php'; 
if (!isAdmin()) header("Location: login.php");

// --- Handle Event Creation ---
if (isset($_POST['create_event'])) {
    $image_path = null;
    
    // Handle Image Upload
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
        $target_dir = "uploads/events/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $file_ext = strtolower(pathinfo($_FILES["event_image"]["name"], PATHINFO_EXTENSION));
        $image_path = $target_dir . uniqid() . "." . $file_ext;
        move_uploaded_file($_FILES["event_image"]["tmp_name"], $image_path);
    }

    $sql = "INSERT INTO events (title, description, event_date, location, category, image) VALUES (?,?,?,?,?,?)";
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute([$_POST['title'], $_POST['desc'], $_POST['date'], $_POST['loc'], $_POST['cat'], $image_path]);
        echo "<div class='alert alert-success'>Event Created!</div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error creating event: " . $e->getMessage() . "</div>";
    }
}

// --- Handle Event Update ---
if (isset($_POST['update_event'])) {
    $id = $_POST['event_id'];
    $image_path = $_POST['existing_image'];

    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
        $target_dir = "uploads/events/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $file_ext = strtolower(pathinfo($_FILES["event_image"]["name"], PATHINFO_EXTENSION));
        $image_path = $target_dir . uniqid() . "." . $file_ext;
        move_uploaded_file($_FILES["event_image"]["tmp_name"], $image_path);
    }

    $sql = "UPDATE events SET title=?, description=?, event_date=?, location=?, category=?, image=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute([$_POST['title'], $_POST['desc'], $_POST['date'], $_POST['loc'], $_POST['cat'], $image_path, $id]);
        echo "<div class='alert alert-success'>Event Updated! <a href='admin_dashboard.php'>Clear Edit Mode</a></div>";
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error updating event: " . $e->getMessage() . "</div>";
    }
}

// --- Fetch Event for Editing ---
$edit_event = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_event = $stmt->fetch();
}

// --- Handle Event Deletion ---
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: admin_dashboard.php");
    exit;
}

// --- Analytics Queries ---
$genderStats = $pdo->query("SELECT gender, COUNT(*) as count FROM users WHERE role='user' GROUP BY gender")->fetchAll();
$catStats = $pdo->query("SELECT category, COUNT(*) as count FROM events GROUP BY category")->fetchAll();
$ageSql = "SELECT 
    e.category,
    CASE 
        WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) < 20 THEN 'Under 20'
        WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 20 AND 30 THEN '20-30'
        WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 31 AND 40 THEN '31-40'
        ELSE 'Over 40'
    END as age_group, COUNT(*) as count 
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    JOIN events e ON r.event_id = e.id
    GROUP BY e.category, age_group
    ORDER BY e.category";
$ageStats = $pdo->query($ageSql)->fetchAll(PDO::FETCH_GROUP);

// Fetch All Events
$events = $pdo->query("SELECT * FROM events ORDER BY event_date DESC")->fetchAll();
?>

<h2 class="mb-4">Admin Dashboard</h2>

<!-- Analytics Section -->
<div class="row mb-5">
    <div class="col-md-4">
        <div class="card text-white bg-info mb-3">
            <div class="card-header">Participants by Gender</div>
            <div class="card-body">
                <?php foreach($genderStats as $stat): ?>
                    <p class="card-text"><?= $stat['gender'] ?>: <strong><?= $stat['count'] ?></strong></p>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Events by Category</div>
            <div class="card-body">
                <?php foreach($catStats as $stat): ?>
                    <p class="card-text"><?= $stat['category'] ?>: <strong><?= $stat['count'] ?></strong></p>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning mb-3">
            <div class="card-header">Participants by Age (Per Category)</div>
            <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                <?php foreach($ageStats as $category => $stats): ?>
                    <h6 class="fw-bold text-dark bg-white px-2 rounded"><?= htmlspecialchars($category) ?></h6>
                    <?php foreach($stats as $stat): ?>
                        <p class="card-text mb-1 ms-2"><?= $stat['age_group'] ?>: <strong><?= $stat['count'] ?></strong></p>
                    <?php endforeach; ?>
                    <hr class="border-white">
                <?php endforeach; ?>
                <?php if(empty($ageStats)) echo "<p>No participants yet.</p>"; ?>
            </div>
        </div>
    </div>
</div>

<!-- Create Event Form -->
<div class="card mb-4">
    <div class="card-header bg-dark text-white"><?= $edit_event ? 'Edit Event' : 'Create New Event' ?></div>
    <div class="card-body">
        <form method="POST" class="row g-3" enctype="multipart/form-data">
            <?php if ($edit_event): ?>
                <input type="hidden" name="event_id" value="<?= $edit_event['id'] ?>">
                <input type="hidden" name="existing_image" value="<?= $edit_event['image'] ?>">
            <?php endif; ?>

            <div class="col-md-6"><input type="text" name="title" class="form-control" placeholder="Event Title" value="<?= $edit_event['title'] ?? '' ?>" required></div>
            <div class="col-md-3"><input type="datetime-local" name="date" class="form-control" value="<?= isset($edit_event) ? date('Y-m-d\TH:i', strtotime($edit_event['event_date'])) : '' ?>" required></div>
            <div class="col-md-3">
                <select name="cat" class="form-control">
                    <?php $cats = ['Marathon', 'Half-Marathon', '5K', 'Triathlon', 'Cycling']; ?>
                    <?php foreach($cats as $c): ?>
                        <option <?= (isset($edit_event) && $edit_event['category'] == $c) ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6"><input type="text" name="loc" class="form-control" placeholder="Location" value="<?= $edit_event['location'] ?? '' ?>" required></div>
            <div class="col-md-6"><input type="text" name="desc" class="form-control" placeholder="Description" value="<?= $edit_event['description'] ?? '' ?>" required></div>
            <div class="col-md-12"><label>Event Image</label><input type="file" name="event_image" class="form-control" accept="image/*"></div>
            <div class="col-12">
                <button type="submit" name="<?= $edit_event ? 'update_event' : 'create_event' ?>" class="btn <?= $edit_event ? 'btn-warning' : 'btn-primary' ?>"><?= $edit_event ? 'Update Event' : 'Create Event' ?></button>
                <?php if($edit_event): ?><a href="admin_dashboard.php" class="btn btn-secondary">Cancel Edit</a><?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Event List -->
<div class="card">
    <div class="card-header">Manage Events</div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead><tr><th>Title</th><th>Date</th><th>Category</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach($events as $ev): ?>
                <tr>
                    <td><?= htmlspecialchars($ev['title']) ?></td>
                    <td><?= $ev['event_date'] ?></td>
                    <td><?= $ev['category'] ?></td>
                    <td>
                        <a href="view_participants.php?event_id=<?= $ev['id'] ?>" class="btn btn-sm btn-info text-white">Participants</a>
                        <a href="admin_upload_results.php?event_id=<?= $ev['id'] ?>" class="btn btn-sm btn-success">Results</a>
                        <a href="admin_dashboard.php?edit=<?= $ev['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="admin_dashboard.php?delete=<?= $ev['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this event?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body></html>
