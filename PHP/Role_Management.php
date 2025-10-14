<?php
// Initialize RBAC and check page access
require_once 'rbac_middleware.php';
RBACMiddleware::checkPageAccess();

// Only Super Admin can access this page
RBACMiddleware::requirePermission('manage_users');

include 'db_connect.php';
include 'partials/functions.php';

// Fetch pending approvals for notifications
$pendingApprovals = getPendingItems($conn);

// Handle form submissions
$message = '';
$messageType = '';

// Add new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Check if username already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $message = "Username already exists!";
        $messageType = "error";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashedPassword, $role);
        
        if ($stmt->execute()) {
            $message = "User '$username' created successfully!";
            $messageType = "success";
        } else {
            $message = "Error creating user: " . $stmt->error;
            $messageType = "error";
        }
        $stmt->close();
    }
    $checkStmt->close();
}

// Update user role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_role') {
    $userId = intval($_POST['user_id']);
    $newRole = $_POST['new_role'];
    
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $newRole, $userId);
    
    if ($stmt->execute()) {
        $message = "User role updated successfully!";
        $messageType = "success";
    } else {
        $message = "Error updating user role: " . $stmt->error;
        $messageType = "error";
    }
    $stmt->close();
}

// Delete user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $userId = intval($_POST['user_id']);
    
    // Prevent deleting current user
    if ($userId == $_SESSION['user_id']) {
        $message = "You cannot delete your own account!";
        $messageType = "error";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            $message = "User deleted successfully!";
            $messageType = "success";
        } else {
            $message = "Error deleting user: " . $stmt->error;
            $messageType = "error";
        }
        $stmt->close();
    }
}

// Fetch all users
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <title>Role Management | TNVS Dashboard</title>
    
    <?php include 'partials/styles.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body style="background-color: #eeeeee;" class="bg-custom flex h-screen overflow-hidden">
    
    <?php include 'partials/sidebar.php'; ?>

    <section id="content">
        <?php include 'partials/header.php'; ?>

        <main class="max-w-7xl mx-auto px-4 py-8">
            <div class="mb-12">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-light text-gray-900">Role Management</h1>
                        <p class="text-sm text-gray-500 mt-1">Manage user accounts and permissions</p>
                    </div>
                    <button onclick="document.getElementById('addUserModal').classList.remove('hidden')"
                        class="bg-gray-900 hover:bg-gray-800 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
                        Add New User
                    </button>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
            <div class="mb-6 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <!-- Role Permissions Overview -->
            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg border border-gray-100 p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                            <i class='bx bx-crown text-red-600'></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Super Admin</h3>
                            <p class="text-sm text-gray-500">Full system access</p>
                        </div>
                    </div>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• All permissions</li>
                        <li>• User management</li>
                        <li>• System administration</li>
                        <li>• Backup & logs</li>
                    </ul>
                </div>

                <div class="bg-white rounded-lg border border-gray-100 p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class='bx bx-shield text-blue-600'></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Admin</h3>
                            <p class="text-sm text-gray-500">Management access</p>
                        </div>
                    </div>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Document management</li>
                        <li>• Visitor management</li>
                        <li>• Facility approvals</li>
                        <li>• Reports & analytics</li>
                    </ul>
                </div>

                <div class="bg-white rounded-lg border border-gray-100 p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class='bx bx-user text-green-600'></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Employee</h3>
                            <p class="text-sm text-gray-500">Basic access</p>
                        </div>
                    </div>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• View documents</li>
                        <li>• Add visitors</li>
                        <li>• View cases</li>
                        <li>• Reserve facilities</li>
                    </ul>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-lg font-medium text-gray-900">System Users</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($users->num_rows > 0): ?>
                                <?php while($user = $users->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                                <i class='bx bx-user text-gray-600'></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['username']) ?></div>
                                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                <div class="text-xs text-blue-600">(You)</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $roleColors = [
                                            'super_admin' => 'bg-red-100 text-red-800',
                                            'admin' => 'bg-blue-100 text-blue-800',
                                            'employee' => 'bg-green-100 text-green-800'
                                        ];
                                        $roleColor = $roleColors[$user['role']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $roleColor ?>">
                                            <?= htmlspecialchars(str_replace('_', ' ', ucwords($user['role'], '_'))) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('M j, Y', strtotime($user['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button onclick="openEditModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>', '<?= $user['role'] ?>')"
                                                class="text-blue-600 hover:text-blue-900 transition-colors">
                                                <i class='bx bx-edit'></i>
                                            </button>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <button onclick="confirmDelete(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')"
                                                class="text-red-600 hover:text-red-900 transition-colors">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">No users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add User Modal -->
            <div id="addUserModal" class="hidden fixed inset-0 bg-black/20 backdrop-blur-sm flex items-center justify-center z-50">
                <div class="bg-white w-[95%] max-w-md rounded-xl shadow-2xl p-6 relative">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-medium text-gray-900">Add New User</h2>
                        <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class='bx bx-x text-2xl'></i>
                        </button>
                    </div>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="add_user">
                        <input type="text" name="username" placeholder="Username" 
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-400 transition-colors" required>
                        <input type="password" name="password" placeholder="Password" 
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-400 transition-colors" required>
                        <select name="role" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-400 transition-colors" required>
                            <option value="">Select Role</option>
                            <option value="employee">Employee</option>
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                        <div class="flex gap-3 pt-4">
                            <button type="button" onclick="document.getElementById('addUserModal').classList.add('hidden')"
                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm font-medium transition-colors">Cancel</button>
                            <button type="submit" class="flex-1 bg-gray-900 hover:bg-gray-800 text-white py-2 rounded-lg text-sm font-medium transition-colors">Create User</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit User Modal -->
            <div id="editUserModal" class="hidden fixed inset-0 bg-black/20 backdrop-blur-sm flex items-center justify-center z-50">
                <div class="bg-white w-[95%] max-w-md rounded-xl shadow-2xl p-6 relative">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-medium text-gray-900">Edit User Role</h2>
                        <button type="button" onclick="document.getElementById('editUserModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class='bx bx-x text-2xl'></i>
                        </button>
                    </div>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="update_role">
                        <input type="hidden" name="user_id" id="editUserId">
                        <div class="text-sm text-gray-600 mb-4">
                            Editing user: <span id="editUsername" class="font-semibold"></span>
                        </div>
                        <select name="new_role" id="editUserRole" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-gray-400 transition-colors" required>
                            <option value="employee">Employee</option>
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                        <div class="flex gap-3 pt-4">
                            <button type="button" onclick="document.getElementById('editUserModal').classList.add('hidden')"
                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm font-medium transition-colors">Cancel</button>
                            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg text-sm font-medium transition-colors">Update Role</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div id="deleteModal" class="hidden fixed inset-0 bg-black/20 backdrop-blur-sm flex items-center justify-center z-50">
                <div class="bg-white w-[95%] max-w-md rounded-xl shadow-2xl p-6 relative">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-medium text-gray-900">Confirm Delete</h2>
                        <button type="button" onclick="document.getElementById('deleteModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class='bx bx-x text-2xl'></i>
                        </button>
                    </div>
                    <div class="mb-6">
                        <p class="text-gray-600">Are you sure you want to delete user <span id="deleteUsername" class="font-semibold"></span>?</p>
                        <p class="text-sm text-red-600 mt-2">This action cannot be undone.</p>
                    </div>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="user_id" id="deleteUserId">
                        <div class="flex gap-3">
                            <button type="button" onclick="document.getElementById('deleteModal').classList.add('hidden')"
                                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 rounded-lg text-sm font-medium transition-colors">Cancel</button>
                            <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-sm font-medium transition-colors">Delete User</button>
                        </div>
                    </form>
                </div>
            </div>

        </main>
    </section>

    <script>
        function openEditModal(userId, username, role) {
            document.getElementById('editUserId').value = userId;
            document.getElementById('editUsername').textContent = username;
            document.getElementById('editUserRole').value = role;
            document.getElementById('editUserModal').classList.remove('hidden');
        }

        function confirmDelete(userId, username) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteUsername').textContent = username;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
    </script>

    <script src="../JS/script.js"></script>
</body>
</html>
