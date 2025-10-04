<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $facility_id = $_POST['facility_id'];

    if (isset($_POST['approve'])) {
        $status = "Approved";
    } elseif (isset($_POST['reject'])) {
        $status = "Rejected";
    }

    $sql = "UPDATE facilities SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $facility_id);

    if ($stmt->execute()) {
        echo "<script>alert('Facility has been $status!'); window.location.href='Approval_Rejection_Requests.php';</script>";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>
