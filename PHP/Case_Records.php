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
  <title>TNVS — Case Records Submodule</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-slate-800">
  <div class="max-w-7xl mx-auto p-6">
    <header class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-semibold">TNVS — Case Records Submodule</h1>
      <div class="flex gap-2 items-center">
        <input id="search" type="search" placeholder="Search case records..." class="border rounded-md px-3 py-2 w-64" />
        <button id="btnAdd" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow hover:bg-indigo-500">Add Case</button>
      </div>
    </header>

    <!-- Case filters -->
    <nav class="mb-4">
      <div class="flex gap-2">
        <button data-status="all" class="statusBtn px-3 py-1 rounded-md bg-white border">All</button>
        <button data-status="open" class="statusBtn px-3 py-1 rounded-md bg-white border">Open</button>
        <button data-status="resolved" class="statusBtn px-3 py-1 rounded-md bg-white border">Resolved</button>
        <button data-status="pending" class="statusBtn px-3 py-1 rounded-md bg-white border">Pending</button>
      </div>
    </nav>

    <!-- Case records grid -->
    <section id="caseGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    </section>

    <!-- Empty state -->
    <div id="emptyState" class="hidden text-center py-10 text-slate-500">
      No case records found. Click "Add Case" to create one.
    </div>

    <!-- Modal (Add / Edit) -->
    <div id="modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4">
      <div class="bg-white rounded-xl w-full max-w-2xl shadow-lg p-6">
        <header class="flex items-center justify-between mb-4">
          <h2 id="modalTitle" class="text-lg font-medium">Add Case Record</h2>
          <button id="modalClose" class="text-slate-500 hover:text-slate-700">✕</button>
        </header>

        <form id="caseForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Case Title</label>
            <input id="caseTitle" required class="w-full border rounded-md px-3 py-2" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Complainant</label>
            <input id="complainant" required class="w-full border rounded-md px-3 py-2" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Respondent</label>
            <input id="respondent" required class="w-full border rounded-md px-3 py-2" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Status</label>
            <select id="status" class="w-full border rounded-md px-3 py-2">
              <option value="open">Open</option>
              <option value="pending">Pending</option>
              <option value="resolved">Resolved</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Details</label>
            <textarea id="details" rows="6" class="w-full border rounded-md px-3 py-2"></textarea>
          </div>

          <div class="flex justify-end gap-2">
            <button type="button" id="cancelBtn" class="px-4 py-2 rounded-md border">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white">Save Case</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <template id="caseTemplate">
    <article class="caseCard bg-white rounded-xl p-4 shadow hover:shadow-md transition">
      <div class="flex items-start justify-between">
        <div>
          <h3 class="caseTitle text-lg font-semibold"></h3>
          <p class="caseStatus text-xs mt-1 rounded-full px-2 py-1 inline-block"></p>
        </div>
        <div class="flex gap-2">
          <button class="btnView text-sm px-2 py-1 border rounded">View</button>
          <button class="btnEdit text-sm px-2 py-1 border rounded">Edit</button>
          <button class="btnDelete text-sm px-2 py-1 border rounded text-red-600">Delete</button>
        </div>
      </div>
      <p class="caseParties text-sm mt-2 text-slate-600"></p>
    </article>
  </template>

  <script>
    var cases = [
      {
        id: Date.now() + 1,
        title: 'Complaint about Driver Behavior',
        complainant: 'Student A',
        respondent: 'Driver X',
        status: 'open',
        details: 'Driver was reportedly rude during the trip.'
      },
      {
        id: Date.now() + 2,
        title: 'Vehicle Safety Concern',
        complainant: 'Student B',
        respondent: 'Driver Y',
        status: 'pending',
        details: 'Air conditioning not working properly, needs review.'
      }
    ];

    var caseGrid = document.getElementById('caseGrid');
    var emptyState = document.getElementById('emptyState');
    var searchInput = document.getElementById('search');
    var modal = document.getElementById('modal');
    var modalTitle = document.getElementById('modalTitle');
    var btnAdd = document.getElementById('btnAdd');
    var modalClose = document.getElementById('modalClose');
    var cancelBtn = document.getElementById('cancelBtn');
    var caseForm = document.getElementById('caseForm');
    var caseTitleInput = document.getElementById('caseTitle');
    var complainantInput = document.getElementById('complainant');
    var respondentInput = document.getElementById('respondent');
    var statusInput = document.getElementById('status');
    var detailsInput = document.getElementById('details');
    var caseTemplate = document.getElementById('caseTemplate');

    var editingId = null;
    var activeStatusFilter = 'all';

    function render() {
      var q = searchInput.value.trim().toLowerCase();
      var filtered = cases.filter(function(c) {
        var matchesQ = (c.title + ' ' + c.complainant + ' ' + c.respondent + ' ' + c.details).toLowerCase().indexOf(q) !== -1;
        var matchesStatus = activeStatusFilter === 'all' ? true : c.status === activeStatusFilter;
        return matchesQ && matchesStatus;
      });

      caseGrid.innerHTML = '';
      if (filtered.length === 0) {
        emptyState.classList.remove('hidden');
      } else {
        emptyState.classList.add('hidden');
      }

      filtered.forEach(function(c) {
        var node = caseTemplate.content.cloneNode(true);
        node.querySelector('.caseTitle').textContent = c.title;
        var statusEl = node.querySelector('.caseStatus');
        statusEl.textContent = c.status.toUpperCase();
        if (c.status === 'open') {
          statusEl.classList.add('bg-green-100','text-green-700');
        } else if (c.status === 'pending') {
          statusEl.classList.add('bg-amber-100','text-amber-700');
        } else {
          statusEl.classList.add('bg-sky-100','text-sky-700');
        }
        node.querySelector('.caseParties').textContent = 'Complainant: ' + c.complainant + ' | Respondent: ' + c.respondent;

        node.querySelector('.btnView').addEventListener('click', function() {
          alert(c.title + '\nStatus: ' + c.status + '\nComplainant: ' + c.complainant + '\nRespondent: ' + c.respondent + '\n\n' + c.details);
        });

        node.querySelector('.btnEdit').addEventListener('click', function() {
          openModal('Edit Case Record', c);
        });

        node.querySelector('.btnDelete').addEventListener('click', function() {
          if (confirm('Delete this case record?')) {
            cases = cases.filter(function(x){ return x.id !== c.id });
            render();
          }
        });

        caseGrid.appendChild(node);
      });
    }

    function openModal(mode, c) {
      editingId = c ? c.id : null;
      modalTitle.textContent = mode;
      caseTitleInput.value = c ? c.title : '';
      complainantInput.value = c ? c.complainant : '';
      respondentInput.value = c ? c.respondent : '';
      statusInput.value = c ? c.status : 'open';
      detailsInput.value = c ? c.details : '';
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function closeModal() {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      editingId = null;
    }

    btnAdd.addEventListener('click', function(){ openModal('Add Case Record'); });
    modalClose.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', function(e){ e.preventDefault(); closeModal(); });

    caseForm.addEventListener('submit', function(e){
      e.preventDefault();
      var payload = {
        id: editingId || Date.now(),
        title: caseTitleInput.value.trim(),
        complainant: complainantInput.value.trim(),
        respondent: respondentInput.value.trim(),
        status: statusInput.value,
        details: detailsInput.value.trim(),
      };

      if (editingId) {
        cases = cases.map(function(c){ return c.id === editingId ? payload : c });
      } else {
        cases.unshift(payload);
      }

      render();
      closeModal();
      caseForm.reset();
    });

    searchInput.addEventListener('input', function(){ render(); });

    var statusBtns = document.querySelectorAll('.statusBtn');
    statusBtns.forEach(function(btn){
      btn.addEventListener('click', function(){
        statusBtns.forEach(function(b){ b.classList.remove('bg-indigo-50','border-indigo-300'); });
        btn.classList.add('bg-indigo-50','border-indigo-300');
        activeStatusFilter = btn.getAttribute('data-status');
        render();
      });
    });

    document.querySelector('.statusBtn[data-status="all"]').classList.add('bg-indigo-50','border-indigo-300');
    render();
  </script>
</body>
</html>
        </main>
    </section>



    <script src="../JS/script.js"></script>
</body>
</html>