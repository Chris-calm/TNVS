<?php
include 'db_connect.php'; // Ensure this file correctly establishes $conn

// --- 1. HANDLE APPROVE / REJECT ACTIONS (POST Request) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['facility_id']) && isset($_POST['action'])) {
    $facility_id = intval($_POST['facility_id']);
    $action = $_POST['action'];

    if ($action === 'approve') {
        // ACTION: Approve - Set status to 'Approved'
        $stmt = $conn->prepare("UPDATE facilities SET status='Approved' WHERE facility_id=?");
        $stmt->bind_param("i", $facility_id);
    } elseif ($action === 'reject') {
        // ACTION: Reject - DELETE the facility record (as requested)
        $stmt = $conn->prepare("DELETE FROM facilities WHERE facility_id=?");
        $stmt->bind_param("i", $facility_id);
    } else {
        // Stop execution if action is invalid
        header("Location: Approval_Rejection_Requests.php");
        exit();
    }
    
    $stmt->execute();
    $stmt->close();

    // Redirect back to the approval requests page
    header("Location: Approval_Rejection_Requests.php");
    exit();
}

// --- 2. FETCH ONLY PENDING FACILITIES FOR DISPLAY on this page ---
$sql = "SELECT * FROM facilities WHERE status='Pending' ORDER BY created_at DESC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../CSS/index.css">
    <link rel="stylesheet" href="../CSS/crud.css">
    <title>TNVS Facility Approval Requests</title>
    <style>
    @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    .animate-fadeIn { animation: fadeIn 0.25s ease-out; }
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    <section id="sidebar">
        <a href="" class="brand">
            <img src="../PICTURES/Black and White Circular Art & Design Logo.png" alt="Trail Ad Corporation Logo" class="brand-logo">
            <span class="text">TNVS</span>
        </a>

        <ul class="side-menu top">
            <li><a href="../PHP/Dashboard.php"><i class='bx bxs-dashboard'></i><span class="text">Dashboard</span></a></li>
            <li class="dropdown active">
                <a href="#" class="dropdown-toggle">
                    <i class='bx bxs-store-alt'></i><span class="text">Facilities Reservation</span><i class='bx bx-chevron-down arrow'></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="../PHP/Reserve_Room.php"><span class="text">Reserve Room</span></a></li>
                    <li class="active"><a href="../PHP/Approval_Rejection_Requests.php"><span class="text">Approval/Rejection Request</span></a></li>
                    <li><a href="../PHP/Reservation_Calendar.php"><span class="text">Reservation Calendar</span></a></li>
                    <li><a href="../PHP/Facilities_Maintenance.php"><span class="text">Facilities Maintenance</span></a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle">
                    <i class='bx bxs-archive'></i>
                    <span class="text">Documents Management</span>
                    <i class='bx bx-chevron-down arrow'></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="../PHP/Upload_Document.php"><span class="text">Upload Document</span></a></li>
                    <li><a href="../PHP/Document_Access_Permissions.php"><span class="text">Document Access Permission</span></a></li>
                    <li><a href="../PHP/View_Records.php"><span class="text">View Records</span></a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle">
                    <i class='bx bxs-landmark'></i>
                    <span class="text">Legal Management</span>
                    <i class='bx bx-chevron-down arrow'></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="../PHP/Contracts.php"><span class="text">Contracts</span></a></li>
                    <li><a href="../PHP/Policies.php"><span class="text">Policies</span></a></li>
                    <li><a href="../PHP/Case_Records.php"><span class="text">Case Records</span></a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle">
                    <i class='bx bxs-universal-access'></i>
                    <span class="text">Visitor Management</span>
                    <i class='bx bx-chevron-down arrow'></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="../PHP/Visitor_Pre_Registration.php"><span class="text">Visitor Pre-Registration</span></a></li>
                    <li><a href="../PHP/Visitor_Logs.php"><span class="text">Visitor Logs</span></a></li>
                    <li><a href="../PHP/Pass_Requests.php"><span class="text">Pass Requests</span></a></li>
                    <li><a href="../PHP/Blacklist_Watchlist.php"><span class="text">Blacklist/Watchlist</span></a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle">
                    <i class='bx bxs-circle-three-quarter'></i>
                    <span class="text">Statistics</span>
                    <i class='bx bx-chevron-down arrow'></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="../PHP/Yearly_Reports.php"><span class="text">Yearly Reports</span></a></li>
                    <li><a href="../PHP/Monthly_Reports.php"><span class="text">Monthly Reports</span></a></li>
                    <li><a href="../PHP/Weekly_Reports.php"><span class="text">Weekly Reports</span></a></li>
                    <li><a href="../PHP/Daily_Reports.php"><span class="text">Daily Reports</span></a></li>
                </ul>
            </li>
        </ul>

        <ul class="side-menu">
            <li><a href="#"><i class='bx bxs-cog' ></i><span class="text">Settings</span></a></li>
            <li><a href="../PHP/index.php" class="logout"><i class='bx bxs-log-out-circle' ></i><span class="text">Logout</span></a></li>
        </ul>
    </section>

    <section id="content">
        <nav>
            <i class='bx bx-menu' ></i>
            <a href="#" class="nav-link">Categories</a>
            <form action="#">
                <div class="form-input">
                    <input type="search" placeholder="Search...">
                    <button type="submit" class="search-btn"><i class='bx bx-search' ></i></button>
                </div>
            </form>
            <a href="#" class="notification"><i class='bx bxs-bell' ></i><span class="num">8</span></a>
            <a href="#" class="profile"><img src="../PICTURES/Ser.jpg"></a>
        </nav>

        <main class="p-6">
            <h1 class="text-3xl font-semibold text-gray-800 mb-6">Pending Facility Approval Requests</h1>

            <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        
                        // FIX: Corrected the image path to point to the 'uploads' directory
                        $picturePath = !empty($row['picture']) ? "../uploads/" . htmlspecialchars($row['picture']) : "https://via.placeholder.com/300x200?text=No+Image";
                        
                        // Status class is set based on the current status
                        $statusClass = "bg-yellow-100 text-yellow-700 border border-yellow-300";

                        echo "
                        <div class='bg-white rounded-xl shadow-md hover:shadow-xl transition transform hover:-translate-y-1 overflow-hidden animate-fadeIn'>
                            <img src='{$picturePath}' alt='Facility' class='w-full h-48 object-cover'>
                            <div class='p-5'>
                                <h3 class='text-lg font-semibold text-gray-800'>{$row['name']}</h3>
                                <p class='text-sm text-gray-600 mt-1'>Capacity: {$row['capacity']}</p>
                                <p class='text-sm text-gray-600'>Location: {$row['location']}</p>
                                <p class='text-sm mt-2'><span class='px-3 py-1 rounded-full text-xs font-medium {$statusClass}'>{$row['status']}</span></p>
                                <p class='text-sm text-gray-600 mt-2'>Date: {$row['available_date']}</p>
                                <p class='text-sm text-gray-600'>Time: {$row['available_time']}</p>
                                
                                <div class='flex justify-between items-center mt-4 gap-2'>
                                    <form method='POST' onsubmit=\"return confirm('Approve facility: {$row['name']}?');\" class='w-1/2'>
                                        <input type='hidden' name='facility_id' value='{$row['facility_id']}'>
                                        <input type='hidden' name='action' value='approve'>
                                        <button type='submit' class='w-full bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition'>Approve</button>
                                    </form>
                                    
                                    <form method='POST' onsubmit=\"return confirm('Reject and permanently DELETE facility: {$row['name']}?');\" class='w-1/2'>
                                        <input type='hidden' name='facility_id' value='{$row['facility_id']}'>
                                        <input type='hidden' name='action' value='reject'>
                                        <button type='submit' class='w-full bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition'>Reject</button>
                                    </form>
                                </div>
                            </div>
                        </div>";
                    }
                } else {
                    echo "<p class='text-gray-500'>No pending facility requests at this time.</p>";
                }
                ?>
            </div>
        </main>
    </section>

    <script src="../JS/script.js"></script>
    <script src="../JS/modal.js"></script> 
</body>
</html>