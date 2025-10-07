<?php
/**
 * Common Functions for TNVS System
 * Include this file at the top of your pages with: include 'partials/functions.php';
 */

/**
 * Function to get total count from any table.
 * Escapes the table name to prevent SQL Injection for the table identifier.
 */
function getTotalCount($conn, $table) {
    // Sanitize the table name
    $table = mysqli_real_escape_string($conn, $table);
    $sql = "SELECT COUNT(*) as total FROM `$table`";
    $result = $conn->query($sql);
    $data = $result ? $result->fetch_assoc() : null;
    return $data ? $data['total'] : 0;
}

/**
 * Function to get the most recent case records.
 * Fetches the latest entries from the 'case_records' table.
 */
function getRecentCaseRecords($conn, $limit = 5) {
    $sql = "SELECT id, title, complainant, respondent, status, created_at
            FROM case_records
            ORDER BY created_at DESC
            LIMIT $limit";
    
    $result = $conn->query($sql);
    $records = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
    }
    return $records;
}

/**
 * Function to get items that need attention/approval from different modules.
 * Combines pending reservations, new facility approvals, and visitor pre-registrations.
 */
function getPendingItems($conn) {
    $pendingItems = [];

    // 1. Pending Facility Reservations
    $sql_res = "SELECT 'Reservation' as type, reserved_by as name, created_at, 'Approval_Rejection_Requests.php' as link
                FROM reservations WHERE status = 'Pending' ORDER BY created_at DESC LIMIT 3";
    $result_res = $conn->query($sql_res);
    if ($result_res && $result_res->num_rows > 0) {
        while ($row = $result_res->fetch_assoc()) {
            $pendingItems[] = $row;
        }
    }
    
    // 2. Pending New Facility Approvals
    $sql_fac = "SELECT 'New Facility' as type, name, created_at, 'Reserve_Room.php' as link
                FROM facilities WHERE status = 'Pending' ORDER BY created_at DESC LIMIT 3";
    $result_fac = $conn->query($sql_fac);
    if ($result_fac && $result_fac->num_rows > 0) {
        while ($row = $result_fac->fetch_assoc()) {
            $pendingItems[] = $row;
        }
    }

    // 3. Pending Visitor Pre-Registrations
    $sql_vis = "SELECT 'Visitor Pre-Reg' as type, name, created_at, 'Visitor_Pre_Registration.php' as link
                FROM visitors WHERE request_status = 'pending' ORDER BY created_at DESC LIMIT 3";
    $result_vis = $conn->query($sql_vis);
    if ($result_vis && $result_vis->num_rows > 0) {
        while ($row = $result_vis->fetch_assoc()) {
            $pendingItems[] = $row;
        }
    }
    
    // Sort all pending items by created_at (most recent first)
    usort($pendingItems, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    // Limit the total list to 5 items for the dashboard
    return array_slice($pendingItems, 0, 5);     
}

/**
 * Helper function for status classes (for table/list status badges)
 */
function getStatusClass($status) {
    switch (strtolower($status)) {
        case 'approved':
        case 'resolved':
            return 'completed';     // Typically green/success color
        case 'rejected':
        case 'denied':
        case 'under maintenance':
            return 'process';      // Typically red/caution color
        case 'pending':
        case 'open':
        case 'checked-in':
            return 'pending';      // Typically neutral/pending color
        default:
            return 'pending';
    }
}
?>
