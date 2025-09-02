<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - BFP Konekt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .section {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Privacy Policy</h1>
    
    <div class="section">
        <h2>Introduction</h2>
        <p>BFP Konekt is committed to protecting your privacy. This Privacy Policy explains how we collect, use, and safeguard your information when you use our application.</p>
    </div>

    <div class="section">
        <h2>Information We Collect</h2>
        <p>We collect information that you provide directly to us, including:</p>
        <ul>
            <li>Facebook profile information (name, email)</li>
            <li>Location data for fire incident reporting</li>
            <li>User preferences and settings</li>
        </ul>
    </div>

    <div class="section">
        <h2>How We Use Your Information</h2>
        <p>We use the collected information to:</p>
        <ul>
            <li>Provide and maintain our service</li>
            <li>Monitor and analyze fire incidents</li>
            <li>Improve our application</li>
            <li>Communicate with you about updates</li>
        </ul>
    </div>

    <div class="section">
        <h2>Data Security</h2>
        <p>We implement appropriate security measures to protect your personal information. However, no method of transmission over the Internet is 100% secure.</p>
    </div>

    <div class="section">
        <h2>Contact Us</h2>
        <p>If you have any questions about this Privacy Policy, please contact us at bfpkonek@gmail.com</p>
    </div>

    <div class="section">
        <h2>Last Updated</h2>
        <p>This Privacy Policy was last updated on March 19, 2024.</p>
    </div>
</body>
</html> 