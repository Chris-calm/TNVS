<?php
include 'db_connect.php'; // Assumes this file connects to $conn

// Define table names
$blacklist_table = 'blacklist_watchlist';
$visitors_table = 'visitors';

// Global variable for feedback messages
$feedback_message = '';
$feedback_type = ''; // 'success' or 'error'

// Function to set a feedback message
function set_feedback($message, $type) {
    global $feedback_message, $feedback_type;
    $feedback_message = $message;
    $feedback_type = $type;
}

// --- Data Submission Handler (Add/Edit) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    // Crucial: person_id must be received and validated
    $person_id = $_POST['person_id'] ?? null;
    $type = $_POST['type'] ?? 'blacklist'; // Default to blacklist if not set
    $reason = $_POST['reason'] ?? null;
    $date = $_POST['date'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? 'active';
    $entry_id = $_POST['entry_id'] ?? null;

    // VALIDATION: Ensure person_id is a non-empty, positive integer
    if ($person_id && is_numeric($person_id) && (int)$person_id > 0) {
        $person_id = (int)$person_id;
        
        // 1. Fetch person details from the 'visitors' table for the entry
        $stmt_person = $conn->prepare("SELECT name, contact FROM $visitors_table WHERE id = ?");
        if ($stmt_person === false) {
             set_feedback("Database error preparing visitor fetch: " . $conn->error, 'error');
        } else {
            $stmt_person->bind_param("i", $person_id);
            $stmt_person->execute();
            $result_person = $stmt_person->get_result();
            $person_data = $result_person->fetch_assoc();
            $stmt_person->close();

            if ($person_data) {
                $name = $person_data['name'] ?? 'Unknown Visitor';
                $contact = $person_data['contact'] ?? '-';
            } else {
                set_feedback("Error: Visitor ID $person_id not found in logs. Entry not saved.", 'error');
                $name = 'Visitor Not Found';
                $contact = '-';
            }
        }

        if ($feedback_type !== 'error') {
            // 2. Perform CRUD operation
            $success = false;
            if ($action === "add") {
                $stmt = $conn->prepare("INSERT INTO $blacklist_table (person_id, type, name, contact, reason, date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("issssss", $person_id, $type, $name, $contact, $reason, $date, $status);
                    if ($stmt->execute()) {
                        set_feedback("Successfully added entry to " . ucfirst($type) . ".", 'success');
                        $success = true;
                    } else {
                        set_feedback("Database error adding entry: " . $stmt->error, 'error');
                    }
                    $stmt->close();
                } else {
                    set_feedback("Database error preparing 'add' statement: " . $conn->error, 'error');
                }
            } elseif ($action === "edit" && $entry_id && is_numeric($entry_id)) {
                $entry_id = (int)$entry_id;
                $stmt = $conn->prepare("UPDATE $blacklist_table SET person_id=?, type=?, name=?, contact=?, reason=?, date=?, status=? WHERE id=?");
                if ($stmt) {
                    $stmt->bind_param("issssssi", $person_id, $type, $name, $contact, $reason, $date, $status, $entry_id);
                    if ($stmt->execute()) {
                        set_feedback("Successfully updated entry.", 'success');
                        $success = true;
                    } else {
                        set_feedback("Database error updating entry: " . $stmt->error, 'error');
                    }
                    $stmt->close();
                } else {
                     set_feedback("Database error preparing 'edit' statement: " . $conn->error, 'error');
                }
            }

            if ($success) {
                // Redirect on success to prevent form resubmission
                header("Location: Blacklist_Watchlist.php?tab=" . $type . "&msg=success");
                exit();
            }
        }
    } else {
         // This block handles the "Invalid Person ID" error
         set_feedback("Error: Invalid Person ID. Please ensure a person is selected for new entries.", 'error');
    }
}

// --- Delete Handler ---
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $tab_to_return_to = $_GET['tab'] ?? 'blacklist';

    $stmt_delete = $conn->prepare("DELETE FROM $blacklist_table WHERE id=?");
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $id);
        if ($stmt_delete->execute()) {
             set_feedback("Entry successfully deleted.", 'success');
        } else {
             set_feedback("Database error deleting entry: " . $stmt_delete->error, 'error');
        }
        $stmt_delete->close();
    } else {
         set_feedback("Database error preparing 'delete' statement: " . $conn->error, 'error');
    }

    header("Location: Blacklist_Watchlist.php?tab=" . $tab_to_return_to . ($feedback_type === 'success' ? "&msg=delete_success" : "&msg=delete_error"));
    exit();
}

// --- CSV Export Handler ---
if (isset($_GET['action']) && $_GET['action'] == 'export_csv' && isset($_GET['tab']) && in_array($_GET['tab'], ['blacklist', 'watchlist'])) {
    $export_tab = $_GET['tab'];
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $export_tab . '_report_' . date('Ymd_His') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['ID', 'Person ID', 'Type', 'Name', 'Contact', 'Reason', 'Date Added', 'Status']);
    
    $sql = $conn->prepare("SELECT id, person_id, type, name, contact, reason, date, status FROM $blacklist_table WHERE type = ? ORDER BY id DESC");
    if ($sql) {
        $sql->bind_param("s", $export_tab);
        $sql->execute();
        $result = $sql->get_result();
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                fputcsv($output, array_values($row));
            }
        }
        $sql->close();
    }
    
    fclose($output);
    exit();
}


// --- Data Fetching for View and Modal ---
$current_tab = isset($_GET['tab']) && in_array($_GET['tab'], ['blacklist', 'watchlist']) ? $_GET['tab'] : 'blacklist';

// 1. Fetch all entries for the view
$all_entries = [];
$stmt_all = $conn->prepare("SELECT * FROM $blacklist_table ORDER BY id DESC");
if ($stmt_all) {
    $stmt_all->execute();
    $result_all = $stmt_all->get_result();
    if ($result_all) {
        while ($row = $result_all->fetch_assoc()) {
            $all_entries[] = $row;
        }
    }
    $stmt_all->close();
}

$blacklist_js = json_encode(array_values(array_filter($all_entries, fn($e) => $e['type'] === 'blacklist')));
$watchlist_js = json_encode(array_values(array_filter($all_entries, fn($e) => $e['type'] === 'watchlist')));

// 2. Fetch ALL visitors from the logs table (Visitor_Logs.php uses 'visitors' table)
$all_visitors_data = [];
$stmt_visitors = $conn->prepare("SELECT id, name, contact FROM $visitors_table WHERE name IS NOT NULL AND name != '' ORDER BY name ASC");
if ($stmt_visitors) {
    $stmt_visitors->execute();
    $result_visitors = $stmt_visitors->get_result();
    if ($result_visitors) {
        while ($row = $result_visitors->fetch_assoc()) {
            $all_visitors_data[] = [
                'id' => (int)$row['id'],
                'full_name' => $row['name'],
                'contact_number' => $row['contact'],
            ];
        }
    }
    $stmt_visitors->close();
}
$all_visitors_js = json_encode($all_visitors_data);

// Prepare the list of IDs currently in the blacklist/watchlist
$listed_person_ids = array_column($all_entries, 'person_id');
$listed_person_ids_js = json_encode(array_map('intval', $listed_person_ids));

// Check for and display messages after redirect
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'success') {
        $feedback_message = "Operation completed successfully.";
        $feedback_type = 'success';
    } elseif ($_GET['msg'] === 'delete_success') {
        $feedback_message = "Entry successfully deleted.";
        $feedback_type = 'success';
    } elseif ($_GET['msg'] === 'delete_error') {
        $feedback_message = "An error occurred during deletion.";
        $feedback_type = 'error';
    }
    // Remove query string messages after display to clean URL
    // echo "<script>window.history.replaceState({}, document.title, window.location.pathname + '?tab={$current_tab}');</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <title>TNVS Dashboard</title>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    <section id="sidebar">
        <a href="" class="brand">
    <img src="../PICTURES/Black and White Circular Art & Design Logo.png" alt="Trail Ad Corporation Logo" class="brand-logo">
    <span class="text">TNVS</span>
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
                <a href="../PHP/index.php" class="logout">
                    <i class='bx bxs-log-out-circle' ></i>
                    <span class="text">Logout</span>
                </a>
            </li>
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
            <a href="#" class="notification">
                <i class='bx bxs-bell' ></i>
                <span class="num">8</span>
            </a>
            <a href="#" class="profile">
                <img src="../PICTURES/Ser.jpg">
            </a>
        </nav>

        <main>
            <!doctype html>
<html lang="en">
<head>
 <meta charset="utf-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1" />
 <title>TNVS — Blacklist & Watchlist</title>
 <script src="https://cdn.tailwindcss.com"></script>
 <style>
    /* Ensure modal remains hidden until JS is ready, overriding inline style for a moment */
    #modal.loading { display: none !important; } 
 </style>
</head>
<body class="bg-gray-50 text-slate-800">
 <div class="max-w-6xl mx-auto p-6">
    <header class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-semibold">Blacklist & Watchlist</h1>
      <div class="flex gap-2">
        <button id="btnAdd" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow hover:bg-indigo-500">Add Entry</button>
        <a id="btnExport" href="Blacklist_Watchlist.php?action=export_csv&tab=<?= $current_tab ?>" class="bg-emerald-600 text-white px-4 py-2 rounded-md shadow hover:bg-emerald-500">Export CSV (Active Tab)</a>
      </div>
    </header>

    <?php if ($feedback_message): ?>
    <div id="feedback-alert" class="p-4 mb-4 text-sm rounded-lg <?= $feedback_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>" role="alert">
        <span class="font-medium"><?= ucfirst($feedback_type) ?>!</span> <?= htmlspecialchars($feedback_message) ?>
    </div>
    <?php endif; ?>

    <div class="mb-4 bg-white rounded-md shadow p-3">
      <div class="flex gap-2" role="tablist">
        <a href="?tab=blacklist" id="tabBlacklist" class="tabBtn px-4 py-2 rounded-md border text-red-700 font-medium <?= $current_tab == 'blacklist' ? 'bg-red-50 ring-2 ring-red-200' : 'bg-white hover:bg-red-50' ?>">Blacklist</a>
        <a href="?tab=watchlist" id="tabWatchlist" class="tabBtn px-4 py-2 rounded-md border text-amber-700 font-medium <?= $current_tab == 'watchlist' ? 'bg-amber-50 ring-2 ring-amber-200' : 'bg-white hover:bg-amber-50' ?>">Watchlist</a>
      </div>
    </div>

    <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div class="flex items-center gap-2">
        <input id="search" type="search" placeholder="Search name, reason, contact..." class="border rounded-md px-3 py-2 w-64" />
        <select id="statusFilter" class="border rounded-md px-3 py-2">
          <option value="all">All</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
      <div class="text-sm text-slate-500">Active rows are highlighted. Inactive are muted.</div>
    </div>

    <div id="tablesContainer">
      <div id="activeTabTable" class="bg-white rounded-lg shadow p-2">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="<?= $current_tab == 'blacklist' ? 'bg-red-50' : 'bg-amber-50' ?>">
              <tr>
                <th class="px-4 py-3 text-left text-sm font-medium">Name</th>
                <th class="px-4 py-3 text-left text-sm font-medium">Contact</th>
                <th class="px-4 py-3 text-left text-sm font-medium">Reason</th>
                <th class="px-4 py-3 text-left text-sm font-medium">Date Added</th>
                <th class="px-4 py-3 text-left text-sm font-medium">Status</th>
                <th class="px-4 py-3 text-right text-sm font-medium">Actions</th>
              </tr>
            </thead>
            <tbody id="entriesTbody" class="bg-white divide-y divide-slate-100">
                </tbody>
          </table>
        </div>
      </div>
    </div>

    <div id="emptyState" class="hidden text-center py-10 text-slate-500">No entries yet.</div>

    <div id="modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4 z-50 loading">
      <div class="bg-white rounded-xl w-full max-w-xl shadow-lg p-6">
        <header class="flex items-center justify-between mb-4">
          <h2 id="modalTitle" class="text-lg font-medium">Add Entry</h2>
          <button id="modalClose" class="text-slate-500 hover:text-slate-700">✕</button>
        </header>

        <form id="entryForm" method="POST" action="Blacklist_Watchlist.php" class="space-y-4">
            <input type="hidden" name="entry_id" id="entryId">
            <input type="hidden" name="action" id="entryAction" value="add">

          <div>
            <label class="block text-sm font-medium mb-1">Type</label>
            <select name="type" id="entryType" class="w-full border rounded-md px-3 py-2">
              <option value="blacklist">Blacklist</option>
              <option value="watchlist">Watchlist</option>
            </select>
          </div>

          <div id="personSelectContainer">
            <label class="block text-sm font-medium mb-1">Person to List</label>
            <select name="person_id" id="personSelect" required class="w-full border rounded-md px-3 py-2">
                </select>
          </div>
          
          <div id="readOnlyNameContact" class="hidden">
            <label class="block text-sm font-medium mb-1">Full Name (Listed)</label>
            <input id="entryName" disabled class="w-full border rounded-md px-3 py-2 bg-gray-100" />
            <label class="block text-sm font-medium mb-1 mt-2">Contact (Listed)</label>
            <input id="entryContact" disabled class="w-full border rounded-md px-3 py-2 bg-gray-100" />
            <input type="hidden" name="person_id" id="hiddenPersonId">
          </div>
          
          <div>
            <label class="block text-sm font-medium mb-1">Reason</label>
            <textarea name="reason" id="entryReason" rows="3" class="w-full border rounded-md px-3 py-2"></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Date Added</label>
              <input name="date" id="entryDate" type="date" class="w-full border rounded-md px-3 py-2" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Status</label>
              <select name="status" id="entryStatus" class="w-full border rounded-md px-3 py-2">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>

          <div class="flex justify-end gap-2">
            <button type="button" id="cancelBtn" class="px-4 py-2 rounded-md border">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white">Save</button>
          </div>
        </form>
      </div>
    </div>

 </div>

<script>
    // --- PHP Data Injection ---
    var blacklist = <?= $blacklist_js ?>;
    var watchlist = <?= $watchlist_js ?>;
    var current_tab = "<?= $current_tab ?>";
    var allVisitors = <?= $all_visitors_js ?>; // Contains all visitors from logs
    var listedPersonIds = <?= $listed_person_ids_js ?>.map(String); 
    // --------------------------

    // elements
    var searchInput = document.getElementById('search');
    var statusFilter = document.getElementById('statusFilter');
    var entriesTbody = document.getElementById('entriesTbody');
    var emptyState = document.getElementById('emptyState');
    var btnAdd = document.getElementById('btnAdd');
    var feedbackAlert = document.getElementById('feedback-alert');

    var modal = document.getElementById('modal');
    var modalTitle = document.getElementById('modalTitle');
    var modalClose = document.getElementById('modalClose');
    var cancelBtn = document.getElementById('cancelBtn');
    var entryForm = document.getElementById('entryForm');
    
    // Form fields
    var entryId = document.getElementById('entryId');
    var entryAction = document.getElementById('entryAction');
    var entryType = document.getElementById('entryType');
    var entryReason = document.getElementById('entryReason');
    var entryDate = document.getElementById('entryDate');
    var entryStatus = document.getElementById('entryStatus');

    // New/Modified Modal Elements
    var personSelectContainer = document.getElementById('personSelectContainer');
    var personSelect = document.getElementById('personSelect'); 
    var readOnlyNameContact = document.getElementById('readOnlyNameContact');
    var entryName = document.getElementById('entryName'); 
    var entryContact = document.getElementById('entryContact');
    var hiddenPersonId = document.getElementById('hiddenPersonId');


    // ** FIX FOR MODAL VISIBILITY ON LOAD **
    document.addEventListener('DOMContentLoaded', function() {
        if (modal) {
            modal.classList.remove('loading');
        }
        
        // Hide feedback message after 5 seconds
        if (feedbackAlert) {
            setTimeout(() => {
                feedbackAlert.style.display = 'none';
            }, 5000);
        }
    });
    // **************************************


    function nowDate() {
      var d = new Date();
      var yyyy = d.getFullYear();
      var mm = String(d.getMonth()+1).padStart(2,'0');
      var dd = String(d.getDate()).padStart(2,'0');
      return yyyy + '-' + mm + '-' + dd;
    }

    function render() {
      var q = searchInput.value.trim().toLowerCase();
      var status = statusFilter.value;

      // choose source based on current URL parameter
      var source = (current_tab === 'blacklist') ? blacklist : watchlist;
      var tbody = entriesTbody;
      var colorClass = (current_tab === 'blacklist') ? 'bg-red-50 hover:bg-red-100' : 'bg-amber-50 hover:bg-amber-100';

      var filtered = source.filter(function(item){
        var text = (item.name + ' ' + item.contact + ' ' + item.reason).toLowerCase();
        var matchesQ = text.indexOf(q) !== -1;
        var matchesStatus = (status === 'all') ? true : item.status === status;
        return matchesQ && matchesStatus;
      });

      // Update the table header color based on the tab
      var tableHead = entriesTbody.previousElementSibling.querySelector('thead');
      tableHead.className = (current_tab === 'blacklist') ? 'bg-red-50' : 'bg-amber-50';

      tbody.innerHTML = '';

      if (filtered.length === 0) {
        emptyState.classList.remove('hidden');
      } else {
        emptyState.classList.add('hidden');
      }

      for (var i = 0; i < filtered.length; i++) {
        (function(item){
          var tr = document.createElement('tr');
          // row color coding
          if (item.status === 'inactive') {
            tr.className = 'bg-slate-50 text-slate-500 hover:bg-slate-100 transition-colors duration-150';
          } else {
            tr.className = colorClass + ' transition-colors duration-150';
          }

          // URL to delete entry
          var deleteUrl = 'Blacklist_Watchlist.php?action=delete&id=' + item.id + '&tab=' + current_tab;

          tr.innerHTML = '\
              <td class="px-4 py-3 align-top text-sm">' + escapeHtml(item.name) + '</td>\
              <td class="px-4 py-3 align-top text-sm">' + escapeHtml(item.contact || '-') + '</td>\
              <td class="px-4 py-3 align-top text-sm">' + escapeHtml(item.reason || '-') + '</td>\
              <td class="px-4 py-3 align-top text-sm">' + (item.date || '-') + '</td>\
              <td class="px-4 py-3 align-top text-sm">' + (item.status === 'active' ? '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Active</span>' : '<span class="px-2 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">Inactive</span>') + '</td>\
              <td class="px-4 py-3 align-top text-sm text-right">\
                  <div class="inline-flex items-center gap-2">\
                      <button class="btnView px-3 py-1 rounded-md border border-slate-300 bg-white hover:bg-slate-100 text-sm">View</button>\
                      <button class="btnEdit px-3 py-1 rounded-md border border-indigo-300 bg-indigo-50 hover:bg-indigo-100 text-sm text-indigo-700">Edit</button>\
                      <a href="' + deleteUrl + '" onclick="return confirm(\'Are you sure you want to delete this entry? This action is irreversible.\')" class="btnDelete px-3 py-1 rounded-md border border-red-300 bg-red-50 hover:bg-red-100 text-sm text-red-600">Delete</a>\
                  </div>\
              </td>\
            ';

          tbody.appendChild(tr);

          // wire buttons
          var btnView = tr.querySelector('.btnView');
          var btnEdit = tr.querySelector('.btnEdit');

          btnView.addEventListener('click', function(){
            alert('Name: ' + item.name + '\nContact: ' + (item.contact||'-') + '\nReason: ' + (item.reason||'-') + '\nDate Added: ' + (item.date||'-') + '\nStatus: ' + item.status.toUpperCase());
          });

          btnEdit.addEventListener('click', function(){
            openModal('Edit Entry', item);
          });

        })(filtered[i]);
      }
    }

    function populateEdit(item) {
        // Hide select, show read-only fields
        personSelect.required = false;
        personSelectContainer.classList.add('hidden');
        readOnlyNameContact.classList.remove('hidden');
        
        // Find the original visitor data to ensure the most current info is shown
        const currentPerson = allVisitors.find(v => String(v.id) === String(item.person_id));
        
        // Set values for the read-only display and hidden field
        if (currentPerson) {
            entryName.value = currentPerson.full_name;
            entryContact.value = currentPerson.contact_number;
            hiddenPersonId.value = item.person_id;
        } else {
            // Fallback: use the stored name/contact in the blacklist table
            entryName.value = item.name || 'Visitor Not Found';
            entryContact.value = item.contact || '-';
            hiddenPersonId.value = item.person_id;
        }
    }
    
    function populateAdd() {
        // Show select, hide read-only fields
        personSelect.required = true;
        personSelectContainer.classList.remove('hidden');
        readOnlyNameContact.classList.add('hidden');
        
        // Add the empty default option which triggers required/validation check
        personSelect.innerHTML = '<option value="">-- Select Person --</option>';
        
        // Iterate over ALL visitors
        allVisitors.forEach(person => {
            const option = document.createElement('option');
            option.value = person.id;

            // Check if the person is already listed in the blacklist/watchlist
            const isListed = listedPersonIds.includes(String(person.id));
            let nameDisplay = person.full_name;
            
            if (isListed) {
                // Add a visual cue for listed visitors
                nameDisplay += ' (ALREADY LISTED)';
                option.style.backgroundColor = '#fee2e2'; // Light red background for listed names
                option.style.color = '#991b1b';
                option.style.fontWeight = 'bold';
            }
            
            option.textContent = `${nameDisplay} (ID: ${person.id} | ${person.contact_number || 'No Contact'})`;
            personSelect.appendChild(option);
        });
        
        // Clear the hidden person ID used for editing
        hiddenPersonId.value = '';
    }


    function openModal(mode, item) {
      modalTitle.textContent = mode;

      if (mode === 'Edit Entry' && item) {
        // Populate form for editing
        entryId.value = item.id; 
        entryAction.value = 'edit';
        entryType.value = item.type;
        entryReason.value = item.reason;
        entryDate.value = item.date;
        entryStatus.value = item.status;
        
        populateEdit(item);

      } else {
        // 'Add Entry' - reset form and set defaults
        entryForm.reset(); 
        entryId.value = '';
        entryAction.value = 'add';
        entryType.value = current_tab; // Default to active tab
        entryDate.value = nowDate();
        entryStatus.value = 'active';

        populateAdd(); 
      }

      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function closeModal() {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      entryForm.reset();
    }

    function escapeHtml(str) {
      if (!str) return '';
      return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    // ------------------------------------------------
    //  INPUT/EVENT WIRING (Including the validation fix)
    // ------------------------------------------------

    entryForm.addEventListener('submit', function(e) {
        const action = entryAction.value;
        let personIdValue = '';
        
        if (action === 'add') {
            // In 'add' mode, check the selected value from the dropdown
            personIdValue = personSelect.value;
            // Validate: Must be non-empty AND a positive integer
            if (!personIdValue || isNaN(parseInt(personIdValue)) || parseInt(personIdValue) <= 0) {
                e.preventDefault(); // Stop form submission
                alert("Validation Failed: Please select a valid person from the Visitor Logs list to add a new entry.");
                personSelectContainer.scrollIntoView({ behavior: 'smooth' });
                return;
            }
        } else if (action === 'edit') {
            // In 'edit' mode, ensure the hidden ID for the original person is present
            personIdValue = hiddenPersonId.value;
            if (!personIdValue || isNaN(parseInt(personIdValue)) || parseInt(personIdValue) <= 0) {
                 e.preventDefault(); // Stop form submission
                 alert("Error: The original Visitor ID for this entry is missing or invalid. Cannot save the edit.");
                 return;
            }
        }
        // If validation passes, the form submits to the PHP script.
    });
    
    btnAdd.addEventListener('click', function(){ openModal('Add Entry', null); });
    modalClose.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', function(e){ e.preventDefault(); closeModal(); });

    searchInput.addEventListener('input', function(){ render(); });
    statusFilter.addEventListener('change', function(){ render(); });
    
    // Initial state setup
    entryDate.value = nowDate();
    render();
</script>
</body>
</html>
        </main>
    </section>



    <script src="../JS/script.js"></script>
</body>
</html>