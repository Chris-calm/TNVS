<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

include 'db_connect.php';
include 'partials/functions.php';

// Fetch pending approvals for notifications
$pendingApprovals = getPendingItems($conn);
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
                
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Profile Settings -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-xl font-semibold mb-4 flex items-center">
                            <i class='bx bx-user mr-2'></i>
                            Profile Settings
                        </h2>
                        <form class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                <input type="text" value="<?= htmlspecialchars($_SESSION['username']) ?>" class="w-full border rounded-lg px-3 py-2" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                <input type="text" value="<?= str_replace('_', ' ', htmlspecialchars($_SESSION['role'])) ?>" class="w-full border rounded-lg px-3 py-2" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" placeholder="user@example.com" class="w-full border rounded-lg px-3 py-2">
                            </div>
                            <button type="button" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
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
                        <form class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                <input type="password" class="w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <input type="password" class="w-full border rounded-lg px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                <input type="password" class="w-full border rounded-lg px-3 py-2">
                            </div>
                            <button type="button" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
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
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Email Notifications</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" checked>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Push Notifications</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" checked>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">SMS Notifications</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- System Settings -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-xl font-semibold mb-4 flex items-center">
                            <i class='bx bx-cog mr-2'></i>
                            System Settings
                        </h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Theme</label>
                                <select class="w-full border rounded-lg px-3 py-2">
                                    <option>Light Mode</option>
                                    <option>Dark Mode</option>
                                    <option>Auto</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Language</label>
                                <select class="w-full border rounded-lg px-3 py-2">
                                    <option>English</option>
                                    <option>Filipino</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                                <select class="w-full border rounded-lg px-3 py-2">
                                    <option>Asia/Manila</option>
                                    <option>UTC</option>
                                </select>
                            </div>
                            <button type="button" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                                Save Settings
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="bg-red-50 border border-red-200 rounded-xl p-6 mt-6">
                    <h2 class="text-xl font-semibold text-red-800 mb-4 flex items-center">
                        <i class='bx bx-error mr-2'></i>
                        Danger Zone
                    </h2>
                    <p class="text-red-700 mb-4">These actions are irreversible. Please be careful.</p>
                    <div class="flex gap-4">
                        <button type="button" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                            Clear All Data
                        </button>
                        <button type="button" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                            Export Data
                        </button>
                    </div>
                </div>
            </div>
            <script src="../JS/script.js"></script>
        </main>
    </section>
</body>
</html>
