<?php 
session_start();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Register</title>

  <style>
    body{font-family:Arial; padding:20px;}

    label{display:block; margin-top:12px;}

    input{
      padding:8px;
      width:320px;
      max-width:100%;
    }

    button{
      margin-top:15px;
      padding:8px 14px;
      cursor:pointer;
    }

    .msg{
      margin-top:12px;
    }

    .error{ color:#b00020; }
    .success{ color:green; }

    a{
      display:inline-block;
      margin-top:12px;
    }
  </style>
</head>
<body>

<h2>Create Account</h2>

<form id="registerForm">

  <label>Full Name:</label>
  <input type="text" name="full_name" required>

  <label>Email (optional if phone provided):</label>
  <input type="email" name="email">

  <label>Phone (optional if email provided):</label>
  <input type="text" name="phone">

  <label>Password:</label>
  <input type="password" name="password" required>

  <button type="submit">Register</button>

</form>

<p id="msg" class="msg"></p>

<a href="login_page.php">Back to Login</a>


<script>
document.getElementById("registerForm").addEventListener("submit", async function(e){

  e.preventDefault();

  const msg = document.getElementById("msg");
  msg.className = "msg";
  msg.innerText = "Creating account...";

  const formData = new FormData(this);

  try {

    const res = await fetch("/digital-loan-system/backend/api/auth/register.php", {
      method: "POST",
      body: formData
    });

    const data = await res.json();

    msg.innerText = data.message;

    if (data.success){

      msg.classList.add("success");

      if (data.role === "admin")
        window.location.href = "admin_dashboard.php";
      else
        window.location.href = "borrower_dashboard.php";

    } else {

      msg.classList.add("error");

    }

  } catch (err) {

    msg.classList.add("error");
    msg.innerText = "Something went wrong. Please try again.";

  }

});
</script>

</body>
</html>