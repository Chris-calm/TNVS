<?php
// Header/Navbar partial - Include this in your pages with: include 'partials/header.php';
// Make sure to include db_connect.php and fetch $pendingApprovals before including this file

// If $pendingApprovals is not set, fetch it
if (!isset($pendingApprovals)) {
    $pendingApprovals = [];
}

// Get current user's profile picture
$currentUserProfilePic = '../PICTURES/Ser.jpg'; // Default fallback
try {
    if (isset($conn) && isset($_SESSION['user_id'])) {
        // Check if profile_picture column exists
        $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
        if ($columns && $columns->num_rows > 0) {
            $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            
            if (!empty($user['profile_picture']) && file_exists("../uploads/profiles/" . $user['profile_picture'])) {
                $currentUserProfilePic = "../uploads/profiles/" . $user['profile_picture'];
            }
        }
    }
} catch (Exception $e) {
    // Keep default if there's any error
}
?>
<!-- Overlay for closing popups -->
<div class="popup-overlay" id="popupOverlay"></div>

<nav>
    <i class='bx bx-menu' ></i>
    <a href="#" class="nav-link">Categories</a>
    <form action="#">
    
    </form>
    <a href="#" class="notification" id="notificationBtn">
        <i class='bx bxs-bell' ></i>
        <span class="num"><?= count($pendingApprovals) ?></span>
    </a>
    <a href="#" class="profile" id="profileBtn">
        <img src="<?= htmlspecialchars($currentUserProfilePic) ?>" alt="Profile">
    </a>

    <!-- Notification Dropdown -->
    <div class="notification-dropdown" id="notificationDropdown">
        <div class="header">
            <span>Notifications</span>
            <span><?= count($pendingApprovals) ?></span>
        </div>
        <div class="notification-list">
            <?php if (empty($pendingApprovals)): ?>
                <div class="empty-state">
                    <i class='bx bx-bell-off' style="font-size: 48px; margin-bottom: 10px;"></i>
                    <p>No new notifications</p>
                </div>
            <?php else: ?>
                <?php foreach ($pendingApprovals as $item): 
                    $date_time = date('M j, Y h:i A', strtotime($item['created_at']));
                    $item_link = !empty($item['link']) ? $item['link'] : '#';
                ?>
                <a href="../PHP/<?= $item_link ?>" class="notification-item">
                    <div class="title"><?= htmlspecialchars($item['type']) ?></div>
                    <div class="desc"><?= htmlspecialchars($item['name']) ?> needs approval</div>
                    <div class="time"><?= $date_time ?></div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Profile Dropdown -->
    <div class="profile-dropdown" id="profileDropdown">
        <div class="profile-header">
            <img src="<?= htmlspecialchars($currentUserProfilePic) ?>" alt="Profile">
            <div class="name"><?= htmlspecialchars($_SESSION['username']) ?></div>
            <div class="role"><?= str_replace('_', ' ', htmlspecialchars($_SESSION['role'])) ?></div>
        </div>
        <div class="profile-menu">
            <a href="../PHP/Settings.php">
                <i class='bx bx-cog'></i>
                <span>Settings</span>
            </a>
            <a href="../PHP/logout.php" class="logout">
                <i class='bx bx-log-out'></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</nav>

<script>
    // Notification and Profile Popup Functionality
    const notificationBtn = document.getElementById('notificationBtn');
    const profileBtn = document.getElementById('profileBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const profileDropdown = document.getElementById('profileDropdown');
    const popupOverlay = document.getElementById('popupOverlay');

    // Toggle Notification Dropdown
    notificationBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Close profile if open
        profileDropdown.classList.remove('active');
        
        // Toggle notification
        notificationDropdown.classList.toggle('active');
        popupOverlay.classList.toggle('active');
    });

    // Toggle Profile Dropdown
    profileBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Close notification if open
        notificationDropdown.classList.remove('active');
        
        // Toggle profile
        profileDropdown.classList.toggle('active');
        popupOverlay.classList.toggle('active');
    });

    // Close popups when clicking overlay
    popupOverlay.addEventListener('click', function() {
        notificationDropdown.classList.remove('active');
        profileDropdown.classList.remove('active');
        popupOverlay.classList.remove('active');
    });

    // Close popups when clicking outside
    document.addEventListener('click', function(e) {
        if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target) &&
            !profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
            notificationDropdown.classList.remove('active');
            profileDropdown.classList.remove('active');
            popupOverlay.classList.remove('active');
        }
    });

    // Prevent dropdown clicks from closing the popup
    notificationDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    profileDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });
</script>