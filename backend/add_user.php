<?php
session_start();
require 'connection.php';

//Processes the Form Submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname  = trim($_POST['lastname'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $role      = $_POST['role'] ?? 'Member';

    if (empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
        echo "All fields are required.";
        exit;
    }

    try {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (firstname, lastname, email, password, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$firstname, $lastname, $email, $hashed_password, $role]);

        echo "User created successfully!";
    } catch (PDOException $e) {
        echo ($e->getCode() == 23000 ? "Email already exists." : "Database error.");
    }
    exit;
}
?>

<style>
    .new-user-container {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        width: 80%;
        max-width: 80%;
        height: 70%;
    }
    .new-user-container h1 {
        margin-bottom: 20px;
        color: #2d3748;
        font-size: 24px;
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

<div class="new-user-container">
    <h1>New User</h1>

    <div id="form-message"></div>

    <form id="ajax-add-user-form">
        <div class="form-grid">
            <div class="input-group">
                <label>First Name</label>
                <input type="text" name="firstname" placeholder="Jane" required>
            </div>
            <div class="input-group">
                <label>Last Name</label>
                <input type="text" name="lastname" placeholder="Doe" required>
            </div>
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="something@example.com" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="input-group">
                <label>Role</label>
                <select name="role">
                    <option value="Member">Member</option>
                    <option value="Admin">Admin</option>
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
    const form = document.getElementById('ajax-add-user-form');
    const msgDiv = document.getElementById('form-message');

    if (!form) return;

    //Removes any existing listeners
    const newForm = form.cloneNode(true);
    form.parentNode.replaceChild(newForm, form);

    newForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = this.querySelector('.save-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        fetch('add_user.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.text())
        .then(response => {
            const msgDiv = document.getElementById('form-message');

            if (msgDiv) {
                msgDiv.classList.add('show');
                msgDiv.innerHTML = response;

                if (response.includes("successfully")) {
                    msgDiv.classList.remove('error');
                    msgDiv.classList.add('success');

                    //clears the form
                    newForm.reset();

                    //redirects to users list after 2 seconds
                    setTimeout(() => {
                        if (typeof loadPage === 'function') {
                            loadPage('view_users.php');
                        }
                    }, 2000);
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
                msgDiv.innerHTML = "An error occurred.";
            }
            submitBtn.disabled = false;
            submitBtn.textContent = 'Save';
        });
    });
})();
</script>
