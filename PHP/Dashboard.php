<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

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
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Dashboard</h1>
                    <ul class="breadcrumb">
                    </ul>
                </div>
            </div>

            <ul class="box-info">
                <li>
                    <i class='bx bxs-file-archive' ></i>
                    <span class="text">
                        <h3><?= $totalDocuments ?></h3>
                        <p>Documents</p>
                    </span>
                </li>
                <li>
                    <i class='bx bxs-group' ></i>
                    <span class="text">
                        <h3><?= $totalVisitors ?></h3>
                        <p>Visitors</p>
                    </span>
                </li>
                <li>
                    <i class='bx bxs-briefcase-alt-2' ></i>
                    <span class="text">
                        <h3><?= $totalCaseRecords ?></h3>
                        <p>Case Records</p>
                    </span>
                </li>
                 <li>
                    <i class='bx bxs-store-alt' ></i>
                    <span class="text">
                        <h3><?= $totalReservations ?></h3>
                        <p>Total Reservations</p>
                    </span>
                </li>
            </ul>

            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3>Recent Case Records</h3> 
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Complainant</th>
                                <th>Respondent</th>
                                <th>Status</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentCaseRecords)): ?> <tr>
                                <td colspan="5" style="text-align: center;">No recent case records found.</td> </tr>
                            <?php else: ?>
                                <?php foreach ($recentCaseRecords as $record): ?> <tr>
                                    <td>
                                        <p><?= htmlspecialchars($record['title']) ?></p>
                                    </td>
                                    <td>
                                        <p><?= htmlspecialchars($record['complainant']) ?></p>
                                    </td>
                                    <td>
                                        <p><?= htmlspecialchars($record['respondent']) ?></p>
                                    </td>
                                    <td>
                                        <span class="status <?= getStatusClass($record['status']) ?>"><?= htmlspecialchars($record['status']) ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($record['created_at'])): ?>
                                            <?= date('M j, Y h:i A', strtotime($record['created_at'])) ?>
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

                <div class="todo">
                    <div class="head">
                        <h3>Pending Approvals (To Do)</h3>
                    </div>
                    <ul class="todo-list">
                        <?php if (empty($pendingApprovals)): ?>
                            <li class="completed" style="justify-content: center; border-left: 10px solid var(--blue);">
                                <p style="font-weight: bold;">🎉 Nothing needs approval!</p>
                                <i class='bx bx-check-circle' ></i>
                            </li>
                        <?php else: ?>
                            <?php foreach ($pendingApprovals as $item): 
                                $item_class = 'not-completed';    
                                $item_link = !empty($item['link']) ? $item['link'] : '#';
                                // Display the formatted created_at timestamp
                                $date_time = date('M j, Y h:i A', strtotime($item['created_at']));
                            ?>
                            <li class="<?= $item_class ?>">
                                <a href="../PHP/<?= $item_link ?>" style="flex-grow: 1; text-decoration: none; color: inherit;">
                                    <p>
                                        <strong><?= htmlspecialchars($item['type']) ?></strong>: Added on <?= $date_time ?>
                                    </p>
                                </a>
                                <i class='bx bx-right-arrow-alt' title="Go to Approval Page"></i>
                            </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </main>
    </section>
    
    <script src="../JS/script.js"></script>
</body>
</html>