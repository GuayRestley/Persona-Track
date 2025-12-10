<?php
if (!isset($_SESSION)) {
    session_start();
}

// Prevent access without login
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$username   = $_SESSION['username'];
$role       = $_SESSION['role'];   // <-- FIXED. Using EXACT column.
?>
<style>
    :root {
        --pink-light: #ffc2c2;
        --pink-medium: #ff9999;
        --purple: #9881f2;
        --red: #d12525;
        --white: #ffffff;
    }

    .topbar {
        width: 100%;
        background: linear-gradient(135deg, var(--red), var(--purple));
        padding: 16px 24px;
        color: var(--white);
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        box-shadow: 0 5px 20px rgba(0,0,0,0.25);
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .topbar-left h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        letter-spacing: 1px;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .topbar-right {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .user-info {
        text-align: right;
        font-size: 15px;
        line-height: 1.2;
    }

    .user-info strong {
        font-size: 16px;
        color: var(--white);
    }

    .user-role {
        font-size: 13px;
        opacity: 0.9;
        color: var(--pink-light);
        font-weight: 600;
    }

    .logout-btn {
        background: var(--white);
        color: var(--red);
        padding: 8px 18px;
        border-radius: 10px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        font-size: 14px;
        text-decoration: none;
        transition: 0.3s ease;
        box-shadow: 0 3px 10px rgba(0,0,0,0.15);
    }

    .logout-btn:hover {
        background: var(--pink-light);
        color: var(--red);
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0,0,0,0.25);
    }
</style>

<div class="topbar">
    <div class="topbar-left">
        <h2>PersonaTrack Dashboard</h2>
    </div>

    <div class="topbar-right">
        <div class="user-info">
            <div><strong><?php echo htmlspecialchars($username); ?></strong></div>
            <div class="user-role"><?php echo htmlspecialchars($role); ?></div>
        </div>

        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
</div>
