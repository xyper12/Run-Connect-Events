<?php 
include 'header.php'; 
if (!isAdmin()) header("Location: login.php");

$event_id = $_GET['event_id'] ?? 0;

// Fetch Event Info
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Event not found.</div></div>";
    exit;
}

// Handle Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rows = [];

    // 1. Handle File Upload (CSV)
    if (isset($_FILES['result_file']) && $_FILES['result_file']['error'] == 0) {
        $file = fopen($_FILES['result_file']['tmp_name'], 'r');
        while (($line = fgetcsv($file)) !== FALSE) {
            // Expecting: Rank, Name, Time
            if (count($line) >= 3) {
                $rows[] = $line;
            }
        }
        fclose($file);
    } 
    // 2. Handle Text Paste
    elseif (!empty($_POST['result_text'])) {
        $lines = explode("\n", $_POST['result_text']);
        foreach ($lines as $line) {
            $data = str_getcsv(trim($line));
            if (count($data) >= 3) {
                $rows[] = $data;
            }
        }
    }

    // Insert into Database
    if (!empty($rows)) {
        $sql = "INSERT INTO race_results (event_id, rank, participant_name, finish_time) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        $count = 0;
        foreach ($rows as $row) {
            // row[0] = Rank, row[1] = Name, row[2] = Time
            // Basic validation
            if (is_numeric($row[0]) && !empty($row[1])) {
                $stmt->execute([$event_id, $row[0], $row[1], $row[2]]);
                $count++;
            }
        }
        echo "<div class='alert alert-success'>Successfully uploaded $count results!</div>";
    } else {
        echo "<div class='alert alert-warning'>No valid data found. Please check the format.</div>";
    }
}

// Fetch Existing Results
$resStmt = $pdo->prepare("SELECT * FROM race_results WHERE event_id = ? ORDER BY rank ASC");
$resStmt->execute([$event_id]);
$results = $resStmt->fetchAll();
?>

<div class="row">
    <div class="col-md-5">
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">Upload Results for: <?= htmlspecialchars($event['title']) ?></div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Option 1: Upload CSV/Excel File</label>
                        <input type="file" name="result_file" class="form-control" accept=".csv">
                        <div class="form-text">Format: Rank, Name, Time (Save Excel as .csv)</div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Option 2: Paste Text</label>
                        <textarea name="result_text" class="form-control" rows="5" placeholder="1, John Doe, 01:30:22&#10;2, Jane Smith, 01:32:10"></textarea>
                        <div class="form-text">One participant per line: Rank, Name, Time</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Upload Results</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header">Current Results</div>
            <div class="card-body">
                <table class="table table-sm table-striped">
                    <thead><tr><th>Rank</th><th>Name</th><th>Time</th></tr></thead>
                    <tbody>
                        <?php foreach($results as $r): ?>
                        <tr>
                            <td>#<?= $r['rank'] ?></td>
                            <td><?= htmlspecialchars($r['participant_name']) ?></td>
                            <td><?= htmlspecialchars($r['finish_time']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if(empty($results)) echo "<p class='text-muted'>No results uploaded yet.</p>"; ?>
            </div>
        </div>
    </div>
</div>
<div class="mt-3"><a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a></div>
</body></html>