<div class="user-horizontal-navigation">
    <ul>
        <li><a href="/Webler/profile.php">Overview</a></li>
        <li><a href="/Webler/edit-profile.php">Edit Profile</a></li>
        <?php if ($isAdmin) : ?>
            <li><a href="/Webler/admin.php">Admin Panel</a></li>
        <?php endif; ?>
    </ul>
</div>