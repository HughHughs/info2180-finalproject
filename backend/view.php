<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

$contact_id = intval($_GET['id'] ?? 0);

if ($contact_id <= 0) {
    exit('Invalid contact ID');
}

// Fetch contact info
$stmt = $pdo->prepare("
    SELECT c.*, u.firstname AS assigned_first, u.lastname AS assigned_last,
           cu.firstname AS created_first, cu.lastname AS created_last
    FROM contacts c
    LEFT JOIN users u ON c.assigned_to = u.id
    LEFT JOIN users cu ON c.created_by = cu.id
    WHERE c.id = ?
");
$stmt->execute([$contact_id]);
$contact = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contact) {
    exit('Contact not found.');
}

//Fetches notes for this contact
$notes_stmt = $pdo->prepare("
    SELECT n.*, u.firstname, u.lastname
    FROM notes n
    LEFT JOIN users u ON n.created_by = u.id
    WHERE n.contact_id = ?
    ORDER BY n.created_at DESC
");
$notes_stmt->execute([$contact_id]);
$notes = $notes_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .contact-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    .back-btn {
        background: #e2e8f0;
        color: #2d3748;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }
    .back-btn:hover {
        background: #cbd5e0;
    }
    .contact-card {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    .contact-name {
        font-size: 24px;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 20px;
    }
    .badge {
        display: inline-block;
        padding: 6px 16px;
        border-radius: 16px;
        font-size: 13px;
        font-weight: 600;
        margin-left: 10px;
    }
    .badge-sales {
        background: #fef3c7;
        color: #92400e;
    }
    .badge-support {
        background: #ddd6fe;
        color: #5b21b6;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-top: 20px;
    }
    .info-item {
        margin-bottom: 15px;
    }
    .info-label {
        font-size: 12px;
        color: #718096;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }
    .info-value {
        font-size: 15px;
        color: #2d3748;
        font-weight: 500;
    }
    .notes-section {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .notes-header {
        font-size: 18px;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 20px;
    }
    .note-form {
        margin-bottom: 30px;
        padding: 20px;
        background: #f7fafc;
        border-radius: 6px;
    }
    .note-form textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #cbd5e0;
        border-radius: 4px;
        font-family: inherit;
        font-size: 14px;
        resize: vertical;
        min-height: 80px;
    }
    .add-note-btn {
        background: #5A67D8;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        margin-top: 10px;
    }
    .add-note-btn:hover {
        background: #4C51BF;
    }
    .note-item {
        padding: 15px;
        border-left: 3px solid #5A67D8;
        background: #f7fafc;
        margin-bottom: 15px;
        border-radius: 4px;
    }
    .note-author {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 5px;
    }
    .note-date {
        font-size: 12px;
        color: #718096;
        margin-bottom: 10px;
    }
    .note-text {
        color: #4a5568;
        line-height: 1.6;
    }
    .no-notes {
        text-align: center;
        color: #718096;
        padding: 20px;
    }
</style>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel = "stylesheet" href="../styles/view.css"/>
</head>
<body>
    <div class="contact-details-container">
    <div class="top-header">
        <div class="user-profile">
            <div class="profile-icon">👤</div>
            <div class="user-meta">
                <h1><?= htmlspecialchars($contact['title'] . '. ' . $contact['first_name'] . ' ' . $contact['last_name']) ?></h1>
                <p>Created on <?= date('F j, Y', strtotime($contact['created_at'])) ?> by <?= htmlspecialchars($contact['created_first'] . ' ' . $contact['created_last']) ?></p>
                <p>Updated on <?= date('F j, Y', strtotime($contact['updated_at'])) ?></p>
            </div>
        </div>
        <div class="header-actions">
            <button class="btn-assign" onclick="handleAction('assign', <?= $contact_id ?>)">
                ✋ Assign to me
            </button>
            <button class="btn-switch" onclick="handleAction('switch', <?= $contact_id ?>)">
                ⇄ Switch to <?= $contact['type'] === 'Sales Lead' ? 'Support' : 'Sales Lead' ?>
            </button>
        </div>
    </div>

    <div class="info-card">
        <div class="info-grid">
            <div class="info-group">
                <label>Email</label>
                <div class="value"><?= htmlspecialchars($contact['email']) ?></div>
            </div>
            <div class="info-group">
                <label>Telephone</label>
                <div class="value"><?= htmlspecialchars($contact['telephone'] ?: 'N/A') ?></div>
            </div>
            <div class="info-group">
                <label>Company</label>
                <div class="value"><?= htmlspecialchars($contact['company'] ?: 'N/A') ?></div>
            </div>
            <div class="info-group">
                <label>Assigned To</label>
                <div class="value"><?= htmlspecialchars(($contact['assigned_first'] ?? '') . ' ' . ($contact['assigned_last'] ?? '') ?: 'Unassigned') ?></div>
            </div>
        </div>
    </div>

    <div class="notes-container">
        <div class="notes-header">
            <span class="edit-icon">📝</span> Notes
        </div>
        <hr>

        <div id="notes-list">
            <?php foreach ($notes as $note): ?>
                <div class="note-entry">
                    <strong><?= htmlspecialchars($note['firstname'] . ' ' . $note['lastname']) ?></strong>
                    <p class="note-comment"><?= nl2br(htmlspecialchars($note['comment'])) ?></p>
                    <span class="note-timestamp"><?= date('F j, Y \a\t ga', strtotime($note['created_at'])) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="add-note-box">
            <label>Add a note about <?= htmlspecialchars($contact['first_name']) ?></label>
            <textarea id="note-comment" placeholder="Enter details here"></textarea>
            <button class="save-note-btn" onclick="addNote(<?= $contact_id ?>)">Add Note</button>
        </div>
    </div>
</div>
</body>
</html>


<script>
(function() {
    window.addNote = function(contactId) {
        const comment = document.getElementById('note-comment').value.trim();

        if (!comment) {
            alert('Please enter a note');
            return;
        }

        const formData = new FormData();
        formData.append('contact_id', contactId);
        formData.append('comment', comment);

        fetch('add_note.php', {
            method: 'POST',
            body: formData
        })
        .then(resp => resp.text())
        .then(msg => {
            if (msg.includes('success')) {
                //Reloads the contact view
                loadPage('view.php?id=' + contactId);
            } else {
                alert('Error adding note: ' + msg);
            }
        })
        .catch(err => {
            alert('Error adding note');
            console.error(err);
        });
    };

}
)();

(function() {
    window.handleAction = function(actionType, contactId) {
    const formData = new FormData();
    formData.append('id', contactId);
    formData.append('action', actionType);

    fetch('process_contact_update.php', {
        method: 'POST',
        body: formData
    })
    .then(resp => resp.text())
    .then(msg => {
        if (msg.includes('success')) {
            //Refresh the current view
            loadPage('view.php?id=' + contactId);
        } else {
            alert('Update failed: ' + msg);
        }
    });
};
})();
</script>