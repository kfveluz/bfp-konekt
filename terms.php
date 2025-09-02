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
    <title>Terms of Service - BFPKonekt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #0066cc;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        h2 {
            margin-top: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        p {
            margin-bottom: 15px;
        }
        ul {
            margin-bottom: 15px;
        }
        li {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Terms of Service</h1>
        
        <h2>1. Acceptance of Terms</h2>
        <p>By accessing and using BFPKonekt, you accept and agree to be bound by the terms and provision of this agreement.</p>

        <h2>2. Use License</h2>
        <p>Permission is granted to temporarily use BFPKonekt for monitoring fire incidents. This is the grant of a license, not a transfer of title.</p>

        <h2>3. User Responsibilities</h2>
        <p>Users agree to:</p>
        <ul>
            <li>Use the service only for legitimate purposes</li>
            <li>Not interfere with the service's operation</li>
            <li>Comply with all applicable laws and regulations</li>
            <li>Maintain the confidentiality of their account</li>
        </ul>

        <h2>4. Service Limitations</h2>
        <p>BFPKonekt is provided "as is" and we make no warranties regarding:</p>
        <ul>
            <li>The accuracy of incident data</li>
            <li>The availability of the service</li>
            <li>The timeliness of alerts</li>
        </ul>

        <h2>5. Data Usage</h2>
        <p>By using our service, you agree to our collection and use of data as described in our Privacy Policy.</p>

        <h2>6. Modifications</h2>
        <p>We reserve the right to modify these terms at any time. Users will be notified of any changes.</p>

        <h2>7. Contact Information</h2>
        <p>For any questions regarding these Terms of Service, please contact us at:</p>
        <p>Email: support@bfpkonekt.com</p>
        <p>Phone: (046) 123-4567</p>
    </div>
</body>
</html> 