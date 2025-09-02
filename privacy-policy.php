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
    <title>BFP Konekt - Privacy Policy</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            color: #333;
        }
        h1 {
            color: #FF8C00;
            border-bottom: 2px solid #FF8C00;
            padding-bottom: 10px;
        }
        h2 {
            color: #1A237E;
            margin-top: 30px;
        }
        .last-updated {
            color: #666;
            font-style: italic;
            margin-bottom: 30px;
        }
        .section {
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <h1>BFP Konekt Privacy Policy</h1>
    <p class="last-updated">Last Updated: March 19, 2024</p>

    <div class="section">
        <h2>1. Introduction</h2>
        <p>BFP Konekt ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, and safeguard your information when you use our Facebook application.</p>
    </div>

    <div class="section">
        <h2>2. Information We Collect</h2>
        <p>We collect the following types of information:</p>
        <ul>
            <li>Public Facebook posts related to fire incidents</li>
            <li>Location data from reported incidents</li>
            <li>Basic profile information (when you connect with Facebook)</li>
            <li>Incident reports and related metadata</li>
        </ul>
    </div>

    <div class="section">
        <h2>3. How We Use Your Information</h2>
        <p>We use the collected information to:</p>
        <ul>
            <li>Monitor and respond to fire incidents</li>
            <li>Improve our emergency response system</li>
            <li>Generate incident reports and statistics</li>
            <li>Enhance public safety awareness</li>
        </ul>
    </div>

    <div class="section">
        <h2>4. Data Storage and Security</h2>
        <p>We implement appropriate security measures to protect your information. Data is stored securely and accessed only by authorized personnel.</p>
    </div>

    <div class="section">
        <h2>5. Facebook Integration</h2>
        <p>When you connect with Facebook, we request the following permissions:</p>
        <ul>
            <li>public_profile - to identify you</li>
            <li>pages_read_engagement - to monitor relevant posts</li>
            <li>pages_show_list - to display relevant pages</li>
        </ul>
    </div>

    <div class="section">
        <h2>6. Data Sharing</h2>
        <p>We may share information with:</p>
        <ul>
            <li>Emergency response teams</li>
            <li>Authorized government agencies</li>
            <li>Public safety organizations</li>
        </ul>
    </div>

    <div class="section">
        <h2>7. Your Rights</h2>
        <p>You have the right to:</p>
        <ul>
            <li>Access your personal information</li>
            <li>Request data correction</li>
            <li>Request data deletion</li>
            <li>Opt-out of data collection</li>
        </ul>
    </div>

    <div class="section">
        <h2>8. Contact Information</h2>
        <p>For privacy-related questions, contact:</p>
        <p>Bureau of Fire Protection - Dasmariñas City<br>
        Email: bfp.dasmarinas@yahoo.com<br>
        Address: Aguinaldo Highway, Dasmariñas, Cavite</p>
    </div>

    <div class="section">
        <h2>9. Changes to This Policy</h2>
        <p>We may update this privacy policy from time to time. We will notify you of any changes by posting the new policy on this page.</p>
    </div>
</body>
</html> 