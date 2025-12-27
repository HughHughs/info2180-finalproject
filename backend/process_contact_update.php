<?php
session_start();
require 'connection.php';

$id = $_POST['id'];
$action = $_POST['action'];
$user_id = $_SESSION['user_id'];

if ($action === 'assign') {
    $stmt = $pdo->prepare("UPDATE contacts SET assigned_to = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$user_id, $id]);
} elseif ($action === 'switch') {
    //toggles type logic
    $stmt = $pdo->prepare("UPDATE contacts SET type = CASE WHEN type = 'Sales Lead' THEN 'Support' ELSE 'Sales Lead' END, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);
}

echo "success";