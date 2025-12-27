<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit();
}

$user_id = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'all';

//Builds the query based on filter
$sql = "SELECT c.*, u.firstname AS assigned_first, u.lastname AS assigned_last
        FROM contacts c
        LEFT JOIN users u ON c.assigned_to = u.id";

$params = [];

if ($filter === 'sales') {
    $sql .= " WHERE c.type = 'Sales Lead'";
} elseif ($filter === 'support') {
    $sql .= " WHERE c.type = 'Support'";
} elseif ($filter === 'assigned') {
    $sql .= " WHERE c.assigned_to = ?";
    $params[] = $user_id;
}

$sql .= " ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .dashboard-header h3 {
        margin: 0;
    }
    .add-contact-btn {
        background: #5A67D8;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }
    .add-contact-btn:hover {
        background: #4C51BF;
    }
    .filter-section {
        margin-bottom: 20px;
        padding: 10px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    .filter-label {
        display: inline-block;
        margin-right: 10px;
        font-weight: bold;
    }
    .filter-btn {
        background: none;
        border: none;
        padding: 8px 16px;
        margin-right: 5px;
        cursor: pointer;
        color: #4a5568;
        font-size: 14px;
        border-bottom: 2px solid transparent;
    }
    .filter-btn:hover {
        color: #2d3748;
    }
    .filter-btn.active {
        color: #5A67D8;
        border-bottom: 2px solid #5A67D8;
        font-weight: 600;
    }
    .contacts-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .contacts-table th {
        background: #f7fafc;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #2d3748;
        border-bottom: 2px solid #e2e8f0;
    }
    .contacts-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
    }
    .contacts-table tr:hover {
        background: #f7fafc;
    }
    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge-sales {
        background: #fef3c7;
        color: #92400e;
    }
    .badge-support {
        background: #ddd6fe;
        color: #5b21b6;
    }
    .view-link {
        color: #5A67D8;
        text-decoration: none;
        font-weight: 500;
    }
    .view-link:hover {
        text-decoration: underline;
    }
    .no-contacts {
        text-align: center;
        padding: 40px;
        color: #718096;
    }
</style>

<div class="dashboard-header">
    <h3>Dashboard</h3>
    <button class="add-contact-btn" onclick="loadPage('add_contact.php')">
        ➕ Add Contact
    </button>
</div>

<div class="filter-section">
    <span class="filter-label">🔻 Filter By:</span>
    <button class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>" onclick="filterContacts('all')">
        All
    </button>
    <button class="filter-btn <?= $filter === 'sales' ? 'active' : '' ?>" onclick="filterContacts('sales')">
        Sales Leads
    </button>
    <button class="filter-btn <?= $filter === 'support' ? 'active' : '' ?>" onclick="filterContacts('support')">
        Support
    </button>
    <button class="filter-btn <?= $filter === 'assigned' ? 'active' : '' ?>" onclick="filterContacts('assigned')">
        Assigned to me
    </button>
</div>

<?php if (count($contacts) > 0): ?>
<table class="contacts-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Company</th>
            <th>Type</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($contacts as $c): ?>
        <tr>
            <td>
                <strong><?= htmlspecialchars($c['title'] . '. ' . $c['first_name'] . ' ' . $c['last_name']) ?></strong>
            </td>
            <td><?= htmlspecialchars($c['email']) ?></td>
            <td><?= htmlspecialchars($c['company']) ?></td>
            <td>
                <span class="badge <?= $c['type'] === 'Sales Lead' ? 'badge-sales' : 'badge-support' ?>">
                    <?= htmlspecialchars(strtoupper($c['type'])) ?>
                </span>
            </td>
            <td>
                <a href="#" class="view-link" onclick="viewContact(<?= $c['id'] ?>); return false;">
                    View
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<div class="no-contacts">
    <p>No contacts found.</p>
</div>
<?php endif; ?>

<script>
(function() {
    window.filterContacts = function(filter) {
        loadPage('view_contacts.php?filter=' + filter);
    };

    window.viewContact = function(id) {
        loadPage('view.php?id=' + id);
    };
})();
</script>