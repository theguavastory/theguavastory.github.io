<div class="sidebar">
    <img src="../images/logo.png" alt="Logo">
    <h2>DENR-CENRO</h2>
    <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="verified_land_list.php"><i class="fas fa-file-alt"></i> List of Verified Land Titles</a>
    <a href="add_land_title.php"><i class="fas fa-plus-circle"></i> Add Land Title</a>
    <a href="messages.php">
        <i class="fas fa-user"></i> My Messages 
        <?php if ($unread_message_count > 0): ?> 
            <span class="message-count"><?php echo $unread_message_count; ?></span>
        <?php endif; ?> 
    </a>
    <a href="notifications.php">
        <i class="fas fa-bell"></i> Approval Request 
        <span class="notification-count"><?php echo $notification_count; ?></span>
    </a>
    <a href="activity_logs.php"><i class="fas fa-list"></i> Activity Logs</a>
    <a href="user_management.php"><i class="fas fa-users"></i> User Management</a>
    <a href="archive.php"><i class="fas fa-archive"></i> Archive</a>
    <a href="logout.php" class="logout-btn" onclick="return confirmLogout();"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>
