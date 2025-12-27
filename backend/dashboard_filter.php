<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

$user_id = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'all';

//Builds the query based on filter
$sql = "SELECT c.*, u.firstname AS assigned_first, u.lastname AS assigned_last
        FROM contacts c
        LEFT JOIN users u ON c.assigned_to=u.id";

$params = [];

if ($filter === 'sales') {
    $sql .= " WHERE c.type='Sales Lead'";
} elseif ($filter === 'support') {
    $sql .= " WHERE c.type='Support'";
} elseif ($filter === 'mine') {
    $sql .= " WHERE c.assigned_to=?";
    $params[] = $user_id;
}

$sql .= " ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$contacts = $stmt->fetchAll();

if ($contacts):
?>
<table>
    <tr>
        <th>Title</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Company</th>
        <th>Type</th>
        <th>Assigned To</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($contacts as $c): ?>
    <tr>
        <td><?= htmlspecialchars($c['title']) ?></td>
        <td><?= htmlspecialchars($c['first_name'].' '.$c['last_name']) ?></td>
        <td><?= htmlspecialchars($c['email']) ?></td>
        <td><?= htmlspecialchars($c['company']) ?></td>
        <td><?= htmlspecialchars($c['type']) ?></td>
        <td><?= htmlspecialchars($c['assigned_first'].' '.$c['assigned_last']) ?></td>
        <td>
        <a href="#" onclick="loadPage('view_contact.php?id=<?= $c['id'] ?>'); return false;">View</a>
        </td>

    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p>No contacts found.</p>
<?php endif; ?>
