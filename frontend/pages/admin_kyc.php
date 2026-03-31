<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_page.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>KYC Review</title>
</head>
<body>

<h2>KYC Documents (Admin Review)</h2>

<table border="1" id="kycTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Doc Type</th>
            <th>File</th>
            <th>Status</th>
            <th>Uploaded</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script>
function loadKyc(){
    fetch("../../backend/api/admin/kyc/list.php")
    .then(res => res.json())
    .then(data => {
        const tbody = document.querySelector("#kycTable tbody");
        tbody.innerHTML = "";

        if (!data.success) {
            tbody.innerHTML = "<tr><td colspan='7'>Not authorized or error</td></tr>";
            return;
        }

        data.rows.forEach(r => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${r.id}</td>
                <td>${r.full_name} (${r.email})</td>
                <td>${r.doc_type}</td>
                <td><a href="../../${r.file_path}" target="_blank">View</a></td>
                <td>${r.status}</td>
                <td>${r.uploaded_at}</td>
                <td>
                    <button onclick="updateStatus(${r.id}, 'approved')">Approve</button>
                    <button onclick="updateStatus(${r.id}, 'rejected')">Reject</button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    });
}

function updateStatus(id, status){
    fetch("../../backend/api/admin/kyc/update_status.php", {
        method: "POST",
        headers: {"Content-Type":"application/x-www-form-urlencoded"},
        body: `id=${id}&status=${status}`
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        loadKyc();
    });
}

loadKyc();
</script>
<?php include "../partials/chatbot_widget.php"; ?>
</body>
</html>