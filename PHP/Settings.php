<?php
// Initialize RBAC and check page access - Super Admin only
require_once 'rbac_middleware.php';
RBACMiddleware::checkPageAccess();
// Restrict to Super Admin only
RBACMiddleware::requirePermission('system_admin');

include 'db_connect.php';
include 'partials/functions.php';

// Fetch pending approvals for notifications
$pendingApprovals = getPendingItems($conn);

// Handle form submissions
$message = '';
$messageType = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if ($newPassword !== $confirmPassword) {
        $message = "New passwords do not match!";
        $messageType = "error";
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (password_verify($currentPassword, $user['password'])) {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateStmt->bind_param("si", $hashedPassword, $_SESSION['user_id']);
            
            if ($updateStmt->execute()) {
                $message = "Password changed successfully!";
                $messageType = "success";
            } else {
                $message = "Error updating password. Please try again.";
                $messageType = "error";
            }
            $updateStmt->close();
        } else {
            $message = "Current password is incorrect!";
            $messageType = "error";
        }
        $stmt->close();
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $targetUserId = $_POST['target_user_id'] ?? $_SESSION['user_id']; // Get selected user ID
    $newUsername = trim($_POST['username']);
    $email = trim($_POST['email']);
    
    // Check if username already exists (excluding target user)
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $checkStmt->bind_param("si", $newUsername, $targetUserId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $message = "Username already exists!";
        $messageType = "error";
    } else {
        // Handle profile picture upload
        $profilePicture = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
            $uploadDir = "../uploads/profiles/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($fileExtension, $allowedExtensions)) {
                $profilePicture = "profile_" . $targetUserId . "_" . time() . "." . $fileExtension;
                move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadDir . $profilePicture);
            }
        }
        
        // Update user profile - check which columns exist
        $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
        $hasEmail = $columns && $columns->num_rows > 0;
        
        $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
        $hasProfilePicture = $columns && $columns->num_rows > 0;
        
        // Build update query based on available columns
        if ($profilePicture && $hasProfilePicture && $hasEmail) {
            $updateStmt = $conn->prepare("UPDATE users SET username = ?, email = ?, profile_picture = ? WHERE id = ?");
            $updateStmt->bind_param("sssi", $newUsername, $email, $profilePicture, $targetUserId);
        } else if ($hasEmail && !$profilePicture) {
            $updateStmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $updateStmt->bind_param("ssi", $newUsername, $email, $targetUserId);
        } else if ($profilePicture && $hasProfilePicture && !$hasEmail) {
            $updateStmt = $conn->prepare("UPDATE users SET username = ?, profile_picture = ? WHERE id = ?");
            $updateStmt->bind_param("ssi", $newUsername, $profilePicture, $targetUserId);
        } else {
            // Only update username if other columns don't exist
            $updateStmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
            $updateStmt->bind_param("si", $newUsername, $targetUserId);
        }
        
        if ($updateStmt->execute()) {
            // Only update session if we're updating the current user
            if ($targetUserId == $_SESSION['user_id']) {
                $_SESSION['username'] = $newUsername;
                $message = "Your profile updated successfully!";
            } else {
                // Get the updated user's username for the message
                $userStmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
                $userStmt->bind_param("i", $targetUserId);
                $userStmt->execute();
                $userResult = $userStmt->get_result();
                $updatedUser = $userResult->fetch_assoc();
                $userStmt->close();
                
                $message = "Profile updated successfully for user: " . htmlspecialchars($updatedUser['username']);
            }
            $messageType = "success";
        } else {
            $message = "Error updating profile. Please try again.";
            $messageType = "error";
        }
        $updateStmt->close();
    }
    $checkStmt->close();
}

// Handle notification settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_notifications'])) {
    $_SESSION['email_notifications'] = isset($_POST['email_notifications']) ? 1 : 0;
    $_SESSION['push_notifications'] = isset($_POST['push_notifications']) ? 1 : 0;
    
    $message = "Notification settings saved successfully!";
    $messageType = "success";
}

// Handle system settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $theme = $_POST['theme'];
    $language = $_POST['language'];
    $timezone = $_POST['timezone'];
    $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
    $pushNotifications = isset($_POST['push_notifications']) ? 1 : 0;
    
    // Save settings to database or session
    $_SESSION['user_theme'] = $theme;
    $_SESSION['user_language'] = $language;
    $_SESSION['user_timezone'] = $timezone;
    
    // You can also save to database if you have a user_settings table
    $message = "System settings saved successfully!";
    $messageType = "success";
}

// Handle Clear All Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_all_data'])) {
    $confirmText = $_POST['confirm_text'];
    
    if ($confirmText === 'DELETE ALL DATA') {
        try {
            // Clear all tables (be very careful with this!)
            $tables = ['facilities', 'case_records', 'policies'];
            $clearedTables = [];
            
            foreach ($tables as $table) {
                // Check if table exists first
                $checkTable = $conn->query("SHOW TABLES LIKE '$table'");
                if ($checkTable && $checkTable->num_rows > 0) {
                    $result = $conn->query("DELETE FROM $table");
                    if ($result) {
                        $clearedTables[] = $table;
                    }
                }
            }
            
            if (!empty($clearedTables)) {
                $message = "Data cleared successfully from tables: " . implode(', ', $clearedTables);
                $messageType = "success";
            } else {
                $message = "No data was cleared. Tables may be empty or not exist.";
                $messageType = "warning";
            }
        } catch (Exception $e) {
            $message = "Error clearing data: " . $e->getMessage();
            $messageType = "error";
        }
    } else {
        $message = "Confirmation text incorrect. Data not cleared.";
        $messageType = "error";
    }
}

// Handle Export Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_data'])) {
    $exportType = $_POST['export_type'];
    
    if ($exportType === 'json') {
        try {
            // Export as JSON
            $data = [];
            $tables = ['users', 'facilities', 'case_records', 'policies'];
            
            foreach ($tables as $table) {
                // Check if table exists first
                $checkTable = $conn->query("SHOW TABLES LIKE '$table'");
                if ($checkTable && $checkTable->num_rows > 0) {
                    $result = $conn->query("SELECT * FROM $table");
                    if ($result) {
                        $data[$table] = $result->fetch_all(MYSQLI_ASSOC);
                    } else {
                        $data[$table] = [];
                    }
                } else {
                    $data[$table] = [];
                }
            }
            
            $filename = "tnvs_export_" . date('Y-m-d_H-i-s') . ".json";
            $filepath = "../exports/" . $filename;
            
            if (!is_dir("../exports/")) {
                mkdir("../exports/", 0777, true);
            }
            
            file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
            
            $message = "Data exported successfully! <a href='../exports/$filename' class='underline' target='_blank'>Download here</a>";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Export failed: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Get all users for dropdown
$allUsers = [];
try {
    $usersResult = $conn->query("SELECT id, username, role FROM users ORDER BY username");
    if ($usersResult) {
        $allUsers = $usersResult->fetch_all(MYSQLI_ASSOC);
    }
} catch (Exception $e) {
    $allUsers = [];
}

// Get selected user data (default to current user)
$selectedUserId = $_GET['user_id'] ?? $_SESSION['user_id'];
try {
    // First check if email and profile_picture columns exist
    $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
    $hasEmail = $columns && $columns->num_rows > 0;
    
    $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
    $hasProfilePicture = $columns && $columns->num_rows > 0;
    
    // Build query based on available columns
    $selectFields = "id, username, role";
    if ($hasEmail) $selectFields .= ", email";
    if ($hasProfilePicture) $selectFields .= ", profile_picture";
    
    $userStmt = $conn->prepare("SELECT $selectFields FROM users WHERE id = ?");
    $userStmt->bind_param("i", $selectedUserId);
    $userStmt->execute();
    $userData = $userStmt->get_result()->fetch_assoc();
    $userStmt->close();
    
    // Set defaults if columns don't exist
    if (!$hasEmail) $userData['email'] = '';
    if (!$hasProfilePicture) $userData['profile_picture'] = '';
    
} catch (Exception $e) {
    // Fallback if there are any issues
    $userData = [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['role'],
        'email' => '',
        'profile_picture' => ''
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <title>Settings | TNVS Dashboard</title>
    
    <?php include 'partials/styles.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
    #content main {
        background-color: transparent;
    }
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    
    <?php include 'partials/sidebar.php'; ?>
    
    <section id="content">
        <?php include 'partials/header.php'; ?>
        
        <main>
            <div class="max-w-4xl mx-auto p-6">
                <h1 class="text-3xl font-bold text-gray-800 mb-8">Settings</h1>
                
                <!-- Messages -->
                <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
                <?php endif; ?>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Profile Settings -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-xl font-semibold mb-4 flex items-center">
                            <i class='bx bx-user mr-2'></i>
                            Profile Settings
                        </h2>
                        <form method="POST" enctype="multipart/form-data" class="space-y-4">
                            <!-- User Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Select User to Edit</label>
                                <select name="user_selector" onchange="changeUser(this.value)" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:border-blue-400 transition-colors">
                                    <?php foreach ($allUsers as $user): ?>
                                        <option value="<?= $user['id'] ?>" <?= $user['id'] == $selectedUserId ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($user['username']) ?> (<?= str_replace('_', ' ', ucwords($user['role'], '_')) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="target_user_id" value="<?= $selectedUserId ?>">
                            </div>
                            
                            <!-- Profile Picture -->
                            <div class="text-center mb-4">
                                <div class="w-24 h-24 mx-auto mb-3 rounded-full overflow-hidden bg-gray-200">
                                    <?php if (!empty($userData['profile_picture'])): ?>
                                        <img src="../uploads/profiles/<?= htmlspecialchars($userData['profile_picture']) ?>" alt="Profile" class="w-full h-full object-cover">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-gray-500">
                                            <i class='bx bx-user text-3xl'></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <input type="file" name="profile_picture" accept="image/*" class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <p class="text-xs text-gray-500 mt-1">Upload new profile picture for <?= htmlspecialchars($userData['username']) ?></p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                <input type="text" name="username" value="<?= htmlspecialchars($userData['username']) ?>" class="w-full border rounded-lg px-3 py-2" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                <input type="text" value="<?= str_replace('_', ' ', ucwords($userData['role'], '_')) ?>" class="w-full border rounded-lg px-3 py-2 bg-gray-100" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>" placeholder="user@example.com" class="w-full border rounded-lg px-3 py-2">
                            </div>
                            <button type="submit" name="update_profile" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                                Update Profile
                            </button>
                        </form>
                    </div>

                    <!-- Security Settings -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-xl font-semibold mb-4 flex items-center">
                            <i class='bx bx-shield mr-2'></i>
                            Security Settings
                        </h2>
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                <input type="password" name="current_password" class="w-full border rounded-lg px-3 py-2" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <input type="password" name="new_password" class="w-full border rounded-lg px-3 py-2" required minlength="6">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="w-full border rounded-lg px-3 py-2" required minlength="6">
                            </div>
                            <button type="submit" name="change_password" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                Change Password
                            </button>
                        </form>
                    </div>

                    <!-- Notification Settings -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-xl font-semibold mb-4 flex items-center">
                            <i class='bx bx-bell mr-2'></i>
                            Notification Settings
                        </h2>
                        <form method="POST" class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Email Notifications</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="email_notifications" class="sr-only peer" <?= isset($_SESSION['email_notifications']) && $_SESSION['email_notifications'] ? 'checked' : '' ?>>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Push Notifications</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="push_notifications" class="sr-only peer" <?= isset($_SESSION['push_notifications']) && $_SESSION['push_notifications'] ? 'checked' : '' ?>>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <button type="submit" name="save_notifications" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm">
                                Save Notification Settings
                            </button>
                        </form>
                    </div>

                    <!-- System Settings -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-xl font-semibold mb-4 flex items-center">
                            <i class='bx bx-cog mr-2'></i>
                            System Settings
                        </h2>
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Theme</label>
                                <select name="theme" class="w-full border rounded-lg px-3 py-2" onchange="changeTheme(this.value)">
                                    <option value="light" <?= ($_SESSION['user_theme'] ?? 'light') === 'light' ? 'selected' : '' ?>>Light Mode</option>
                                    <option value="dark" <?= ($_SESSION['user_theme'] ?? 'light') === 'dark' ? 'selected' : '' ?>>Dark Mode</option>
                                    <option value="auto" <?= ($_SESSION['user_theme'] ?? 'light') === 'auto' ? 'selected' : '' ?>>Auto</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Language</label>
                                <select name="language" class="w-full border rounded-lg px-3 py-2" onchange="changeLanguage(this.value)">
                                    <option value="en" <?= ($_SESSION['user_language'] ?? 'en') === 'en' ? 'selected' : '' ?>>English</option>
                                    <option value="fil" <?= ($_SESSION['user_language'] ?? 'en') === 'fil' ? 'selected' : '' ?>>Filipino</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                                <select name="timezone" class="w-full border rounded-lg px-3 py-2">
                                    <option value="Asia/Manila" <?= ($_SESSION['user_timezone'] ?? 'Asia/Manila') === 'Asia/Manila' ? 'selected' : '' ?>>Asia/Manila (GMT+8)</option>
                                    <option value="UTC" <?= ($_SESSION['user_timezone'] ?? 'Asia/Manila') === 'UTC' ? 'selected' : '' ?>>UTC (GMT+0)</option>
                                    <option value="America/New_York" <?= ($_SESSION['user_timezone'] ?? 'Asia/Manila') === 'America/New_York' ? 'selected' : '' ?>>Eastern Time (GMT-5)</option>
                                    <option value="Europe/London" <?= ($_SESSION['user_timezone'] ?? 'Asia/Manila') === 'Europe/London' ? 'selected' : '' ?>>London Time (GMT+0)</option>
                                </select>
                            </div>
                            <button type="submit" name="save_settings" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                                Save Settings
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Export Data Section -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mt-6">
                    <h2 class="text-xl font-semibold text-blue-800 mb-4 flex items-center">
                        <i class='bx bx-download mr-2'></i>
                        Data Export
                    </h2>
                    <p class="text-blue-700 mb-4">Export your system data for backup or migration purposes.</p>
                    <form method="POST" class="flex gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-blue-700 mb-1">Export Format</label>
                            <select name="export_type" class="border border-blue-300 rounded-lg px-3 py-2">
                                <option value="json">JSON Format</option>
                                <option value="csv">CSV Format (Coming Soon)</option>
                            </select>
                        </div>
                        <button type="submit" name="export_data" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            <i class='bx bx-download mr-1'></i>
                            Export Data
                        </button>
                    </form>
                </div>

                <!-- Danger Zone -->
                <div class="bg-red-50 border border-red-200 rounded-xl p-6 mt-6">
                    <h2 class="text-xl font-semibold text-red-800 mb-4 flex items-center">
                        <i class='bx bx-error mr-2'></i>
                        Danger Zone
                    </h2>
                    <p class="text-red-700 mb-4">These actions are irreversible. Please be careful.</p>
                    
                    <!-- Clear All Data -->
                    <div class="bg-white rounded-lg p-4 mb-4 border border-red-200">
                        <h3 class="font-semibold text-red-800 mb-2">Clear All System Data</h3>
                        <p class="text-sm text-red-600 mb-3">This will permanently delete all facilities, cases, policies, visitors, and documents. User accounts will remain.</p>
                        <form method="POST" onsubmit="return confirmClearData()">
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-red-700 mb-1">Type "DELETE ALL DATA" to confirm:</label>
                                <input type="text" name="confirm_text" class="border border-red-300 rounded-lg px-3 py-2 w-full" placeholder="DELETE ALL DATA" required>
                            </div>
                            <button type="submit" name="clear_all_data" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                                <i class='bx bx-trash mr-1'></i>
                                Clear All Data
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <script src="../JS/script.js"></script>
            
            <script>
                // Theme switching functionality
                function changeTheme(theme) {
                    const body = document.body;
                    const sidebar = document.getElementById('sidebar');
                    const content = document.getElementById('content');
                    
                    // Remove existing theme classes
                    body.classList.remove('theme-light', 'theme-dark');
                    
                    if (theme === 'dark') {
                        body.classList.add('theme-dark');
                        body.style.backgroundColor = '#1a1a1a';
                        body.style.color = '#ffffff';
                        
                        if (sidebar) {
                            sidebar.style.backgroundColor = '#2d2d2d';
                            sidebar.style.color = '#ffffff';
                        }
                        
                        if (content) {
                            content.style.backgroundColor = '#1a1a1a';
                        }
                        
                        // Update all white backgrounds to dark
                        const whiteElements = document.querySelectorAll('.bg-white');
                        whiteElements.forEach(el => {
                            el.style.backgroundColor = '#2d2d2d';
                            el.style.color = '#ffffff';
                        });
                        
                    } else if (theme === 'light') {
                        body.classList.add('theme-light');
                        body.style.backgroundColor = '#f3f4f6';
                        body.style.color = '#000000';
                        
                        if (sidebar) {
                            sidebar.style.backgroundColor = '#ffffff';
                            sidebar.style.color = '#000000';
                        }
                        
                        if (content) {
                            content.style.backgroundColor = '#f3f4f6';
                        }
                        
                        // Reset white backgrounds
                        const whiteElements = document.querySelectorAll('.bg-white');
                        whiteElements.forEach(el => {
                            el.style.backgroundColor = '#ffffff';
                            el.style.color = '#000000';
                        });
                    } else if (theme === 'auto') {
                        // Auto theme based on system preference
                        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                        changeTheme(prefersDark ? 'dark' : 'light');
                    }
                }
                
                // Language switching functionality
                function changeLanguage(language) {
                    const translations = {
                        'en': {
                            'Settings': 'Settings',
                            'Profile Settings': 'Profile Settings',
                            'Security Settings': 'Security Settings',
                            'System Settings': 'System Settings',
                            'Notification Settings': 'Notification Settings',
                            'Username': 'Username',
                            'Email': 'Email',
                            'Role': 'Role',
                            'Theme': 'Theme',
                            'Language': 'Language',
                            'Timezone': 'Timezone'
                        },
                        'fil': {
                            'Settings': 'Mga Setting',
                            'Profile Settings': 'Mga Setting ng Profile',
                            'Security Settings': 'Mga Setting ng Security',
                            'System Settings': 'Mga Setting ng System',
                            'Notification Settings': 'Mga Setting ng Notification',
                            'Username': 'Username',
                            'Email': 'Email',
                            'Role': 'Tungkulin',
                            'Theme': 'Tema',
                            'Language': 'Wika',
                            'Timezone': 'Timezone'
                        }
                    };
                    
                    // Update text content based on selected language
                    if (translations[language]) {
                        Object.keys(translations[language]).forEach(key => {
                            const elements = document.querySelectorAll(`[data-translate="${key}"]`);
                            elements.forEach(el => {
                                el.textContent = translations[language][key];
                            });
                        });
                    }
                }
                
                // Confirmation for clearing all data
                function confirmClearData() {
                    const confirmText = document.querySelector('input[name="confirm_text"]').value;
                    if (confirmText !== 'DELETE ALL DATA') {
                        alert('Please type "DELETE ALL DATA" exactly to confirm.');
                        return false;
                    }
                    
                    return confirm('Are you absolutely sure you want to delete ALL system data? This action cannot be undone!');
                }
                
                // Apply saved theme on page load
                document.addEventListener('DOMContentLoaded', function() {
                    const savedTheme = '<?= $_SESSION['user_theme'] ?? 'light' ?>';
                    if (savedTheme !== 'light') {
                        changeTheme(savedTheme);
                    }
                    
                    // Profile picture preview
                    const profileInput = document.querySelector('input[name="profile_picture"]');
                    if (profileInput) {
                        profileInput.addEventListener('change', function(e) {
                            const file = e.target.files[0];
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    const img = document.querySelector('.w-24.h-24 img');
                                    if (img) {
                                        img.src = e.target.result;
                                    } else {
                                        const container = document.querySelector('.w-24.h-24');
                                        container.innerHTML = `<img src="${e.target.result}" alt="Profile" class="w-full h-full object-cover">`;
                                    }
                                };
                                reader.readAsDataURL(file);
                            }
                        });
                    }
                });
                
                // Auto-save settings when changed
                document.querySelectorAll('select[name="theme"], select[name="language"]').forEach(select => {
                    select.addEventListener('change', function() {
                        // Auto-submit the form when settings change
                        setTimeout(() => {
                            this.closest('form').submit();
                        }, 500);
                    });
                });
                
                // Function to change user selection
                function changeUser(userId) {
                    // Redirect to the same page with the selected user ID
                    window.location.href = 'Settings.php?user_id=' + userId;
                }
            </script>
        </main>
    </section>
</body>
</html>
