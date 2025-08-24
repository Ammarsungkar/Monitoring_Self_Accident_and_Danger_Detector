<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Emergency Help</title>
    <link rel="stylesheet" href="StylesHP.css" />
    <!-- <link rel="stylesheet" href="MediaStyles.css" /> -->
  </head>
  <body>
    <nav id="desktop-nav">
      <div class="logo">Emergency Help</div>
      <div>
        <ul class="nav-links">
          <li><a href="#about">About</a></li>
          <li><a href="#product">Product</a></li>
          <li><a href="#contact">Contact Us</a></li>
          <li><a href="login.php"><img src="images/profile.png" alt="Profile" class="profile-icon" /></a></li>
        </ul>
    </nav>
    <section id="profile">
      <div class="section__pic-container">
        <img src="LOGOEH.png" alt="" />
      </div>
      <div class="section__text">
        <p class="section__text__p1">Hi, Welcome to</p>
        <h1 class="title">Emergency Help</h1>
        <br />
        <p class="section__text__p2">Have our Product?</p>
        <div class="btn-container">
          <button class="btn btn-color-2" onclick="redirectToLogin()">
          Yes
        </button>
          <button class="btn btn-color-1" onclick="location.href='#product'">
            No 
          </button>
        </div>
      </div>
    </section>
    <br />
    <section id="about">
      <p class="section__text__p1">Get To Know More</p>
      <h1 class="title">About Us</h1>
      <div class="section-container">
        <div class="about-details-container">
          <div class="about-containers">
            <div class="details-container">
              <p>Welcome to Emergency Help, where innovation meets safety. We are a dedicated team passionate about creating solutions that prioritize your well-being. Our journey began with the vision of empowering individuals in times of distress, leading to the birth of our first product, the Emergency Help, or SOS Project.<br><br>

With cutting-edge voice recognition technology, our product swiftly identifies distress signals and initiates immediate action. Upon activation, it seamlessly places a call to the pre-set emergency contact, ensuring rapid assistance. Simultaneously, an automatic emergency message is dispatched, providing crucial information to the designated contact.<br><br>

We understand the importance of timely assistance, which is why our product goes a step further by sending the user's precise location to the designated contact. This real-time information facilitates swift response and aids in providing accurate assistance when it's needed most.<br><br>

At Emergency Help, our mission is simple: to provide peace of mind and security through innovation. Join us in shaping a safer tomorrow, one innovation at a time.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section id="product">
      <p class="section__text__p1">Browse Our</p>
      <h1 class="title">Product</h1>
      <div class="experience-details-container">
        <div class="about-containers">
          <div class="details-container color-container">
            <div class="article-container">
              <img
                src="images/SOS.jpg"
                alt="Project 1"
                class="project-img"
              />
            </div>
            <h2 class="experience-sub-title project-title">SOS Voice Command</h2>
            <div class="btn-container">
              <button
                class="btn btn-color-2 project-btn"
                onclick="location.href=''"
              >
                Buy Now
              </button>
              <button
                class="btn btn-color-2 project-btn"
                onclick="location.href='Setting.php'"
              >
                Already have
              </button>
            </div>
          </div>
          <div class="details-container color-container">
            <div class="article-container">
              <img
                src="images/NoImage.jpg"
                alt="Product 2"
                class="project-img"
              />
            </div>
            <h2 class="experience-sub-title project-title">Product not available</h2>
            <div class="btn-container">
              <button
                class="btn btn-color-2 project-btn"
                onclick="location.href=''"
              >
                Buy Now
              </button>
              <button
                class="btn btn-color-2 project-btn"
                onclick="location.href=''"
              >
              Already have
              </button>
            </div>
          </div>
          <div class="details-container color-container">
            <div class="article-container">
              <img
                src="images/NoImage.jpg"
                alt="Product 3"
                class="project-img"
              />
            </div>
            <h2 class="experience-sub-title project-title">Product not available</h2>
            <div class="btn-container">
              <button
                class="btn btn-color-2 project-btn"
                onclick="location.href=''"
              >
                Buy Now
              </button>
              <button
                class="btn btn-color-2 project-btn"
                onclick="location.href=''"
              >
              Already have
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section id="contact">
      <p class="section__text__p1">Get in Touch</p>
      <h1 class="title">Contact Us</h1>
      <div class="contact-info-upper-container">
        <div class="contact-info-container">
          <img
            src="email.png"
            alt="Email icon"
            class="icon contact-icon email-icon"
          />
          <p><a href="mailto:examplemail@gmail.com">EmergencyHelp@gmail.com</a></p>
        </div>
      </div>
    </section>
    <footer>
      <p>Copyright &#169; 2024 Emergency Help. All Rights Reserved.</p>
    </footer>
    <script src="script.js"></script>
  <script>
      function redirectToLogin() {
        // Make an AJAX call to check_session.php
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "check_session.php", true);
        xhr.onreadystatechange = function () {
          if (xhr.readyState == 4 && xhr.status == 200) {
            var response = JSON.parse(xhr.responseText);
            // Redirect based on the response
            window.location.href = response.redirect;
          }
        };
        xhr.send();
      }
    </script>
  </body>
</html>