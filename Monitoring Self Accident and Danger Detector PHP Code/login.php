<?php
session_start();
include 'connection.php'; // Include the connection file

// Set session timeout to 30 minutes (1800 seconds)
$sessionTimeout = 1800;

// Set the last activity time in the session
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
} elseif (time() - $_SESSION['last_activity'] > $sessionTimeout) {
    // If idle time exceeds session timeout, destroy the session and prompt for login
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
} else {
    // Update last activity time
    $_SESSION['last_activity'] = time();
}

// Google API credentials
$clientId = '1024180672431-ht28o2sut71v5kvjbn9dmfimp4g1u76v.apps.googleusercontent.com';
$clientSecret = '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';
$redirectUri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

// Check if it's the first login of the day
if (!isset($_SESSION['first_login'])) {
    $_SESSION['first_login'] = true;
} else {
    $_SESSION['first_login'] = false;
}

if (isset($_REQUEST['logout'])) {
    unset($_SESSION['access_token']);
    unset($_SESSION['password_inserted']); // Clear password-inserted flag
    unset($_SESSION['user_id']); // Clear user ID from session
    header('Location: login.php'); // Redirect to login page after logout
    exit;
}

if (isset($_GET['code'])) {
    $tokenUrl = "https://oauth2.googleapis.com/token";
    $postData = array(
        'code' => $_GET['code'],
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'redirect_uri' => $redirectUri,
        'grant_type' => 'authorization_code'
    );

    // Use cURL to make the POST request to exchange code for access token
    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if ($response === false) {
        // Handle cURL error
        die('Error fetching access token: ' . curl_error($ch));
    }
    curl_close($ch);

    // Decode the response
    $tokenInfo = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Handle JSON decoding error
        die('Error decoding JSON response: ' . json_last_error_msg());
    }

    // Store the access token in the session
    if (isset($tokenInfo['access_token'])) {
        $_SESSION['access_token'] = $tokenInfo['access_token'];

        // Fetch user details using access token
        $userInfoUrl = "https://www.googleapis.com/oauth2/v1/userinfo?access_token=" . $_SESSION['access_token'];
        $userInfo = json_decode(file_get_contents($userInfoUrl), true);
        if ($userInfo === false) {
            // Handle error fetching user details
            die('Error fetching user details');
        }

        // Check if the user already exists in the database
        $user_email = $userInfo['email'];
        $stmt = $conn->prepare("SELECT id_user FROM user WHERE user_email = ?");
        if (!$stmt) {
            // Handle database error
            die('Error preparing statement: ' . $conn->error);
        }
        $stmt->bind_param("s", $user_email);
        if (!$stmt->execute()) {
            // Handle query execution error
            die('Error executing statement: ' . $stmt->error);
        }
        $result = $stmt->get_result();
        if ($result === false) {
            // Handle error getting result
            die('Error getting result: ' . $stmt->error);
        }

        if ($result->num_rows > 0) {
            // User already exists, retrieve user ID from database and store in session
            $row = $result->fetch_assoc();
            $_SESSION['user_id'] = $row['id_user'];
        } else {
            // User doesn't exist, insert new record into user table and store new user ID in session
            $user_name = $userInfo['given_name'] . ' ' . $userInfo['family_name'];
            $stmt = $conn->prepare("INSERT INTO user (user_name, user_email) VALUES (?, ?)");
            if (!$stmt) {
                // Handle database error
                die('Error preparing statement: ' . $conn->error);
            }
            $stmt->bind_param("ss", $user_name, $user_email);
            if (!$stmt->execute()) {
                // Handle query execution error
                die('Error executing statement: ' . $stmt->error);
            }
            $_SESSION['user_id'] = $stmt->insert_id;
        }
        $stmt->close();

        // Set session variable for successful login
        $_SESSION['login_success'] = true;

        // If it's the first login of the day, prompt for password insertion
        if ($_SESSION['first_login']) {
            $_SESSION['password_inserted'] = false; // Set password-inserted flag to false
        }

        // Redirect to homepage.php
        header('Location: homepage.php');
        exit;
    }
}

if (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
    // Fetch user details from the database using user ID
    $stmt = $conn->prepare("SELECT user_name, user_email FROM user WHERE id_user = ?");
    if (!$stmt) {
        // Handle database error
        die('Error preparing statement: ' . $conn->error);
    }
    $stmt->bind_param("i", $_SESSION['user_id']);
    if (!$stmt->execute()) {
        // Handle query execution error
        die('Error executing statement: ' . $stmt->error);
    }
    $result = $stmt->get_result();
    if ($result === false) {
        // Handle error getting result
        die('Error getting result: ' . $stmt->error);
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Display user information
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="login.css" />
</head>
<a href="homepage.php" class="back-btn2">
    <span class="arrow">&#8592;</span>Back</a>
<body class="profile">
    <div class="container">
        <h1>WELCOME, <?php echo $user['user_name']; ?></h1>
        <p>Email: <?php echo $user['user_email']; ?></p>
        <form method="post" action="login.php">
            <button type="submit" name="logout" class="logout-btn">Logout</button>
        </form>
    </div>
</body>
</html>
<?php
} else {
    // Generate Google login URL
    $authUrl = "https://accounts.google.com/o/oauth2/auth?"
    . "client_id=$clientId&redirect_uri=$redirectUri&scope=email%20profile&response_type=code";
    ?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body class="login">
<a href="homepage.php" class="back-btn1">
    <span class="arrow">&#8592;</span>Back</a>
    <div class="container">
        <h1>Login</h1>
        <form method="post" action="login.php">
            <input type="text" name="user_name" placeholder="Username">
            <span class="icon person-icon">&#x1F464;</span>
            <div class="underline"></div>
            <input type="password" name="password" placeholder="Password">
            <span class="icon lock-icon">&#x1F512;</span>
            <div class="underline"></div>
            <input type="submit" value="Login">
        </form>
        <hr class="horizontal-line">
        <p class="login-with-text">Or Login With:</p>
        <div class="google-login">
            <a href="<?php echo $authUrl; ?>">
                <img src="images/google.png" alt="Login with Google" title="Login with Google" id="googleIcon">
            </a>
        </div>
        <div class="register-link">
            <a href="register.php">Don't have an account?</a>
        </div>
    </div>

    <script>
    // Get the input fields and corresponding icons
    const usernameInput = document.querySelector('input[name="user_name"]');
    const passwordInput = document.querySelector('input[name="password"]');
    const personIcon = document.querySelector('.person-icon');
    const lockIcon = document.querySelector('.lock-icon');
    const googleIcon = document.getElementById('googleIcon');

    // Add event listeners to input fields
    usernameInput.addEventListener('focus', () => {
        personIcon.classList.add('glow-person');
    });

    usernameInput.addEventListener('blur', () => {
        personIcon.classList.remove('glow-person');
    });

    passwordInput.addEventListener('focus', () => {
        lockIcon.classList.add('glow-lock');
    });

    passwordInput.addEventListener('blur', () => {
        lockIcon.classList.remove('glow-lock');
    });
    
    googleIcon.addEventListener('focus', () => {
        googleIcon.classList.add('glow');
    });

    googleIcon.addEventListener('blur', () => {
        googleIcon.classList.remove('glow');
    });
</script>
</body>
</html>
<?php } ?>