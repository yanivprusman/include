<?php include_once ("../include/g_h_projects_include.php");?>
<!DOCTYPE html>
<html>

<head>
  <title>Login Page</title>
    <link rel="stylesheet"  href="../include/g_h_projects_css.css">    
    <!-- <link rel="stylesheet"  href="../include/style.css">     -->
</head>

<body>
  <div class="container">
    <h1>Login</h1>
    <form id="loginForm">
      <input type="text" placeholder="Username" id="usernameInput">
      <input type="password" placeholder="Password" id="passwordInput">
      <button type="submit">Login</button>
    </form>
    <p id="errorText" class="error"></p>
  </div>
  <script>
    document.getElementById("loginForm").addEventListener("submit", function(event) {
    event.preventDefault();
    const username = document.getElementById("usernameInput").value;
    const password = document.getElementById("passwordInput").value;
    
    // Perform login validation
    if (username === "user" && password === "password") {
      // Successful login
      document.getElementById("errorText").textContent = "";
      // alert("Login successful!");
      // Redirect to dashboard or perform other actions here
    } else {
      // Failed login
      document.getElementById("errorText").textContent = "Invalid username or password";
    }
  });
</script>
</body>

</html>
