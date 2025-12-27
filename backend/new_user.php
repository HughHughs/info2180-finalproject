<?php
session_start();
//Basic security check: only admins should add users
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    //echos "Access Denied"; exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New User - Dolphin CRM</title>
    <link rel="stylesheet" href="../styles/new_user.css">
</head>
<body>
    <div class="new-user-container">
        <h1>New User</h1>

        <div class="form-card">
            <form action="add_user.php" method="POST">
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

                    <div class="input-group full-width">
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
    </div>
</body>
</html>
