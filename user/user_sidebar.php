<div class="sidebar">
    <img src="../images/logo.png" alt="Logo">
    <h2>DENR-CENRO</h2>
    <a href="user_index.php"><i class="fas fa-newspaper"></i>My Feed</a>
    <a href="user_approved_list.php"><i class="fas fa-check-circle"></i> My Approved Land Titles</a>
    <a href="user_submitted_land_list.php"><i class="fas fa-file-alt"></i> My Submitted Land Titles</a>
    <a href="user_add_land_title.php"><i class="fas fa-plus-circle"></i> Submit Land Title</a>
    <a href="user_messages.php">
        <i class="fas fa-user"></i> My Messages 
        <?php if ($unread_message_count > 0): ?> 
            <span class="message-count"><?php echo $unread_message_count; ?></span>
        <?php endif; ?> 
    </a>
    <a class="logout-btn" onclick="return confirmLogout();"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>
