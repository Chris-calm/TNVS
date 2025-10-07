<?php
// Sidebar partial - Include this in your pages with: include 'partials/sidebar.php';
?>
<section id="sidebar">
    <a href="" class="brand">
        <img src="../PICTURES/TONVS_Logo_Transparent.png" alt="Trail Ad Corporation Logo" class="brand-logo" style="width: 68px; height: 68px;">
        <span class="text" style="font-size: 14px; font-weight: 600;">Transport Network Vehicle Service</span>
    </a>

    <ul class="side-menu top">
        <li class="active">
            <a href="../PHP/Dashboard.php">
                <i class='bx bxs-dashboard'></i>
                <span class="text">Dashboard</span>
            </a>
        </li>
        <li class="dropdown">
            <a href="#" class="dropdown-toggle">
                <i class='bx bxs-store-alt'></i>
                <span class="text">Facilities Reservation</span>
                <i class='bx bx-chevron-down arrow'></i>
            </a>
            <ul class="dropdown-menu">
                <li><a href="../PHP/Reserve_Room.php"><span class="text">Reserve Room</span></a></li>
                <li><a href="../PHP/Approval_Rejection_Requests.php"><span class="text">Approval/Rejection Request</span></a></li>
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
            </ul>
        </li>

        <li class="side-menu-top">
            <a href="../PHP/Statistics.php" class="dropdown-toggle">
                <i class='bx bxs-circle-three-quarter'></i>
                <span class="text">Statistics</span>
            </a>
        </li>
    </ul>
    <ul class="side-menu">
        <li>
            <a href="../PHP/logout.php" class="logout">
                <i class='bx bxs-log-out-circle' ></i>
                <span class="text">Logout</span>
            </a>
        </li>
    </ul>
</section>
