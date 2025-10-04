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
  <title>TNVS — Blacklist & Watchlist</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-slate-800">
  <div class="max-w-6xl mx-auto p-6">
    <header class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-semibold">Blacklist & Watchlist</h1>
      <div class="flex gap-2">
        <button id="btnAdd" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow hover:bg-indigo-500">Add Entry</button>
        <button id="btnExport" class="bg-emerald-600 text-white px-4 py-2 rounded-md shadow hover:bg-emerald-500">Export CSV (Active Tab)</button>
      </div>
    </header>

    <!-- Tabs -->
    <div class="mb-4 bg-white rounded-md shadow p-3">
      <div class="flex gap-2" role="tablist">
        <button id="tabBlacklist" class="tabBtn px-4 py-2 rounded-md bg-red-50 border text-red-700 font-medium">Blacklist</button>
        <button id="tabWatchlist" class="tabBtn px-4 py-2 rounded-md bg-amber-50 border text-amber-700 font-medium">Watchlist</button>
      </div>
    </div>

    <!-- Controls: Search + Status filter -->
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

    <!-- Tables container -->
    <div id="tablesContainer">
      <!-- Blacklist Table -->
      <div id="blacklistTab" class="bg-white rounded-lg shadow p-2">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-red-50">
              <tr>
                <th class="px-4 py-3 text-left text-sm font-medium">Name</th>
                <th class="px-4 py-3 text-left text-sm font-medium">Contact</th>
                <th class="px-4 py-3 text-left text-sm font-medium">Reason</th>
                <th class="px-4 py-3 text-left text-sm font-medium">Date Added</th>
                <th class="px-4 py-3 text-left text-sm font-medium">Status</th>
                <th class="px-4 py-3 text-right text-sm font-medium">Actions</th>
              </tr>
            </thead>
            <tbody id="blacklistTbody" class="bg-white divide-y divide-slate-100"></tbody>
          </table>
        </div>
      </div>

      <!-- Watchlist Table -->
      <div id="watchlistTab" class="hidden bg-white rounded-lg shadow p-2">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-amber-50">
              <tr>
                <th class="px-4 py-3 text-left text-sm font-medium">Name</th>
                <th class="px-4 py-3 text-left text-sm font-medium">Contact</th>
                <th class="px-4 py-3 text-left text-sm font-medium">Reason</th>
                <th class="px-4 py-3 text-left text-sm font-medium">Date Added</th>
                <th class="px-4 py-3 text-left text-sm font-medium">Status</th>
                <th class="px-4 py-3 text-right text-sm font-medium">Actions</th>
              </tr>
            </thead>
            <tbody id="watchlistTbody" class="bg-white divide-y divide-slate-100"></tbody>
          </table>
        </div>
      </div>
    </div>

    <div id="emptyState" class="hidden text-center py-10 text-slate-500">No entries yet.</div>

    <!-- Modal Add/Edit -->
    <div id="modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4">
      <div class="bg-white rounded-xl w-full max-w-xl shadow-lg p-6">
        <header class="flex items-center justify-between mb-4">
          <h2 id="modalTitle" class="text-lg font-medium">Add Entry</h2>
          <button id="modalClose" class="text-slate-500 hover:text-slate-700">✕</button>
        </header>

        <form id="entryForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Type</label>
            <select id="entryType" class="w-full border rounded-md px-3 py-2">
              <option value="blacklist">Blacklist</option>
              <option value="watchlist">Watchlist</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Full Name</label>
            <input id="entryName" required class="w-full border rounded-md px-3 py-2" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Contact</label>
            <input id="entryContact" class="w-full border rounded-md px-3 py-2" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Reason</label>
            <textarea id="entryReason" rows="3" class="w-full border rounded-md px-3 py-2"></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Date Added</label>
              <input id="entryDate" type="date" class="w-full border rounded-md px-3 py-2" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Status</label>
              <select id="entryStatus" class="w-full border rounded-md px-3 py-2">
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
    // in-memory stores
    var blacklist = [];
    var watchlist = [];

    // elements
    var tabBlacklist = document.getElementById('tabBlacklist');
    var tabWatchlist = document.getElementById('tabWatchlist');
    var blacklistTab = document.getElementById('blacklistTab');
    var watchlistTab = document.getElementById('watchlistTab');
    var searchInput = document.getElementById('search');
    var statusFilter = document.getElementById('statusFilter');
    var blacklistTbody = document.getElementById('blacklistTbody');
    var watchlistTbody = document.getElementById('watchlistTbody');
    var emptyState = document.getElementById('emptyState');
    var btnAdd = document.getElementById('btnAdd');
    var btnExport = document.getElementById('btnExport');

    var modal = document.getElementById('modal');
    var modalTitle = document.getElementById('modalTitle');
    var modalClose = document.getElementById('modalClose');
    var cancelBtn = document.getElementById('cancelBtn');
    var entryForm = document.getElementById('entryForm');
    var entryType = document.getElementById('entryType');
    var entryName = document.getElementById('entryName');
    var entryContact = document.getElementById('entryContact');
    var entryReason = document.getElementById('entryReason');
    var entryDate = document.getElementById('entryDate');
    var entryStatus = document.getElementById('entryStatus');

    var activeTab = 'blacklist'; // or 'watchlist'
    var editingId = null;

    function switchTo(tab) {
      activeTab = tab;
      if (tab === 'blacklist') {
        blacklistTab.classList.remove('hidden');
        watchlistTab.classList.add('hidden');
        tabBlacklist.classList.add('ring-2','ring-red-200');
        tabWatchlist.classList.remove('ring-2','ring-amber-200');
      } else {
        watchlistTab.classList.remove('hidden');
        blacklistTab.classList.add('hidden');
        tabWatchlist.classList.add('ring-2','ring-amber-200');
        tabBlacklist.classList.remove('ring-2','ring-red-200');
      }
      render();
    }

    tabBlacklist.addEventListener('click', function(){ switchTo('blacklist'); });
    tabWatchlist.addEventListener('click', function(){ switchTo('watchlist'); });

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

      // choose source
      var source = (activeTab === 'blacklist') ? blacklist : watchlist;
      var tbody = (activeTab === 'blacklist') ? blacklistTbody : watchlistTbody;

      var filtered = source.filter(function(item){
        var text = (item.name + ' ' + item.contact + ' ' + item.reason).toLowerCase();
        var matchesQ = text.indexOf(q) !== -1;
        var matchesStatus = (status === 'all') ? true : item.status === status;
        return matchesQ && matchesStatus;
      });

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
            tr.className = 'bg-slate-50 text-slate-400';
          } else if (activeTab === 'blacklist') {
            tr.className = 'bg-red-50';
          } else {
            tr.className = 'bg-amber-50';
          }

          tr.innerHTML = '\n            <td class="px-4 py-3 align-top text-sm">' + escapeHtml(item.name) + '</td>\n            <td class="px-4 py-3 align-top text-sm">' + escapeHtml(item.contact || '-') + '</td>\n            <td class="px-4 py-3 align-top text-sm">' + escapeHtml(item.reason || '-') + '</td>\n            <td class="px-4 py-3 align-top text-sm">' + (item.date || '-') + '</td>\n            <td class="px-4 py-3 align-top text-sm">' + (item.status === 'active' ? '<span class="px-2 py-1 rounded-full bg-green-100 text-green-700">Active</span>' : '<span class="px-2 py-1 rounded-full bg-slate-100 text-slate-500">Inactive</span>') + '</td>\n            <td class="px-4 py-3 align-top text-sm text-right">\n              <div class="inline-flex items-center gap-2">\n                <button class="btnView px-3 py-1 rounded-md border text-sm">View</button>\n                <button class="btnEdit px-3 py-1 rounded-md border text-sm">Edit</button>\n                <button class="btnDelete px-3 py-1 rounded-md border text-sm text-red-600">Delete</button>\n              </div>\n            </td>\n          ';

          tbody.appendChild(tr);

          // wire buttons
          var btnView = tr.querySelector('.btnView');
          var btnEdit = tr.querySelector('.btnEdit');
          var btnDelete = tr.querySelector('.btnDelete');

          btnView.addEventListener('click', function(){
            alert('Name: ' + item.name + '\nContact: ' + (item.contact||'-') + '\nReason: ' + (item.reason||'-') + '\nDate Added: ' + (item.date||'-') + '\nStatus: ' + item.status.toUpperCase());
          });

          btnEdit.addEventListener('click', function(){
            openModal('Edit Entry', item);
          });

          btnDelete.addEventListener('click', function(){
            if (confirm('Remove this entry?')) {
              if (activeTab === 'blacklist') {
                blacklist = blacklist.filter(function(x){ return x.id !== item.id });
              } else {
                watchlist = watchlist.filter(function(x){ return x.id !== item.id });
              }
              render();
            }
          });
        })(filtered[i]);
      }
    }

    function openModal(mode, item) {
      editingId = item ? item.id : null;
      modalTitle.textContent = mode;
      entryType.value = item ? item.type : activeTab;
      entryName.value = item ? item.name : '';
      entryContact.value = item ? item.contact : '';
      entryReason.value = item ? item.reason : '';
      entryDate.value = item ? item.date : nowDate();
      entryStatus.value = item ? item.status : 'active';

      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function closeModal() {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      editingId = null;
    }

    function escapeHtml(str) {
      if (!str) return '';
      return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    btnAdd.addEventListener('click', function(){ openModal('Add Entry', null); });
    modalClose.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', function(e){ e.preventDefault(); closeModal(); });

    entryForm.addEventListener('submit', function(e){
      e.preventDefault();
      var payload = {
        id: editingId || Date.now(),
        type: entryType.value,
        name: entryName.value.trim(),
        contact: entryContact.value.trim(),
        reason: entryReason.value.trim(),
        date: entryDate.value || nowDate(),
        status: entryStatus.value
      };

      if (editingId) {
        // replace in appropriate list (could have changed type)
        blacklist = blacklist.filter(function(x){ return x.id !== editingId });
        watchlist = watchlist.filter(function(x){ return x.id !== editingId });
      }

      if (payload.type === 'blacklist') {
        blacklist.unshift(payload);
      } else {
        watchlist.unshift(payload);
      }

      closeModal();
      render();
      entryForm.reset();
    });

    searchInput.addEventListener('input', function(){ render(); });
    statusFilter.addEventListener('change', function(){ render(); });

    btnExport.addEventListener('click', function(){
      var source = (activeTab === 'blacklist') ? blacklist : watchlist;
      if (source.length === 0) { alert('No entries to export.'); return; }

      var headers = ['Name','Contact','Reason','Date Added','Status'];
      var rows = source.map(function(item){
        return [
          '"' + (item.name||'').replace(/"/g,'""') + '"',
          '"' + (item.contact||'').replace(/"/g,'""') + '"',
          '"' + (item.reason||'').replace(/"/g,'""') + '"',
          '"' + (item.date||'') + '"',
          '"' + (item.status||'') + '"'
        ].join(',');
      });

      var csv = headers.join(',') + '\n' + rows.join('\n');
      var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      var url = URL.createObjectURL(blob);
      var a = document.createElement('a');
      a.href = url;
      var now = new Date();
      var stamp = now.getFullYear() + String(now.getMonth()+1).padStart(2,'0') + String(now.getDate()).padStart(2,'0') + '_' + String(now.getHours()).padStart(2,'0') + String(now.getMinutes()).padStart(2,'0');
      a.download = (activeTab === 'blacklist' ? 'blacklist_' : 'watchlist_') + stamp + '.csv';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    });

    // initial state
    entryDate.value = nowDate();
    switchTo('blacklist');
  </script>
</body>
</html>
        </main>
    </section>



    <script src="../JS/script.js"></script>
</body>
</html>