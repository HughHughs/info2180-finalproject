<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed";
    exit();
}

$contact_id = intval($_POST['contact_id'] ?? 0);
$comment = trim($_POST['comment'] ?? '');
$created_by = $_SESSION['user_id'];

if ($contact_id <= 0) {
    echo "Invalid contact ID";
    exit();
}

if (empty($comment)) {
    echo "Comment cannot be empty";
    exit();
}

try {
    //Verifies that the contact exists
    $check_stmt = $pdo->prepare("SELECT id FROM contacts WHERE id = ?");
    $check_stmt->execute([$contact_id]);

    if (!$check_stmt->fetch()) {
        echo "Contact not found";
        exit();
    }

    //Inserts note
    $stmt = $pdo->prepare("
        INSERT INTO notes (contact_id, comment, created_by, created_at)
        VALUES (?, ?, ?, NOW())
    ");

    $result = $stmt->execute([$contact_id, $comment, $created_by]);

    if ($result) {
        echo "Note added successfully";
    } else {
        echo "Error adding note";
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo "Error: Unable to add note";
}
?>