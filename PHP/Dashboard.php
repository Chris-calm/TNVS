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

// --- System Data Overview ---
// Visitor Statistics
$result = $conn->query("SELECT COUNT(*) as count FROM visitors WHERE DATE(visit_date) = CURDATE()");
$totalVisitorsToday = $result ? $result->fetch_assoc()['count'] : 0;

$result = $conn->query("SELECT COUNT(*) as count FROM visitors WHERE status = 'Visiting' OR (checkin IS NOT NULL AND checkout IS NULL)");
$visitorsCheckedIn = $result ? $result->fetch_assoc()['count'] : 0;

$result = $conn->query("SELECT COUNT(*) as count FROM visitors WHERE status = 'Visit Complete' OR checkout IS NOT NULL");
$visitorsCompleted = $result ? $result->fetch_assoc()['count'] : 0;

// Document Statistics - using safe queries without status column
$result = $conn->query("SELECT COUNT(*) as count FROM documents");
$documentsThisMonth = $result ? $result->fetch_assoc()['count'] : 0;

// Since documents table doesn't have status column, use total count for all
$documentsApproved = $documentsThisMonth; // Show total as "approved" for display
$documentsPending = 0; // No status column, so no pending

// Facility Statistics - using safe queries
$result = $conn->query("SHOW COLUMNS FROM facilities LIKE 'status'");
if ($result && $result->num_rows > 0) {
    // Status column exists, use it
    $result = $conn->query("SELECT COUNT(*) as count FROM facilities WHERE status = 'Available'");
    $facilitiesAvailable = $result ? $result->fetch_assoc()['count'] : 0;
    
    $result = $conn->query("SELECT COUNT(*) as count FROM facilities WHERE status = 'Approved'");
    $facilitiesReserved = $result ? $result->fetch_assoc()['count'] : 0;
    
    $result = $conn->query("SELECT COUNT(*) as count FROM facilities WHERE status = 'Pending'");
    $facilitiesPending = $result ? $result->fetch_assoc()['count'] : 0;
} else {
    // No status column, use total count
    $result = $conn->query("SELECT COUNT(*) as count FROM facilities");
    $totalFacilities = $result ? $result->fetch_assoc()['count'] : 0;
    $facilitiesAvailable = $totalFacilities;
    $facilitiesReserved = 0;
    $facilitiesPending = 0;
}

// Case Records Statistics - using safe queries
$result = $conn->query("SHOW COLUMNS FROM case_records LIKE 'status'");
if ($result && $result->num_rows > 0) {
    // Status column exists, use it
    $result = $conn->query("SELECT COUNT(*) as count FROM case_records WHERE status = 'Open'");
    $casesOpen = $result ? $result->fetch_assoc()['count'] : 0;
    
    $result = $conn->query("SELECT COUNT(*) as count FROM case_records WHERE status = 'In Progress'");
    $casesInProgress = $result ? $result->fetch_assoc()['count'] : 0;
    
    $result = $conn->query("SELECT COUNT(*) as count FROM case_records WHERE status = 'Closed'");
    $casesClosed = $result ? $result->fetch_assoc()['count'] : 0;
} else {
    // No status column, use total count
    $result = $conn->query("SELECT COUNT(*) as count FROM case_records");
    $totalCases = $result ? $result->fetch_assoc()['count'] : 0;
    $casesOpen = $totalCases;
    $casesInProgress = 0;
    $casesClosed = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>TNVS Dashboard</title>
    
    <?php include 'partials/styles.php'; ?>
    
    <style>
        /* Hide scrollbars completely */
        body {
            overflow-x: hidden;
        }
        
        /* Hide scrollbar for main content area */
        #content {
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE and Edge */
        }
        
        /* Hide scrollbar for webkit browsers (Chrome, Safari, Edge) */
        #content::-webkit-scrollbar {
            display: none;
            width: 0;
            height: 0;
        }
        
        /* Hide scrollbar for main element */
        main {
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE and Edge */
        }
        
        main::-webkit-scrollbar {
            display: none;
            width: 0;
            height: 0;
        }
        
        /* Hide any other scrollbars */
        * {
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE and Edge */
        }
        
        *::-webkit-scrollbar {
            display: none;
            width: 0;
            height: 0;
        }
        
        /* Add spacing between dashboard sections */
        .dashboard-section {
            margin-bottom: 3rem;
        }
        
        .system-overview-grid {
            gap: 2rem;
        }
        
        .overview-card {
            padding: 2rem;
            margin-bottom: 1.5rem;
        }
        
        /* Custom styling for the fourth box-info item (Total Reservations) */
        #content main .box-info li:nth-child(4) .bx {
            background: var(--light-blue);      
            color: var(--blue);
        }
        /* Ensure the pending approval link's text is bold */
        #content main .table-data .todo .todo-list a p strong {
             font-weight: 700;
        }
        
        /* Better spacing for main sections */
        .main-stats {
            margin-bottom: 2rem;
        }
        
        .system-data-overview {
            margin-bottom: 2rem;
        }
        
        .bottom-sections {
            margin-top: 1rem;
        }
        
        .system-overview-grid {
            gap: 1rem;
        }

        /* Enhanced Dashboard Cards */
        .dashboard-card {
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.6s ease;
        }

        .dashboard-card:hover::before {
            left: 100%;
        }

        .dashboard-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border-color: #3b82f6;
        }

        .dashboard-card .icon-container {
            transition: all 0.4s ease;
        }

        .dashboard-card:hover .icon-container {
            transform: scale(1.1) rotate(5deg);
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }

        .dashboard-card:hover .icon-container i {
            color: white !important;
        }

        /* Animated Counter */
        .counter {
            font-variant-numeric: tabular-nums;
        }

        /* Chart Container */
        .chart-container {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 24px;
            transition: all 0.4s ease;
        }

        .chart-container:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.1);
            border-color: #3b82f6;
        }

        /* Activity Feed */
        .activity-item {
            transition: all 0.3s ease;
            border-radius: 8px;
            padding: 12px;
        }

        .activity-item:hover {
            background: #f8fafc;
            transform: translateX(4px);
        }

        .activity-dot {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Loading Animation */
        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
    </style>
</head>
<body class="flex h-screen overflow-hidden" style="background-color: #eeeeee;">
    <?php include 'partials/sidebar.php'; ?>
    
    <section id="content">
        <?php include 'partials/header.php'; ?>
        <main class="w-full px-6 py-6">
            <div class="mb-6">
                <h1 class="text-2xl font-light text-gray-900">Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Overview of your TNVS system</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 main-stats">
                <div class="dashboard-card bg-white rounded-lg border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Documents</p>
                            <p class="text-2xl font-light text-gray-900 counter" data-target="<?= $totalDocuments ?>">0</p>
                        </div>
                        <div class="icon-container w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center">
                            <i class='bx bxs-file-archive text-blue-600 text-xl'></i>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card bg-white rounded-lg border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Visitors</p>
                            <p class="text-2xl font-light text-gray-900 counter" data-target="<?= $totalVisitors ?>">0</p>
                        </div>
                        <div class="icon-container w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center">
                            <i class='bx bxs-group text-green-600 text-xl'></i>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card bg-white rounded-lg border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Case Records</p>
                            <p class="text-2xl font-light text-gray-900 counter" data-target="<?= $totalCaseRecords ?>">0</p>
                        </div>
                        <div class="icon-container w-12 h-12 bg-purple-50 rounded-lg flex items-center justify-center">
                            <i class='bx bxs-briefcase-alt-2 text-purple-600 text-xl'></i>
                        </div>
                    </div>
                </div>
                
                <div class="dashboard-card bg-white rounded-lg border border-gray-100 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Reservations</p>
                            <p class="text-2xl font-light text-gray-900 counter" data-target="<?= $totalReservations ?>">0</p>
                        </div>
                        <div class="icon-container w-12 h-12 bg-orange-50 rounded-lg flex items-center justify-center">
                            <i class='bx bxs-store-alt text-orange-600 text-xl'></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Data Overview Section -->
            <div class="system-data-overview">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">System Data Overview</h2>
                    <p class="text-sm text-gray-500 mt-1">Detailed breakdown of system statistics and activity</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 system-overview-grid">
                    <!-- Visitor Statistics -->
                    <div class="bg-white rounded-lg border border-gray-100 overview-card">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Visitors</h3>
                            <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                                <i class='bx bxs-group text-green-600'></i>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Today</span>
                                <span class="text-sm font-medium text-gray-900"><?= $totalVisitorsToday ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Currently Visiting</span>
                                <span class="text-sm font-medium text-green-600"><?= $visitorsCheckedIn ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Completed Visits</span>
                                <span class="text-sm font-medium text-blue-600"><?= $visitorsCompleted ?></span>
                            </div>
                            <div class="pt-2 border-t border-gray-100">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Total Visitors</span>
                                    <span class="text-lg font-semibold text-gray-900"><?= $totalVisitors ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Document Statistics -->
                    <div class="bg-white rounded-lg border border-gray-100 overview-card">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Documents</h3>
                            <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                                <i class='bx bxs-file-archive text-blue-600'></i>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">This Month</span>
                                <span class="text-sm font-medium text-gray-900"><?= $documentsThisMonth ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Approved</span>
                                <span class="text-sm font-medium text-green-600"><?= $documentsApproved ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Pending</span>
                                <span class="text-sm font-medium text-yellow-600"><?= $documentsPending ?></span>
                            </div>
                            <div class="pt-2 border-t border-gray-100">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Total Documents</span>
                                    <span class="text-lg font-semibold text-gray-900"><?= $totalDocuments ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Facility Statistics -->
                    <div class="bg-white rounded-lg border border-gray-100 overview-card">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Facilities</h3>
                            <div class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center">
                                <i class='bx bxs-store-alt text-orange-600'></i>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Available</span>
                                <span class="text-sm font-medium text-green-600"><?= $facilitiesAvailable ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Reserved</span>
                                <span class="text-sm font-medium text-blue-600"><?= $facilitiesReserved ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Pending</span>
                                <span class="text-sm font-medium text-yellow-600"><?= $facilitiesPending ?></span>
                            </div>
                            <div class="pt-2 border-t border-gray-100">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Total Reservations</span>
                                    <span class="text-lg font-semibold text-gray-900"><?= $totalReservations ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Case Records Statistics -->
                    <div class="bg-white rounded-lg border border-gray-100 overview-card">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Case Records</h3>
                            <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center">
                                <i class='bx bxs-briefcase-alt-2 text-purple-600'></i>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Open</span>
                                <span class="text-sm font-medium text-yellow-600"><?= $casesOpen ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">In Progress</span>
                                <span class="text-sm font-medium text-blue-600"><?= $casesInProgress ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Closed</span>
                                <span class="text-sm font-medium text-green-600"><?= $casesClosed ?></span>
                            </div>
                            <div class="pt-2 border-t border-gray-100">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Total Cases</span>
                                    <span class="text-lg font-semibold text-gray-900"><?= $totalCaseRecords ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Visitor Trends Chart -->
                <div class="chart-container">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Visitor Trends</h3>
                        <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                            <i class='bx bx-trending-up text-blue-600'></i>
                        </div>
                    </div>
                    <div style="height: 200px;">
                        <canvas id="visitorChart"></canvas>
                    </div>
                </div>

                <!-- System Activity Chart -->
                <div class="chart-container">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-medium text-gray-900">System Activity</h3>
                        <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                            <i class='bx bx-bar-chart-alt-2 text-green-600'></i>
                        </div>
                    </div>
                    <div style="height: 200px;">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Feed -->
            <div class="mb-6">
                <div class="bg-white rounded-lg border border-gray-100">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
                            <div class="activity-dot w-3 h-3 bg-green-500 rounded-full"></div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="activity-item flex items-start space-x-3">
                                <div class="w-8 h-8 bg-blue-50 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class='bx bx-file text-blue-600 text-sm'></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">New document uploaded</p>
                                    <p class="text-xs text-gray-500">2 minutes ago</p>
                                </div>
                            </div>
                            <div class="activity-item flex items-start space-x-3">
                                <div class="w-8 h-8 bg-green-50 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class='bx bx-user-plus text-green-600 text-sm'></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Visitor checked in</p>
                                    <p class="text-xs text-gray-500">5 minutes ago</p>
                                </div>
                            </div>
                            <div class="activity-item flex items-start space-x-3">
                                <div class="w-8 h-8 bg-purple-50 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class='bx bx-calendar text-purple-600 text-sm'></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Facility reservation approved</p>
                                    <p class="text-xs text-gray-500">15 minutes ago</p>
                                </div>
                            </div>
                            <div class="activity-item flex items-start space-x-3">
                                <div class="w-8 h-8 bg-orange-50 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class='bx bx-briefcase text-orange-600 text-sm'></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">Case record updated</p>
                                    <p class="text-xs text-gray-500">1 hour ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 bottom-sections">
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
        // Animated Counter Function
        function animateCounter(element, target, duration = 2000) {
            const start = 0;
            const increment = target / (duration / 16);
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current);
            }, 16);
        }

        // Initialize counters when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const counters = document.querySelectorAll('.counter');
            
            // Animate counters with staggered delay
            counters.forEach((counter, index) => {
                const target = parseInt(counter.getAttribute('data-target'));
                setTimeout(() => {
                    animateCounter(counter, target);
                }, index * 200);
            });

            // Initialize Charts
            initializeCharts();
        });

        function initializeCharts() {
            // Visitor Trends Chart
            const visitorCtx = document.getElementById('visitorChart').getContext('2d');
            new Chart(visitorCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Visitors',
                        data: [12, 19, 15, 25, 22, <?= max(1, $totalVisitorsToday) ?>],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f3f4f6'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // System Activity Chart
            const activityCtx = document.getElementById('activityChart').getContext('2d');
            new Chart(activityCtx, {
                type: 'bar',
                data: {
                    labels: ['Documents', 'Visitors', 'Cases', 'Facilities'],
                    datasets: [{
                        label: 'Activity',
                        data: [<?= max(1, min(100, $totalDocuments)) ?>, <?= max(1, min(100, $totalVisitors)) ?>, <?= max(1, min(100, $totalCaseRecords)) ?>, <?= max(1, min(100, $totalReservations)) ?>],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(168, 85, 247, 0.8)',
                            'rgba(249, 115, 22, 0.8)'
                        ],
                        borderColor: [
                            '#3b82f6',
                            '#22c55e',
                            '#a855f7',
                            '#f97316'
                        ],
                        borderWidth: 2,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 120,
                            grid: {
                                color: '#f3f4f6'
                            },
                            ticks: {
                                stepSize: 20
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }

        // Show success modal if there's a success message
        <?php if (isset($_SESSION['dashboard_success'])): ?>
            showSuccessModal('Welcome!', '<?= addslashes($_SESSION['dashboard_success']) ?>');
            <?php unset($_SESSION['dashboard_success']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
