<?php 
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login_page.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body{
      background: #f5f7fb;
      font-family: Arial, sans-serif;
    }

    #splashScreen{
      position: fixed;
      inset: 0;
      background: linear-gradient(135deg, #0d6efd, #084298);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      color: #fff;
      z-index: 99999;
    }

    .splash-logo{
      width: 90px;
      height: 90px;
      border-radius: 50%;
      background: #fff;
      color: #0d6efd;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 34px;
      font-weight: bold;
      margin-bottom: 18px;
      box-shadow: 0 10px 24px rgba(0,0,0,0.2);
      animation: pulse 1.2s infinite;
    }

    .loader{
      width: 42px;
      height: 42px;
      border: 4px solid rgba(255,255,255,0.3);
      border-top: 4px solid #fff;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin-top: 18px;
    }

    @keyframes spin { 100% { transform: rotate(360deg); } }
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.08); }
      100% { transform: scale(1); }
    }

    #pageContent{ display:none; }

    .sidebar{
      min-height: 100vh;
      background: #111827;
      color: #fff;
      padding: 24px 16px;
    }

    .sidebar a{
      display: block;
      color: #d1d5db;
      text-decoration: none;
      padding: 12px 14px;
      border-radius: 10px;
      margin-bottom: 8px;
    }

    .sidebar a:hover,
    .sidebar a.active{
      background: #1f2937;
      color: #fff;
    }

    .top-card{
      border: none;
      border-radius: 18px;
      box-shadow: 0 8px 22px rgba(0,0,0,0.06);
    }

    .stat-card{
      border: none;
      border-radius: 16px;
      box-shadow: 0 8px 18px rgba(0,0,0,0.05);
    }
  </style>
</head>
<body>

<div id="splashScreen">
  <div class="splash-logo">A</div>
  <h2 class="fw-bold mb-2">Admin Dashboard</h2>
  <div>Loading admin panel...</div>
  <div class="loader"></div>
</div>

<div id="pageContent">
  <div class="container-fluid">
    <div class="row g-0">
      <div class="col-md-3 col-lg-2 sidebar">
        <h3 class="fw-bold mb-4">Admin Panel</h3>
        <a href="admin_dashboard.php" class="active">Dashboard</a>
        <a href="admin_users.php">Users</a>
        <a href="admin_loans.php">Applications</a>
        <a href="admin_kyc.php">KYC Review</a>
        <a href="admin_reports.php">Reports</a>
        <a href="../../backend/api/auth/logout.php">Logout</a>
      </div>

      <div class="col-md-9 col-lg-10 p-4">
        <div class="card top-card mb-4">
          <div class="card-body p-4">
            <h2 class="fw-bold mb-2">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h2>
            <p class="text-muted mb-0">Manage users, loans, KYC verification, and system reports from one place.</p>
          </div>
        </div>

        <div class="row g-4">
          <div class="col-md-6 col-xl-3">
            <div class="card stat-card">
              <div class="card-body">
                <h6 class="text-muted">Users</h6>
                <h3 class="fw-bold">Manage</h3>
                <a href="admin_users.php" class="btn btn-primary btn-sm mt-2">Open</a>
              </div>
            </div>
          </div>

          <div class="col-md-6 col-xl-3">
            <div class="card stat-card">
              <div class="card-body">
                <h6 class="text-muted">Applications</h6>
                <h3 class="fw-bold">Review</h3>
                <a href="admin_loans.php" class="btn btn-dark btn-sm mt-2">Open</a>
              </div>
            </div>
          </div>

          <div class="col-md-6 col-xl-3">
            <div class="card stat-card">
              <div class="card-body">
                <h6 class="text-muted">KYC Review</h6>
                <h3 class="fw-bold">Verify</h3>
                <a href="admin_kyc.php" class="btn btn-warning btn-sm mt-2">Open</a>
              </div>
            </div>
          </div>

          <div class="col-md-6 col-xl-3">
            <div class="card stat-card">
              <div class="card-body">
                <h6 class="text-muted">Reports</h6>
                <h3 class="fw-bold">Analyze</h3>
                <a href="admin_reports.php" class="btn btn-success btn-sm mt-2">Open</a>
              </div>
            </div>
          </div>
        </div>

        <?php include "../partials/chatbot_widget.php"; ?>
      </div>
    </div>
  </div>
</div>

<script>
  window.addEventListener("load", function () {
    setTimeout(function () {
      document.getElementById("splashScreen").style.display = "none";
      document.getElementById("pageContent").style.display = "block";
    }, 1200);
  });
</script>

</body>
</html>