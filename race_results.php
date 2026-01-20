<?php 
include 'header.php'; 

$category = $_GET['category'] ?? '';
$event_id = $_GET['event_id'] ?? '';
$search = $_GET['search'] ?? '';

// Fetch Events for Dropdown
$events_list = $pdo->query("SELECT id, title FROM events ORDER BY event_date DESC")->fetchAll();

// Build Query
$sql = "SELECT rr.*, e.title, e.category, e.event_date 
        FROM race_results rr 
        JOIN events e ON rr.event_id = e.id 
        WHERE 1=1";
$params = [];

if (!empty($event_id)) {
    $sql .= " AND e.id = ?";
    $params[] = $event_id;
}

if (!empty($category)) {
    $sql .= " AND e.category = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $sql .= " AND rr.participant_name LIKE ?";
    $params[] = "%$search%";
}

$sql .= " ORDER BY e.event_date DESC, rr.rank ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

// Get Categories for Filter
$cats = ['Marathon', 'Half-Marathon', '5K', 'Triathlon', 'Cycling'];
?>

<div class="card shadow mb-4">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">🏁 Race Results Archive</h4>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Filter by Event</label>
                <select name="event_id" class="form-select">
                    <option value="">All Events</option>
                    <?php foreach($events_list as $evt): ?>
                        <option value="<?= $evt['id'] ?>" <?= $event_id == $evt['id'] ? 'selected' : '' ?>><?= htmlspecialchars($evt['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Filter by Category</label>
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach($cats as $c): ?>
                        <option value="<?= $c ?>" <?= $category == $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Search Participant</label>
                <input type="text" name="search" class="form-control" placeholder="Enter name..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Rank</th>
                        <th>Participant</th>
                        <th>Time</th>
                        <th>Event</th>
                        <th>Category</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($results as $r): ?>
                    <tr>
                        <td>
                            <?php if($r['rank'] == 1): ?>🥇
                            <?php elseif($r['rank'] == 2): ?>🥈
                            <?php elseif($r['rank'] == 3): ?>🥉
                            <?php else: ?>#<?= $r['rank'] ?>
                            <?php endif; ?>
                        </td>
                        <td class="fw-bold"><?= htmlspecialchars($r['participant_name']) ?></td>
                        <td><?= htmlspecialchars($r['finish_time']) ?></td>
                        <td><?= htmlspecialchars($r['title']) ?></td>
                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($r['category']) ?></span></td>
                        <td><?= date('M d, Y', strtotime($r['event_date'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if(empty($results)) echo "<div class='alert alert-info text-center'>No results found matching your criteria.</div>"; ?>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>