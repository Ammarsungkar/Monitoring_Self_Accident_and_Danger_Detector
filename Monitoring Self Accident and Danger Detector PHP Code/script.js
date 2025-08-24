document.getElementById("login-form").addEventListener("submit", function(event) {
    event.preventDefault();
    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;
    
    // Here you can perform authentication using username and password
    // For simplicity, let's just check if username is "admin" and password is "admin"
    if (username === "admin" && password === "admin") {
      alert("Login successful!");
      // Redirect to homepage
      window.location.href = "homepage.html";
    } else {
      document.getElementById("error-message").innerText = "Invalid username or password. Please try again.";
    }
  });
  




  document.getElementById("signup-form").addEventListener("submit", function(event) {
    event.preventDefault();
    var username = document.getElementById("username").value;
    var email = document.getElementById("email").value;
    var password = document.getElementById("password").value;
    
    // Here you can perform sign-up logic
    // For simplicity, let's just display a success message
    alert("Sign up successful! Welcome, " + username + "!");
    // Redirect to login page
    window.location.href = "signin.html";
  });






  function toggleMenu() {
    const menu = document.querySelector(".menu-links");
    const icon = document.querySelector(".hamburger-icon");
    menu.classList.toggle("open");
    icon.classList.toggle("open");
  }