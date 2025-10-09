<?php
// Initialize RBAC and check page access
require_once 'rbac_middleware.php';
RBACMiddleware::checkPageAccess();

// Include database connection and common functions
include 'db_connect.php';
include 'partials/functions.php';
// --- Data Fetching ---
$totalDocuments = getTotalCount($conn, 'documents');
$totalVisitors = getTotalCount($conn, 'visitors');
$totalCaseRecords = getTotalCount($conn, 'case_records');

// Total Approved Facilities Count (as used for the 'Total Reservations' box)
$sql = "SELECT COUNT(*) as total FROM `facilities` WHERE status = 'Approved'";
$result = $conn->query($sql);
$totalReservations = $result ? $result->fetch_assoc()['total'] : 0;

// Fetch the recent case records
$recentCaseRecords = getRecentCaseRecords($conn); 
$pendingApprovals = getPendingItems($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>TNVS Dashboard</title>
    
    <?php include 'partials/styles.php'; ?>
    
    <style>
        /* Custom styling for the fourth box-info item (Total Reservations) */
        #content main .box-info li:nth-child(4) .bx {
            background: var(--light-blue);      
            color: var(--blue);
        }
        /* Ensure the pending approval link's text is bold */
        #content main .table-data .todo .todo-list a p strong {
             font-weight: 700;
        }
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    <?php include 'partials/sidebar.php'; ?>
    
    <section id="content">
        <?php include 'partials/header.php'; ?>
        <main class="max-w-7xl mx-auto px-4 py-8">
            <div class="mb-12">
                <h1 class="text-2xl font-light text-gray-900">Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Overview of your TNVS system</p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                <div class="bg-white rounded-lg border border-gray-100 p-6 hover:border-gray-200 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Documents</p>
                            <p class="text-2xl font-light text-gray-900"><?= $totalDocuments ?></p>
                        </div>
                        <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                            <i class='bx bxs-file-archive text-blue-600 text-xl'></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg border border-gray-100 p-6 hover:border-gray-200 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Visitors</p>
                            <p class="text-2xl font-light text-gray-900"><?= $totalVisitors ?></p>
                        </div>
                        <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                            <i class='bx bxs-group text-green-600 text-xl'></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg border border-gray-100 p-6 hover:border-gray-200 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Case Records</p>
                            <p class="text-2xl font-light text-gray-900"><?= $totalCaseRecords ?></p>
                        </div>
                        <div class="w-12 h-12 bg-purple-50 rounded-lg flex items-center justify-center">
                            <i class='bx bxs-briefcase-alt-2 text-purple-600 text-xl'></i>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg border border-gray-100 p-6 hover:border-gray-200 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Reservations</p>
                            <p class="text-2xl font-light text-gray-900"><?= $totalReservations ?></p>
                        </div>
                        <div class="w-12 h-12 bg-orange-50 rounded-lg flex items-center justify-center">
                            <i class='bx bxs-store-alt text-orange-600 text-xl'></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-8">
                <div class="bg-white rounded-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="text-lg font-medium text-gray-900">Recent Case Records</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Complainant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Respondent</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($recentCaseRecords)): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">No recent case records found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentCaseRecords as $record): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($record['title']) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?= htmlspecialchars($record['complainant']) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?= htmlspecialchars($record['respondent']) ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php
                                                     // Get the database status value
                                                    $status_db = $record['status'];
                                                    // Convert to lowercase for reliable comparison
                                                    $status_lower = strtolower($status_db); 

                                                    // Default text and class
                                                    $status_text = 'Unknown';
                                                    $status_class = 'bg-gray-100 text-gray-800'; 

                                                    if ($status_lower === 'open') {
                                                        $status_text = 'Open';
                                                        $status_class = 'bg-yellow-100 text-yellow-800';
                                                    } elseif ($status_lower === 'in progress') {
                                                        $status_text = 'In Progress';
                                                        $status_class = 'bg-blue-100 text-blue-800';
                                                    } elseif ($status_lower === 'closed') {
                                                        $status_text = 'Closed'; // Changed from 'Close' for better clarity/consistency
                                                        $status_class = 'bg-green-100 text-green-800';
                                                    }
                                                ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= htmlspecialchars($status_class) ?>">
                                                    <?= htmlspecialchars($status_text) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php if (!empty($record['created_at'])): ?>
                                                    <?= date('M j, Y', strtotime($record['created_at'])) ?>
                                                <?php else: ?>
                                                    N/A
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-lg border border-gray-100">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="text-lg font-medium text-gray-900">Pending Approvals</h3>
                    </div>
                    <div class="p-6">
                        <?php if (empty($pendingApprovals)): ?>
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class='bx bx-check-circle text-green-600 text-2xl'></i>
                                </div>
                                <p class="text-gray-500 font-medium">All caught up!</p>
                                <p class="text-sm text-gray-400 mt-1">No pending approvals</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-3">
                                <?php foreach ($pendingApprovals as $item): 
                                    $item_link = !empty($item['link']) ? $item['link'] : '#';
                                    $date_time = date('M j, Y', strtotime($item['created_at']));
                                ?>
                                    <a href="../PHP/<?= $item_link ?>" class="block p-4 rounded-lg border border-gray-100 hover:border-gray-200 hover:bg-gray-50 transition-colors group">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-gray-900"><?= htmlspecialchars($item['type']) ?></p>
                                                <p class="text-sm text-gray-500">Added on <?= $date_time ?></p>
                                            </div>
                                            <i class='bx bx-right-arrow-alt text-gray-400 group-hover:text-gray-600 transition-colors'></i>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </section>
    
    <?php include 'partials/success_modal.php'; ?>
    
    <script src="../JS/script.js"></script>
    <script>
        // Show success modal if there's a success message
        <?php if (isset($_SESSION['dashboard_success'])): ?>
            showSuccessModal('Welcome!', '<?= addslashes($_SESSION['dashboard_success']) ?>');
            <?php unset($_SESSION['dashboard_success']); ?>
        <?php endif; ?>
    </script>
</body>
</html>