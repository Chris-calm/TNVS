<?php
// Sidebar partial - Include this in your pages with: include 'partials/sidebar.php';
require_once __DIR__ . '/../rbac_middleware.php';
RBACMiddleware::init();

// Get current user's profile information
$currentUserProfilePic = '../PICTURES/Ser.jpg'; // Default fallback
$currentUserName = $_SESSION['username'] ?? 'User';
$currentUserRole = $_SESSION['role'] ?? 'employee';

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
<section id="sidebar">
    <a href="" class="brand">
        <img src="../PICTURES/Black and White Circular Art & Design Logo1.png" alt="Trail Ad Corporation Logo" class="brand-logo" style="width: 48px; height: 48px;">
        <span class="text" style="font-size: 14px; font-weight: 600;">Transport Network Vehicle System</span>
    </a>

    <div class="profile-status">
        <div class="profile-info">
            <div class="profile-avatar">
                <div class="profile-circle">
                    <img src="<?= htmlspecialchars($currentUserProfilePic) ?>" alt="Profile Picture">
                    <div class="status-indicator"></div>
                </div>
            </div>
            <div class="profile-details">
                <div class="profile-name"><?= htmlspecialchars($currentUserName) ?></div>
                <div class="profile-role"><?= str_replace('_', ' ', ucwords($currentUserRole, '_')) ?></div>
            </div>
        </div>
    </div>

    <ul class="side-menu top">
        <?php if (RBACMiddleware::hasPermission('view_dashboard')): ?>
        <li <?= (basename($_SERVER['PHP_SELF']) == 'Dashboard.php') ? 'class="active"' : '' ?>>
            <a href="../PHP/Dashboard.php">
                <i class='bx bxs-dashboard'></i>
                <span class="text">Dashboard</span>
            </a>
        </li>
        <?php endif; ?>

        <?php 
        $hasFacilityPermissions = RBACMiddleware::hasPermission('reserve_rooms') || 
                                 RBACMiddleware::hasPermission('view_approvals') || 
                                 RBACMiddleware::hasPermission('view_reservations') || 
                                 RBACMiddleware::hasPermission('view_facilities');
        if ($hasFacilityPermissions): 
        ?>
        <li class="dropdown">
            <a href="#" class="dropdown-toggle">
                <i class='bx bxs-store-alt'></i>
                <span class="text">Facilities Reservation</span>
                <i class='bx bx-chevron-down arrow'></i>
            </a>
            <ul class="dropdown-menu">
                <?php if (RBACMiddleware::hasPermission('reserve_rooms')): ?>
                <li><a href="../PHP/Reserve_Room.php"><span class="text">Reserve Room</span></a></li>
                <?php endif; ?>
                <?php if (RBACMiddleware::hasPermission('view_approvals')): ?>
                <li><a href="../PHP/Approval_Rejection_Requests.php"><span class="text">Approval/Rejection Request</span></a></li>
                <?php endif; ?>
                <?php if (RBACMiddleware::hasPermission('view_reservations')): ?>
                <li><a href="../PHP/Reservation_Calendar.php"><span class="text">Reservation Calendar</span></a></li>
                <?php endif; ?>
                <?php if (RBACMiddleware::hasPermission('view_facilities')): ?>
                <li><a href="../PHP/Facilities_Maintenance.php"><span class="text">Facilities Maintenance</span></a></li>
                <?php endif; ?>
            </ul>
        </li>
        <?php endif; ?>

        <?php 
        $hasDocumentPermissions = RBACMiddleware::hasPermission('upload_documents') || 
                                 RBACMiddleware::hasPermission('manage_document_permissions') || 
                                 RBACMiddleware::hasPermission('view_documents');
        if ($hasDocumentPermissions): 
        ?>
        <li class="dropdown">
            <a href="#" class="dropdown-toggle">
                <i class='bx bxs-archive'></i>
                <span class="text">Documents Management</span>
                <i class='bx bx-chevron-down arrow'></i>
            </a>
            <ul class="dropdown-menu">
                <?php if (RBACMiddleware::hasPermission('upload_documents')): ?>
                <li><a href="../PHP/Upload_Document.php"><span class="text">Upload Document</span></a></li>
                <?php endif; ?>
                <?php if (RBACMiddleware::hasPermission('manage_document_permissions')): ?>
                <li><a href="../PHP/Document_Access_Permissions.php"><span class="text">Document Access Permission</span></a></li>
                <?php endif; ?>
                <?php if (RBACMiddleware::hasPermission('view_documents')): ?>
                <li><a href="../PHP/View_Records.php"><span class="text">View Records</span></a></li>
                <?php endif; ?>
            </ul>
        </li>
        <?php endif; ?>

        <?php 
        $hasLegalPermissions = RBACMiddleware::hasPermission('view_contracts') || 
                              RBACMiddleware::hasPermission('view_policies') || 
                              RBACMiddleware::hasPermission('view_cases');
        if ($hasLegalPermissions): 
        ?>
        <li class="dropdown">
            <a href="#" class="dropdown-toggle">
                <i class='bx bxs-landmark'></i>
                <span class="text">Legal Management</span>
                <i class='bx bx-chevron-down arrow'></i>
            </a>
            <ul class="dropdown-menu">
                <?php if (RBACMiddleware::hasPermission('view_contracts')): ?>
                <li><a href="../PHP/Contracts.php"><span class="text">Contracts</span></a></li>
                <?php endif; ?>
                <?php if (RBACMiddleware::hasPermission('view_policies')): ?>
                <li><a href="../PHP/Policies.php"><span class="text">Policies</span></a></li>
                <?php endif; ?>
                <?php if (RBACMiddleware::hasPermission('view_cases')): ?>
                <li><a href="../PHP/Case_Records.php"><span class="text">Case Records</span></a></li>
                <?php endif; ?>
            </ul>
        </li>
        <?php endif; ?>

        <?php 
        $hasVisitorPermissions = RBACMiddleware::hasPermission('add_visitors') || 
                                RBACMiddleware::hasPermission('view_visitor_logs');
        if ($hasVisitorPermissions): 
        ?>
        <li class="dropdown">
            <a href="#" class="dropdown-toggle">
                <i class='bx bxs-universal-access'></i>
                <span class="text">Visitor Management</span>
                <i class='bx bx-chevron-down arrow'></i>
            </a>
            <ul class="dropdown-menu">
                <?php if (RBACMiddleware::hasPermission('add_visitors')): ?>
                <li><a href="../PHP/Visitor_Pre_Registration.php"><span class="text">Visitor Pre-Registration</span></a></li>
                <?php endif; ?>
                <?php if (RBACMiddleware::hasPermission('view_visitor_logs')): ?>
                <li><a href="../PHP/Visitor_Logs.php"><span class="text">Visitor Logs</span></a></li>
                <?php endif; ?>
            </ul>
        </li>
        <?php endif; ?>

        <?php if (RBACMiddleware::hasPermission('view_statistics')): ?>
        <li class="side-menu-top">
            <a href="../PHP/Statistics.php" class="dropdown-toggle">
                <i class='bx bxs-circle-three-quarter'></i>
                <span class="text">Statistics</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if (RBACMiddleware::hasPermission('manage_users')): ?>
        <li class="side-menu-top">
            <a href="../PHP/Role_Management.php" class="dropdown-toggle">
                <i class='bx bxs-user-account'></i>
                <span class="text">Role Management</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if (RBACMiddleware::hasPermission('system_admin')): ?>
        <li class="side-menu-top">
            <a href="../PHP/Settings.php" class="dropdown-toggle">
                <i class='bx bxs-cog'></i>
                <span class="text">Settings</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
    <ul class="side-menu">
        <li>
            <a href="logout.php" class="logout">
                <i class='bx bxs-log-out-circle' ></i>
                <span class="text">Logout</span>
            </a>
        </li>
    </ul>
</section>

<style>
    /* Basic dropdown structure */
    #sidebar .side-menu.top li.dropdown {
        position: relative;
        padding-right: 10px;
    }

    /* Set z-index ONLY when the dropdown is active to bring it to the front */
    #sidebar .side-menu.top li.dropdown.open {
        z-index: 10;
    }

    /* Dropdown menu hidden by default */
    #sidebar .side-menu.top li.dropdown .dropdown-menu {
        display: none;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    /* When dropdown is open, position it absolutely to cover the full width */
    #sidebar .side-menu.top li.dropdown.open .dropdown-menu {
        display: block;
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background: #e8e8e8;
        animation: slideDown 0.3s ease;
        padding: 8px 0;
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Dropdown items styling */
    #sidebar .side-menu.top li.dropdown.open .dropdown-menu li a {
        display: block;
        padding: 8px 20px 8px 50px;
        color: #666;
        text-decoration: none;
        font-size: 13px;
    }

    /* Ensure sidebar allows scrolling if content overflows */
    #sidebar {
        overflow-y: auto;
        height: 100vh;
        width: 290px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get the main sidebar element
    const sidebar = document.getElementById('sidebar');

    document.addEventListener('click', function(e) {
        const toggle = e.target.closest('.dropdown-toggle');
        
        if (toggle) {
            e.preventDefault();
            
            const dropdown = toggle.closest('.dropdown');
            
            // Close all other open dropdowns first
            document.querySelectorAll('#sidebar .dropdown.open').forEach(function(item) {
                if (item !== dropdown) {
                    item.classList.remove('open');
                }
            });
            
            // Toggle the clicked dropdown
            dropdown.classList.toggle('open');

            // --- NEW LOGIC TO CONTROL SCROLLBAR ---
            // Check if any dropdown is currently open
            if (document.querySelector('#sidebar .dropdown.open')) {
                // If a dropdown is open, hide the sidebar's scrollbar
                sidebar.style.overflowY = 'hidden';
            } else {
                // If no dropdowns are open, restore the default scrollbar behavior
                sidebar.style.overflowY = 'auto';
            }
        }
    });
});
</script>