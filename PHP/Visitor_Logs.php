<?php
include 'db_connect.php';
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
              <li>
                <a href="#">
                    <i class='bx bxs-cog' ></i>
                    <span class="text">Settings</span>
                </a>
            </li>
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
  <title>TNVS — Visitor Logs</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-slate-800">
  <div class="max-w-6xl mx-auto p-6">
    <header class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-semibold">Visitor Logs</h1>
      <div class="flex gap-2">
        <input id="search" type="search" placeholder="Search logs..." class="border rounded-md px-3 py-2 w-64" />
        <select id="filterStatus" class="border rounded-md px-3 py-2">
          <option value="all">All</option>
          <option value="checked-in">Checked-in</option>
          <option value="checked-out">Checked-out</option>
        </select>
        <button id="btnExport" class="bg-emerald-600 text-white px-4 py-2 rounded-md shadow hover:bg-emerald-500">Export CSV</button>
        <button id="btnAdd" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow hover:bg-indigo-500">Add Log</button>
      </div>
    </header>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-medium">Visitor</th>
            <th class="px-4 py-3 text-left text-sm font-medium">Purpose</th>
            <th class="px-4 py-3 text-left text-sm font-medium">Check-in</th>
            <th class="px-4 py-3 text-left text-sm font-medium">Check-out</th>
            <th class="px-4 py-3 text-left text-sm font-medium">Status</th>
            <th class="px-4 py-3 text-right text-sm font-medium">Actions</th>
          </tr>
        </thead>
        <tbody id="logsTbody" class="bg-white divide-y divide-slate-100"></tbody>
      </table>
    </div>

    <div id="emptyState" class="hidden text-center py-10 text-slate-500">
      No logs yet. Click "Add Log" to create one.
    </div>

    <!-- Add/Edit Modal -->
    <div id="modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4">
      <div class="bg-white rounded-xl w-full max-w-2xl shadow-lg p-6">
        <header class="flex items-center justify-between mb-4">
          <h2 id="modalTitle" class="text-lg font-medium">Add Visitor Log</h2>
          <button id="modalClose" class="text-slate-500 hover:text-slate-700">✕</button>
        </header>

        <form id="logForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Visitor Name</label>
            <input id="visitorName" required class="w-full border rounded-md px-3 py-2" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Purpose</label>
            <input id="purpose" required class="w-full border rounded-md px-3 py-2" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Check-in Date</label>
              <input id="checkinDate" type="date" required class="w-full border rounded-md px-3 py-2" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Check-in Time</label>
              <input id="checkinTime" type="time" required class="w-full border rounded-md px-3 py-2" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Check-out Date (optional)</label>
              <input id="checkoutDate" type="date" class="w-full border rounded-md px-3 py-2" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Check-out Time (optional)</label>
              <input id="checkoutTime" type="time" class="w-full border rounded-md px-3 py-2" />
            </div>
          </div>

          <div class="flex justify-end gap-2">
            <button type="button" id="cancelBtn" class="px-4 py-2 rounded-md border">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white">Save Log</button>
          </div>
        </form>
      </div>
    </div>

  </div>

  <template id="rowTemplate">
    <tr class="logRow">
      <td class="px-4 py-3 align-top text-sm visitorNameCell"></td>
      <td class="px-4 py-3 align-top text-sm purposeCell"></td>
      <td class="px-4 py-3 align-top text-sm checkinCell"></td>
      <td class="px-4 py-3 align-top text-sm checkoutCell"></td>
      <td class="px-4 py-3 align-top text-sm statusCell"></td>
      <td class="px-4 py-3 align-top text-sm text-right">
        <div class="inline-flex items-center gap-2">
          <button class="btnCheckout px-3 py-1 rounded-md border text-sm">Check-out</button>
          <button class="btnView px-3 py-1 rounded-md border text-sm">View</button>
          <button class="btnEdit px-3 py-1 rounded-md border text-sm">Edit</button>
          <button class="btnDelete px-3 py-1 rounded-md border text-sm text-red-600">Delete</button>
        </div>
      </td>
    </tr>
  </template>

  <script>
    // In-memory store
    var logs = [];

    // Elements
    var logsTbody = document.getElementById('logsTbody');
    var emptyState = document.getElementById('emptyState');
    var searchInput = document.getElementById('search');
    var filterStatus = document.getElementById('filterStatus');
    var btnExport = document.getElementById('btnExport');
    var btnAdd = document.getElementById('btnAdd');
    var modal = document.getElementById('modal');
    var modalTitle = document.getElementById('modalTitle');
    var modalClose = document.getElementById('modalClose');
    var cancelBtn = document.getElementById('cancelBtn');
    var logForm = document.getElementById('logForm');
    var visitorNameInput = document.getElementById('visitorName');
    var purposeInput = document.getElementById('purpose');
    var checkinDateInput = document.getElementById('checkinDate');
    var checkinTimeInput = document.getElementById('checkinTime');
    var checkoutDateInput = document.getElementById('checkoutDate');
    var checkoutTimeInput = document.getElementById('checkoutTime');
    var rowTemplate = document.getElementById('rowTemplate');

    var editingId = null;

    function nowDate() {
      var d = new Date();
      var yyyy = d.getFullYear();
      var mm = String(d.getMonth()+1).padStart(2,'0');
      var dd = String(d.getDate()).padStart(2,'0');
      return yyyy + '-' + mm + '-' + dd;
    }

    function nowTime() {
      var d = new Date();
      var hh = String(d.getHours()).padStart(2,'0');
      var mm = String(d.getMinutes()).padStart(2,'0');
      return hh + ':' + mm;
    }

    function render() {
      var q = searchInput.value.trim().toLowerCase();
      var statusFilter = filterStatus.value;

      var filtered = logs.filter(function(l){
        var text = (l.name + ' ' + l.purpose + ' ' + (l.checkin || '') + ' ' + (l.checkout || '')).toLowerCase();
        var matchesQ = text.indexOf(q) !== -1;
        var matchesStatus = (statusFilter === 'all') ? true : ((statusFilter === 'checked-in') ? !l.checkout : !!l.checkout);
        return matchesQ && matchesStatus;
      });

      logsTbody.innerHTML = '';
      if (filtered.length === 0) {
        emptyState.classList.remove('hidden');
      } else {
        emptyState.classList.add('hidden');
      }

      filtered.forEach(function(l){
        var node = rowTemplate.content.cloneNode(true);
        node.querySelector('.visitorNameCell').textContent = l.name;
        node.querySelector('.purposeCell').textContent = l.purpose;
        node.querySelector('.checkinCell').textContent = l.checkin || '-';
        node.querySelector('.checkoutCell').textContent = l.checkout || '-';
        node.querySelector('.statusCell').textContent = l.checkout ? 'Checked-out' : 'Checked-in';

        var btnCheckout = node.querySelector('.btnCheckout');
        var btnView = node.querySelector('.btnView');
        var btnEdit = node.querySelector('.btnEdit');
        var btnDelete = node.querySelector('.btnDelete');

        // If already checked out, disable checkout button
        if (l.checkout) {
          btnCheckout.setAttribute('disabled','disabled');
          btnCheckout.classList.add('opacity-50','cursor-not-allowed');
        }

        btnCheckout.addEventListener('click', function(){
          if (!l.checkout) {
            var d = new Date();
            var date = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
            var time = String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
            l.checkout = date + ' ' + time;
            render();
          }
        });

        btnView.addEventListener('click', function(){
          alert('Visitor: ' + l.name + '\nPurpose: ' + l.purpose + '\nCheck-in: ' + (l.checkin||'-') + '\nCheck-out: ' + (l.checkout||'-'));
        });

        btnEdit.addEventListener('click', function(){
          openModal('Edit Visitor Log', l);
        });

        btnDelete.addEventListener('click', function(){
          if (confirm('Delete this log?')) {
            logs = logs.filter(function(x){ return x.id !== l.id });
            render();
          }
        });

        logsTbody.appendChild(node);
      });
    }

    function openModal(mode, l) {
      editingId = l ? l.id : null;
      modalTitle.textContent = mode;
      if (l) {
        visitorNameInput.value = l.name;
        purposeInput.value = l.purpose;
        if (l.checkin) {
          var parts = l.checkin.split(' ');
          checkinDateInput.value = parts[0];
          checkinTimeInput.value = parts[1];
        } else {
          checkinDateInput.value = nowDate();
          checkinTimeInput.value = nowTime();
        }
        if (l.checkout) {
          var parts2 = l.checkout.split(' ');
          checkoutDateInput.value = parts2[0];
          checkoutTimeInput.value = parts2[1];
        } else {
          checkoutDateInput.value = '';
          checkoutTimeInput.value = '';
        }
      } else {
        visitorNameInput.value = '';
        purposeInput.value = '';
        checkinDateInput.value = nowDate();
        checkinTimeInput.value = nowTime();
        checkoutDateInput.value = '';
        checkoutTimeInput.value = '';
      }

      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function closeModal() {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      editingId = null;
    }

    btnAdd.addEventListener('click', function(){ openModal('Add Visitor Log'); });
    modalClose.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', function(e){ e.preventDefault(); closeModal(); });

    logForm.addEventListener('submit', function(e){
      e.preventDefault();
      var checkinVal = checkinDateInput.value && checkinTimeInput.value ? (checkinDateInput.value + ' ' + checkinTimeInput.value) : '';
      var checkoutVal = checkoutDateInput.value && checkoutTimeInput.value ? (checkoutDateInput.value + ' ' + checkoutTimeInput.value) : '';

      var payload = {
        id: editingId || Date.now(),
        name: visitorNameInput.value.trim(),
        purpose: purposeInput.value.trim(),
        checkin: checkinVal,
        checkout: checkoutVal
      };

      if (editingId) {
        logs = logs.map(function(item){ return item.id === editingId ? payload : item });
      } else {
        logs.unshift(payload);
      }

      render();
      closeModal();
      logForm.reset();
    });

    searchInput.addEventListener('input', function(){ render(); });
    filterStatus.addEventListener('change', function(){ render(); });

    btnExport.addEventListener('click', function(){
      if (logs.length === 0) {
        alert('No logs to export.');
        return;
      }

      var headers = ['Visitor','Purpose','Check-in','Check-out','Status'];
      var rows = logs.map(function(l){
        return [
          '"' + l.name.replace(/"/g,'""') + '"',
          '"' + l.purpose.replace(/"/g,'""') + '"',
          '"' + (l.checkin || '') + '"',
          '"' + (l.checkout || '') + '"',
          '"' + (l.checkout ? 'Checked-out' : 'Checked-in') + '"'
        ].join(',');
      });

      var csv = headers.join(',') + '\n' + rows.join('\n');
      var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      var url = URL.createObjectURL(blob);
      var a = document.createElement('a');
      a.href = url;
      var now = new Date();
      var stamp = now.getFullYear() + String(now.getMonth()+1).padStart(2,'0') + String(now.getDate()).padStart(2,'0') + '_' + String(now.getHours()).padStart(2,'0') + String(now.getMinutes()).padStart(2,'0');
      a.download = 'visitor_logs_' + stamp + '.csv';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    });

    // initial render
    render();
  </script>
</body>
</html>
        </main>
    </section>



    <script src="../JS/script.js"></script>
</body>
</html>