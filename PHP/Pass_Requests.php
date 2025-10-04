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
  <title>TNVS — Pass Requests</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-slate-800">
  <div class="max-w-6xl mx-auto p-6">
    <header class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-semibold">Pass Requests</h1>
      <div class="flex gap-2">
        <input id="search" type="search" placeholder="Search requests..." class="border rounded-md px-3 py-2 w-64" />
        <select id="filterStatus" class="border rounded-md px-3 py-2">
          <option value="all">All</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="denied">Denied</option>
        </select>
        <button id="btnExport" class="bg-emerald-600 text-white px-4 py-2 rounded-md shadow hover:bg-emerald-500">Export CSV</button>
        <button id="btnAdd" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow hover:bg-indigo-500">New Request</button>
      </div>
    </header>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
      <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-medium">Name</th>
            <th class="px-4 py-3 text-left text-sm font-medium">Affiliation</th>
            <th class="px-4 py-3 text-left text-sm font-medium">Visit Date</th>
            <th class="px-4 py-3 text-left text-sm font-medium">Person to Visit</th>
            <th class="px-4 py-3 text-left text-sm font-medium">Status</th>
            <th class="px-4 py-3 text-right text-sm font-medium">Actions</th>
          </tr>
        </thead>
        <tbody id="requestsTbody" class="bg-white divide-y divide-slate-100"></tbody>
      </table>
    </div>

    <div id="emptyState" class="hidden text-center py-10 text-slate-500">
      No pass requests yet. Click "New Request" to create one.
    </div>

    <!-- Modal (Add / Edit) -->
    <div id="modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4">
      <div class="bg-white rounded-xl w-full max-w-2xl shadow-lg p-6">
        <header class="flex items-center justify-between mb-4">
          <h2 id="modalTitle" class="text-lg font-medium">New Pass Request</h2>
          <button id="modalClose" class="text-slate-500 hover:text-slate-700">✕</button>
        </header>

        <form id="requestForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Full Name</label>
            <input id="fullName" required class="w-full border rounded-md px-3 py-2" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Contact / Email</label>
            <input id="contact" class="w-full border rounded-md px-3 py-2" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Affiliation / Organization</label>
            <input id="affiliation" class="w-full border rounded-md px-3 py-2" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">Visit Date</label>
              <input id="visitDate" type="date" required class="w-full border rounded-md px-3 py-2" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Visit Time</label>
              <input id="visitTime" type="time" required class="w-full border rounded-md px-3 py-2" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Person / Department to Visit</label>
            <input id="toVisit" required class="w-full border rounded-md px-3 py-2" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Purpose of Visit</label>
            <textarea id="purpose" rows="3" class="w-full border rounded-md px-3 py-2"></textarea>
          </div>

          <div class="flex justify-end gap-2">
            <button type="button" id="cancelBtn" class="px-4 py-2 rounded-md border">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white">Save Request</button>
          </div>
        </form>
      </div>
    </div>

  </div>

  <template id="rowTemplate">
    <tr class="requestRow">
      <td class="px-4 py-3 align-top text-sm nameCell"></td>
      <td class="px-4 py-3 align-top text-sm affCell"></td>
      <td class="px-4 py-3 align-top text-sm dateCell"></td>
      <td class="px-4 py-3 align-top text-sm toVisitCell"></td>
      <td class="px-4 py-3 align-top text-sm statusCell"></td>
      <td class="px-4 py-3 align-top text-sm text-right">
        <div class="inline-flex items-center gap-2">
          <button class="btnApprove px-3 py-1 rounded-md border text-sm bg-green-50">Approve</button>
          <button class="btnDeny px-3 py-1 rounded-md border text-sm bg-rose-50">Deny</button>
          <button class="btnView px-3 py-1 rounded-md border text-sm">View</button>
          <button class="btnEdit px-3 py-1 rounded-md border text-sm">Edit</button>
          <button class="btnDelete px-3 py-1 rounded-md border text-sm text-red-600">Delete</button>
        </div>
      </td>
    </tr>
  </template>

  <script>
    // In-memory store
    var requests = [];

    // Elements
    var requestsTbody = document.getElementById('requestsTbody');
    var emptyState = document.getElementById('emptyState');
    var searchInput = document.getElementById('search');
    var filterStatus = document.getElementById('filterStatus');
    var btnExport = document.getElementById('btnExport');
    var btnAdd = document.getElementById('btnAdd');
    var modal = document.getElementById('modal');
    var modalTitle = document.getElementById('modalTitle');
    var modalClose = document.getElementById('modalClose');
    var cancelBtn = document.getElementById('cancelBtn');
    var requestForm = document.getElementById('requestForm');
    var fullNameInput = document.getElementById('fullName');
    var contactInput = document.getElementById('contact');
    var affInput = document.getElementById('affiliation');
    var visitDateInput = document.getElementById('visitDate');
    var visitTimeInput = document.getElementById('visitTime');
    var toVisitInput = document.getElementById('toVisit');
    var purposeInput = document.getElementById('purpose');
    var rowTemplate = document.getElementById('rowTemplate');

    var editingId = null;

    function generatePassId() {
      var now = Date.now();
      var rand = Math.floor(Math.random() * 900 + 100); // 3-digit random
      return 'PASS-' + now.toString().slice(-6) + '-' + rand;
    }

    function render() {
      var q = searchInput.value.trim().toLowerCase();
      var statusFilter = filterStatus.value;

      var filtered = requests.filter(function(r){
        var text = (r.name + ' ' + r.aff + ' ' + r.toVisit + ' ' + r.purpose + ' ' + (r.date || '')).toLowerCase();
        var matchesQ = text.indexOf(q) !== -1;
        var matchesStatus = (statusFilter === 'all') ? true : r.status === statusFilter;
        return matchesQ && matchesStatus;
      });

      requestsTbody.innerHTML = '';
      if (filtered.length === 0) {
        emptyState.classList.remove('hidden');
      } else {
        emptyState.classList.add('hidden');
      }

      filtered.forEach(function(r){
        var node = rowTemplate.content.cloneNode(true);
        node.querySelector('.nameCell').textContent = r.name;
        node.querySelector('.affCell').textContent = r.aff || '-';
        node.querySelector('.dateCell').textContent = (r.date || '-') + ' ' + (r.time || '');
        node.querySelector('.toVisitCell').textContent = r.toVisit;
        var statusEl = node.querySelector('.statusCell');
        statusEl.textContent = r.status.toUpperCase();
        if (r.status === 'approved') {
          statusEl.classList.add('bg-emerald-100','text-emerald-700','px-2','py-1','rounded-full','inline-block');
        } else if (r.status === 'denied') {
          statusEl.classList.add('bg-rose-100','text-rose-700','px-2','py-1','rounded-full','inline-block');
        } else {
          statusEl.classList.add('bg-amber-100','text-amber-700','px-2','py-1','rounded-full','inline-block');
        }

        var btnApprove = node.querySelector('.btnApprove');
        var btnDeny = node.querySelector('.btnDeny');
        var btnView = node.querySelector('.btnView');
        var btnEdit = node.querySelector('.btnEdit');
        var btnDelete = node.querySelector('.btnDelete');

        btnApprove.addEventListener('click', function(){
          if (confirm('Approve this pass request?')) {
            r.status = 'approved';
            r.passId = r.passId || generatePassId();
            r.approvedAt = new Date().toISOString();
            render();
            alert('Request approved. Pass ID: ' + r.passId);
          }
        });

        btnDeny.addEventListener('click', function(){
          var reason = prompt('Enter reason for denial (optional):');
          if (confirm('Deny this pass request?')) {
            r.status = 'denied';
            r.deniedReason = reason || '';
            r.deniedAt = new Date().toISOString();
            render();
          }
        });

        btnView.addEventListener('click', function(){
          var text = 'Name: ' + r.name + '\nAffiliation: ' + (r.aff||'-') + '\nContact: ' + (r.contact||'-') + '\nVisit: ' + (r.date||'-') + ' ' + (r.time||'') + '\nTo visit: ' + r.toVisit + '\nPurpose: ' + (r.purpose||'-') + '\nStatus: ' + r.status.toUpperCase();
          if (r.passId) text += '\nPass ID: ' + r.passId;
          if (r.deniedReason) text += '\nDenied Reason: ' + r.deniedReason;
          alert(text);
        });

        btnEdit.addEventListener('click', function(){ openModal('Edit Pass Request', r); });

        btnDelete.addEventListener('click', function(){
          if (confirm('Delete this request?')) {
            requests = requests.filter(function(x){ return x.id !== r.id });
            render();
          }
        });

        requestsTbody.appendChild(node);
      });
    }

    function openModal(mode, r) {
      editingId = r ? r.id : null;
      modalTitle.textContent = mode;
      fullNameInput.value = r ? r.name : '';
      contactInput.value = r ? r.contact : '';
      affInput.value = r ? r.aff : '';
      visitDateInput.value = r ? r.date : '';
      visitTimeInput.value = r ? r.time : '';
      toVisitInput.value = r ? r.toVisit : '';
      purposeInput.value = r ? r.purpose : '';

      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function closeModal() {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      editingId = null;
    }

    btnAdd.addEventListener('click', function(){ openModal('New Pass Request'); });
    modalClose.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', function(e){ e.preventDefault(); closeModal(); });

    requestForm.addEventListener('submit', function(e){
      e.preventDefault();
      var payload = {
        id: editingId || Date.now(),
        name: fullNameInput.value.trim(),
        contact: contactInput.value.trim(),
        aff: affInput.value.trim(),
        date: visitDateInput.value,
        time: visitTimeInput.value,
        toVisit: toVisitInput.value.trim(),
        purpose: purposeInput.value.trim(),
        status: 'pending'
      };

      if (editingId) {
        requests = requests.map(function(item){ return item.id === editingId ? payload : item });
      } else {
        requests.unshift(payload);
      }

      render();
      closeModal();
      requestForm.reset();
    });

    searchInput.addEventListener('input', function(){ render(); });
    filterStatus.addEventListener('change', function(){ render(); });

    btnExport.addEventListener('click', function(){
      if (requests.length === 0) { alert('No requests to export.'); return; }
      var headers = ['Name','Affiliation','Contact','Visit Date','Visit Time','To Visit','Purpose','Status','Pass ID'];
      var rows = requests.map(function(r){
        return [
          '"' + (r.name||'').replace(/"/g,'""') + '"',
          '"' + (r.aff||'').replace(/"/g,'""') + '"',
          '"' + (r.contact||'').replace(/"/g,'""') + '"',
          '"' + (r.date||'') + '"',
          '"' + (r.time||'') + '"',
          '"' + (r.toVisit||'').replace(/"/g,'""') + '"',
          '"' + (r.purpose||'').replace(/"/g,'""') + '"',
          '"' + (r.status||'') + '"',
          '"' + (r.passId||'') + '"'
        ].join(',');
      });
      var csv = headers.join(',') + '\n' + rows.join('\n');
      var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      var url = URL.createObjectURL(blob);
      var a = document.createElement('a');
      a.href = url;
      var now = new Date();
      var stamp = now.getFullYear() + String(now.getMonth()+1).padStart(2,'0') + String(now.getDate()).padStart(2,'0') + '_' + String(now.getHours()).padStart(2,'0') + String(now.getMinutes()).padStart(2,'0');
      a.download = 'pass_requests_' + stamp + '.csv';
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