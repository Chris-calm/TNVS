<?php
// delete_document.php
include 'db_connect.php';

if (!isset($_GET['id'])) {
    header('Location: View_Records.php');
    exit;
}

$id = intval($_GET['id']);

// fetch file info
$res = $conn->query("SELECT filename, filepath FROM documents WHERE id = $id LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    $filename = $row['filename'];
    $filepath = $row['filepath'];

    // Try possible server paths and unlink if exists
    $candidates = [];

    if (!empty($filepath)) {
        // relative stored like 'uploads/...' -> server path __DIR__/../uploads/...
        $candidates[] = __DIR__ . '/../' . $filepath;
        $candidates[] = __DIR__ . '/' . $filepath;
    }

    if (!empty($filename)) {
        $candidates[] = __DIR__ . '/../uploads/' . $filename;
        $candidates[] = __DIR__ . '/uploads/' . $filename;
    }

    foreach ($candidates as $p) {
        if (is_file($p)) {
            @unlink($p);
            // once removed, break (still attempt DB delete after)
            break;
        }
    }
}

// delete DB row
$conn->query("DELETE FROM documents WHERE id = $id");

header('Location: View_Records.php?deleted=1');
exit;
