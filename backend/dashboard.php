<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Dolphin CRM</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: #f5f5f5;
        }
        .header {
            background: #2c3e50;
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
        }
        .header-icon {
            margin-right: 10px;
        }
        .header-title {
            font-size: 18px;
            font-weight: 600;
        }
        .sidebar {
            position: fixed;
            left: 0;
            top: 50px;
            width: 200px;
            background: white;
            padding: 20px 0;
            min-height: calc(100vh - 50px);
            margin-top: 5px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        }
        .sidebar a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            cursor: pointer;
            text-decoration: none;
            color: #2d3748;
            transition: all 0.2s;
        }
        .sidebar a:hover {
            background: #f7fafc;
            color: #5A67D8;
            border-left: 3px solid #5A67D8;
        }
        .sidebar a.active {
            background: #eef2ff;
            color: #5A67D8;
            border-left: 3px solid #5A67D8;
            font-weight: 600;
        }
        .sidebar-icon {
            margin-right: 10px;
            font-size: 16px;
        }
        .content {
        position: fixed;
        top: 50px;
        left: 200px;
        right: 0;
        bottom: 0;
        overflow-y: auto;
        padding: 30px;
        background: #f5f5f5;
        margin-top: 10px;
}

    </style>
</head>
<body>

<div class="header">
    <span class="header-icon">🐬</span>
    <span class="header-title">Dolphin CRM</span>
</div>

<div class="sidebar">
    <a data-page="view_contacts.php" class="active">
        <span class="sidebar-icon">🏠</span> Home
    </a>
    <a data-page="add_contact.php">
        <span class="sidebar-icon">👤</span> New Contact
    </a>
    <a data-page="view_users.php">
        <span class="sidebar-icon">👥</span> Users
    </a>
    <a href="logout.php">
        <span class="sidebar-icon">🚪</span> Logout
    </a>
</div>

<div class="content" id="dashboard-content">
    <p style="text-align: center; color: #718096;">Loading...</p>
</div>

<script>
function loadPage(page) {
    const contentDiv = document.getElementById('dashboard-content');
    contentDiv.innerHTML = '<p style="text-align: center; color: #718096;">Loading...</p>';

    fetch(page)
        .then(resp => {
            if (!resp.ok) {
                throw new Error('Failed to load page');
            }
            return resp.text();
        })
        .then(html => {
            contentDiv.innerHTML = html;

            //executes scripts in the loaded content
            const scripts = contentDiv.querySelectorAll('script');
            scripts.forEach(oldScript => {
                const newScript = document.createElement('script');
                if (oldScript.src) {
                    newScript.src = oldScript.src;
                } else {
                    newScript.textContent = oldScript.textContent;
                }
                document.body.appendChild(newScript);
                setTimeout(() => newScript.remove(), 100);
            });
        })
        .catch(err => {
            console.error('Error:', err);
            contentDiv.innerHTML = "<p style='color: red; text-align: center;'>Error loading content. Please try again.</p>";
        });
}

//sets the active menu item
function setActiveMenu(link) {
    document.querySelectorAll('.sidebar a[data-page]').forEach(a => {
        a.classList.remove('active');
    });
    link.classList.add('active');
}

//dynamic page loading for the dashboard
document.querySelectorAll('[data-page]').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const page = this.getAttribute('data-page');
        setActiveMenu(this);
        loadPage(page);
    });
});

//Loads the home page by default
window.addEventListener('DOMContentLoaded', function() {
    loadPage('view_contacts.php');
});
</script>

</body>
</html>
