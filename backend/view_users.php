<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit();
}

$stmt = $pdo->query("SELECT id, firstname, lastname, email, role, created_at FROM users ORDER BY id");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .page-container {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .page-header h2 {
        margin: 0;
        color: #2d3748;
    }
    .add-user-btn {
        background: #5A67D8;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .add-user-btn:hover {
        background: #4C51BF;
    }
    .plus-icon {
        font-size: 18px;
    }
    .user-table {
        width: 100%;
        border-collapse: collapse;
    }
    .user-table thead {
        background: #f7fafc;
    }
    .user-table th {
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #2d3748;
        border-bottom: 2px solid #e2e8f0;
    }
    .user-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
    }
    .user-table tbody tr:hover {
        background: #f7fafc;
    }
    .user-name {
        font-weight: 500;
        color: #2d3748;
    }
    .user-email {
        color: #4a5568;
    }
    .user-role {
        color: #5A67D8;
        font-weight: 500;
    }
    .user-date {
        color: #718096;
        font-size: 14px;
    }
</style>

<div class="page-container">
    <div class="page-header">
        <h2>Users</h2>
        <button class="add-user-btn" onclick="addNewUser()">
            <span class="plus-icon">+</span> Add User
        </button>
    </div>

    <table class="user-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($users as $u): ?>
            <tr>
                <td class="user-name">
                    <?= htmlspecialchars($u['firstname'] . ' ' . $u['lastname']) ?>
                </td>
                <td class="user-email"><?= htmlspecialchars($u['email']) ?></td>
                <td class="user-role"><?= htmlspecialchars($u['role']) ?></td>
                <td class="user-date">
                    <?= isset($u['created_at']) ? date("F j, Y g:i a", strtotime($u['created_at'])) : '-' ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
(function() {
    window.addNewUser = function() {
        if (typeof loadPage === 'function') {
            loadPage('add_user.php');
        }
    };
})();
</script>
