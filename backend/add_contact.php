<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit();
}

//If POST it will handle the submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $first_name  = trim($_POST['first_name'] ?? '');
    $last_name   = trim($_POST['last_name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $telephone   = trim($_POST['telephone'] ?? '');
    $company     = trim($_POST['company'] ?? '');
    $type        = trim($_POST['type'] ?? '');
    $assigned_to = intval($_POST['assigned_to'] ?? 0);
    $created_by  = $_SESSION['user_id'];

    //Ensure the user validates required fields
    if (empty($title) || empty($first_name) || empty($last_name) || empty($email) || empty($type)) {
        echo "Please fill in all required fields.";
        exit();
    }

    //Validates the email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email address.";
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO contacts
            (title, first_name, last_name, email, telephone, company, type, assigned_to, created_by, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $result = $stmt->execute([
            $title,
            $first_name,
            $last_name,
            $email,
            $telephone,
            $company,
            $type,
            $assigned_to,
            $created_by
        ]);

        if ($result) {
            echo "Contact added successfully!";
        } else {
            echo "Error: Unable to add contact.";
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo "Error: Unable to add contact. Please try again.";
    }
    exit();
}

//GET request to show the form
try {
    $users_stmt = $pdo->query("SELECT id, firstname, lastname FROM users ORDER BY firstname");
    $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $users = [];
}
?>

<style>
    .new-contact-container {
        background: white;
        padding: 30px;
        border-radius: 8px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        width: 80%;
        max-width: 80%;
        height: 70%;
    }
    .new-contact-container h2 {
        margin-bottom: 20px;
        color: #2d3748;
    }
    #form-message {
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 4px;
        display: none;
    }
    #form-message.show {
        display: block;
    }
    #form-message.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    #form-message.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    .input-group {
        display: flex;
        flex-direction: column;
    }
    .input-group.full-width {
        grid-column: span 2;
    }
    .input-group label {
        font-size: 14px;
        font-weight: 500;
        color: #4a5568;
        margin-bottom: 5px;
    }
    .input-group input,
    .input-group select {
        padding: 10px;
        border: 1px solid #cbd5e0;
        border-radius: 4px;
        font-size: 14px;
    }
    .input-group input:focus,
    .input-group select:focus {
        outline: none;
        border-color: #5A67D8;
    }
    .form-footer {
        margin-top: 20px;
        display: flex;
        gap: 10px;
    }
    .save-btn {
        background: #5A67D8;
        color: white;
        border: none;
        padding: 10px 30px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }
    .save-btn:hover {
        background: #4C51BF;
    }
    .save-btn:disabled {
        background: #a0aec0;
        cursor: not-allowed;
    }
</style>

<div class="new-contact-container">
    <h2>New Contact</h2>

    <div id="form-message"></div>

    <form id="add-contact-form">
        <div class="form-grid">
            <div class="input-group full-width">
                <label>Title</label>
                <select name="title" required>
                    <option value="Mr">Mr</option>
                    <option value="Mrs">Mrs</option>
                    <option value="Ms">Ms</option>
                    <option value="Dr">Dr</option>
                </select>
            </div>

            <div class="input-group">
                <label>First Name</label>
                <input type="text" name="first_name" placeholder="Jane" required>
            </div>
            <div class="input-group">
                <label>Last Name</label>
                <input type="text" name="last_name" placeholder="Doe" required>
            </div>

            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="something@example.com" required>
            </div>
            <div class="input-group">
                <label>Telephone</label>
                <input type="text" name="telephone" placeholder="(123) 456-7890">
            </div>

            <div class="input-group">
                <label>Company</label>
                <input type="text" name="company" placeholder="Company Name">
            </div>
            <div class="input-group">
                <label>Type</label>
                <select name="type" required>
                    <option value="">--Select--</option>
                    <option value="Sales Lead">Sales Lead</option>
                    <option value="Support">Support</option>
                </select>
            </div>

            <div class="input-group full-width">
                <label>Assigned To</label>
                <select name="assigned_to">
                    <option value="0">--Select User--</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= htmlspecialchars($u['id']) ?>">
                            <?= htmlspecialchars($u['firstname'] . ' ' . $u['lastname']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-footer">
            <button type="submit" class="save-btn">Save</button>
        </div>
    </form>
</div>



<script>
(function() {
    const form = document.getElementById('add-contact-form');
    const messageDiv = document.getElementById('form-message');

    if (!form) return;

    //Removes any existing listeners
    const newForm = form.cloneNode(true);
    form.parentNode.replaceChild(newForm, form);

    newForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        const formData = new FormData(this);

        fetch('add_contact.php', {
            method: 'POST',
            body: formData
        })
        .then(resp => {
            if (!resp.ok) {
                throw new Error('Network response was not ok');
            }
            return resp.text();
        })
        .then(msg => {
            const msgDiv = document.getElementById('form-message');
            if (msgDiv) {
                msgDiv.classList.add('show');
                msgDiv.innerHTML = msg;

                if (msg.includes("successfully")) {
                    msgDiv.classList.remove('error');
                    msgDiv.classList.add('success');

                    // Clear the form
                    newForm.reset();

                    // Hide message after 3 seconds
                    setTimeout(() => {
                        msgDiv.classList.remove('show');
                    }, 3000);
                } else {
                    msgDiv.classList.remove('success');
                    msgDiv.classList.add('error');
                }
            }

            submitBtn.disabled = false;
            submitBtn.textContent = 'Save';
        })
        .catch(err => {
            const msgDiv = document.getElementById('form-message');
            if (msgDiv) {
                msgDiv.classList.add('show', 'error');
                msgDiv.classList.remove('success');
                msgDiv.innerHTML = "Error adding contact. Please try again.";
            }
            console.error(err);

            submitBtn.disabled = false;
            submitBtn.textContent = 'Save';
        });
    });
})();
</script>