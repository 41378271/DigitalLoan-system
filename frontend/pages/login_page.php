<!DOCTYPE html>  
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    *{
      box-sizing: border-box;
    }

    body{
      margin: 0;
      min-height: 100vh;
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #2415ca, #0d6efd);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }

    .login-wrapper{
      width: 100%;
      max-width: 1000px;
    }

    .login-card{
      border: none;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 20px 50px rgba(0,0,0,0.18);
      background: #fff;
    }

    .left-panel{
      background: linear-gradient(160deg, #1b0fa3, #0d6efd);
      color: #fff;
      padding: 48px 38px;
      min-height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .logo-box{
      width: 72px;
      height: 72px;
      border-radius: 18px;
      background: rgba(255,255,255,0.16);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      font-weight: bold;
      margin-bottom: 22px;
    }

    .left-panel h1{
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 12px;
    }

    .left-panel p{
      color: rgba(255,255,255,0.92);
      font-size: 1rem;
      line-height: 1.6;
      margin-bottom: 24px;
    }

    .feature-list{
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .feature-list li{
      margin-bottom: 14px;
      padding-left: 26px;
      position: relative;
    }

    .feature-list li::before{
      content: "✓";
      position: absolute;
      left: 0;
      top: 0;
      font-weight: bold;
    }

    .right-panel{
      padding: 48px 38px;
      background: #fff;
    }

    .form-title{
      font-size: 2rem;
      font-weight: 700;
      color: #111827;
      margin-bottom: 8px;
    }

    .form-subtitle{
      color: #6b7280;
      margin-bottom: 28px;
    }

    .form-label{
      font-weight: 600;
      color: #374151;
      margin-bottom: 8px;
    }

    .form-control{
      min-height: 52px;
      border-radius: 14px;
      border: 1px solid #d1d5db;
      box-shadow: none;
      font-size: 15px;
    }

    .form-control:focus{
      border-color: #2415ca;
      box-shadow: 0 0 0 0.2rem rgba(36,21,202,0.12);
    }

    .btn-login{
      min-height: 52px;
      border-radius: 14px;
      background: linear-gradient(135deg, #2415ca, #0d6efd);
      border: none;
      font-weight: 600;
      font-size: 16px;
    }

    .btn-login:hover{
      opacity: 0.96;
    }

    #msg{
      margin-top: 16px;
      padding: 12px 14px;
      border-radius: 12px;
      display: none;
      font-size: 14px;
    }

    #msg.error{
      display: block;
      background: #fff1f2;
      color: #b42318;
      border: 1px solid #f1b0b7;
    }

    #msg.success{
      display: block;
      background: #ecfdf3;
      color: #027a48;
      border: 1px solid #a6f4c5;
    }

    #msg.loading{
      display: block;
      background: #eff6ff;
      color: #1d4ed8;
      border: 1px solid #bfdbfe;
    }

    .register-link{
      margin-top: 22px;
      display: block;
      text-align: center;
      color: #4b5563;
      text-decoration: none;
    }

    .register-link a{
      text-decoration: none;
      font-weight: 600;
    }

    .register-link a:hover{
      text-decoration: underline;
    }

    @media (max-width: 991.98px){
      .left-panel{
        display: none;
      }

      .right-panel{
        padding: 34px 24px;
      }

      .form-title{
        font-size: 1.7rem;
      }
    }
  </style>
</head>
<body>

  <div class="login-wrapper">
    <div class="card login-card">
      <div class="row g-0">
        <div class="col-lg-6">
          <div class="left-panel">
            <div class="logo-box">DL</div>
            <h1>Digital Loan System</h1>
            <p>
              Secure access for borrowers and administrators. Sign in to manage loans,
              KYC, wallet activity, and notifications from one place.
            </p>

            <ul class="feature-list">
              <li>Fast loan application access</li>
              <li>Easy KYC status tracking</li>
              <li>Wallet and notifications in one dashboard</li>
              <li>Secure admin and borrower login</li>
            </ul>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="right-panel">
            <div class="form-title">Welcome back</div>
            <div class="form-subtitle">Login with your phone number or email address.</div>

            <form id="loginForm">
              <div class="mb-3">
                <label class="form-label">Phone or Email</label>
                <input
                  name="identifier"
                  class="form-control"
                  placeholder="Enter phone number or email"
                  required
                >
              </div>

              <div class="mb-3">
                <label class="form-label">Password</label>
                <input
                  name="password"
                  type="password"
                  class="form-control"
                  placeholder="Enter your password"
                  required
                >
              </div>

              <button type="submit" class="btn btn-primary btn-login w-100">
                Login
              </button>
            </form>

            <p id="msg"></p>

            <div class="register-link">
              Don’t have an account?
              <a href="register_page.php">Create account</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.getElementById("loginForm").addEventListener("submit", async function(e){
      e.preventDefault();

      const msg = document.getElementById("msg");
      msg.className = "loading";
      msg.style.display = "block";
      msg.innerText = "Logging in...";

      const formData = new FormData(this);

      try {
        const res = await fetch("/digital-loan-system/backend/api/auth/login.php", {
          method: "POST",
          body: formData
        });

        const data = await res.json();

        console.log("LOGIN RESPONSE:", data);

        msg.innerText = data.message;

        if (data.success){
          msg.className = "success";

          if (data.role === "admin"){
            window.location.href = "admin_dashboard.php";
          } else {
            window.location.href = "borrower_dashboard.php";
          }

        } else {
          msg.className = "error";

          if (data.message === "User not found"){
            msg.innerHTML =
              "User not found.<br><br>" +
              "<a href='register_page.php'>Click here to create account</a>";
          }
        }

      } catch(err){
        console.error(err);
        msg.innerText = "Login failed. Check console.";
        msg.className = "error";
      }
    });
  </script>

  <?php include "../partials/chatbot_widget.php"; ?>

</body>
</html>