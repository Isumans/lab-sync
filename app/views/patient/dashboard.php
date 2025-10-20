<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LabSync - Home</title>
  <link rel="stylesheet" href="/lab_sync/public/css/patient.css" />
</head>
<body>
  <?php require 'C:\xampp\htdocs\lab_sync\public\partials\header.php'; ?>
<main class="container">
  <h2 class="page-title">Good morning, John!</h2>

  <div class="dash-grid">
    <div class="card">
      <div class="card-head">
        <h3>Next Appointment</h3>
        <span id="nextStatus" class="pill pill-pending">Pending</span>
      </div>
      <p id="nextInfo" class="muted">No appointment yet.</p>
      <div class="row">
        <button class="btn-outline" onclick="openRescheduleFromNext()">Reschedule</button>
        <button class="btn-outline" onclick="cancelFromNext()">Cancel</button>
        <a class="btn-ghost" target="_blank" href="https://maps.google.com">Directions</a>
      </div>
    </div>

    <aside class="card">
      <h3>Quick Actions</h3>
      <div class="list">
        <a class="btn-primary" href="/explore.php">Book Test</a>
        <a class="btn-outline" href="/results.php">View Results</a>
      </div>
    </aside>
  </div>

  <div class="card" style="margin-top:16px">
    <div class="toolbar">
      <h3>Your Appointments</h3>
      <input id="q" class="input input-sm" placeholder="Search…" oninput="renderAppointments()">
    </div>
    <div class="row" style="margin-bottom:10px">
      <a class="btn-primary" href="/explore.php">+ New Appointment</a>
    </div>
    <div id="list" class="list"></div>
    <div id="empty" class="muted empty">
      No appointments yet — <a href="/explore.php">Book your first test</a>.
    </div>
  </div>
</main>

<!-- Edit/Reschedule Modal -->
<div id="editModal" class="modal">
  <div class="modal-sheet small">
    <div class="modal-head">
      <h3>Edit / Reschedule</h3>
      <button class="modal-x" onclick="closeEdit()">×</button>
    </div>
    <div class="modal-body">
      <label class="label">New Date</label>
      <input id="editDate" type="date" class="input">
      <label class="label" style="margin-top:8px">New Time</label>
      <select id="editTime" class="input">
        <option>08:00</option><option>08:30</option><option>09:00</option><option>09:30</option>
        <option>10:00</option><option>10:30</option><option>11:00</option>
        <option>13:00</option><option>13:30</option><option>14:00</option><option>14:30</option><option>15:00</option>
      </select>
    </div>
    <div class="modal-foot">
      <button class="btn-outline" onclick="closeEdit()">Close</button>
      <button class="btn-primary" onclick="saveEdit()">Save</button>
    </div>
  </div>
</div>

<script>
// same tiny CRUD as before (UI only)
const KEY='labsync_appointments';
const getAll=()=>JSON.parse(localStorage.getItem(KEY)||'[]');
const putAll=(arr)=>localStorage.setItem(KEY,JSON.stringify(arr));
let editingId=null;

function renderAppointments(){
  const q=document.getElementById('q').value.toLowerCase();
  const listEl=document.getElementById('list'); listEl.innerHTML='';
  const appts=getAll()
    .filter(a=>`${a.test} ${a.date} ${a.time}`.toLowerCase().includes(q))
    .sort((a,b)=>(a.date+a.time).localeCompare(b.date+b.time));
  document.getElementById('empty').style.display = appts.length?'none':'block';

  appts.forEach(a=>{
    const row=document.createElement('div'); row.className='row-card';
    row.innerHTML=`
      <div><strong>${a.test}</strong><div class="muted">${a.date} • ${a.time}</div></div>
      <div class="row">
        <span class="pill ${a.status==='Pending'?'pill-pending':'pill-ok'}">${a.status}</span>
        <button class="btn-outline" onclick="openEdit('${a.id}')">Edit</button>
        <button class="btn-outline" onclick="cancel('${a.id}')">Cancel</button>
        <button class="btn-outline" onclick="del('${a.id}')">Delete</button>
      </div>`;
    listEl.appendChild(row);
  });

  const next=appts[0];
  const nextInfo=document.getElementById('nextInfo');
  const nextStatus=document.getElementById('nextStatus');
  if(next){ nextInfo.textContent=`${next.test} — ${next.date} at ${next.time}`;
            nextStatus.textContent=next.status;
            nextStatus.className='pill ' + (next.status==='Pending'?'pill-pending':'pill-ok'); }
  else   { nextInfo.textContent='No appointment yet.'; nextStatus.textContent='Pending'; nextStatus.className='pill pill-pending'; }
}

function openEdit(id){ editingId=id; const a=getAll().find(x=>x.id===id);
  editDate.value=a.date; editTime.value=a.time; document.getElementById('editModal').classList.add('open'); }
function closeEdit(){ document.getElementById('editModal').classList.remove('open'); }
function saveEdit(){
  putAll(getAll().map(a=>a.id===editingId?({...a,date:editDate.value,time:editTime.value,status:'Confirmed'}):a));
  closeEdit(); renderAppointments();
}
function cancel(id){ if(!confirm('Cancel this appointment?'))return;
  putAll(getAll().map(a=>a.id===id?({...a,status:'Cancelled'}):a)); renderAppointments(); }
function del(id){ if(!confirm('Delete this appointment?'))return;
  putAll(getAll().filter(a=>a.id!==id)); renderAppointments(); }
function openRescheduleFromNext(){ const a=getAll()[0]; if(!a) return alert('No appointment to reschedule.'); openEdit(a.id); }
function cancelFromNext(){ const a=getAll()[0]; if(!a) return alert('No appointment to cancel.'); cancel(a.id); }

renderAppointments();
</script>
<?php require 'C:\xampp\htdocs\lab_sync\public\partials\footer.php'; ?>
</body>
</html>
