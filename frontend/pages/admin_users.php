<?php 
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  header("Location: login_page.php");
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin - Users</title>
  <style>
    body{font-family:Arial;padding:20px;}
    .top{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:12px;}
    input,select,button{padding:8px;}
    table{border-collapse:collapse;width:100%;margin-top:12px;}
    th,td{border:1px solid #ddd;padding:8px;text-align:left;}
    thead th{background:#f3f4f6;}
    .pill{padding:3px 8px;border-radius:999px;font-size:12px;display:inline-block;}
    .active{background:#e7f7ee;color:#0a7a3a;border:1px solid #bfead1;}
    .inactive{background:#fdecec;color:#9b1c1c;border:1px solid #f5bcbc;}
    .btn{border:1px solid #ddd;background:#f8f9fa;cursor:pointer;border-radius:8px;}
    .btn:hover{background:#eef1f4;}
    .danger{border-color:#f1b0b7;background:#fff5f5;}
    .danger:hover{background:#ffecec;}
    .muted{color:#666;font-size:13px;}
  </style>
</head>
<body>

<h2>Users</h2>
<p class="muted">Reactivate / deactivate users using the buttons on the right.</p>

<div class="top">
  <a class="btn" href="admin_dashboard.php" style="text-decoration:none; padding:8px 10px;">⬅ Back to Dashboard</a>

  <input id="q" placeholder="Search name/email/phone..." />
  <select id="only">
    <option value="all">All</option>
    <option value="active">Active only</option>
    <option value="deactivated">Deactivated only</option>
  </select>
  <button class="btn" onclick="loadUsers()">Search</button>
</div>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Full Name</th>
      <th>Email</th>
      <th>Phone</th>
      <th>Role</th>
      <th>Status</th>
      <th>Created</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody id="tbody">
    <tr><td colspan="8">Loading...</td></tr>
  </tbody>
</table>

<script>
async function loadUsers(){
  const q = document.getElementById("q").value.trim();
  const only = document.getElementById("only").value;

  const url = `/digital-loan-system/backend/api/admin/users/list.php?q=${encodeURIComponent(q)}&only=${encodeURIComponent(only)}`;
  const res = await fetch(url);
  const data = await res.json();

  const tbody = document.getElementById("tbody");
  tbody.innerHTML = "";

  if(!data.success){
    tbody.innerHTML = `<tr><td colspan="8">${data.message || "Error"}</td></tr>`;
    return;
  }

  if(!data.users.length){
    tbody.innerHTML = `<tr><td colspan="8">No users found.</td></tr>`;
    return;
  }

  data.users.forEach(u => {
    const isActive = Number(u.is_active) === 1;

    const statusHtml = isActive
      ? `<span class="pill active">Active</span>`
      : `<span class="pill inactive">Deactivated</span>`;

    // hide action buttons for admins (Protected)
    let actionBtn = "";
    if ((u.role || "").toLowerCase() === "admin") {
      actionBtn = `<span style="color:gray;">Protected</span>`;
    } else {
      actionBtn = isActive
        ? `<button class="btn danger" onclick="setActive(${u.id}, 0)">Deactivate</button>`
        : `<button class="btn" onclick="setActive(${u.id}, 1)">Reactivate</button>`;
    }

    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${u.id}</td>
      <td>${escapeHtml(u.full_name || "")}</td>
      <td>${escapeHtml(u.email || "")}</td>
      <td>${escapeHtml(u.phone || "")}</td>
      <td>${escapeHtml(u.role || "")}</td>
      <td>${statusHtml}</td>
      <td>${escapeHtml(u.created_at || "")}</td>
      <td>${actionBtn}</td>
    `;
    tbody.appendChild(tr);
  });
}

async function setActive(userId, isActive){
  const action = isActive ? "reactivate" : "deactivate";
  if(!confirm(`Are you sure you want to ${action} this user?`)) return;

  const fd = new FormData();
  fd.append("user_id", userId);
  fd.append("is_active", isActive);

  const res = await fetch("/digital-loan-system/backend/api/admin/users/set_active.php", {
    method: "POST",
    body: fd
  });

  const data = await res.json();
  alert(data.message || (data.success ? "Done" : "Failed"));

  if(data.success) loadUsers();
}

function escapeHtml(s){
  return String(s)
    .replaceAll("&","&amp;")
    .replaceAll("<","&lt;")
    .replaceAll(">","&gt;")
    .replaceAll('"',"&quot;")
    .replaceAll("'","&#039;");
}

loadUsers();
</script>

</body>
</html>