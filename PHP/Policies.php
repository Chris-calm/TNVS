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
  <title>TNVS — Policies Submodule</title>
  <!-- Tailwind CDN (play-cdn for quick prototyping) -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-slate-800">
  <div class="max-w-7xl mx-auto p-6">
    <header class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-semibold">TNVS — Policies Submodule</h1>
      <div class="flex gap-2 items-center">
        <input id="search" type="search" placeholder="Search policies..." class="border rounded-md px-3 py-2 w-64" />
        <button id="btnAdd" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow hover:bg-indigo-500">Add Policy</button>
      </div>
    </header>

    <!-- Role filters -->
    <nav class="mb-4">
      <div class="flex gap-2">
        <button data-role="all" class="roleBtn px-3 py-1 rounded-md bg-white border">All</button>
        <button data-role="administrative" class="roleBtn px-3 py-1 rounded-md bg-white border">Administrative</button>
        <button data-role="student" class="roleBtn px-3 py-1 rounded-md bg-white border">Student</button>
        <button data-role="admin-only" class="roleBtn px-3 py-1 rounded-md bg-white border">Admin-only</button>
      </div>
    </nav>

    <!-- Policies grid -->
    <section id="policiesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <!-- Policy cards rendered here by JS -->
    </section>

    <!-- Empty state -->
    <div id="emptyState" class="hidden text-center py-10 text-slate-500">
      No policies found. Click "Add Policy" to create one.
    </div>

    <!-- Modal (Add / Edit) -->
    <div id="modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4">
      <div class="bg-white rounded-xl w-full max-w-2xl shadow-lg p-6">
        <header class="flex items-center justify-between mb-4">
          <h2 id="modalTitle" class="text-lg font-medium">Add Policy</h2>
          <button id="modalClose" class="text-slate-500 hover:text-slate-700">✕</button>
        </header>

        <form id="policyForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium mb-1">Title</label>
            <input id="title" required class="w-full border rounded-md px-3 py-2" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Role</label>
            <select id="role" class="w-full border rounded-md px-3 py-2">
              <option value="administrative">Administrative</option>
              <option value="student">Student</option>
              <option value="admin-only">Admin-only</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Short Description</label>
            <input id="short" class="w-full border rounded-md px-3 py-2" />
          </div>

          <div>
            <label class="block text-sm font-medium mb-1">Full Policy (details)</label>
            <textarea id="details" rows="6" class="w-full border rounded-md px-3 py-2"></textarea>
          </div>

          <div class="flex justify-end gap-2">
            <button type="button" id="cancelBtn" class="px-4 py-2 rounded-md border">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white">Save Policy</button>
          </div>
        </form>
      </div>
    </div>

  </div>

  <template id="cardTemplate">
    <article class="policyCard bg-white rounded-xl p-4 shadow hover:shadow-md transition">
      <div class="flex items-start justify-between">
        <div>
          <h3 class="policyTitle text-lg font-semibold"></h3>
          <p class="policyRole text-xs mt-1 rounded-full px-2 py-1 inline-block"></p>
        </div>
        <div class="flex gap-2">
          <button class="btnView text-sm px-2 py-1 border rounded">View</button>
          <button class="btnEdit text-sm px-2 py-1 border rounded">Edit</button>
          <button class="btnDelete text-sm px-2 py-1 border rounded text-red-600">Delete</button>
        </div>
      </div>
      <p class="policyShort text-sm mt-3 text-slate-600"></p>
    </article>
  </template>

  <script>
    // Sample data store (in-memory). Replace with server calls in production.
    var policies = [
      {
        id: Date.now() + 1,
        title: 'Driver Accreditation & Requirements',
        role: 'administrative',
        short: 'Requirements for drivers to be approved on the platform.',
        details: 'Drivers must have a valid professional license, vehicle accreditation, and clear background checks.'
      },
      {
        id: Date.now() + 2,
        title: 'Passenger Conduct',
        role: 'student',
        short: 'Rules students must follow while riding.',
        details: 'Students should be respectful, wear seatbelts, and avoid vandalism.'
      },
      {
        id: Date.now() + 3,
        title: 'Admin Access & Credentialing',
        role: 'admin-only',
        short: 'Internal rules about admin accounts and access control.',
        details: 'Admin accounts must use 2FA. Sharing credentials is prohibited.'
      }
    ];

    var policiesGrid = document.getElementById('policiesGrid');
    var emptyState = document.getElementById('emptyState');
    var searchInput = document.getElementById('search');
    var modal = document.getElementById('modal');
    var modalTitle = document.getElementById('modalTitle');
    var btnAdd = document.getElementById('btnAdd');
    var modalClose = document.getElementById('modalClose');
    var cancelBtn = document.getElementById('cancelBtn');
    var policyForm = document.getElementById('policyForm');
    var titleInput = document.getElementById('title');
    var roleInput = document.getElementById('role');
    var shortInput = document.getElementById('short');
    var detailsInput = document.getElementById('details');
    var cardTemplate = document.getElementById('cardTemplate');

    var editingId = null;
    var activeRoleFilter = 'all';

    function render() {
      var q = searchInput.value.trim().toLowerCase();
      var filtered = policies.filter(function(p) {
        var matchesQ = (p.title + ' ' + p.short + ' ' + p.details).toLowerCase().indexOf(q) !== -1;
        var matchesRole = activeRoleFilter === 'all' ? true : p.role === activeRoleFilter;
        return matchesQ && matchesRole;
      });

      policiesGrid.innerHTML = '';

      if (filtered.length === 0) {
        emptyState.classList.remove('hidden');
      } else {
        emptyState.classList.add('hidden');
      }

      filtered.forEach(function(p) {
        var node = cardTemplate.content.cloneNode(true);
        node.querySelector('.policyTitle').textContent = p.title;
        var roleEl = node.querySelector('.policyRole');
        roleEl.textContent = p.role.replace('-', ' ').toUpperCase();
        if (p.role === 'admin-only') {
          roleEl.classList.add('bg-rose-100','text-rose-700');
        } else if (p.role === 'administrative') {
          roleEl.classList.add('bg-amber-100','text-amber-700');
        } else {
          roleEl.classList.add('bg-sky-100','text-sky-700');
        }
        node.querySelector('.policyShort').textContent = p.short;

        node.querySelector('.btnView').addEventListener('click', function() {
          alert(p.title + '\n\nRole: ' + p.role + '\n\n' + p.details);
        });

        node.querySelector('.btnEdit').addEventListener('click', function() {
          openModal('Edit Policy', p);
        });

        node.querySelector('.btnDelete').addEventListener('click', function() {
          if (confirm('Delete this policy? This action cannot be undone.')) {
            policies = policies.filter(function(x){ return x.id !== p.id });
            render();
          }
        });

        policiesGrid.appendChild(node);
      });
    }

    function openModal(mode, p) {
      editingId = p ? p.id : null;
      modalTitle.textContent = mode;
      titleInput.value = p ? p.title : '';
      roleInput.value = p ? p.role : 'administrative';
      shortInput.value = p ? p.short : '';
      detailsInput.value = p ? p.details : '';
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function closeModal() {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      editingId = null;
    }

    btnAdd.addEventListener('click', function(){ openModal('Add Policy'); });
    modalClose.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', function(e){ e.preventDefault(); closeModal(); });

    policyForm.addEventListener('submit', function(e){
      e.preventDefault();
      var payload = {
        id: editingId || Date.now(),
        title: titleInput.value.trim(),
        role: roleInput.value,
        short: shortInput.value.trim(),
        details: detailsInput.value.trim(),
      };

      if (editingId) {
        policies = policies.map(function(p){ return p.id === editingId ? payload : p });
      } else {
        policies.unshift(payload);
      }

      render();
      closeModal();
      policyForm.reset();
    });

    searchInput.addEventListener('input', function(){ render(); });

    var roleBtns = document.querySelectorAll('.roleBtn');
    roleBtns.forEach(function(btn){
      btn.addEventListener('click', function(){
        roleBtns.forEach(function(b){ b.classList.remove('bg-indigo-50','border-indigo-300'); });
        btn.classList.add('bg-indigo-50','border-indigo-300');
        activeRoleFilter = btn.getAttribute('data-role');
        render();
      });
    });

    document.querySelector('.roleBtn[data-role="all"]').classList.add('bg-indigo-50','border-indigo-300');
    render();
  </script>
</body>
</html>

        </main>
    </section>



    <script src="../JS/script.js"></script>
</body>
</html>