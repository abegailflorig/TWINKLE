<?php
// Start the session and check if the user is logged in (for demo purposes)
session_start();

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
  session_destroy();
  header("Location: login.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings</title>
  <!--for icons -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background-color: #f4f7fb;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .settings-container {
      width: 100%;
      height: 100%;
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      padding: 20px;
      overflow-y: auto;
    }

    .settings-menu {
      list-style: none;
      padding-left: 0;
      margin-top: 30px; /* Space from the top */
    }

    .settings-menu li {
      padding: 15px;
      border-bottom: 1px solid #ddd;
      font-size: 16px;
      display: flex;
      align-items: center;
    }

    .settings-menu a {
      text-decoration: none;
      color: #333;
      display: flex;
      align-items: center;
      width: 100%;
    }

    .settings-menu .icon {
      width: 20px;
      height: 20px;
      margin-right: 15px;
      color: #007bff;
    }

    .settings-menu .icon-follow:before {
      content: "\f3c5"; 
    }

    .settings-menu .icon-notification:before {
      content: "\f0f3"; 
    }

    .settings-menu .icon-liked:before {
      content: "\f004"; 
    }

    .settings-menu .icon-saved:before {
      content: "\f0c7";
    }

    .settings-menu .icon-archive:before {
      content: "\f128"; 
    }

    .settings-menu .icon-privacy:before {
      content: "\f23f"; 
    }

    .settings-menu .icon-accessibility:before {
      content: "\f29d"; 
    }

    .settings-menu .icon-account:before {
      content: "\f007"; 
    }

    .settings-menu .icon-language:before {
      content: "\f1ab"; 
    }

    .settings-menu .icon-help:before {
      content: "\f128"; 
    }

    .settings-menu .icon-about:before {
      content: "\f19c"; 
    }

    .logout {
      padding: 10px;
      text-align: center;
      margin-top: auto; 
    }

    .logout a {
      text-decoration: none;
      color: #007bff;
      font-weight: bold;
    }

    .logout a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="settings-container">
    <nav class="settings-menu">
      <ul>
        <li><a href="#"><i class="icon icon-follow"></i> Follow and invite friends</a></li>
        <li><a href="#"><i class="icon icon-notification"></i> Notification</a></li>
        <li><a href="#"><i class="icon icon-liked"></i> Liked</a></li>
        <li><a href="#"><i class="icon icon-saved"></i> Saved</a></li>
        <li><a href="#"><i class="icon icon-archive"></i> Archive</a></li>
        <li><a href="#"><i class="icon icon-privacy"></i> Privacy</a></li>
        <li><a href="#"><i class="icon icon-accessibility"></i> Accessibility</a></li>
        <li><a href="#"><i class="icon icon-account"></i> Account</a></li>
        <li><a href="#"><i class="icon icon-language"></i> Language</a></li>
        <li><a href="#"><i class="icon icon-help"></i> Help</a></li>
        <li><a href="#"><i class="icon icon-about"></i> About</a></li>
      </ul>
      <div class="logout">
        <a href="login.php">Log out</a>
      </div>
    </nav>
  </div>
</body>
</html>

