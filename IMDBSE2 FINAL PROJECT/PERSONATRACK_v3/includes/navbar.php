<?php
require_once __DIR__ . '/auth.php';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="../dashboard.php">Barangay Profiling System</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../dashboard.php">Dashboard</a>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown">
                        Residents
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../modules/profile/list.php">Resident Profiles</a></li>
                        <li><a class="dropdown-item" href="../modules/contact/list.php">Contact Information</a></li>
                        <li><a class="dropdown-item" href="../modules/household/list.php">Households</a></li>
                    </ul>
                </li>
                
                <?php if (hasRole(['Admin'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                        Administration
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../modules/accounts/list.php">Accounts</a></li>
                        <li><a class="dropdown-item" href="../modules/roles/list.php">Roles</a></li>
                        <li><a class="dropdown-item" href="../modules/departments/list.php">Departments</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a class="nav-link" href="../modules/activity_logs/list.php">Activity Logs</a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
