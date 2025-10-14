<?php
// Check current facility statuses
include 'db_connect.php';

echo "<h1>Current Facility Status Check</h1>";

// Check all facilities by status
echo "<h2>All Facilities by Status:</h2>";
$status_sql = "SELECT status, COUNT(*) as count FROM facilities GROUP BY status";
$status_result = $conn->query($status_sql);

if ($status_result) {
    echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
    echo "<tr><th>Status</th><th>Count</th></tr>";
    while ($status_row = $status_result->fetch_assoc()) {
        echo "<tr><td>" . $status_row['status'] . "</td><td>" . $status_row['count'] . "</td></tr>";
    }
    echo "</table>";
}

// Show all facilities with their current status
echo "<h2>All Facilities Details:</h2>";
$all_sql = "SELECT facility_id, name, capacity, location, status, created_at FROM facilities ORDER BY created_at DESC";
$all_result = $conn->query($all_sql);

if ($all_result && $all_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Capacity</th><th>Location</th><th>Status</th><th>Created</th><th>Action</th></tr>";
    
    while ($row = $all_result->fetch_assoc()) {
        $statusColor = '';
        switch($row['status']) {
            case 'Pending': $statusColor = 'background: yellow;'; break;
            case 'Approved': $statusColor = 'background: lightgreen;'; break;
            case 'Rejected': $statusColor = 'background: lightcoral;'; break;
        }
        
        echo "<tr>";
        echo "<td>" . $row['facility_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . $row['capacity'] . "</td>";
        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
        echo "<td style='$statusColor'><strong>" . $row['status'] . "</strong></td>";
        echo "<td>" . $row['created_at'] . "</td>";
        
        // Add quick action to reset to pending if not already pending
        if ($row['status'] != 'Pending') {
            echo "<td>";
            echo "<form method='POST' style='display: inline;'>";
            echo "<input type='hidden' name='facility_id' value='" . $row['facility_id'] . "'>";
            echo "<input type='hidden' name='action' value='reset_to_pending'>";
            echo "<button type='submit' style='background: orange; color: white; padding: 2px 8px; border: none; border-radius: 3px;'>Reset to Pending</button>";
            echo "</form>";
            echo "</td>";
        } else {
            echo "<td>Already Pending</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No facilities found in database.";
}

// Handle reset to pending action
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'reset_to_pending') {
    $facility_id = intval($_POST['facility_id']);
    $update_sql = "UPDATE facilities SET status='Pending' WHERE facility_id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $facility_id);
    
    if ($stmt->execute()) {
        echo "<div style='background: lightgreen; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "✅ Facility ID $facility_id has been reset to Pending status!";
        echo "</div>";
        echo "<script>setTimeout(function(){ location.reload(); }, 1000);</script>";
    } else {
        echo "<div style='background: lightcoral; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "❌ Failed to update facility status.";
        echo "</div>";
    }
    $stmt->close();
}

$conn->close();
?>
