<?php
session_start(); // Added session start for consistency

include 'db_connect.php';

// Handle AJAX CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $id = $_POST['id'] ?? null;
        $title = $_POST['title'];
        $complainant = $_POST['complainant'];
        $respondent = $_POST['respondent'];
        $status = $_POST['status'];
        $details = $_POST['details'];

        if ($action === 'add') {
            $stmt = $conn->prepare("INSERT INTO case_records (title, complainant, respondent, status, details) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $title, $complainant, $respondent, $status, $details);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("UPDATE case_records SET title=?, complainant=?, respondent=?, status=?, details=? WHERE id=?");
            $stmt->bind_param("sssssi", $title, $complainant, $respondent, $status, $details, $id);
            $stmt->execute();
        }
        echo json_encode(["success" => true]);
        exit;
    }

    if ($action === 'delete') {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM case_records WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(["success" => true]);
        exit;
    }
}

// Fetch all case records for display
$result = $conn->query("SELECT * FROM case_records ORDER BY id DESC");
$records = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
$records_json = json_encode($records);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../CSS/index.css">
    <title>Case Records | TNVS Dashboard</title>
    
    <?php include 'partials/styles.php'; ?>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
    @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    .animate-fadeIn { animation: fadeIn 0.25s ease-out; }
    #content main {
        background-color: transparent; /* Ensures main background is correct */
    }
    </style>
</head>
<body class="bg-gray-100 flex h-screen overflow-hidden">
    
    <?php include 'partials/sidebar.php'; ?>

    <section id="content">
        <?php include 'partials/header.php'; ?>

        <main>
      

            <div class="max-w-7xl mx-auto p-6">
                <header class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-semibold">Legal Case Records</h1>
                    <button id="btnAdd" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow hover:bg-indigo-500">Add Case</button>
                </header>

                <div class="mb-6 flex space-x-2">
                    <button data-status="all" class="statusBtn px-4 py-2 border rounded-full text-sm bg-indigo-50 border-indigo-300">All</button>
                    <button data-status="Open" class="statusBtn px-4 py-2 border rounded-full text-sm">Open</button>
                    <button data-status="Closed" class="statusBtn px-4 py-2 border rounded-full text-sm">Closed</button>
                    <button data-status="In Progress" class="statusBtn px-4 py-2 border rounded-full text-sm">In Progress</button>
                    <input type="text" id="searchInput" placeholder="Search cases..." class="flex-grow max-w-xs border rounded-full px-4 py-2 text-sm ml-4">
                </div>

                <div id="casesGrid" class="space-y-4">
                    </div>
                
                <div id="emptyState" class="hidden text-center py-10 text-slate-500">No matching case records found.</div>
            </div>
        
            <template id="cardTemplate">
                <article class="bg-white rounded-xl p-4 shadow hover:shadow-md transition">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="caseTitle text-lg font-semibold"></h3>
                            <p class="caseComplainant text-sm text-slate-600 mt-1"></p>
                            <p class="caseRespondent text-sm text-slate-600"></p>
                        </div>
                        <div class="flex gap-2 items-center">
                            <span class="caseStatus text-xs font-medium rounded-full px-2 py-1 inline-block"></span>
                            <button class="btnView text-sm px-2 py-1 border rounded">View</button>
                            <button class="btnEdit text-sm px-2 py-1 border rounded">Edit</button>
                            <button class="btnDelete text-sm px-2 py-1 border rounded text-red-600">Delete</button>
                        </div>
                    </div>
                </article>
            </template>


            <div id="modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4 z-[999]">
                <div class="bg-white rounded-xl w-full max-w-2xl shadow-lg p-6 animate-fadeIn">
                    <header class="flex items-center justify-between mb-4">
                        <h2 id="modalTitle" class="text-lg font-medium">Add Case</h2>
                        <button id="modalClose" class="text-slate-500 hover:text-slate-700">✕</button>
                    </header>

                    <form id="caseForm" class="space-y-4">
                        <input type="hidden" name="action" id="actionInput" value="add">
                        <input type="hidden" name="id" id="caseId">
                        
                        <div>
                            <label class="block text-sm font-medium mb-1">Case Title</label>
                            <input id="titleInput" name="title" required class="w-full border rounded-md px-3 py-2" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Complainant</label>
                                <input id="complainantInput" name="complainant" required class="w-full border rounded-md px-3 py-2" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Respondent</label>
                                <input id="respondentInput" name="respondent" required class="w-full border rounded-md px-3 py-2" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Status</label>
                            <select id="statusInput" name="status" class="w-full border rounded-md px-3 py-2">
                                <option value="Open">Open</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Details</label>
                            <textarea id="detailsInput" name="details" rows="6" class="w-full border rounded-md px-3 py-2"></textarea>
                        </div>
                        
                        <div class="flex justify-end gap-2">
                            <button type="button" id="cancelBtn" class="px-4 py-2 rounded-md border">Cancel</button>
                            <button type="submit" class="px-4 py-2 rounded-md bg-indigo-600 text-white">Save Record</button>
                        </div>
                    </form>
                </div>
            </div>


            <script>
                const cases = <?= $records_json ?>; // Load PHP data into JS variable

                const modal = document.getElementById('modal');
                const btnAdd = document.getElementById('btnAdd');
                const modalClose = document.getElementById('modalClose');
                const cancelBtn = document.getElementById('cancelBtn');
                const modalTitle = document.getElementById('modalTitle');
                const caseForm = document.getElementById('caseForm');
                const casesGrid = document.getElementById('casesGrid');
                const cardTemplate = document.getElementById('cardTemplate');
                const emptyState = document.getElementById('emptyState');
                const searchInput = document.getElementById('searchInput');

                const caseId = document.getElementById('caseId');
                const titleInput = document.getElementById('titleInput');
                const complainantInput = document.getElementById('complainantInput');
                const respondentInput = document.getElementById('respondentInput');
                const statusInput = document.getElementById('statusInput');
                const detailsInput = document.getElementById('detailsInput');
                const actionInput = document.getElementById('actionInput');

                var editingId = null;
                var activeStatusFilter = 'all';

                function render() {
                    var q = searchInput.value.trim().toLowerCase();
                    var filtered = cases.filter(function(c) {
                        var matchesQ = (c.title + ' ' + c.complainant + ' ' + c.respondent + ' ' + c.details).toLowerCase().indexOf(q) !== -1;
                        var matchesStatus = activeStatusFilter === 'all' ? true : c.status === activeStatusFilter;
                        return matchesQ && matchesStatus;
                    });

                    casesGrid.innerHTML = '';

                    if (filtered.length === 0) {
                        emptyState.classList.remove('hidden');
                    } else {
                        emptyState.classList.add('hidden');
                    }

                    filtered.forEach(function(c) {
                        var node = cardTemplate.content.cloneNode(true);
                        node.querySelector('.caseTitle').textContent = c.title;
                        node.querySelector('.caseComplainant').textContent = 'Complainant: ' + c.complainant;
                        node.querySelector('.caseRespondent').textContent = 'Respondent: ' + c.respondent;
                        
                        var statusEl = node.querySelector('.caseStatus');
                        statusEl.textContent = c.status.toUpperCase();
                        statusEl.className = 'caseStatus text-xs font-medium rounded-full px-2 py-1 inline-block';
                        
                        if (c.status === 'Open') {
                            statusEl.classList.add('bg-green-100', 'text-green-700');
                        } else if (c.status === 'In Progress') {
                            statusEl.classList.add('bg-amber-100', 'text-amber-700');
                        } else { // Closed
                            statusEl.classList.add('bg-gray-100', 'text-gray-700');
                        }

                        node.querySelector('.btnView').addEventListener('click', function() {
                            alert('Case Title: ' + c.title + '\nComplainant: ' + c.complainant + '\nRespondent: ' + c.respondent + '\nStatus: ' + c.status + '\n\nDetails: ' + c.details);
                        });

                        node.querySelector('.btnEdit').addEventListener('click', function() {
                            openModal('Edit Case', c);
                        });

                        node.querySelector('.btnDelete').addEventListener('click', function() {
                            if (confirm('Delete this case record? This action cannot be undone.')) {
                                fetch("", {
                                    method: "POST",
                                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                                    body: new URLSearchParams({ action: 'delete', id: c.id })
                                }).then(r => r.json()).then(res => {
                                    if(res.success){
                                        // Remove from local array and re-render
                                        const index = cases.findIndex(item => item.id === c.id);
                                        if (index > -1) {
                                            cases.splice(index, 1);
                                        }
                                        render();
                                    } else {
                                        alert('Failed to delete record.');
                                    }
                                });
                            }
                        });

                        casesGrid.appendChild(node);
                    });
                }

                function openModal(mode, c) {
                    editingId = c ? c.id : null;
                    modalTitle.textContent = mode;
                    actionInput.value = c ? 'edit' : 'add';
                    caseId.value = c ? c.id : '';
                    
                    titleInput.value = c ? c.title : '';
                    complainantInput.value = c ? c.complainant : '';
                    respondentInput.value = c ? c.respondent : '';
                    statusInput.value = c ? c.status : 'Open';
                    detailsInput.value = c ? c.details : '';
                    
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }

                function closeModal() {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    editingId = null;
                    caseForm.reset();
                }

                btnAdd.addEventListener('click', function(){ openModal('Add Case'); });
                modalClose.addEventListener('click', closeModal);
                cancelBtn.addEventListener('click', function(e){ e.preventDefault(); closeModal(); });

                caseForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    var payload = {
                        action: actionInput.value,
                        id: caseId.value,
                        title: titleInput.value.trim(),
                        complainant: complainantInput.value.trim(),
                        respondent: respondentInput.value.trim(),
                        status: statusInput.value,
                        details: detailsInput.value.trim(),
                    };

                    fetch("", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: new URLSearchParams(payload)
                    }).then(r => r.json()).then(res => {
                        if(res.success){
                            if(actionInput.value === 'edit'){
                                // Update local array
                                const index = cases.findIndex(item => item.id == payload.id);
                                if (index > -1) {
                                    cases[index] = { ...cases[index], ...payload };
                                }
                            } else {
                                // Add to local array (needs a real ID, but this is client-side only before reload)
                                payload.id = Date.now(); // fake id until reload
                                cases.unshift(payload);
                            }
                            render();
                            closeModal();
                            caseForm.reset();
                            // Optional: Reload window to get server-side generated ID and fresh data
                            // window.location.reload(); 
                        } else {
                            alert('An error occurred while saving the record.');
                        }
                    });
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
        </main>
    </section>

    <script src="../JS/script.js"></script>
</body>
</html>