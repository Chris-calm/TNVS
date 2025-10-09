<?php
// Initialize RBAC and check page access
require_once 'rbac_middleware.php';
RBACMiddleware::checkPageAccess();

include 'db_connect.php';
include 'partials/functions.php';

// Fetch pending approvals for notifications
$pendingApprovals = getPendingItems($conn);

// --- Initialization and Mode Handling ---
$currentYear = date('Y');
$currentMonth = date('m');
// Determine the report mode and the specific period
$reportMode = isset($_GET['mode']) ? $_GET['mode'] : 'year';

$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : $currentYear;
$selectedMonth = isset($_GET['month']) ? intval($_GET['month']) : $currentMonth;

// Ensure selectedYear is a valid 4-digit number (basic validation)
if (strlen((string)$selectedYear) !== 4) {
    $selectedYear = $currentYear;
}

// --- Utility Functions ---

// Function to get monthly count for a SPECIFIC year
function getMonthlyCount($conn, $table, $dateColumn, $year) {
    $counts = array_fill(0, 12, 0); // Jan-Dec
    // Query uses the passed $year variable
    $sql = "SELECT MONTH($dateColumn) as month, COUNT(*) as total 
            FROM $table 
            WHERE YEAR($dateColumn) = $year
            GROUP BY MONTH($dateColumn)";
    $result = $conn->query($sql);
    if($result) {
        while($row = $result->fetch_assoc()){
            // PHP months are 1-12, array indices are 0-11
            $counts[$row['month'] - 1] = $row['total']; 
        }
    }
    return $counts;
}

// Function to get daily count for a SPECIFIC month and year
function getDailyCount($conn, $table, $dateColumn, $year, $month) {
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $counts = array_fill(0, $daysInMonth, 0); // 1st to 31st (or less)
    
    // Query uses the passed $year and $month variables
    $sql = "SELECT DAY($dateColumn) as day, COUNT(*) as total 
            FROM $table 
            WHERE YEAR($dateColumn) = $year AND MONTH($dateColumn) = $month
            GROUP BY DAY($dateColumn)";
    $result = $conn->query($sql);
    if($result) {
        while($row = $result->fetch_assoc()){
            // Day 1 to 31, array indices 0 to 30
            $counts[$row['day'] - 1] = $row['total'];
        }
    }
    return $counts;
}

// --- Data Fetching Logic ---

$labels = [];
$documentCounts = [];
$visitorCounts = [];
$contractCounts = [];
$caseCounts = [];

if ($reportMode === 'month') {
    // Daily Report for a specific month/year
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);
    for ($i = 1; $i <= $daysInMonth; $i++) {
        $labels[] = "Day " . $i;
    }
    
    $documentCounts = getDailyCount($conn, 'documents', 'uploaded_at', $selectedYear, $selectedMonth);
    $visitorCounts = getDailyCount($conn, 'visitors', 'checkin', $selectedYear, $selectedMonth);
    $contractCounts = getDailyCount($conn, 'contracts', 'start_date', $selectedYear, $selectedMonth);
    $caseCounts = getDailyCount($conn, 'case_records', 'created_at', $selectedYear, $selectedMonth);
    
    $title = "Daily Statistics for " . date("F Y", mktime(0, 0, 0, $selectedMonth, 1, $selectedYear));
    
} else {
    // Annual Report (Default)
    $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    $documentCounts = getMonthlyCount($conn, 'documents', 'uploaded_at', $selectedYear);
    $visitorCounts = getMonthlyCount($conn, 'visitors', 'checkin', $selectedYear);
    $contractCounts = getMonthlyCount($conn, 'contracts', 'start_date', $selectedYear);
    $caseCounts = getMonthlyCount($conn, 'case_records', 'created_at', $selectedYear);
    
    $title = "Monthly Statistics for the Year " . $selectedYear;
}

// Convert PHP arrays to JSON for use in JavaScript
$jsonLabels = json_encode($labels);
$jsonDocuments = json_encode($documentCounts);
$jsonVisitors = json_encode($visitorCounts);
$jsonContracts = json_encode($contractCounts);
$jsonCases = json_encode($caseCounts);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <title>Statistics | TNVS Dashboard</title>
    
    <?php include 'partials/styles.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
    #content main {
        background-color: transparent; /* Ensures main background is correct */
    }
    
    /* --- Enhanced Print Styles --- */
    @media print {
        body * {
            visibility: hidden;
        }
        
        .printable, .printable * {
            visibility: visible;
        }
        
        .printable {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            max-width: 100% !important; /* Override Tailwind's max-w-7xl */
            margin: 0 !important;
            padding: 25px !important;
            font-size: 12pt;
        }
        
        .no-print {
            display: none !important;
        }
        
        /* General Style Resets for Print */
        .bg-gray-100, .bg-white {
            background: white !important;
            color: #000 !important;
        }
        
        .shadow-lg, .shadow {
            box-shadow: none !important;
            border: 1px solid #ccc !important;
        }
        
        .rounded-xl, .rounded-lg {
            border-radius: 0 !important;
        }
        
        .text-gray-800, .text-gray-900, .text-gray-700 {
            color: #000 !important;
        }
        
        .text-gray-600, .text-gray-500 {
            color: #333 !important;
        }
        
        /* Header and Chart Styling for Print */
        .print-header h1 {
            font-size: 20pt !important;
        }
        .print-header p {
            font-size: 14pt !important;
        }

        .chart-container {
            page-break-inside: avoid !important;
            padding: 10px !important;
            margin-bottom: 30px !important;
        }

        .chart-print-image {
            display: block !important;
            width: 100% !important;
            max-width: 900px !important; /* Limit max size on paper */
            height: 450px !important; /* Adjusted height for better fit */
            object-fit: contain !important;
            margin: 20px auto !important;
            page-break-inside: avoid !important;
        }
        
        /* Counter Grid Styling for Print */
        .print-counters-grid {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important; /* Force 2 columns */
            gap: 25px !important;
            margin-top: 25px !important;
        }
        
        .print-counters-grid > div {
            padding: 15px !important;
        }
        
        .print-counters-grid p.text-2xl {
            font-size: 2.5rem !important; /* Make counter numbers much larger */
            line-height: 1 !important;
        }
        
        /* Print Header and Date Visibility */
        .print-header, .print-date {
            display: block !important;
        }
    }
    
    .print-header {
        display: none;
        text-align: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #000;
        padding-bottom: 15px;
    }
    
    .print-date {
        display: none;
        text-align: right;
        font-size: 12px;
        margin-bottom: 20px;
    }
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    
    <?php include 'partials/sidebar.php'; ?>

    <section id="content">
        <?php include 'partials/header.php'; ?>

        <main>
            <div class="max-w-7xl mx-auto p-6 printable">
                <div class="print-header">
                    <h1 style="font-size: 24px; font-weight: bold; margin: 0;">TNVS System Analytics Report</h1>
                    <p style="margin: 5px 0 0 0;"><?= htmlspecialchars($title) ?></p>
                </div>
                
                <div class="print-date">
                    Generated on: <?= date('F j, Y \a\t g:i A') ?>
                </div>
                
                <header class="mb-8 no-print">
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">System Analytics</h1>
                    <p class="text-gray-600"><?= htmlspecialchars($title) ?></p>
                </header>

                <div class="flex justify-between items-center mb-6 no-print">
                    <form method="GET" class="flex items-center space-x-3">
                        <label for="mode" class="text-sm font-medium">View Mode:</label>
                        <select id="mode" name="mode" class="border rounded-md px-3 py-2 text-sm" onchange="this.form.submit()">
                            <option value="year" <?= $reportMode === 'year' ? 'selected' : '' ?>>Yearly (Monthly Data)</option>
                            <option value="month" <?= $reportMode === 'month' ? 'selected' : '' ?>>Monthly (Daily Data)</option>
                        </select>
                        
                        <label for="year" class="text-sm font-medium">Year:</label>
                        <select id="year" name="year" class="border rounded-md px-3 py-2 text-sm" onchange="this.form.submit()">
                            <?php for ($y = $currentYear; $y >= $currentYear - 5; $y--): ?>
                                <option value="<?= $y ?>" <?= $selectedYear == $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>

                        <?php if ($reportMode === 'month'): ?>
                            <label for="month" class="text-sm font-medium">Month:</label>
                            <select id="month" name="month" class="border rounded-md px-3 py-2 text-sm" onchange="this.form.submit()">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= $selectedMonth == $m ? 'selected' : '' ?>>
                                        <?= date('F', mktime(0, 0, 0, $m, 10)) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        <?php endif; ?>
                    </form>
                    
                    <button id="printBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center space-x-2">
                        <i class='bx bx-printer'></i>
                        <span>Print Report</span>
                    </button>
                </div>


                <div class="bg-white p-6 rounded-xl shadow-lg chart-container">
                    <h2 class="text-xl font-semibold mb-4 text-gray-700">Documents, Visitors, Contracts, & Cases Trend</h2>
                    <div class="relative h-[400px]" id="chartWrapper">
                        <canvas id="systemTrendChart"></canvas>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 print-counters-grid">
                    <div class="bg-white p-4 rounded-xl shadow border-l-4 border-blue-500">
                        <p class="text-sm font-medium text-gray-500">Total Documents</p>
                        <p class="text-2xl font-bold text-gray-900"><?= array_sum($documentCounts) ?></p>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow border-l-4 border-yellow-500">
                        <p class="text-sm font-medium text-gray-500">Total Visitors</p>
                        <p class="text-2xl font-bold text-gray-900"><?= array_sum($visitorCounts) ?></p>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow border-l-4 border-green-500">
                        <p class="text-sm font-medium text-gray-500">Total Contracts</p>
                        <p class="text-2xl font-bold text-gray-900"><?= array_sum($contractCounts) ?></p>
                    </div>
                    <div class="bg-white p-4 rounded-xl shadow border-l-4 border-red-500">
                        <p class="text-sm font-medium text-gray-500">Total Cases</p>
                        <p class="text-2xl font-bold text-gray-900"><?= array_sum($caseCounts) ?></p>
                    </div>
                </div>

            </div>
            <script>
                // PHP data variables converted to JavaScript
                const labels = <?= $jsonLabels ?>;
                const documents = <?= $jsonDocuments ?>;
                const visitors = <?= $jsonVisitors ?>;
                const contracts = <?= $jsonContracts ?>;
                const cases = <?= $jsonCases ?>;

                const ctx = document.getElementById('systemTrendChart').getContext('2d');
                
                const systemTrendChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            // Documents Dataset (blue)
                            { 
                                label:'Documents', 
                                data: documents, 
                                borderColor:'rgba(59,130,246,1)', 
                                backgroundColor:'rgba(59,130,246,0.1)', 
                                fill:true, 
                                tension: 0.3 
                            },
                            // Visitors Dataset (yellow)
                            { 
                                label:'Visitors', 
                                data: visitors, 
                                borderColor:'rgba(234,179,8,1)', 
                                backgroundColor:'rgba(234,179,8,0.1)', 
                                fill:false, 
                                tension: 0.3 
                            },
                            // Contracts Dataset (green)
                            { 
                                label:'Contracts', 
                                data: contracts, 
                                borderColor:'rgba(16,185,129,1)', 
                                backgroundColor:'rgba(16,185,129,0.1)', 
                                fill:false, 
                                tension: 0.3 
                            },
                            // Cases Dataset (red)
                            { 
                                label:'Cases', 
                                data: cases, 
                                borderColor:'rgba(239,68,68,1)', 
                                backgroundColor:'rgba(239,68,68,0.1)', 
                                fill:false, 
                                tension: 0.3 
                            }
                        ]
                    },
                    options: { 
                        responsive:true, 
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
                
                // Enhanced Print functionality for full chart printing
                document.getElementById('printBtn').addEventListener('click', function() {
                    const canvas = document.getElementById('systemTrendChart');
                    const chartWrapper = document.getElementById('chartWrapper');
                    
                    // Check if a print image already exists to avoid duplication
                    let existingImg = chartWrapper.querySelector('.chart-print-image');
                    if (existingImg) {
                        chartWrapper.removeChild(existingImg);
                    }
                    
                    // Create high-resolution chart image from canvas
                    const chartImage = canvas.toDataURL('image/png', 1.0);
                    
                    // Create a temporary image element for printing
                    const img = document.createElement('img');
                    img.src = chartImage;
                    img.className = 'chart-print-image'; // Class is styled in the main <style> tag
                    
                    // Hide the canvas and insert the image for printing
                    canvas.style.display = 'none';
                    chartWrapper.appendChild(img);
                    
                    // Trigger the browser's print dialog
                    window.print();
                    
                    // Use a timeout to clean up after the print dialog closes
                    setTimeout(() => {
                        canvas.style.display = 'block';
                        // Check if the image is still a child before trying to remove it
                        if (img.parentNode === chartWrapper) {
                            chartWrapper.removeChild(img);
                        }
                    }, 1000);
                });
            </script>
        </main>
    </section>

    <script src="../JS/script.js"></script>
</body>
</html>