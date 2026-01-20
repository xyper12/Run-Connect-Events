<?php 
include 'header.php'; 

// Fetch Events
$stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
$events = $stmt->fetchAll();
?>

<h2 class="mb-4">Upcoming Marathon & Multisport Events</h2>
<div class="row">
    <?php foreach($events as $event): ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
            <?php if (!empty($event['image'])): ?>
                <img src="<?= htmlspecialchars($event['image']) ?>" class="card-img-top" alt="Event Image" style="height: 200px; object-fit: cover;">
            <?php endif; ?>
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($event['title']) ?></h5>
                <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($event['category']) ?></h6>
                <p class="card-text"><?= htmlspecialchars($event['description']) ?></p>
                <ul class="list-unstyled text-secondary">
                    <li>📅 <?= date('F j, Y g:i A', strtotime($event['event_date'])) ?></li>
                    <li>📍 <?= htmlspecialchars($event['location']) ?></li>
                </ul>
                
                <?php if(isLoggedIn() && !isAdmin()): ?>
                    <a href="event_registration.php?event_id=<?= $event['id'] ?>" class="btn btn-primary w-100">Register Now</a>
                <?php elseif(!isLoggedIn()): ?>
                    <a href="login.php" class="btn btn-outline-primary w-100">Login to Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
</body></html>
