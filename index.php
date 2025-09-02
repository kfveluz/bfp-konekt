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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFP Konekt Dashboard</title>
    
    <!-- Add Leaflet CSS and JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    
    <!-- Add Leaflet Draw plugin for radius editing -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css"/>
    <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
    
    <!-- Leaflet Control Geocoder (Search Bar) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    
    <!-- Add QR Code library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    
    <!-- NLP Libraries -->
    <script src="https://unpkg.com/compromise"></script>
    <script src="https://cdn.jsdelivr.net/npm/natural@6.5.0/dist/natural.min.js"></script>
    
    <!-- Geocoding and Location Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/turf@3.0.14/turf.min.js"></script>
    
    <!-- Add User Manager Script -->
    <script src="js/user-manager.js"></script>

    <!-- Add MarkerCluster CSS and JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />
    <script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>

    <!-- NEW: Incident Actions CSS -->
    <link rel="stylesheet" href="css/incident-actions.css"/>

    <style>
        :root {
            /* Primary Colors */
            --primary: #FF8C00;
            --primary-light: #FFB84D;
            --primary-dark: #E67A00;

            /* Secondary Colors */
            --secondary: #1A237E;
            --secondary-light: #534BAE;
            --secondary-dark: #000051;

            /* Background Colors */
            --bg-main: #F8F9FA;
            --bg-light: #FFFFFF;
            --bg-dark: #343A40;
            --sidebar-bg: #1A1A1A;

            /* Text Colors */
            --text-dark: #333333;
            --text-light: #FFFFFF;
            --text-muted: #6C757D;

            /* Utility Colors */
            --shadow: rgba(0, 0, 0, 0.1);
            --danger: #dc3545;
            --success: #28a745;
            --warning: #ffc107;
            --info: #17a2b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        body {
            display: flex;
            background: var(--bg-main);
            color: var(--text-dark);
        }

        /* Sidebar Styles */
        .sidebar {
            width: 240px;
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
            color: var(--text-light);
            padding: 20px 0;
            position: fixed;
            height: 100%;
            left: 0;
            top: 0;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 99;
            /* Remove border and box-shadow to eliminate gap */
            border-right: none !important;
            box-shadow: none !important;
        }

        .logo-container {
            padding: 20px;
            text-align: center;
            background: rgba(255, 255, 255, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 25px;
        }

        .logo-container img {
            max-width: 170px;
            max-height: 85px;
            width: auto;
            height: auto;
            margin-bottom: 15px;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3))
                   drop-shadow(0 8px 16px rgba(0, 0, 0, 0.2));
            transform: perspective(1000px) rotateX(5deg);
            transition: all 0.3s ease;
        }

        .logo-container img:hover {
            transform: perspective(1000px) rotateX(0deg) scale(1.05);
            filter: drop-shadow(0 6px 12px rgba(0, 0, 0, 0.4))
                   drop-shadow(0 12px 24px rgba(0, 0, 0, 0.3));
        }

        .menu-items {
            padding: 0 15px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--text-light);
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .menu-item.active {
            background: rgba(255, 255, 255, 0.2);
            font-weight: 600;
        }

        .menu-item i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 240px !important;
            padding-left: 0 !important;
            padding: 0;
            padding-bottom: 80px; /* Add padding to prevent footer overlap */
            background: var(--bg-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Dashboard Grid Layout */
        .dashboard-panels {
            display: grid;
            grid-template-columns: 1fr 2.5fr; /* Quick Stats 1x, Active Incidents 2.5x */
            gap: 10px;
            margin: 20px;
            padding: 0;
        }

        /* Panel Styling */
        .panel {
            background: var(--bg-light);
            border-radius: 15px;
            box-shadow: 0 4px 15px var(--shadow);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .panel:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .panel-header {
            padding: 20px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--text-light);
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-body {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Profile Section Styles Update */
        .profile-section {
            padding: 20px;
            margin-top: auto;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .profile-pic {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto;
            border: 3px solid var(--primary-light);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .profile-pic img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info {
            display: flex;
            flex-direction: column; /* Ensure items stack vertically */
            align-items: center;   /* Center items horizontally */
            gap: 5px;              /* Space between elements */
            text-align: center;    /* Center text within the info block */
        }

        .profile-info p {
            margin: 0;
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-light);
            width: 100%; /* Ensure text takes full width */
        }

        .badge {
            background: var(--danger);
            color: var(--text-light);
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            width: fit-content;
        }

        .badge:hover {
            background: var(--danger-dark);
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        /* Form Controls */
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255, 140, 0, 0.1);
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--text-light);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        /* Map Styles */
        .map-container {
            padding: 20px;
            flex: 1;
            min-height: 400px;
            height: 400px;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        #map {
            width: 100%;
            height: 400px;
            min-height: 400px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px var(--shadow);
        }

        /* Content Body */
        .content-body {
            flex: 1;
            display: none;
            padding: 0;
        }

        .content-body.active,
        #dashboard-content {
            display: flex;
            flex-direction: column;
        }

        /* Content Header */
        .content-header {
            padding: 20px;
            margin: 0;
            border-bottom: none;
            font-size: 1.5rem;
            font-weight: 600;
            background: linear-gradient(135deg, var(--secondary-light) 0%, var(--secondary) 100%);
            color: var(--text-light);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .stat-item {
            background: var(--bg-main);
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* Reports Tabs */
        .reports-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--primary);
        }

        .tab-button {
            padding: 12px 24px;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 500;
            color: var(--text-muted);
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .tab-button.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .tab-button:hover {
            color: var(--primary);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Ensure proper content visibility */
        .content-body {
            overflow: visible;
            min-height: 100vh;
        }

        #reports-content {
            overflow: visible;
            padding-bottom: 50px;
        }

        /* Fix any potential clipping issues */
        .reports-tabs {
            position: relative;
            z-index: 5;
        }

        .tab-content {
            position: relative;
            z-index: 1;
        }

        /* Evidence Styles */
        .evidence-stats-panel {
            background: var(--bg-light);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px var(--shadow);
            margin-bottom: 25px;
            overflow: visible;
            position: relative;
            z-index: 10;
        }

        /* Enhanced Stats Grid for Evidence */
        .evidence-stats-panel .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 0;
        }

        .evidence-stats-panel .stat-item {
            background: linear-gradient(135deg, var(--bg-main) 0%, #f0f0f0 100%);
            padding: 20px 15px;
            border-radius: 12px;
            text-align: center;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .evidence-stats-panel .stat-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            border-color: var(--primary);
        }

        .evidence-stats-panel .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 8px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
            line-height: 1.2;
        }

        .evidence-stats-panel .stat-label {
            color: var(--text-dark);
            font-size: 0.95rem;
            font-weight: 500;
            line-height: 1.3;
        }

        /* Ensure statistics are always visible */
        .evidence-stats-panel .stat-value,
        .evidence-stats-panel .stat-label {
            visibility: visible !important;
            opacity: 1 !important;
            display: block !important;
        }

        /* Add some animation for better visibility */
        .evidence-stats-panel .stat-item {
            animation: fadeInUp 0.5s ease-out;
        }

        /* Ensure proper spacing and visibility */
        .evidence-stats-panel {
            min-height: 120px;
        }

        .evidence-stats-panel .stats-grid {
            min-height: 80px;
        }

        .evidence-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }

        .evidence-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .evidence-item {
            background: var(--bg-main);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary);
        }

        .evidence-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .evidence-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .evidence-incident-id {
            font-weight: 600;
            color: var(--primary);
            font-size: 1.1rem;
        }

        .evidence-user {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .evidence-date {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .evidence-comment {
            background: var(--bg-light);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-style: italic;
            color: var(--text-dark);
        }

        .evidence-image {
            margin-bottom: 15px;
        }

        .evidence-link {
            display: inline-block;
            margin-top: 5px;
        }

        .evidence-thumbnail {
            border-radius: 8px;
            border: 2px solid var(--bg-light);
            transition: transform 0.2s ease;
        }

        .evidence-thumbnail:hover {
            transform: scale(1.05);
        }

        .evidence-incident-info,
        .evidence-location {
            margin-bottom: 10px;
            color: var(--text-dark);
        }

        .evidence-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        /* Feedback Styles */
        .feedback-stats-panel {
            background: var(--bg-light);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 15px var(--shadow);
            margin-bottom: 25px;
            overflow: visible;
            position: relative;
            z-index: 10;
        }

        /* Enhanced Stats Grid for Feedback */
        .feedback-stats-panel .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 0;
        }

        .feedback-stats-panel .stat-item {
            background: linear-gradient(135deg, var(--bg-main) 0%, #f0f0f0 100%);
            padding: 20px 15px;
            border-radius: 12px;
            text-align: center;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .feedback-stats-panel .stat-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            border-color: var(--primary);
        }

        .feedback-stats-panel .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 8px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
            line-height: 1.2;
        }

        .feedback-stats-panel .stat-label {
            color: var(--text-dark);
            font-size: 0.95rem;
            font-weight: 500;
            line-height: 1.3;
        }

        /* Ensure statistics are always visible */
        .feedback-stats-panel .stat-value,
        .feedback-stats-panel .stat-label {
            visibility: visible !important;
            opacity: 1 !important;
            display: block !important;
        }

        /* Add some animation for better visibility */
        .feedback-stats-panel .stat-item {
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Ensure proper spacing and visibility */
        .feedback-stats-panel {
            min-height: 120px;
        }

        .feedback-stats-panel .stats-grid {
            min-height: 80px;
        }

        .feedback-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }

        .feedback-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        /* Report Feedback Modal Styles */
        .feedback-modal-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--bg-main);
        }

        .feedback-modal-header h2 {
            color: var(--secondary);
            margin-bottom: 10px;
            font-size: 1.8rem;
        }

        .feedback-modal-header p {
            color: var(--text-muted);
            font-size: 1rem;
        }

        .feedback-section {
            margin-bottom: 30px;
            padding: 20px;
            background: var(--bg-main);
            border-radius: 10px;
        }

        .feedback-section h3 {
            color: var(--secondary);
            margin-bottom: 15px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .incident-summary {
            background: var(--bg-light);
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid var(--primary);
        }

        .incident-summary h4 {
            color: var(--primary);
            margin-bottom: 10px;
        }

        .incident-summary p {
            margin: 5px 0;
            color: var(--text-dark);
        }

        .rating-group {
            margin-bottom: 20px;
        }

        .rating-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: var(--text-dark);
        }

        .star-rating {
            display: flex;
            gap: 5px;
            margin-bottom: 10px;
        }

        .star {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .star:hover,
        .star:hover ~ .star {
            color: #ffd700;
        }

        .star.active {
            color: #ffd700;
        }

        .rating-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .detailed-ratings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .rating-item {
            text-align: center;
        }

        .rating-item label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: var(--text-dark);
        }

        .mini-star-rating {
            display: flex;
            justify-content: center;
            gap: 3px;
        }

        .mini-star {
            font-size: 1.2rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .mini-star:hover,
        .mini-star:hover ~ .mini-star {
            color: #ffd700;
        }

        .mini-star.active {
            color: #ffd700;
        }

        .feedback-categories {
            margin-top: 20px;
        }

        .feedback-categories label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: var(--text-dark);
        }

        .category-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
        }

        .category-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px;
            border: 2px solid var(--bg-main);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .category-option:hover {
            border-color: var(--primary);
            background: var(--bg-main);
        }

        .category-option input[type="radio"] {
            margin: 0;
        }

        .category-option input[type="radio"]:checked + .category-label {
            color: var(--primary);
            font-weight: 500;
        }

        .category-label {
            font-size: 0.9rem;
            color: var(--text-dark);
        }

        .feedback-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid var(--bg-main);
        }

        /* Enhanced Feedback Item Styles */
        .feedback-item {
            background: var(--bg-light);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px var(--shadow);
            border-left: 4px solid var(--primary);
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .feedback-info {
            flex: 1;
        }

        .feedback-incident-id {
            font-weight: 600;
            color: var(--primary);
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .feedback-phone {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 3px;
        }

        .feedback-date {
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .feedback-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-reviewed { background: #d1ecf1; color: #0c5460; }
        .status-addressed { background: #d4edda; color: #155724; }
        .status-closed { background: #f8d7da; color: #721c24; }

        .feedback-rating {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .feedback-text {
            background: var(--bg-main);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-style: italic;
            color: var(--text-dark);
        }

        .feedback-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }

        .feedback-detail {
            text-align: center;
            padding: 10px;
            background: var(--bg-main);
            border-radius: 8px;
        }

        .feedback-detail-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .feedback-detail-value {
            font-weight: 600;
            color: var(--primary);
        }

        .feedback-actions {
            display: flex;
            gap: 10px;
        }

        .feedback-actions .btn {
            padding: 8px 16px;
            font-size: 0.9rem;
        }

        /* Action Buttons in History Table */
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .action-buttons .btn {
            font-size: 0.8rem;
            padding: 5px 10px;
            white-space: nowrap;
        }

        /* History Table Enhancements */
        .history-table td {
            vertical-align: middle;
        }

        .history-table .action-buttons {
            min-width: 200px;
        }

        .feedback-item {
            background: var(--bg-light);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px var(--shadow);
            border-left: 4px solid var(--primary);
        }

        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .feedback-info {
            flex: 1;
        }

        .feedback-incident-id {
            font-weight: 600;
            color: var(--primary);
            font-size: 1.1rem;
        }

        .feedback-phone {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .feedback-date {
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .feedback-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 10px 0;
        }

        .star {
            color: #ffd700;
            font-size: 1.2rem;
        }

        .star.empty {
            color: #ddd;
        }

        .feedback-text {
            background: var(--bg-main);
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-style: italic;
        }

        .feedback-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }

        .feedback-detail {
            text-align: center;
            padding: 10px;
            background: var(--bg-main);
            border-radius: 8px;
        }

        .feedback-detail-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .feedback-detail-value {
            font-weight: 600;
            color: var(--primary);
        }

        .feedback-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .feedback-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-pending {
            background: var(--warning);
            color: var(--text-dark);
        }

        .status-reviewed {
            background: var(--info);
            color: var(--text-light);
        }

        .status-addressed {
            background: var(--success);
            color: var(--text-light);
        }

        .status-closed {
            background: var(--text-muted);
            color: var(--text-light);
        }

        /* Settings Grid */
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dark);
            font-weight: 500;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 0;
            }

            .dashboard-panels {
                grid-template-columns: 1fr;
                margin: 15px;
                gap: 15px;
            }

            .panel {
                margin-bottom: 0;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            }
        }

        /* Incident Popup Styles */
        .incident-popup {
            padding: 10px;
            max-width: 300px;
        }

        .incident-popup h3 {
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 1.2rem;
        }

        .incident-popup p {
            margin: 5px 0;
            font-size: 0.9rem;
        }

        .incident-popup .status-active {
            color: var(--danger);
            font-weight: bold;
        }

        .incident-popup .status-resolved {
            color: var(--success);
            font-weight: bold;
        }

        /* Marker Cluster Styles */
        .marker-cluster {
            background-color: rgba(255, 140, 0, 0.6);
        }

        .marker-cluster div {
            background-color: rgba(255, 140, 0, 0.8);
            color: var(--text-light);
        }

        /* Logo Settings Styles */
        .logo-settings {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .logo-preview {
            text-align: center;
            padding: 20px;
            background: var(--bg-main);
            border-radius: 10px;
        }

        .logo-preview h3 {
            margin-bottom: 15px;
            color: var(--text-dark);
        }

        .logo-preview img {
            max-width: 200px;
            max-height: 200px;
            width: auto;
            height: auto;
            display: block;
            margin-left: auto;
            margin-right: auto;
            border-radius: 10px;
            box-shadow: 0 2px 8px var(--shadow);
        }

        .logo-info {
            margin-top: 10px;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .logo-upload {
            text-align: center;
        }

        .selected-file {
            margin-top: 10px;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .logo-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 10px;
        }

        .btn-secondary {
            background: var(--bg-dark);
            color: var(--text-light);
        }

        .btn-secondary:hover {
            background: var(--secondary);
            transform: translateY(-1px);
        }

        /* Keyword Management Styles */
        /* .keyword-management {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .keyword-input-area {
            display: flex;
            gap: 10px;
        }

        .keyword-input-area input {
            flex-grow: 1;
        }

        .keyword-list {
            list-style: none;
            padding: 0;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 10px;
        }

        .keyword-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 10px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .keyword-list li:last-child {
            border-bottom: none;
        }

        .keyword-list .btn-remove-keyword {
            background-color: var(--danger);
            color: white;
            padding: 5px 10px;
            font-size: 0.8rem;
            border-radius: 5px;
        }
         .keyword-list .btn-remove-keyword:hover {
            background-color: var(--primary-dark);
        } */

        /* Add loading animation */
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading::after {
            content: '';
            display: inline-block;
            width: 12px;
            height: 12px;
            margin-left: 10px;
            border: 2px solid var(--text-light);
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        /* Add Footer Styles */
        .dashboard-footer {
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
            color: var(--text-light);
            padding: 15px 20px;
            position: fixed;
            bottom: 0;
            right: 0;
            left: 240px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 -4px 15px var(--shadow);
            z-index: 98;
        }

        .footer-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .footer-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .footer-item i {
            font-size: 1.1rem;
        }

        .bfp-brand {
            font-weight: 600;
            font-size: 1rem;
            color: var(--text-light);
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .dashboard-footer {
                left: 0;
                flex-direction: column;
                gap: 10px;
                padding: 10px;
            }

            .footer-section {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        /* Styles for FB Auth in Sidebar */
        .profile-section #fbAuthSidebarContainer .fb-auth-container {
            padding: 10px 5px; /* More compact padding */
            margin: 15px 0 0 0; /* Margin above, aligned with profile items */
            border: 1px solid rgba(255, 255, 255, 0.1);
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
        }

        .profile-section #fbAuthSidebarContainer .fb-login-btn {
            padding: 8px 12px; /* Smaller button */
            font-size: 0.85rem;
            width: 100%;
            justify-content: center;
        }

        .profile-section #fbAuthSidebarContainer .fb-status {
            font-size: 0.8rem;
            text-align: center;
            margin-bottom: 8px;
        }

        .profile-section #fbAuthSidebarContainer .fb-user-profile {
            flex-direction: column; /* Stack profile items vertically */
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
        }
        .profile-section #fbAuthSidebarContainer .fb-user-profile img {
            width: 30px;
            height: 30px;
        }
        .profile-section #fbAuthSidebarContainer .fb-logout-btn {
            padding: 5px 10px;
            font-size: 0.75rem;
            width: 80%;
            margin-top: 5px;
        }

        /* Htory Table Styles */
        .history-container {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px; /* Space between button and table */
        }

        #downloadHistoryBtn {
            align-self: flex-end; /* Position button to the right */
            margin-bottom: 15px;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 2px 8px var(--shadow);
            background-color: var(--bg-light);
            border-radius: 8px;
            overflow: hidden; /* Ensures border-radius is applied to table */
        }

        .history-table th,
        .history-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--bg-main); /* Light line between rows */
        }

        .history-table thead th {
            background-color: var(--secondary-dark); /* Darker blue for header */
            color: var(--text-light);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .history-table tbody tr:last-child td {
            border-bottom: none;
        }

        .history-table tbody tr:hover {
            background-color: var(--bg-main); /* Slight hover effect */
        }

        .history-table .status-active {
            color: var(--danger);
            font-weight: bold;
        }

        .history-table .status-resolved {
            color: var(--success);
            font-weight: bold;
        }

        /* Active Incidents List Styles */
        #active-incidents-panel-body {
            padding: 0; /* Remove default panel-body padding if list items have their own */
        }
        .incident-list {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 500px; /* Increased for more visible items */
            overflow-y: auto;
        }

        .incident-list li {
            padding: 12px 15px;
            border-bottom: 1px solid var(--bg-main); /* Similar to table rows */
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            transition: background-color 0.2s ease-in-out;
        }
        .incident-list li:hover {
            background-color: var(--bg-main);
        }

        .incident-list li:last-child {
            border-bottom: none;
        }

        .incident-list .incident-info {
            flex-grow: 1;
            margin-right: 10px;
        }
        .incident-list .incident-location {
            font-weight: 500;
            display: block;
            color: var(--text-dark);
            margin-bottom: 3px;
        }
        .incident-list .incident-time {
            font-size: 0.8rem;
            color: var(--text-muted);
            display: block;
        }

        .incident-list .btn-resolve {
            background-color: var(--success);
            color: white;
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            white-space: nowrap; /* Prevent button text from wrapping */
            transition: background-color 0.2s ease-in-out;
        }
        .incident-list .btn-resolve:hover {
            background-color: #218838; /* Darker success green */
        }
        .incident-list .no-active-incidents {
            text-align: center;
            color: var(--text-muted);
            padding: 20px;
            border-bottom: none; /* No border if it's the only item */
        }
        .incident-list .no-active-incidents:hover {
            background-color: transparent; /* No hover effect for this item */
        }

        /* Notification Panel Styles */
        .notification-panel {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 350px;
            max-height: 80vh;
            background: var(--bg-light);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            overflow-y: auto;
            display: none;
        }

        .notification-panel.active {
            display: block;
        }

        .notification-header {
            padding: 15px;
            background: var(--primary);
            color: white;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-header h3 {
            margin: 0;
            font-size: 1.1rem;
        }

        .notification-count {
            background: var(--danger);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.9rem;
        }

        .notification-list {
            padding: 10px;
        }

        .notification-item {
            padding: 15px;
            border-bottom: 1px solid var(--bg-main);
            display: flex;
            gap: 10px;
            align-items: start;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text-dark);
        }

        .notification-message {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .notification-time {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .notification-link {
            font-size: 0.8rem;
            color: var(--primary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .notification-link:hover {
            text-decoration: underline;
        }

        .notification-actions {
            display: flex;
            gap: 5px;
        }

        .notification-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .btn-verify {
            background: var(--success);
            color: white;
        }

        .btn-ignore {
            background: var(--danger);
            color: white;
        }

        .notification-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        /* Notification Bell Icon */
        .notification-bell {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--primary);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 999;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .notification-bell:hover {
            transform: scale(1.1);
        }

        .notification-bell.has-new::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 10px;
            height: 10px;
            background: var(--danger);
            border-radius: 50%;
            border: 2px solid white;
        }

        /* Sound Alert Animation */
        @keyframes soundAlert {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .sound-alert {
            animation: soundAlert 1s ease infinite;
        }

        /* Add new styles for enhanced incident display */
        .incident-metrics {
            display: flex;
            gap: 10px;
            margin-top: 5px;
            font-size: 0.8rem;
        }

        .metric {
            display: flex;
            align-items: center;
            gap: 3px;
            color: var(--text-muted);
        }

        .incident-hashtags {
            margin-top: 5px;
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .hashtag {
            background: var(--primary-light);
            color: var(--text-light);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }

        .incident-actions {
            display: flex;
            gap: 8px;
        }

        .btn-view {
            background-color: var(--info);
            color: white;
            padding: 6px 12px;
            font-size: 0.85rem;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            white-space: nowrap;
            transition: background-color 0.2s ease-in-out;
        }

        .btn-view:hover {
            background-color: #138496;
        }

        .incident-popup .incident-metrics {
            margin: 10px 0;
            padding: 5px 0;
            border-top: 1px solid rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .incident-popup .incident-hashtags {
            margin: 10px 0;
        }

        .incident-popup .incident-actions {
            margin-top: 10px;
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .incident-popup .incident-actions button {
            flex: 1;
            min-width: 100px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 20001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 30px 30px 20px 30px;
            border: 1px solid #888;
            width: 90%;
            max-width: 500px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            position: relative;
        }
        .modal-content h2 {
            margin-top: 0;
            color: var(--primary);
            margin-bottom: 20px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            right: 20px;
            top: 10px;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: var(--danger);
            text-decoration: none;
            cursor: pointer;
        }
        .btn-system-status-success {
            background: var(--success) !important;
            color: #fff !important;
        }
        .btn-system-status-danger {
            background: var(--danger) !important;
            color: #fff !important;
        }

        /* User Management Styles */
        .user-management-section {
            margin-bottom: 30px;
            padding: 20px;
            background: var(--bg-light);
            border-radius: 10px;
            box-shadow: 0 2px 8px var(--shadow);
        }

        .user-list-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .user-list-table th,
        .user-list-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--bg-main);
        }

        .user-list-table th {
            background: var(--secondary);
            color: var(--text-light);
            font-weight: 600;
        }

        .user-list-table tr:hover {
            background: var(--bg-main);
        }

        .user-actions {
            display: flex;
            gap: 8px;
        }

        .btn-change-password {
            background: var(--info);
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }

        .btn-delete-user {
            background: var(--danger);
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }

        .user-type-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .user-type-admin {
            background: var(--primary);
            color: white;
        }

        .user-type-user {
            background: var(--secondary);
            color: white;
        }

        .sidebar-footer {
            padding: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
        }

        .user-info {
            margin-bottom: 10px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            text-align: center;
        }

        .user-id {
            color: var(--text-light);
            font-size: 0.9rem;
            font-weight: 500;
            display: block;
        }

        .btn-danger {
            background: var(--danger) !important;
            color: white !important;
        }

        /* Slider Management Styles */
        .slider-management {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .slider-images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .slider-image-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px var(--shadow);
        }

        .slider-image-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .slider-image-actions {
            position: absolute;
            top: 5px;
            right: 5px;
            display: flex;
            gap: 5px;
        }

        .slider-image-btn {
            background: rgba(0, 0, 0, 0.6);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 4px 8px;
            cursor: pointer;
            font-size: 0.8rem;
        }

        .slider-image-btn:hover {
            background: rgba(0, 0, 0, 0.8);
        }

        .slider-image-order {
            position: absolute;
            bottom: 5px;
            left: 5px;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.8rem;
        }

        /* --- New styles for Incident Action Buttons --- */
        .incident-actions {
            display: flex;
            gap: 10px; /* Space between buttons */
            margin-top: 15px;
            justify-content: flex-end; /* Align buttons to the right */
        }

        .action-button {
            padding: 8px 15px;
            border-radius: 8px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            color: var(--text-light);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .action-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .resolved-button {
            background-color: var(--success);
        }

        .resolved-button:hover {
            background-color: #218838;
        }

        .false-alarm-button {
            background-color: var(--warning);
        }

        .false-alarm-button:hover {
            background-color: #e0a800;
        }

        .non-incident-button {
            background-color: var(--info);
        }

        .non-incident-button:hover {
            background-color: #117a8b;
        }

        /* Style for disabled buttons */
        .action-button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            opacity: 0.7;
            box-shadow: none;
            transform: none;
        }

        /* Styles for incident status display */
        .incident-log-entry.status-resolved .incident-severity,
        .incident-log-entry.status-false_alarm .incident-severity,
        .incident-log-entry.status-non_incident .incident-severity {
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px 8px;
            border-radius: 5px;
            color: var(--text-light);
        }

        .incident-log-entry.status-resolved .incident-severity {
            background-color: var(--success);
        }

        .incident-log-entry.status-false_alarm .incident-severity {
            background-color: var(--warning);
        }

        .incident-log-entry.status-non_incident .incident-severity {
            background-color: var(--info);
        }

        .incident-log-entry.new-incident {
            border: 2px solid var(--primary-light);
            animation: pulse-border 1.5s infinite alternate;
        }

        @keyframes pulse-border {
            from { box-shadow: 0 0 0 rgba(255, 184, 77, 0.4); }
            to { box-shadow: 0 0 10px rgba(255, 184, 77, 0.8); }
        }

    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Add Notification Bell -->
    <!-- <div class="notification-bell" onclick="toggleNotificationPanel()">
        🔔
    </div> -->

    <!-- Add Notification Panel -->
    <!-- <div class="notification-panel" id="notificationPanel">
        <div class="notification-header">
            <h3>Verification Requests</h3>
            <span class="notification-count" id="notificationCount">0</span>
        </div>
        <div class="notification-list" id="notificationList">
            <!-- Notifications will be added here dynamically -->
        </div>
    </div> -->

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-container">
            <a href="#" target="_blank" style="text-decoration: none; display: block;">
                <img id="mainSidebarLogo" src="Konekt (1).png" alt="BFP Logo" style="cursor: pointer;">
            </a>
        </div>
        <div class="menu-items">
            <a class="menu-item active" href="#" data-page="dashboard">
                <i>👤</i>
                <span>DASHBOARD</span>
            </a>
            <a class="menu-item" href="#" data-page="map">
                <i>🗺️</i>
                <span>MAP</span>
            </a>
            <a class="menu-item" href="#" data-page="reports">
                <i>📜</i>
                <span>REPORTS</span>
            </a>
            <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
            <a class="menu-item" href="#" data-page="settings">
                <i>⚙️</i>
                <span>SETTINGS</span>
            </a>
            <?php endif; ?>
        </div>
        <div class="profile-section">
            <div class="profile-pic">
                <img id="sidebarLogo" src="images/logo.png" alt="BFP Logo">
            </div>
            <div class="profile-info">
                <p id="adminUsernameDisplay"><?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Unknown User'; ?></p>
                <p class="user-id" id="sidebarUserId"><?php echo isset($_SESSION['user_id']) ? htmlspecialchars($_SESSION['user_id']) : ''; ?></p>
                <button id="logoutBtn" type="button" class="btn btn-danger" style="margin-top: 10px; width: 100%;" onclick="window.location.href='logout.php'">Logout</button>
            </div>
        </div>
        <div class="sidebar-footer">
            <div class="user-info">
                <span class="user-id" id="sidebarUserId"></span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Content -->
        <div id="dashboard-content" class="content-body" style="display: block;">
            <h1 class="content-header">Dashboard</h1>
            <div class="dashboard-panels">
                <!-- Quick Stats Panel -->
                <div class="panel">
                    <div class="panel-header">Quick Stats</div>
                    <div class="panel-body">
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div id="activeCasesValue" class="stat-value">0</div>
                                <div class="stat-label">Active Cases</div>
                            </div>
                            <div class="stat-item">
                                <div id="totalReportsValue" class="stat-value">0</div>
                                <div class="stat-label">Total Reports (Session)</div>
                            </div>
                            <div class="stat-item">
                                <div id="responseRateValue" class="stat-value">0%</div>
                                <div class="stat-label">Response Rate (Session)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Incidents Panel -->
                <div class="panel">
                    <div class="panel-header">Active Incidents</div>
                    <div class="panel-body" id="active-incidents-panel-body">
                        <div style="margin-bottom: 10px;">
                            <label for="incidentFilter">Filter Incidents:</label>
                            <select id="incidentFilter" class="form-control">
                                <option value="all">All Incidents</option>
                                <option value="active">Active</option>
                                <option value="resolved">Resolved</option>
                            </select>
                        </div>
                        <ul id="activeIncidentsList" class="incident-list">
                            <!-- Active incidents will be populated here by JavaScript -->
                            <li class="no-active-incidents">No active incidents at the moment.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Analytics Panel -->
            <div class="panel" style="margin: 20px; width: 100%; max-width: 100%;">
                <div class="panel-header">Reports Analytics</div>
                <div class="panel-body" style="display: flex; flex-direction: row; gap: 30px; align-items: flex-start;">
                    <div style="flex: 2;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                            <label for="analyticsPeriodSelect">Period:</label>
                            <select id="analyticsPeriodSelect" class="form-control" style="width: 120px;">
                                <option value="day">Day</option>
                                <option value="month">Month</option>
                                <option value="year">Year</option>
                            </select>
                        </div>
                        <canvas id="reportsAnalyticsBarChart" style="width:100%;min-height:350px;"></canvas>
                    </div>
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center;">
                        <canvas id="reportsAnalyticsPieChart" style="width:100%;max-width:250px;min-height:250px;"></canvas>
                    <div id="reportsAnalyticsSummary" style="margin-top: 20px; color: var(--text-muted);">
                        Analytics summary will appear here.
                        </div>
                            </div>
                            </div>
            </div>
        </div>

        <!-- Map Content -->
        <div id="map-content" class="content-body" style="display: none;">
            <h1 class="content-header">Map View</h1>
            <div class="map-container">
                <div id="map"></div>
            </div>
        </div>

        <!-- Keywords Management Content -->
        <!-- (Removed: old admin settings panel to prevent duplicate IDs and conflicts) -->

        <!-- Add styles for slider management -->
        <style>
            .slider-management {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }

            .slider-images-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
                margin-top: 15px;
            }

            .slider-image-item {
                position: relative;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 8px var(--shadow);
            }

            .slider-image-item img {
                width: 100%;
                height: 150px;
                object-fit: cover;
            }

            .slider-image-actions {
                position: absolute;
                top: 5px;
                right: 5px;
                display: flex;
                gap: 5px;
            }

            .slider-image-btn {
                background: rgba(0, 0, 0, 0.6);
                color: white;
                border: none;
                border-radius: 4px;
                padding: 4px 8px;
                cursor: pointer;
                font-size: 0.8rem;
            }

            .slider-image-btn:hover {
                background: rgba(0, 0, 0, 0.8);
            }

            .slider-image-order {
                position: absolute;
                bottom: 5px;
                left: 5px;
                background: rgba(0, 0, 0, 0.6);
                color: white;
                padding: 2px 6px;
                border-radius: 4px;
                font-size: 0.8rem;
            }
        </style>

        <!-- Add slider management script -->
        <script>
            // Function to load slider images
            async function loadSliderImages() {
                try {
                    const response = await fetch('api/get-slider-images.php');
                    const data = await response.json();
                    
                    const sliderImagesList = document.getElementById('sliderImagesList');
                    if (!sliderImagesList) {
                        console.error('sliderImagesList element not found');
                        return;
                    }
                    
                    sliderImagesList.innerHTML = '';
                    
                    if (data.success && data.images.length > 0) {
                        data.images.forEach((image, index) => {
                            const imageItem = document.createElement('div');
                            imageItem.className = 'slider-image-item';
                            imageItem.innerHTML = `
                                <img src="${image.url}" alt="Slider Image">
                                <div class="slider-image-actions">
                                    <button class="slider-image-btn" onclick="deleteSliderImage('${image.id}')">Delete</button>
                                    <button class="slider-image-btn" onclick="moveSliderImage('${image.id}', 'up')" ${index === 0 ? 'disabled' : ''}>↑</button>
                                    <button class="slider-image-btn" onclick="moveSliderImage('${image.id}', 'down')" ${index === data.images.length - 1 ? 'disabled' : ''}>↓</button>
                            </div>
                                <div class="slider-image-order">${index + 1}</div>
                            `;
                            sliderImagesList.appendChild(imageItem);
                        });
                    } else {
                        sliderImagesList.innerHTML = '<p>No slider images uploaded yet.</p>';
                    }
                } catch (error) {
                    console.error('Error loading slider images:', error);
                    createNotification('Error', 'Failed to load slider images', 'error');
                }
            }

            // Initialize slider image upload
            const sliderImageInput = document.getElementById('sliderImageUpload');
            if (!sliderImageInput) {
                console.error('Error: sliderImageUpload input not found in DOM!');
            } else {
                console.log('sliderImageUpload input found, attaching event handler.');
                sliderImageInput.addEventListener('change', async function(e) {
                    const files = e.target.files;
                    const selectedFilesDiv = document.getElementById('selectedSliderFiles');
                    if (!selectedFilesDiv) {
                        console.error('selectedSliderFiles element not found');
                        return;
                    }
                    
                    selectedFilesDiv.textContent = '';
                    if (files.length) {
                        selectedFilesDiv.textContent = Array.from(files).map(f => f.name).join(', ');
                    }
                    if (!files.length) return;

                    const formData = new FormData();
                    for (let i = 0; i < files.length; i++) {
                        formData.append('images[]', files[i]);
                    }

                    try {
                        console.log('Uploading slider images:', Array.from(files).map(f => f.name));
                        const response = await fetch('api/upload-slider-images.php', {
                            method: 'POST',
                            body: formData
                        });
                        const data = await response.json();
                        console.log('Upload response:', data);
                        if (data.success) {
                            createNotification('Success', 'Images uploaded successfully', 'success');
                            loadSliderImages();
                        } else {
                            createNotification('Error', data.message || 'Failed to upload images', 'error');
                        }
                    } catch (error) {
                        console.error('Error uploading images:', error);
                        createNotification('Error', 'Failed to upload images: ' + error.message, 'error');
                    }

                    // Clear file input and selected file names
                    e.target.value = '';
                    selectedFilesDiv.textContent = '';
                });
            }

            // Load slider images when admin settings page is opened
            const settingsMenuItem = document.querySelector('.menu-item[data-page="settings"]');
            if (settingsMenuItem) {
                settingsMenuItem.addEventListener('click', loadSliderImages);
            }
            
            // Also load if settings panel is already visible on page load
            if (document.getElementById('settings-content') && document.getElementById('settings-content').style.display !== 'none') {
                loadSliderImages();
            }
        </script>

        <!-- Function to delete slider image
        async function deleteSliderImage(imageId) {
            if (!confirm('Are you sure you want to delete this image?')) return;

            try {
                const response = await fetch('api/delete-slider-image.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ imageId })
                });

                const data = await response.json();
                if (data.success) {
                    createNotification('Success', 'Image deleted successfully', 'success');
                    loadSliderImages();
                } else {
                    createNotification('Error', data.message || 'Failed to delete image', 'error');
                }
            } catch (error) {
                console.error('Error deleting image:', error);
                createNotification('Error', 'Failed to delete image', 'error');
            }
        }

        // Function to move slider image up or down
        async function moveSliderImage(imageId, direction) {
            try {
                const response = await fetch('api/move-slider-image.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ imageId, direction })
                });

                const data = await response.json();
                if (data.success) {
                    loadSliderImages();
                } else {
                    createNotification('Error', data.message || 'Failed to move image', 'error');
                }
            } catch (error) {
                console.error('Error moving image:', error);
                createNotification('Error', 'Failed to move image', 'error');
            }
        } -->

        <!-- Add Logo Management Script -->
        <script>
            // Logo Management Functions
            document.addEventListener('DOMContentLoaded', function() {
                // Main Logo (Konekt) elements
                const logoUpload = document.getElementById('logoUpload');
                const uploadLogoBtn = document.getElementById('uploadLogoBtn');
                const resetLogoBtn = document.getElementById('resetLogoBtn');
                const currentLogo = document.getElementById('currentLogo');
                const selectedFileName = document.getElementById('selectedFileName');
                const mainSidebarLogo = document.getElementById('mainSidebarLogo');

                // Sidebar Logo elements
                const sidebarLogoUpload = document.getElementById('sidebarLogoUpload');
                const uploadSidebarLogoBtn = document.getElementById('uploadSidebarLogoBtn');
                const resetSidebarLogoBtn = document.getElementById('resetSidebarLogoBtn');
                const currentSidebarLogo = document.getElementById('currentSidebarLogo');
                const selectedSidebarFileName = document.getElementById('selectedSidebarFileName');
                const profileSidebarLogo = document.getElementById('sidebarLogo');

                // Load saved main logo if exists
                const savedMainLogo = localStorage.getItem('bfpLogo');
                if (savedMainLogo) {
                    currentLogo.src = savedMainLogo;
                    if (mainSidebarLogo) mainSidebarLogo.src = savedMainLogo;
                } else {
                    if (mainSidebarLogo) mainSidebarLogo.src = 'Konekt (1).png';
                }

                // Load saved sidebar logo if exists
                const savedSidebarLogo = localStorage.getItem('bfpSidebarLogo');
                if (savedSidebarLogo) {
                    currentSidebarLogo.src = savedSidebarLogo;
                    if (profileSidebarLogo) profileSidebarLogo.src = savedSidebarLogo;
                } else {
                    if (profileSidebarLogo) profileSidebarLogo.src = 'images/logo.png';
                }

                // Main Logo Event Listeners
                logoUpload.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        selectedFileName.textContent = `Selected: ${file.name}`;
                        uploadLogoBtn.disabled = false;
                    }
                });

                uploadLogoBtn.addEventListener('click', function() {
                    const file = logoUpload.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const logoData = e.target.result;
                            localStorage.setItem('bfpLogo', logoData);
                            currentLogo.src = logoData;
                            if (mainSidebarLogo) mainSidebarLogo.src = logoData;
                            createNotification('Success', 'Main logo updated successfully', 'success');
                            selectedFileName.textContent = '';
                            logoUpload.value = '';
                        };
                        reader.readAsDataURL(file);
                    }
                });

                resetLogoBtn.addEventListener('click', function() {
                    localStorage.removeItem('bfpLogo');
                    currentLogo.src = 'Konekt.png';
                    if (mainSidebarLogo) mainSidebarLogo.src = 'Konekt (1).png';
                    createNotification('Success', 'Main logo reset to default', 'success');
                    selectedFileName.textContent = '';
                    logoUpload.value = '';
                });

                // Sidebar Logo Event Listeners
                sidebarLogoUpload.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        selectedSidebarFileName.textContent = `Selected: ${file.name}`;
                        uploadSidebarLogoBtn.disabled = false;
                    }
                });

                uploadSidebarLogoBtn.addEventListener('click', function() {
                    const file = sidebarLogoUpload.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const logoData = e.target.result;
                            localStorage.setItem('bfpSidebarLogo', logoData);
                            currentSidebarLogo.src = logoData;
                            if (profileSidebarLogo) profileSidebarLogo.src = logoData;
                            createNotification('Success', 'Sidebar logo updated successfully', 'success');
                            selectedSidebarFileName.textContent = '';
                            sidebarLogoUpload.value = '';
                        };
                        reader.readAsDataURL(file);
                    }
                });

                resetSidebarLogoBtn.addEventListener('click', function() {
                    localStorage.removeItem('bfpSidebarLogo');
                    currentSidebarLogo.src = 'images/logo.png';
                    if (profileSidebarLogo) profileSidebarLogo.src = 'images/logo.png';
                    createNotification('Success', 'Sidebar logo reset to default', 'success');
                    selectedSidebarFileName.textContent = '';
                    sidebarLogoUpload.value = '';
                });
            });
        </script>

        <!-- Reports Content -->
        <div id="reports-content" class="content-body" style="display: none;">
            <h1 class="content-header">Incident Reports & Evidence</h1>
            
            <!-- Evidence Statistics Panel -->
            <div class="evidence-stats-panel" style="margin-bottom: 20px;">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div id="totalEvidenceValue" class="stat-value">0</div>
                        <div class="stat-label">Total Evidence</div>
                    </div>
                    <div class="stat-item">
                        <div id="incidentsWithEvidenceValue" class="stat-value">0</div>
                        <div class="stat-label">Incidents with Evidence</div>
                    </div>
                    <div class="stat-item">
                        <div id="usersUploadedValue" class="stat-value">0</div>
                        <div class="stat-label">Users Uploaded</div>
                    </div>
                    <div class="stat-item">
                        <div id="recentUploadsValue" class="stat-value">0</div>
                        <div class="stat-label">Recent (24h)</div>
                    </div>
                </div>
            </div>
            
            <!-- Tab Navigation -->
            <div class="reports-tabs">
                <button class="tab-button active" data-tab="incidents">Incident Reports</button>
                <button class="tab-button" data-tab="evidence">Evidence Uploads</button>
            </div>
            
            <!-- Incidents Tab -->
            <div id="incidents-tab" class="tab-content active">
                <div class="history-container">
                    <button id="downloadHistoryBtn" class="btn btn-primary">Download Reports (CSV)</button>
                    <div style="overflow-x: auto;">
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Source</th>
                                    <th>Location</th>
                                    <th>Message Snippet</th>
                                    <th>User/Admin</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                                <tr>
                                    <td colspan="7" style="text-align:center; padding: 20px;">No reports in this session yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Evidence Tab -->
            <div id="evidence-tab" class="tab-content">
                <div class="evidence-container">
                    <div class="evidence-filters">
                        <select id="evidenceIncidentFilter" class="form-control">
                            <option value="">All Incidents</option>
                        </select>
                        <button id="refreshEvidenceBtn" class="btn btn-primary">Refresh</button>
                        <button id="updateDatabaseBtn" class="btn btn-secondary">Update Database</button>
                        <button id="debugEvidenceBtn" class="btn btn-warning">Debug</button>
                    </div>
                    
                    <div id="debugInfo" style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; display: none;">
                        <h4>Debug Information</h4>
                        <div id="debugContent"></div>
                    </div>
                    
                    <div class="evidence-list" id="evidenceList">
                        <!-- Evidence items will be populated here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Feedback Modal -->
        <div id="reportFeedbackModal" class="modal">
            <div class="modal-content" style="max-width: 600px;">
                <span class="close" id="closeReportFeedbackModal">&times;</span>
                <div class="feedback-modal-header">
                    <h2>📊 Report Feedback</h2>
                    <p>Help us improve our incident reporting system</p>
                </div>
                
                <form id="reportFeedbackForm">
                    <input type="hidden" id="feedbackIncidentId" name="incident_id">
                    
                    <div class="feedback-section">
                        <h3>Incident Information</h3>
                        <div class="incident-summary" id="feedbackIncidentSummary">
                            <!-- Incident details will be populated here -->
                        </div>
                    </div>
                    
                    <div class="feedback-section">
                        <h3>Your Rating</h3>
                        <div class="rating-group">
                            <label>Overall Report Quality *</label>
                            <div class="star-rating" id="overallReportRating">
                                <span class="star" data-rating="1">★</span>
                                <span class="star" data-rating="2">★</span>
                                <span class="star" data-rating="3">★</span>
                                <span class="star" data-rating="4">★</span>
                                <span class="star" data-rating="5">★</span>
                            </div>
                            <div class="rating-label" id="overallRatingLabel">Click to rate</div>
                        </div>
                        
                        <div class="detailed-ratings-grid">
                            <div class="rating-item">
                                <label>Accuracy</label>
                                <div class="mini-star-rating" id="accuracyRating">
                                    <span class="mini-star" data-rating="1">★</span>
                                    <span class="mini-star" data-rating="2">★</span>
                                    <span class="mini-star" data-rating="3">★</span>
                                    <span class="mini-star" data-rating="4">★</span>
                                    <span class="mini-star" data-rating="5">★</span>
                                </div>
                            </div>
                            <div class="rating-item">
                                <label>Completeness</label>
                                <div class="mini-star-rating" id="completenessRating">
                                    <span class="mini-star" data-rating="1">★</span>
                                    <span class="mini-star" data-rating="2">★</span>
                                    <span class="mini-star" data-rating="3">★</span>
                                    <span class="mini-star" data-rating="4">★</span>
                                    <span class="mini-star" data-rating="5">★</span>
                                </div>
                            </div>
                            <div class="rating-item">
                                <label>Timeliness</label>
                                <div class="mini-star-rating" id="timelinessRating">
                                    <span class="mini-star" data-rating="1">★</span>
                                    <span class="mini-star" data-rating="2">★</span>
                                    <span class="mini-star" data-rating="3">★</span>
                                    <span class="mini-star" data-rating="4">★</span>
                                    <span class="mini-star" data-rating="5">★</span>
                                </div>
                            </div>
                            <div class="rating-item">
                                <label>Usefulness</label>
                                <div class="mini-star-rating" id="usefulnessRating">
                                    <span class="mini-star" data-rating="1">★</span>
                                    <span class="mini-star" data-rating="2">★</span>
                                    <span class="mini-star" data-rating="3">★</span>
                                    <span class="mini-star" data-rating="4">★</span>
                                    <span class="mini-star" data-rating="5">★</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="feedback-section">
                        <h3>Additional Comments</h3>
                        <div class="form-group">
                            <label for="feedbackComments">What could we improve about this report?</label>
                            <textarea id="feedbackComments" name="comments" rows="4" 
                                      placeholder="Share your thoughts, suggestions, or concerns..."></textarea>
                        </div>
                        
                        <div class="feedback-categories">
                            <label>Category (Optional)</label>
                            <div class="category-options">
                                <label class="category-option">
                                    <input type="radio" name="category" value="accuracy" id="cat-accuracy">
                                    <span class="category-label">Accuracy Issues</span>
                                </label>
                                <label class="category-option">
                                    <input type="radio" name="category" value="completeness" id="cat-completeness">
                                    <span class="category-label">Missing Information</span>
                                </label>
                                <label class="category-option">
                                    <input type="radio" name="category" value="timing" id="cat-timing">
                                    <span class="category-label">Timing Concerns</span>
                                </label>
                                <label class="category-option">
                                    <input type="radio" name="category" value="usability" id="cat-usability">
                                    <span class="category-label">Usability Issues</span>
                                </label>
                                <label class="category-option">
                                    <input type="radio" name="category" value="other" id="cat-other">
                                    <span class="category-label">Other</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="feedback-section">
                        <h3>Contact Information</h3>
                        <div class="form-group">
                            <label for="feedbackPhone">Phone Number (Optional)</label>
                            <input type="tel" id="feedbackPhone" name="phone" 
                                   placeholder="09XXXXXXXXX" pattern="09[0-9]{9}">
                            <small>We'll only contact you if we need clarification about your feedback</small>
                        </div>
                    </div>
                    
                    <div class="feedback-actions">
                        <button type="button" class="btn btn-secondary" id="cancelFeedbackBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitFeedbackBtn">Submit Feedback</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Feedback Response Modal -->
        <div id="feedbackResponseModal" class="modal">
            <div class="modal-content" style="max-width: 500px;">
                <span class="close" id="closeFeedbackResponseModal">&times;</span>
                <h2>Respond to Feedback</h2>
                
                <div class="response-info">
                    <p><strong>Incident ID:</strong> <span id="responseIncidentId"></span></p>
                    <p><strong>User Phone:</strong> <span id="responseUserPhone"></span></p>
                    <p><strong>User Rating:</strong> <span id="responseUserRating"></span></p>
                    <p><strong>User Feedback:</strong> <span id="responseUserFeedback"></span></p>
                </div>
                
                <div class="form-group">
                    <label for="responseStatus">Update Status</label>
                    <select id="responseStatus" class="form-control">
                        <option value="pending">Pending</option>
                        <option value="reviewed">Reviewed</option>
                        <option value="addressed">Addressed</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="responseMessage">Admin Response (Optional)</label>
                    <textarea id="responseMessage" class="form-control" rows="4" 
                              placeholder="Add a response or note..."></textarea>
                </div>
                
                <div class="modal-actions">
                    <button class="btn btn-secondary" id="cancelFeedbackResponseBtn">Cancel</button>
                    <button class="btn btn-primary" id="submitFeedbackResponseBtn">Submit Response</button>
                </div>
            </div>
        </div>

        <!-- Settings Content (Modern, User-Friendly) -->
        <div id="settings-content" class="content-body" style="display: none;">
            <h1 class="content-header">Admin Settings</h1>
            <div class="settings-main-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 32px; margin: 0 0 40px 0;">
                <!-- System Settings Card -->
                <div class="settings-card">
                    <div class="settings-card-header"><i>⚙️</i> System Settings</div>
                    <div class="settings-card-body">
                            <div class="form-group">
                            <label for="updateIntervalSelect">Update Interval</label>
                                <select id="updateIntervalSelect" class="form-control">
                                    <option value="1">1 minute</option>
                                    <option value="2">2 minutes</option>
                                    <option value="5">5 minutes</option>
                                    <option value="10">10 minutes</option>
                                    <option value="15">15 minutes</option>
                                </select>
                            </div>
                            <div class="form-group">
                            <label for="notificationSoundSelect">Notification Sound</label>
                                <select id="notificationSoundSelect" class="form-control">
                                    <option value="enabled">Enabled</option>
                                    <option value="disabled">Disabled</option>
                                </select>
                            </div>
                            <div class="form-group">
                            <label for="sliderIntervalSelect">Login Slider Interval</label>
                                <select id="sliderIntervalSelect" class="form-control">
                                    <option value="3000">3 seconds</option>
                                    <option value="5000">5 seconds</option>
                                    <option value="7000">7 seconds</option>
                                    <option value="10000">10 seconds</option>
                                </select>
                            </div>
                        </div>
                    </div>
                <!-- Apify Settings Card -->
                <div class="settings-card">
                    <div class="settings-card-header"><i>🔍</i> Apify Settings</div>
                    <div class="settings-card-body">
                        <div class="form-group">
                            <label for="apifyQueryInput">Apify Query</label>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="text" id="apifyQueryInput" class="form-control" placeholder="Enter Apify search query..." style="flex:1;">
                                <button id="saveApifyQueryBtn" class="btn btn-primary" style="min-width: 100px;">Save</button>
                </div>
                            <div id="apifyQuerySaveMsg" style="margin-top: 8px; color: var(--success); display: none; font-size: 0.95rem;">Saved!</div>
                        </div>
                        <!-- Apify Token Input -->
                        <div class="form-group">
                            <label for="apifyTokenInput">Apify Token</label>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="text" id="apifyTokenInput" class="form-control" placeholder="Enter Apify API token..." style="flex:1;">
                                <button id="saveApifyTokenBtn" class="btn btn-primary" style="min-width: 100px;">Save</button>
                                <button id="resetApifyTokenBtn" class="btn btn-secondary" style="min-width: 100px;">Reset</button>
                            </div>
                            <div id="apifyTokenSaveMsg" style="margin-top: 8px; color: var(--success); display: none; font-size: 0.95rem;">Saved!</div>
                        </div>
                    </div>
                </div>
                <!-- NLP Locations Card -->
                <div class="settings-card" style="grid-column: span 2;">
                    <div class="settings-card-header"><i>📍</i> NLP Locations</div>
                    <div class="settings-card-body">
                        <div class="form-group" style="display: flex; gap: 10px; align-items: flex-end;">
                            <div style="flex: 1;">
                                <label for="nlpLocationInput">Add Location</label>
                                <input type="text" id="nlpLocationInput" class="form-control" placeholder="Enter new location...">
                            </div>
                            <button id="addNlpLocationBtn" class="btn btn-primary" style="min-width: 120px;">Add</button>
                        </div>
                        <div id="nlpLocationMsg" style="margin-bottom: 10px; color: var(--success); display: none; font-size: 0.95rem;">Location added!</div>
                        <div class="form-group">
                            <label>All Locations (Saved & Hardcoded)</label>
                            <div id="nlpLocationsBox" style="background: var(--bg-main); border-radius: 8px; padding: 15px; min-height: 60px; max-height: 200px; overflow-y: auto; display: flex; flex-wrap: wrap; gap: 8px;"></div>
                        </div>
                    </div>
                </div>
                <!-- Logo Management Card -->
                <div class="settings-card">
                    <div class="settings-card-header"><i>🖼️</i> Logo Management</div>
                    <div class="settings-card-body">
                        <div class="logo-settings">
                            <!-- Main Logo (Konekt) -->
                            <div class="logo-preview">
                                <h3>Main Logo (Konekt)</h3>
                                <img id="currentLogo" src="Konekt.png" alt="Current Logo" style="max-width:120px;max-height:120px;">
                                <div class="logo-info">This logo appears in the sidebar header</div>
                            </div>
                            <div class="logo-upload">
                                <input type="file" id="logoUpload" accept="image/*" style="display: none;">
                                <button class="btn btn-primary" onclick="document.getElementById('logoUpload').click()">Choose New Main Logo</button>
                                <div class="selected-file" id="selectedFileName"></div>
                                <div class="logo-actions" style="margin-top:10px;">
                                    <button class="btn btn-primary" id="uploadLogoBtn">Upload Main Logo</button>
                                    <button class="btn btn-secondary" id="resetLogoBtn">Reset Main Logo</button>
                                </div>
                            </div>
                            
                            <!-- Sidebar Logo -->
                            <div class="logo-preview" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--bg-main);">
                                <h3>Sidebar Logo</h3>
                                <img id="currentSidebarLogo" src="images/logo.png" alt="Current Sidebar Logo" style="max-width:120px;max-height:120px;">
                                <div class="logo-info">This logo appears in the profile section</div>
                            </div>
                            <div class="logo-upload">
                                <input type="file" id="sidebarLogoUpload" accept="image/*" style="display: none;">
                                <button class="btn btn-primary" onclick="document.getElementById('sidebarLogoUpload').click()">Choose New Sidebar Logo</button>
                                <div class="selected-file" id="selectedSidebarFileName"></div>
                                <div class="logo-actions" style="margin-top:10px;">
                                    <button class="btn btn-primary" id="uploadSidebarLogoBtn">Upload Sidebar Logo</button>
                                    <button class="btn btn-secondary" id="resetSidebarLogoBtn">Reset Sidebar Logo</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- User Management Card -->
                <div class="settings-card">
                    <div class="settings-card-header"><i>👥</i> User Management</div>
                    <div class="settings-card-body">
                        <button id="openUserManagementBtn" class="btn btn-primary" style="width: 100%;">Manage Users</button>
                    </div>
                </div>
                <!-- Slider Image Management Card -->
                <div class="settings-card">
                    <div class="settings-card-header"><i>🖼️</i> Login Slider Images</div>
                    <div class="settings-card-body">
                        <div class="slider-management">
                            <p>Manage images that appear in the login page slider.</p>
                            <div class="slider-upload">
                                <input type="file" id="sliderImageUpload" accept="image/*" multiple style="display: none;">
                                <button class="btn btn-primary" onclick="document.getElementById('sliderImageUpload').click()">Add Images</button>
                                <div class="selected-files" id="selectedSliderFiles"></div>
                            </div>
                            <div class="slider-preview">
                                <h4>Current Slider Images</h4>
                                <div id="sliderImagesList" class="slider-images-grid"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <style>
        .settings-main-grid {
            margin-top: 30px;
        }
        .settings-card {
            background: var(--bg-light);
            border-radius: 16px;
            box-shadow: 0 4px 16px var(--shadow);
            padding: 0 0 24px 0;
            display: flex;
            flex-direction: column;
            min-width: 0;
            min-height: 0;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.06);
        }
        .settings-card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--text-light);
            font-weight: 600;
            font-size: 1.1rem;
            padding: 18px 24px;
            border-bottom: 1px solid rgba(0,0,0,0.04);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .settings-card-body {
            padding: 24px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        @media (max-width: 900px) {
            .settings-main-grid {
                grid-template-columns: 1fr;
            }
        }
        </style>
    </div>

    <!-- Footer -->
    <div class="dashboard-footer">
        <div class="footer-section">
            <div class="footer-item">
                <i>📅</i>
                <span id="currentDate">Loading date...</span>
            </div>
            <div class="footer-item">
                <i>⏰</i>
                <span id="currentTime">Loading time...</span>
            </div>
        </div>

        <div class="footer-section">
            <div class="footer-item">
                <i>📍</i>
                <span id="currentLocation">Fetching location...</span>
            </div>
        </div>
    </div>

    <!-- System Status Modal Popup -->
    <div id="systemStatusModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" id="closeSystemStatusModal">&times;</span>
            <h2>System Status</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <div id="systemApiStatusValue" class="stat-value" style="color: var(--warning);">Connecting...</div>
                    <div class="stat-label">API Status</div>
                </div>
                <div class="stat-item">
                    <div id="actorStatusValue" class="stat-value" style="color: var(--warning);">Checking...</div>
                    <div class="stat-label">Actor Status</div>
                </div>
                <div class="stat-item">
                    <div id="lastRunStatusValue" class="stat-value" style="color: var(--warning);">-</div>
                    <div class="stat-label">Last Run</div>
                </div>
                <div class="stat-item">
                    <div id="connectionStatusValue" class="stat-value" style="color: var(--warning);">Checking...</div>
                    <div class="stat-label">Connection</div>
                </div>
            </div>
            <div id="connectionDetails" style="margin-top: 15px; font-size: 0.9rem; color: var(--text-muted);">
                Last checked: Never
            </div>
        </div>
    </div>
    <button id="openSystemStatusModal" class="btn btn-secondary" style="position:fixed; top:20px; right:20px; z-index:100;">System Status</button>

    <!-- Update audio element for notification sound -->
    <audio id="notificationSound" src="mixkit-alert-bells-echo-765.wav" preload="auto"></audio>

    <!-- User Management Modal -->
    <div id="userManagementModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <span class="close" id="closeUserManagementModal">&times;</span>
            <h2>User Management</h2>
            
            <!-- Create New User Section -->
            <div class="user-management-section">
                <h3>Create New User</h3>
                <div class="form-group">
                    <label>User Type</label>
                    <select id="userTypeSelect" class="form-control">
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="newUserName" class="form-control" placeholder="Enter full name">
                </div>
                <!-- Add Email field to user creation form -->
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="newUserEmail" class="form-control" placeholder="Enter email address" required>
                </div>
                <button id="createUserBtn" class="btn btn-primary">Create User</button>
            </div>

            <!-- User List Section -->
            <div class="user-management-section">
                <h3>User List</h3>
                <div class="user-list-container">
                    <table class="user-list-table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Password</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="userListBody">
                            <!-- Users will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="changePasswordModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <span class="close" id="closeChangePasswordModal">&times;</span>
            <h2>Change Password</h2>
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" id="currentPassword" class="form-control">
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" id="newPassword" class="form-control">
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" id="confirmPassword" class="form-control">
            </div>
            <button id="changePasswordBtn" class="btn btn-primary">Change Password</button>
        </div>
    </div>

    <!-- New User Credentials Modal -->
    <div id="newUserCredentialsModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width: 500px;">
            <span class="close" id="closeNewUserCredentialsModal">&times;</span>
            <h2>New User Credentials</h2>
            <div id="newUserCredentialsInfo" style="margin: 15px 0;"></div>
            <div class="modal-actions">
                <button id="copyCredentialsBtn" class="btn btn-primary">Copy Credentials</button>
            </div>
        </div>
    </div>

    <script>
        // Initialize user manager
        const userManager = new UserManager();
        
        // Map markers and data management
        let map;
        let markers = new Map();
        let markerCluster;
        let liveIncidentsData = new Map();
        let resolvedIncidentsData = new Map();
        let dashboardReportStats = {
            totalReportedThisSession: 0,
            activeCases: 0
        };
        let isInitialIncidentLoad = true; // Flag to prevent initial render of the active list

        // --- START: BFP LANDMARK DATA ---
        const bfpLandmarks = [
            { name: "BFP Dasmariñas City Main Station", lat: 14.3285, lng: 120.9370, address: "Aguinaldo Highway, Dasmariñas, Cavite" },
            { name: "BFP Salawag Sub-Station", lat: 14.2900, lng: 120.9700, address: "Salawag, Dasmariñas, Cavite" },
            { name: "BFP Paliparan Sub-Station", lat: 14.3000, lng: 120.9900, address: "Paliparan, Dasmariñas, Cavite" }
        ];
        // --- END: BFP LANDMARK DATA ---

        // Function to update quick stats display
        function updateQuickStatsDisplay() {
            const activeCasesEl = document.getElementById('activeCasesValue');
            const totalReportsEl = document.getElementById('totalReportsValue');
            const responseRateEl = document.getElementById('responseRateValue');

            const activeCases = liveIncidentsData.size;
            const resolvedCases = resolvedIncidentsData.size;
            const totalCases = activeCases + resolvedCases;
            let responseRate = 0;
            if (totalCases > 0) {
                responseRate = (resolvedCases / totalCases) * 100;
            }
            if (activeCasesEl) activeCasesEl.textContent = activeCases;
            if (totalReportsEl) totalReportsEl.textContent = totalCases;
            if (responseRateEl) responseRateEl.textContent = responseRate.toFixed(0) + '%';
        }

        // Function to update API Status display in System Status panel
        function updateApiStatusDisplay(statusText, statusColor = 'var(--text-dark)') {
            const apiStatusEl = document.getElementById('systemApiStatusValue');
            if (apiStatusEl) {
                apiStatusEl.textContent = statusText;
                apiStatusEl.style.color = statusColor;
            }
        }

        // Initialize map with incident markers
        function initMap() {
            map = L.map('map').setView([14.3294, 120.9367], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add marker cluster group for dynamic incidents
            markerCluster = L.markerClusterGroup();
            map.addLayer(markerCluster);

            // Add BFP Landmarks to Map
            bfpLandmarks.forEach(landmark => {
                const landmarkMarker = L.marker([landmark.lat, landmark.lng])
                    .addTo(map)
                    .bindPopup(`<b>${landmark.name}</b><br>${landmark.address || 'Address not available'}`);
            });

            // Add Geocoder Search Control
            L.Control.geocoder({
                defaultMarkGeocode: true,
                geocoder: L.Control.Geocoder.nominatim(),
                placeholder: 'Search for a location...', 
                errorMessage: 'Nothing found.'
            })
            .on('markgeocode', function(e) {
                if (e.geocode && e.geocode.center) {
                    map.setView(e.geocode.center, 15);
                }
            })
            .addTo(map);
        }

        // Convert address to coordinates using server-side proxy
        async function geocodeAddress(address) {
            try {
                const response = await fetch(`api/geocode.php?q=${encodeURIComponent(address)}`);
                const data = await response.json();
                if (Array.isArray(data) && data.length > 0) {
                    return {
                        lat: parseFloat(data[0].lat),
                        lng: parseFloat(data[0].lon)
                    };
                }
                return null;
            } catch (error) {
                console.error('Geocoding error:', error);
                return null;
            }
        }

        // Function to render the active incidents list on the dashboard
        function renderActiveIncidentsList() {
            const listElement = document.getElementById('activeIncidentsList');
            const filterSelect = document.getElementById('incidentFilter');
            if (!listElement || !filterSelect) return;

            listElement.innerHTML = '';

            const filter = filterSelect.value;
            let incidents = [];
            if (filter === 'all') {
                incidents = [...liveIncidentsData.values(), ...resolvedIncidentsData.values()];
            } else if (filter === 'resolved') {
                incidents = Array.from(resolvedIncidentsData.values());
            } else {
                incidents = Array.from(liveIncidentsData.values());
            }

            if (incidents.length === 0) {
                const listItem = document.createElement('li');
                listItem.className = 'no-active-incidents';
                listItem.textContent = 'No incidents match the current filter.';
                listElement.appendChild(listItem);
                return;
            }

            incidents.sort((a, b) => b.timestamp - a.timestamp);

            incidents.forEach(incident => {
                const listItem = document.createElement('li');
                const infoDiv = document.createElement('div');
                infoDiv.className = 'incident-info';
                const locationSpan = document.createElement('span');
                locationSpan.className = 'incident-location';
                locationSpan.textContent =
                    incident.location
                    || ((incident.lat && incident.lng) ? `Lat: ${incident.lat}, Lng: ${incident.lng}` : 'Unknown Location');
                infoDiv.appendChild(locationSpan);
                const timeSpan = document.createElement('span');
                timeSpan.className = 'incident-time';
                let dateObj = null;
                if (incident.created_at) {
                    dateObj = new Date(incident.created_at);
                } else if (incident.timestamp) {
                    dateObj = new Date(incident.timestamp);
                }
                timeSpan.textContent = `Reported: ${dateObj ? dateObj.toLocaleString() : 'Unknown Date'}`;
                infoDiv.appendChild(timeSpan);
                const contentDiv = document.createElement('div');
                contentDiv.className = 'incident-content';
                contentDiv.textContent = incident.message || incident.description || incident.text || 'No content available.';
                contentDiv.style.margin = '8px 0';
                infoDiv.appendChild(contentDiv);
                if (incident.url) {
                    const permalinkDiv = document.createElement('div');
                    permalinkDiv.className = 'incident-permalink';
                    permalinkDiv.innerHTML = `<a href="${incident.url}" target="_blank" style="color: var(--primary); text-decoration: underline;">View Post</a>`;
                    infoDiv.appendChild(permalinkDiv);
                }
                const metricsDiv = document.createElement('div');
                metricsDiv.className = 'incident-metrics';
                metricsDiv.innerHTML = `
                    <span class="metric likes">👍 ${incident.likes || 0}</span>
                    <span class="metric comments">💬 ${incident.comments || 0}</span>
                    <span class="metric shares">🔄 ${incident.shares || 0}</span>
                `;
                infoDiv.appendChild(metricsDiv);
                if (incident.hashtags && incident.hashtags.length > 0) {
                    const hashtagsDiv = document.createElement('div');
                    hashtagsDiv.className = 'incident-hashtags';
                    hashtagsDiv.innerHTML = incident.hashtags.map(tag => `<span class="hashtag">${tag}</span>`).join(' ');
                    infoDiv.appendChild(hashtagsDiv);
                }
                listItem.appendChild(infoDiv);
                const actionsDiv = document.createElement('div');
                actionsDiv.className = 'incident-actions';
                if (incident.currentStatus === 'active') {
                    const resolveButton = document.createElement('button');
                    resolveButton.className = 'btn-resolve';
                    resolveButton.textContent = 'Mark Resolved';
                    resolveButton.onclick = () => {
                        updateIncidentStatus(incident.id, 'resolved');
                    };
                    actionsDiv.appendChild(resolveButton);
                    const falseAlarmButton = document.createElement('button');
                    falseAlarmButton.className = 'false-alarm-button action-button';
                    falseAlarmButton.textContent = 'False Alarm';
                    falseAlarmButton.onclick = () => {
                        updateIncidentStatus(incident.id, 'false_alarm');
                    };
                    actionsDiv.appendChild(falseAlarmButton);
                    const nonIncidentButton = document.createElement('button');
                    nonIncidentButton.className = 'non-incident-button action-button';
                    nonIncidentButton.textContent = 'Non-Incident';
                    nonIncidentButton.onclick = () => {
                        updateIncidentStatus(incident.id, 'non_incident');
                    };
                    actionsDiv.appendChild(nonIncidentButton);
                }
                const viewButton = document.createElement('button');
                viewButton.className = 'btn-view';
                viewButton.textContent = 'View Post';
                viewButton.onclick = () => {
                    window.open(incident.url, '_blank');
                };
                actionsDiv.appendChild(viewButton);
                listItem.appendChild(actionsDiv);
                listElement.appendChild(listItem);
            });
        }

        // Add incident marker to map
        async function addIncidentMarker(incident, openPopup = true) {
            try {
                let coords = null;
                if (incident.lat && incident.lng) {
                    coords = { lat: parseFloat(incident.lat), lng: parseFloat(incident.lng) };
                } else if (incident.location) {
                    coords = await geocodeAddress(incident.location);
                }

                // Fallback: if geocoding fails, use a random BFP station
                let usedFallback = false;
                let fallbackStation = null;
                if (!coords) {
                    fallbackStation = bfpLandmarks[Math.floor(Math.random() * bfpLandmarks.length)];
                    coords = { lat: fallbackStation.lat, lng: fallbackStation.lng };
                    usedFallback = true;
                }

                // Always add to liveIncidentsData, even if coords is null
                if (!liveIncidentsData.has(incident.id)) {
                    const newIncidentEntry = { ...incident, currentStatus: incident.currentStatus || 'active' };
                    liveIncidentsData.set(incident.id, newIncidentEntry);
                    dashboardReportStats.totalReportedThisSession++;
                    dashboardReportStats.activeCases++;
                    updateQuickStatsDisplay();
                    renderHistoryTable();
                    renderActiveIncidentsList();
                }

                if (coords) {
                const incidentEntry = liveIncidentsData.get(incident.id);

                    // Create marker icon (assuming 'icon' is defined elsewhere or using default)
                    // If you have a custom icon definition, ensure it's accessible here
                    const defaultIcon = L.icon({
                        iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
                        shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    });

                    // Create the marker
                    const marker = L.marker([coords.lat, coords.lng], { icon: defaultIcon });

                    let popupContent = `
                    <div class="incident-popup">
                        <h3>Fire Incident</h3>
                        <p><strong>Location:</strong> ${incident.location || (incident.lat && incident.lng ? `Lat: ${incident.lat}, Lng: ${incident.lng}` : 'Unknown Location')}</p>
                        <p><strong>Reported by:</strong> ${incident.source}</p>
                        <p><strong>Time:</strong> ${new Date(incident.timestamp).toLocaleString()}</p>
                        <p><strong>Status:</strong> <span class="incident-status-text ${incidentEntry.currentStatus === 'active' ? 'status-active' : 'status-resolved'}">${incidentEntry.currentStatus === 'active' ? 'Active' : 'Resolved'}</span></p>
                        <p><strong>Details:</strong> ${incident.message}</p>
                        <div class="incident-metrics">
                            <span class="metric">👍 ${incident.likes || 0} likes</span>
                            <span class="metric">💬 ${incident.comments || 0} comments</span>
                            <span class="metric">🔄 ${incident.shares || 0} shares</span>
                        </div>
                            ${(incident.hashtags && incident.hashtags.length > 0) ? 
                            `<div class="incident-hashtags">${incident.hashtags.map(tag => `<span class="hashtag">${tag}</span>`).join(' ')}</div>` : 
                            ''}
                            ${usedFallback ? `<div style=\"color:orange;\"><b>Note:</b> Exact location unknown. Pinned at ${fallbackStation.name}.</div>` : ''}
                        
                            ${incidentEntry.currentStatus === 'active' ? 
                                    `<button onclick=\"updateIncidentStatus('${incident.id}', 'resolved')\">Mark Resolved</button>` : 
                                    `<button onclick=\"updateIncidentStatus('${incident.id}', 'active')\">Mark Active</button>`}
                                <button onclick=\"window.open('${incident.url}', '_blank')\">View Post</button>
                                <button onclick=\"removeIncident('${incident.id}')\">Remove Incident</button>
                        </div>
                    </div>
                `;
                marker.bindPopup(popupContent);
                if (openPopup) {
                    marker.openPopup();
                }
                markerCluster.addLayer(marker);
                markers.set(incident.id, marker);

                    // Add a radius circle (e.g., 2km)
                    const circle = L.circle([coords.lat, coords.lng], {
                        color: 'orange',
                        fillColor: '#ffa50033',
                        fillOpacity: 0.3,
                        radius: 2000 // 2km
                    }).addTo(map);

                    map.setView([coords.lat, coords.lng], 13);
                }
            } catch (error) {
                console.error('Error adding marker:', error);
            }
        }

        // Remove incident marker from map
        function removeIncidentMarker(incidentId) {
            const marker = markers.get(incidentId);
            if (marker) {
                markerCluster.removeLayer(marker);
                markers.delete(incidentId);
            }
        }

        // Remove incident
        function removeIncident(incidentId) {
            const incidentEntry = liveIncidentsData.get(incidentId);
            if (incidentEntry) {
                if (incidentEntry.currentStatus === 'active') {
                    dashboardReportStats.activeCases--;
                }
                liveIncidentsData.delete(incidentId);
                updateQuickStatsDisplay();
                renderHistoryTable();
                renderActiveIncidentsList();
            }
            removeIncidentMarker(incidentId);
            map.closePopup();
        }

        // Update incident status
        async function updateIncidentStatus(incidentId, newStatus) {
            const incidentEntry = liveIncidentsData.get(incidentId);
            if (!incidentEntry) return;

            try {
                const response = await fetch('api/insert_incident.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: incidentId,
                        type: incidentEntry.type,
                        location: incidentEntry.location,
                        message: incidentEntry.message,
                        source: incidentEntry.source,
                        url: incidentEntry.url,
                        timestamp: incidentEntry.timestamp,
                        confidence: incidentEntry.confidence,
                        status: newStatus
                    })
                });

                const data = await response.json();
                if (!data.success) {
                    throw new Error(data.message || 'Failed to update incident status');
                }

                // Move to resolvedIncidentsData if not active
                if (newStatus !== 'active') {
                    liveIncidentsData.delete(incidentId);
                    const resolvedIncident = { ...incidentEntry, currentStatus: newStatus };
                    resolvedIncidentsData.set(incidentId, resolvedIncident);
                } else {
                    incidentEntry.currentStatus = newStatus;
                }

                updateQuickStatsDisplay();
                renderHistoryTable();
                renderActiveIncidentsList();
                updateReportsAnalyticsCharts();

            } catch (error) {
                console.error('Error updating incident status:', error);
                createNotification('Error', 'Failed to update incident status', 'error');
            }
        }

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', async () => {
            try {
                console.log('DOM Content Loaded - Starting initialization...');
                
                // Clear any cached data
                if ('caches' in window) {
                    try {
                        const cacheNames = await caches.keys();
                        await Promise.all(cacheNames.map(name => caches.delete(name)));
                        console.log('Cache cleared successfully');
                    } catch (cacheError) {
                        console.warn('Could not clear cache:', cacheError);
                    }
                }
                
                // Initialize navigation first
                initializeNavigation();
                
                // Test navigation manually
                setTimeout(() => {
                    console.log('Testing navigation...');
                    const testItem = document.querySelector('.menu-item[data-page="reports"]');
                    if (testItem) {
                        console.log('Found reports menu item, testing click...');
                        testItem.click();
                    } else {
                        console.log('Reports menu item not found');
                    }
                }, 1000);
                
                // Initialize dashboard components
                updateQuickStatsDisplay();
                updateApiStatusDisplay('Initializing...', 'var(--warning)');
                renderHistoryTable();
                renderActiveIncidentsList();
                
                // Initialize feedback system
                initializeEvidenceSystem();

                console.log('Dashboard initialized successfully');
            } catch (error) {
                console.error('Failed to initialize:', error);
                createNotification('Error', 'Failed to initialize system: ' + error.message, 'error');
            }
        });

        // Global navigation function
        function navigateToPage(pageId) {
            console.log('navigateToPage called with:', pageId);
            
            const menuItems = document.querySelectorAll('.menu-item');
            const contentSections = {
                'dashboard': document.getElementById('dashboard-content'),
                'map': document.getElementById('map-content'),
                'reports': document.getElementById('reports-content'),
                'settings': document.getElementById('settings-content')
            };

            // Remove active class from all menu items
            menuItems.forEach(i => i.classList.remove('active'));
            
            // Add active class to clicked item
            const activeMenuItem = document.querySelector(`[data-page="${pageId}"]`);
            if (activeMenuItem) {
                activeMenuItem.classList.add('active');
            }
            
            // Hide all content sections
            Object.values(contentSections).forEach(section => {
                if (section) {
                    section.style.display = 'none';
                }
            });
            
            // Show the active section
            const activeSection = contentSections[pageId];
            if (activeSection) {
                activeSection.style.display = 'block';
                console.log('Showing section:', pageId);
            } else {
                console.error('Section not found:', pageId);
            }
            
            // Initialize map if needed
            if (pageId === 'map' && typeof initMap === 'function') {
                if (!window.mapInitialized) {
                    initMap();
                    window.mapInitialized = true;
                } else if (window.map) {
                    setTimeout(() => { window.map.invalidateSize(); }, 200);
                }
            }
            
            // Handle settings panel
            if (pageId === 'settings') {
                const sliderImageInput = document.getElementById('sliderImageUpload');
                if (sliderImageInput && !sliderImageInput.dataset.handlerAttached) {
                    sliderImageInput.addEventListener('change', async function(e) {
                        const files = e.target.files;
                        const selectedFilesDiv = document.getElementById('selectedSliderFiles');
                        if (!selectedFilesDiv) {
                            console.error('selectedSliderFiles element not found');
                            return;
                        }
                        selectedFilesDiv.textContent = '';
                        if (files.length) {
                            selectedFilesDiv.textContent = Array.from(files).map(f => f.name).join(', ');
                        }
                        if (!files.length) return;
                        const formData = new FormData();
                        for (let i = 0; i < files.length; i++) {
                            formData.append('images[]', files[i]);
                        }
                        try {
                            console.log('Uploading slider images:', Array.from(files).map(f => f.name));
                            const response = await fetch('api/upload-slider-images.php', {
                                method: 'POST',
                                body: formData
                            });
                            const data = await response.json();
                            console.log('Upload response:', data);
                            if (data.success) {
                                createNotification('Success', 'Images uploaded successfully', 'success');
                                loadSliderImages();
                            } else {
                                createNotification('Error', data.message || 'Failed to upload images', 'error');
                            }
                        } catch (error) {
                            console.error('Error uploading images:', error);
                            createNotification('Error', 'Failed to upload images: ' + error.message, 'error');
                        }
                        // Clear file input and selected file names
                        e.target.value = '';
                        selectedFilesDiv.textContent = '';
                    });
                    sliderImageInput.dataset.handlerAttached = 'true';
                }
                if (typeof loadSliderImages === 'function') {
                    loadSliderImages();
                }
            }
        }

        // Navigation initialization function
        function initializeNavigation() {
            const menuItems = document.querySelectorAll('.menu-item');
            const contentSections = {
                'dashboard': document.getElementById('dashboard-content'),
                'map': document.getElementById('map-content'),
                'reports': document.getElementById('reports-content'),
                'settings': document.getElementById('settings-content')
            };

            let mapInitialized = false;

            // Debug: Check if elements exist
            console.log('Content sections found:', {
                dashboard: !!contentSections.dashboard,
                map: !!contentSections.map,
                reports: !!contentSections.reports,
                settings: !!contentSections.settings
            });

            console.log('Menu items found:', menuItems.length);
            menuItems.forEach((item, index) => {
                console.log(`Menu item ${index}:`, item.getAttribute('data-page'));
            });

            // Add CSS to ensure menu items are clickable
            const style = document.createElement('style');
            style.textContent = `
                .menu-item {
                    cursor: pointer !important;
                    pointer-events: auto !important;
                    user-select: none;
                    position: relative;
                    z-index: 1000;
                }
                .menu-item:hover {
                    cursor: pointer !important;
                }
                .menu-items {
                    position: relative;
                    z-index: 1000;
                }
            `;
            document.head.appendChild(style);

            // Add global click handler for debugging
            document.addEventListener('click', function(e) {
                if (e.target.closest('.menu-item')) {
                    console.log('Global click detected on menu item:', e.target.closest('.menu-item').getAttribute('data-page'));
                }
            });

            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    console.log('Menu item clicked!', this.getAttribute('data-page'));
                    e.preventDefault();
                    
                    // Remove active class from all menu items
                    menuItems.forEach(i => i.classList.remove('active'));
                    
                    // Add active class to clicked item
                    this.classList.add('active');
                    
                    const pageId = this.getAttribute('data-page');
                    console.log('Navigating to:', pageId);
                    
                    // Hide all content sections
                    Object.values(contentSections).forEach(section => {
                        if (section) {
                            section.style.display = 'none';
                        }
                    });
                    
                    // Show the active section
                    const activeSection = contentSections[pageId];
                    if (activeSection) {
                        activeSection.style.display = 'block';
                        console.log('Showing section:', pageId);
                    } else {
                        console.error('Section not found:', pageId);
                    }
                    
                    // Initialize map if needed
                    if (pageId === 'map') {
                        if (!mapInitialized) {
                            initMap();
                            mapInitialized = true;
                        } else if (window.map) {
                            setTimeout(() => { window.map.invalidateSize(); }, 200);
                        }
                    }
                    
                    // Handle settings panel
                    if (pageId === 'settings') {
                        const sliderImageInput = document.getElementById('sliderImageUpload');
                        if (sliderImageInput && !sliderImageInput.dataset.handlerAttached) {
                            sliderImageInput.addEventListener('change', async function(e) {
                                const files = e.target.files;
                                const selectedFilesDiv = document.getElementById('selectedSliderFiles');
                                if (!selectedFilesDiv) {
                                    console.error('selectedSliderFiles element not found');
                                    return;
                                }
                                selectedFilesDiv.textContent = '';
                                if (files.length) {
                                    selectedFilesDiv.textContent = Array.from(files).map(f => f.name).join(', ');
                                }
                                if (!files.length) return;
                                const formData = new FormData();
                                for (let i = 0; i < files.length; i++) {
                                    formData.append('images[]', files[i]);
                                }
                                try {
                                    console.log('Uploading slider images:', Array.from(files).map(f => f.name));
                                    const response = await fetch('api/upload-slider-images.php', {
                                        method: 'POST',
                                        body: formData
                                    });
                                    const data = await response.json();
                                    console.log('Upload response:', data);
                                    if (data.success) {
                                        createNotification('Success', 'Images uploaded successfully', 'success');
                                        loadSliderImages();
                                    } else {
                                        createNotification('Error', data.message || 'Failed to upload images', 'error');
                                    }
                                } catch (error) {
                                    console.error('Error uploading images:', error);
                                    createNotification('Error', 'Failed to upload images: ' + error.message, 'error');
                                }
                                // Clear file input and selected file names
                                e.target.value = '';
                                selectedFilesDiv.textContent = '';
                            });
                            sliderImageInput.dataset.handlerAttached = 'true';
                        }
                        loadSliderImages();
                    }
                });
            });
        }

        // Feedback System Functions
        let feedbackData = [];
        let feedbackStats = {};
        let evidenceData = [];
        let evidenceStats = {};

        async function initializeEvidenceSystem() {
            // Create feedback table if it doesn't exist
            try {
                await fetch('api/create_feedback_table.php');
            } catch (error) {
                console.error('Error creating feedback table:', error);
            }

            // Load initial feedback data
            await loadEvidenceData();
            
            // Setup tab navigation
            setupTabNavigation();
            
            // Setup evidence event listeners
            setupEvidenceEventListeners();

            // Ensure statistics are visible after initialization
            setTimeout(() => {
                updateEvidenceStats();
                console.log('Evidence system initialized');
            }, 500);
        }

        function setupTabNavigation() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const targetTab = button.dataset.tab;
                    
                    // Update active tab button
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    
                    // Update active tab content
                    tabContents.forEach(content => content.classList.remove('active'));
                    document.getElementById(`${targetTab}-tab`).classList.add('active');
                    
                    // Load data for the active tab
                    if (targetTab === 'evidence') {
                        loadEvidenceData();
                    }
                });
            });
        }

        function setupEvidenceEventListeners() {
            // Evidence incident filter
            const incidentFilter = document.getElementById('evidenceIncidentFilter');
            if (incidentFilter) {
                incidentFilter.addEventListener('change', loadEvidenceData);
            }

            // Refresh evidence button
            const refreshBtn = document.getElementById('refreshEvidenceBtn');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', async () => {
                    await loadEvidenceData();
                    // Force update statistics display
                    setTimeout(() => {
                        updateEvidenceStats();
                    }, 100);
                });
            }

            // Update database button
            const updateDbBtn = document.getElementById('updateDatabaseBtn');
            if (updateDbBtn) {
                updateDbBtn.addEventListener('click', async () => {
                    try {
                        const response = await fetch('api/update_evidence_database.php');
                        const data = await response.json();
                        if (data.success) {
                            createNotification('Success', 'Database updated successfully: ' + data.message, 'success');
                            await loadEvidenceData(); // Reload data after update
                        } else {
                            createNotification('Error', 'Database update failed: ' + data.message, 'error');
                        }
                    } catch (error) {
                        console.error('Database update error:', error);
                        createNotification('Error', 'Database update failed: ' + error.message, 'error');
                    }
                });
            }

            // Feedback response modal
            const responseModal = document.getElementById('feedbackResponseModal');
            const closeResponseModal = document.getElementById('closeFeedbackResponseModal');
            const cancelResponseBtn = document.getElementById('cancelFeedbackResponseBtn');
            const submitResponseBtn = document.getElementById('submitFeedbackResponseBtn');

            if (closeResponseModal) {
                closeResponseModal.addEventListener('click', () => {
                    responseModal.style.display = 'none';
                });
            }

            if (cancelResponseBtn) {
                cancelResponseBtn.addEventListener('click', () => {
                    responseModal.style.display = 'none';
                });
            }

            if (submitResponseBtn) {
                submitResponseBtn.addEventListener('click', submitFeedbackResponse);
            }

            // Close modal when clicking outside
            window.addEventListener('click', (event) => {
                if (event.target === responseModal) {
                    responseModal.style.display = 'none';
                }
            });

            // Report Feedback Modal Event Listeners
            const reportFeedbackModal = document.getElementById('reportFeedbackModal');
            const closeReportFeedbackModal = document.getElementById('closeReportFeedbackModal');
            const cancelFeedbackBtn = document.getElementById('cancelFeedbackBtn');
            const submitFeedbackBtn = document.getElementById('submitFeedbackBtn');
            const reportFeedbackForm = document.getElementById('reportFeedbackForm');

            if (closeReportFeedbackModal) {
                closeReportFeedbackModal.addEventListener('click', () => {
                    reportFeedbackModal.style.display = 'none';
                });
            }

            if (cancelFeedbackBtn) {
                cancelFeedbackBtn.addEventListener('click', () => {
                    reportFeedbackModal.style.display = 'none';
                });
            }

            if (reportFeedbackForm) {
                reportFeedbackForm.addEventListener('submit', submitReportFeedback);
            }

            // Close report feedback modal when clicking outside
            window.addEventListener('click', (event) => {
                if (event.target === reportFeedbackModal) {
                    reportFeedbackModal.style.display = 'none';
                }
            });
        }

        async function loadEvidenceData() {
            console.log('Loading evidence data...');
            try {
                const response = await fetch('api/get_evidence_stats.php');
                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log('Evidence data received:', data);

                if (data.success) {
                    evidenceData = data.recent_evidence || [];
                    evidenceStats = data.stats;
                    console.log('Evidence data set:', evidenceData);
                    console.log('Evidence stats set:', evidenceStats);
                    updateEvidenceStats();
                    renderEvidenceList();
                } else {
                    throw new Error(data.message || 'Failed to load evidence data');
                }
            } catch (error) {
                console.error('Error loading evidence:', error);
                createNotification('Error', 'Failed to load evidence data: ' + error.message, 'error');
            }
        }

        function updateEvidenceStats() {
            try {
                const totalElement = document.getElementById('totalEvidenceValue');
                const incidentsElement = document.getElementById('incidentsWithEvidenceValue');
                const usersElement = document.getElementById('usersUploadedValue');
                const recentElement = document.getElementById('recentUploadsValue');

                if (totalElement) {
                    totalElement.textContent = evidenceStats.total_evidence || 0;
                    totalElement.style.visibility = 'visible';
                    totalElement.style.opacity = '1';
                }

                if (incidentsElement) {
                    incidentsElement.textContent = evidenceStats.total_incidents_with_evidence || 0;
                    incidentsElement.style.visibility = 'visible';
                    incidentsElement.style.opacity = '1';
                }

                if (usersElement) {
                    usersElement.textContent = evidenceStats.total_users_uploaded || 0;
                    usersElement.style.visibility = 'visible';
                    usersElement.style.opacity = '1';
                }

                if (recentElement) {
                    recentElement.textContent = evidenceStats.recent_uploads_24h || 0;
                    recentElement.style.visibility = 'visible';
                    recentElement.style.opacity = '1';
                }

                // Force a reflow to ensure visibility
                if (totalElement) {
                    totalElement.offsetHeight;
                }

                console.log('Evidence stats updated:', evidenceStats);
            } catch (error) {
                console.error('Error updating evidence stats:', error);
            }
        }

        function renderEvidenceList() {
            const evidenceList = document.getElementById('evidenceList');
            if (!evidenceList) return;

            if (evidenceData.length === 0) {
                evidenceList.innerHTML = '<p style="text-align: center; padding: 40px; color: var(--text-muted);">No evidence uploads available.</p>';
                return;
            }

            evidenceList.innerHTML = evidenceData.map(evidence => `
                <div class="evidence-item">
                    <div class="evidence-header">
                        <div class="evidence-info">
                            <div class="evidence-incident-id">Incident ${evidence.incident_id}</div>
                            <div class="evidence-user">${evidence.user_name || evidence.user_id}</div>
                            <div class="evidence-date">${new Date(evidence.created_at).toLocaleString()}</div>
                        </div>
                    </div>
                    
                    ${evidence.comment ? `<div class="evidence-comment">"${evidence.comment}"</div>` : ''}
                    
                    <div class="evidence-image">
                        <strong>Evidence Image:</strong> 
                        <a href="${evidence.image_path}" target="_blank" class="evidence-link">
                            <img src="${evidence.image_path}" alt="Evidence" class="evidence-thumbnail" style="max-width: 100px; max-height: 100px; margin: 5px;">
                        </a>
                    </div>
                    
                    ${evidence.incident_message ? `<div class="evidence-incident-info">
                        <strong>Incident:</strong> ${evidence.incident_message}
                    </div>` : ''}
                    
                    ${evidence.incident_location ? `<div class="evidence-location">
                        <strong>Location:</strong> ${evidence.incident_location}
                    </div>` : ''}
                    
                    <div class="evidence-actions">
                        <button class="btn btn-primary" onclick="viewEvidenceDetails(${evidence.id})">View Details</button>
                        <a href="${evidence.image_path}" target="_blank" class="btn btn-secondary">Open Image</a>
                    </div>
                </div>
            `).join('');
        }

        function generateStars(rating) {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                stars += `<span class="star ${i <= rating ? '' : 'empty'}">★</span>`;
            }
            return stars;
        }

        function generateDetailedRatings(feedback) {
            const details = [];
            
            if (feedback.response_time_rating) {
                details.push(`<div class="feedback-detail">
                    <div class="feedback-detail-label">Response Time</div>
                    <div class="feedback-detail-value">${feedback.response_time_rating}/5</div>
                </div>`);
            }
            
            if (feedback.professionalism_rating) {
                details.push(`<div class="feedback-detail">
                    <div class="feedback-detail-label">Professionalism</div>
                    <div class="feedback-detail-value">${feedback.professionalism_rating}/5</div>
                </div>`);
            }
            
            if (feedback.effectiveness_rating) {
                details.push(`<div class="feedback-detail">
                    <div class="feedback-detail-label">Effectiveness</div>
                    <div class="feedback-detail-value">${feedback.effectiveness_rating}/5</div>
                </div>`);
            }
            
            if (feedback.overall_satisfaction) {
                details.push(`<div class="feedback-detail">
                    <div class="feedback-detail-label">Overall Satisfaction</div>
                    <div class="feedback-detail-value">${feedback.overall_satisfaction}/5</div>
                </div>`);
            }
            
            return details.length > 0 ? `<div class="feedback-details">${details.join('')}</div>` : '';
        }

        function openFeedbackResponse(feedbackId) {
            const feedback = feedbackData.find(f => f.id === feedbackId);
            if (!feedback) return;

            const modal = document.getElementById('feedbackResponseModal');
            document.getElementById('responseIncidentId').value = feedback.incident_id;
            document.getElementById('responseUserPhone').value = feedback.user_name || feedback.user_id;
            document.getElementById('responseUserRating').innerHTML = generateStars(feedback.rating);
            document.getElementById('responseUserFeedback').textContent = feedback.feedback_text || 'No feedback text provided';
            document.getElementById('responseStatus').value = feedback.status;
            document.getElementById('responseMessage').value = '';
            
            // Store feedback ID for submission
            modal.dataset.feedbackId = feedbackId;
            modal.style.display = 'block';
        }

        async function submitFeedbackResponse() {
            const modal = document.getElementById('feedbackResponseModal');
            const feedbackId = modal.dataset.feedbackId;
            const status = document.getElementById('responseStatus').value;
            const message = document.getElementById('responseMessage').value.trim();

            if (!feedbackId) {
                createNotification('Error', 'Invalid feedback ID', 'error');
                return;
            }

            try {
                const response = await fetch('api/update_feedback_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        feedback_id: feedbackId,
                        status: status,
                        admin_response: message || null
                    })
                });

                const data = await response.json();

                if (data.success) {
                    createNotification('Success', 'Feedback response submitted successfully', 'success');
                    modal.style.display = 'none';
                    await loadFeedbackData(); // Refresh feedback list
                } else {
                    throw new Error(data.error || 'Failed to submit response');
                }
            } catch (error) {
                console.error('Error submitting feedback response:', error);
                createNotification('Error', 'Failed to submit feedback response', 'error');
            }
        }

        function viewFeedbackDetails(feedbackId) {
            const feedback = feedbackData.find(f => f.id === feedbackId);
            if (!feedback) return;

            const details = `
                Incident ID: ${feedback.incident_id}
                User: ${feedback.user_name || feedback.user_id}
                Rating: ${feedback.rating}/5
                Status: ${feedback.status}
                Date: ${new Date(feedback.created_at).toLocaleString()}
                ${feedback.feedback_text ? `Feedback: ${feedback.feedback_text}` : ''}
                ${feedback.admin_response ? `Admin Response: ${feedback.admin_response}` : ''}
                ${feedback.evidence_image ? `Evidence: ${feedback.evidence_image}` : ''}
            `;

            alert(details);
        }

        // Function to open report feedback modal
        function openReportFeedback(incidentId) {
            const incident = [...liveIncidentsData.values(), ...resolvedIncidentsData.values()]
                .find(inc => inc.id === incidentId);
            
            if (!incident) {
                createNotification('Error', 'Incident not found', 'error');
                return;
            }

            // Populate incident information
            document.getElementById('feedbackIncidentId').value = incidentId;
            
            const incidentSummary = document.getElementById('feedbackIncidentSummary');
            incidentSummary.innerHTML = `
                <h4>Incident ${incidentId}</h4>
                <p><strong>Location:</strong> ${incident.location || 'Unknown'}</p>
                <p><strong>Source:</strong> ${incident.source || 'Unknown'}</p>
                <p><strong>Status:</strong> ${incident.currentStatus || 'Unknown'}</p>
                <p><strong>Reported:</strong> ${new Date(incident.timestamp).toLocaleString()}</p>
                <p><strong>Message:</strong> ${incident.message || 'No message provided'}</p>
            `;

            // Reset form
            document.getElementById('reportFeedbackForm').reset();
            
            // Reset star ratings
            document.querySelectorAll('.star, .mini-star').forEach(star => {
                star.classList.remove('active');
            });
            
            // Reset rating labels
            document.getElementById('overallRatingLabel').textContent = 'Click to rate';
            
            // Show modal
            document.getElementById('reportFeedbackModal').style.display = 'block';
            
            // Initialize star ratings
            initializeReportFeedbackRatings();
        }

        // Function to initialize star ratings for report feedback
        function initializeReportFeedbackRatings() {
            const ratingLabels = {
                1: 'Poor',
                2: 'Fair', 
                3: 'Good',
                4: 'Very Good',
                5: 'Excellent'
            };

            // Overall rating
            const overallStars = document.querySelectorAll('#overallReportRating .star');
            overallStars.forEach(star => {
                star.addEventListener('click', () => {
                    const rating = parseInt(star.dataset.rating);
                    
                    // Update star display
                    overallStars.forEach((s, index) => {
                        if (index < rating) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                    
                    // Update label
                    document.getElementById('overallRatingLabel').textContent = ratingLabels[rating];
                });
            });

            // Detailed ratings
            const ratingTypes = ['accuracy', 'completeness', 'timeliness', 'usefulness'];
            ratingTypes.forEach(type => {
                const stars = document.querySelectorAll(`#${type}Rating .mini-star`);
                stars.forEach(star => {
                    star.addEventListener('click', () => {
                        const rating = parseInt(star.dataset.rating);
                        
                        // Update star display
                        stars.forEach((s, index) => {
                            if (index < rating) {
                                s.classList.add('active');
                            } else {
                                s.classList.remove('active');
                            }
                        });
                    });
                });
            });
        }

        // Function to submit report feedback
        async function submitReportFeedback(event) {
            event.preventDefault();
            
            const submitBtn = document.getElementById('submitFeedbackBtn');
            const originalText = submitBtn.textContent;
            
            try {
                // Get form data
                const incidentId = document.getElementById('feedbackIncidentId').value;
                const phone = document.getElementById('feedbackPhone').value.trim();
                const comments = document.getElementById('feedbackComments').value.trim();
                const category = document.querySelector('input[name="category"]:checked')?.value || 'general';
                
                // Get ratings
                const overallRating = document.querySelector('#overallReportRating .star.active:last-child')?.dataset.rating || 0;
                const accuracyRating = document.querySelector('#accuracyRating .mini-star.active:last-child')?.dataset.rating || null;
                const completenessRating = document.querySelector('#completenessRating .mini-star.active:last-child')?.dataset.rating || null;
                const timelinessRating = document.querySelector('#timelinessRating .mini-star.active:last-child')?.dataset.rating || null;
                const usefulnessRating = document.querySelector('#usefulnessRating .mini-star.active:last-child')?.dataset.rating || null;
                
                // Validation
                if (!overallRating || overallRating == 0) {
                    createNotification('Error', 'Please provide an overall rating', 'error');
                    return;
                }
                
                if (phone && !phone.match(/^09[0-9]{9}$/)) {
                    createNotification('Error', 'Please enter a valid Philippine mobile number', 'error');
                    return;
                }
                
                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.textContent = 'Submitting...';
                
                // Prepare feedback data
                const feedbackData = {
                    incident_id: incidentId,
                    phone: phone || null,
                    rating: parseInt(overallRating),
                    response_time_rating: accuracyRating ? parseInt(accuracyRating) : null,
                    professionalism_rating: completenessRating ? parseInt(completenessRating) : null,
                    effectiveness_rating: timelinessRating ? parseInt(timelinessRating) : null,
                    overall_satisfaction: usefulnessRating ? parseInt(usefulnessRating) : null,
                    feedback_text: comments,
                    category: category
                };
                
                // Submit feedback
                const response = await fetch('api/submit_feedback.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(feedbackData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    createNotification('Success', 'Thank you for your feedback! Your input helps us improve our reporting system.', 'success');
                    
                    // Close modal
                    document.getElementById('reportFeedbackModal').style.display = 'none';
                    
                    // Refresh feedback data if on feedback tab
                    if (document.getElementById('feedback-tab').classList.contains('active')) {
                        await loadFeedbackData();
                    }
                } else {
                    throw new Error(data.message || 'Failed to submit feedback');
                }
                
            } catch (error) {
                console.error('Error submitting report feedback:', error);
                createNotification('Error', error.message || 'Failed to submit feedback. Please try again.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }

        // Function to request feedback from users
        function requestFeedback(incidentId) {
            const incident = [...liveIncidentsData.values(), ...resolvedIncidentsData.values()]
                .find(inc => inc.id === incidentId);
            
            if (!incident) {
                createNotification('Error', 'Incident not found', 'error');
                return;
            }

            // Create a simple feedback form modal
            const modalHtml = `
                <div id="feedbackRequestModal" class="modal" style="display: block;">
                    <div class="modal-content" style="max-width: 500px;">
                        <span class="close" onclick="closeFeedbackRequestModal()">&times;</span>
                        <h2>Request Feedback for Incident ${incidentId}</h2>
                        <p style="margin-bottom: 20px; color: var(--text-muted);">
                            Send a feedback request to the user who reported this incident.
                        </p>
                        
                        <div class="form-group">
                            <label>Message to User</label>
                            <textarea id="feedbackRequestMessage" class="form-control" rows="4" 
                                      placeholder="Enter your message requesting feedback..."></textarea>
                        </div>
                        
                        <div class="modal-actions">
                            <button class="btn btn-secondary" onclick="closeFeedbackRequestModal()">Cancel</button>
                            <button class="btn btn-primary" onclick="sendFeedbackRequest('${incidentId}')">Send Request</button>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            const existingModal = document.getElementById('feedbackRequestModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Add modal to page
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

        function closeFeedbackRequestModal() {
            const modal = document.getElementById('feedbackRequestModal');
            if (modal) {
                modal.remove();
            }
        }

        async function sendFeedbackRequest(incidentId) {
            const phone = document.getElementById('feedbackUserPhone').value.trim();
            const message = document.getElementById('feedbackRequestMessage').value.trim();

            if (!phone) {
                createNotification('Error', 'Please enter a phone number', 'error');
                return;
            }

            if (!phone.match(/^09[0-9]{9}$/)) {
                createNotification('Error', 'Please enter a valid Philippine mobile number', 'error');
                return;
            }

            try {
                // Send SMS feedback request
                const smsSent = sendFeedbackRequestSMS(phone, incidentId, message);
                
                if (smsSent) {
                    createNotification('Success', 'Feedback request sent successfully', 'success');
                    closeFeedbackRequestModal();
                } else {
                    createNotification('Error', 'Failed to send feedback request', 'error');
                }
            } catch (error) {
                console.error('Error sending feedback request:', error);
                createNotification('Error', 'Failed to send feedback request', 'error');
            }
        }

        function sendFeedbackRequestSMS(phone, incidentId, customMessage) {
            // Philippine carrier email-to-SMS gateways
   const carriers = {
       globe: {
           prefixes: ['905', '906', '915', '916', '917', '926', '927', '935', '936', '937'],
           email: '@globe.com.ph'
                },
       smart: {
           prefixes: ['907', '912', '913', '914', '918', '919', '920', '921', '928', '929', '930', '931', '938', '939'],
           email: '@smart.com.ph'
                },
       sun: {
           prefixes: ['922', '923', '925', '932', '933', '934'],
           email: '@sun.com.ph'
                }
   };
            // Detect carrier based on phone number prefix
            const prefix = phone.substring(0, 3);
            let carrier = null;
            
            for (const [carrierName, data] of Object.entries(carriers)) {
                if (data.prefixes.includes(prefix)) {
                    carrier = carrierName;
                    break;
                }
            }
            
            if (!carrier) {
                console.error("Unknown carrier for phone:", phone);
                return false;
            }
            
            // Format email address for carrier's SMS gateway
            const email = phone + carriers[carrier].email;
            
            // Create SMS message
            const baseMessage = `BFP Konekt: We value your feedback on incident ${incidentId}. Please rate our response at: https://bfpkonekt.com/feedback/${incidentId}`;
            const fullMessage = customMessage ? `${baseMessage}\n\n${customMessage}` : baseMessage;
            
            // Email headers
            const subject = "BFP Konekt Feedback Request";
            const headers = [
                'From: BFP Konekt <noreply@bfpkonekt.com>',
                'Reply-To: noreply@bfpkonekt.com',
                'Content-Type: text/plain; charset=UTF-8'
            ];
            
            // Send email (which gets delivered as SMS)
            const sent = mail(email, subject, fullMessage, headers.join("\r\n"));
            
            if (sent) {
                console.log("Feedback request SMS sent to", phone, "via", carrier, "email gateway:", email);
                return true;
            } else {
                console.error("Failed to send feedback request SMS to", phone, "via", carrier, "email gateway:", email);
                return false;
            }
        }

        // Navigation functionality
        // Remove duplicate navigation code - now handled by initializeNavigation()

        // Update time and date
        function updateDateTime() {
            const now = new Date();
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
            
            document.getElementById('currentDate').textContent = now.toLocaleDateString('en-US', dateOptions);
            document.getElementById('currentTime').textContent = now.toLocaleTimeString('en-US', timeOptions);
        }

        // Update location
        async function updateLocation() {
            const locationElement = document.getElementById('currentLocation');
            if (!locationElement) return;

            if (navigator.geolocation) {
                try {
                    const position = await new Promise((resolve, reject) => {
                        navigator.geolocation.getCurrentPosition(resolve, reject, { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 });
                    });
                    
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&accept-language=en`);
                    if (!response.ok) {
                        throw new Error(`Nominatim API error: ${response.status}`);
                    }
                    const data = await response.json();
                    
                    if (data && data.display_name) {
                        let friendlyAddress = data.display_name;
                        if (data.address) {
                            const city = data.address.city || data.address.town || data.address.village;
                            const state = data.address.state || data.address.county;
                            const country = data.address.country;

                            if (city && country) {
                                friendlyAddress = `${city}, ${country}`;
                            } else if (state && country) {
                                friendlyAddress = `${state}, ${country}`;
                            } else if (city && state) {
                                friendlyAddress = `${city}, ${state}`;
                            }
                        }
                        locationElement.textContent = friendlyAddress;
                    } else {
                        locationElement.textContent = 'Address not found';
                    }
                } catch (error) {
                    locationElement.textContent = 'Location unavailable';
                    console.warn('Geolocation or Reverse Geocoding error:', error.message);
                }
            } else {
                locationElement.textContent = 'Geolocation not supported';
            }
        }

        // Initialize and update time/date/location
        document.addEventListener('DOMContentLoaded', function() {
            updateDateTime();
            updateLocation();
            
            setInterval(updateDateTime, 1000);
            setInterval(updateLocation, 300000);
        });

        function createNotification(title, message, type = 'info') {
            const notificationArea = document.getElementById('notificationArea') || createNotificationArea();
            
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `<strong>${title}</strong><p>${message}</p>`;
            
            notificationArea.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 500);
            }, 5000);
        }

        function createNotificationArea() {
            let area = document.getElementById('notificationArea');
            if (!area) {
                area = document.createElement('div');
                area.id = 'notificationArea';
                area.style.position = 'fixed';
                area.style.top = '20px';
                area.style.right = '20px';
                area.style.zIndex = '10000';
                area.style.display = 'flex';
                area.style.flexDirection = 'column';
                area.style.gap = '10px';
                document.body.appendChild(area);
            }
            
            if (!document.getElementById('notificationStyles')) {
                const styles = `
                    .notification {
                        padding: 15px;
                        border-radius: 8px;
                        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                        color: #fff;
                        min-width: 250px;
                        transition: opacity 0.5s ease-in-out;
                    }
                    .notification strong { display: block; margin-bottom: 5px; }
                    .notification-info { background-color: var(--info); }
                    .notification-success { background-color: var(--success); }
                    .notification-warning { background-color: var(--warning); color: var(--text-dark); }
                    .notification-error { background-color: var(--danger); }
                `;
                const styleSheet = document.createElement('style');
                styleSheet.id = 'notificationStyles';
                styleSheet.textContent = styles;
                document.head.appendChild(styleSheet);
            }
            return area;
        }

        // Function to render the history table
        function renderHistoryTable() {
            const tableBody = document.getElementById('historyTableBody');
            if (!tableBody) return;

            tableBody.innerHTML = '';

            // Combine both active and resolved incidents
            const allIncidents = [
                ...liveIncidentsData.values(),
                ...resolvedIncidentsData.values()
            ];

            if (allIncidents.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding: 20px;">No incidents reported in this session yet.</td></tr>';
                return;
            }

            const sortedIncidents = allIncidents.sort((a, b) => b.timestamp - a.timestamp);

            sortedIncidents.forEach(incident => {
                const row = tableBody.insertRow();
                const messageSnippet = incident.message && incident.message.length > 100 ? incident.message.substring(0, 97) + '...' : incident.message;
                const statusClass = incident.currentStatus === 'active' ? 'status-active' : 'status-resolved';

                row.insertCell().textContent = new Date(incident.timestamp).toLocaleString();
                row.insertCell().textContent = incident.source;
                row.insertCell().textContent = incident.location;
                row.insertCell().textContent = messageSnippet || 'N/A';
                row.insertCell().textContent = incident.user_name || incident.user_id || 'N/A'; // Show user/admin
                const statusCell = row.insertCell();
                statusCell.innerHTML = `<span class="${statusClass}">${incident.currentStatus.charAt(0).toUpperCase() + incident.currentStatus.slice(1)}</span>`;
                // Add actions cell with upload evidence button
                const actionsCell = row.insertCell();
                actionsCell.innerHTML = `
                    <div class="action-buttons" style="display: flex; gap: 5px; flex-wrap: wrap;">
                        <button class="btn btn-success" onclick="openEvidenceUpload('${incident.id}')" style="font-size: 0.8rem; padding: 5px 10px;">
                            📷 Upload Photo of Post
                        </button>
                    </div>
                `;
            });
        }

        // Function to download history data as CSV
        function downloadHistoryData() {
            const allIncidents = [
                ...liveIncidentsData.values(),
                ...resolvedIncidentsData.values()
            ];
            
            if (allIncidents.length === 0) {
                createNotification('Info', 'No history data to download for this session.', 'info');
                return;
            }

            const headers = ['Timestamp', 'Source', 'Location', 'Full Message', 'Status', 'Incident ID', 'Confidence'];
            let csvContent = headers.join(",") + "\r\n";

            const sortedIncidents = allIncidents.sort((a, b) => b.timestamp - a.timestamp);

            sortedIncidents.forEach(incident => {
                const timestamp = new Date(incident.timestamp).toLocaleString().replace(/,/g, ';');
                const source = incident.source ? `"${incident.source.replace(/"/g, '""')}"` : 'N/A';
                const location = incident.location ? `"${incident.location.replace(/"/g, '""')}"` : 'N/A';
                const message = incident.message ? `"${incident.message.replace(/"/g, '""')}"` : 'N/A';
                const status = incident.currentStatus || 'N/A';
                const id = incident.id || 'N/A';
                const confidence = incident.confidence !== undefined ? incident.confidence + '%' : 'N/A';

                const row = [timestamp, source, location, message, status, id, confidence].join(",");
                csvContent += row + "\r\n";
            });

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            
            const now = new Date();
            const dateString = `${now.getFullYear()}${(now.getMonth()+1).toString().padStart(2, '0')}${now.getDate().toString().padStart(2, '0')}`;
            const timeString = `${now.getHours().toString().padStart(2, '0')}${now.getMinutes().toString().padStart(2, '0')}${now.getSeconds().toString().padStart(2, '0')}`;
            link.setAttribute("download", `bfp_incident_history_${dateString}_${timeString}.csv`);
            
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            createNotification('Success', 'Incident history downloaded.', 'success');
        }

        // Attach event listener for download button
        document.addEventListener('DOMContentLoaded', () => {
            const downloadBtn = document.getElementById('downloadHistoryBtn');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', downloadHistoryData);
            }
        });

        // Add logout function
        function logout() {
            // Clear all session data
            localStorage.removeItem('isLoggedIn');
            localStorage.removeItem('adminUsername');
            localStorage.removeItem('userEmail');
            localStorage.removeItem('userId');
            localStorage.removeItem('userType');
            
            // Force redirect to login page
            window.location.replace('login.php');
        }

        // Add event listener to logout button
        document.querySelector('.badge[onclick*="logout"]')?.addEventListener('click', function(e) {
            e.preventDefault();
            logout();
        });

        // Prevent back button after logout
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                checkSession();
            }
        });

        function toggleNotificationPanel() {
            const panel = document.getElementById('notificationPanel');
            if (panel) {
                panel.classList.toggle('active');
            }
        }
        window.toggleNotificationPanel = toggleNotificationPanel;

        // Modal open/close logic
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('systemStatusModal');
            const btn = document.getElementById('openSystemStatusModal');
            const span = document.getElementById('closeSystemStatusModal');
            if (btn && modal && span) {
                btn.onclick = function() {
                    modal.style.display = 'block';
                }
                span.onclick = function() {
                    modal.style.display = 'none';
                }
                window.onclick = function(event) {
                    if (event.target == modal) {
                        modal.style.display = 'none';
                    }
                }
            }
        });

        let reportsAnalyticsBarChart;
        let reportsAnalyticsPieChart;

        function groupIncidentsByPeriodAndStatus(incidents, period) {
            const now = new Date();
            let labels = [];
            let statusTypes = ['active', 'resolved', 'false_alarm', 'non_incident'];
            let statusLabels = {
                'active': 'Incident',
                'resolved': 'Responded',
                'false_alarm': 'False Alarm',
                'non_incident': 'Non-Incident'
            };
            let statusColors = {
                'active': '#ff9800',
                'resolved': '#4caf50',
                'false_alarm': '#ffc107',
                'non_incident': '#2196f3'
            };
            let groupCount = 7;
            let labelFormat = { day: { month: 'short', day: 'numeric' }, month: { year: 'numeric', month: 'short' }, year: { year: 'numeric' } };
            let getLabel = (date) => date.toLocaleDateString('en-US', labelFormat[period]);
            let getPeriodStart = (date, i) => {
                let d = new Date(date);
                if (period === 'day') d.setDate(d.getDate() - (groupCount - 1 - i));
                if (period === 'month') d.setMonth(d.getMonth() - (groupCount - 1 - i), 1);
                if (period === 'year') d.setFullYear(d.getFullYear() - (groupCount - 1 - i), 0, 1);
                d.setHours(0,0,0,0);
                return d;
            };
            for (let i = 0; i < groupCount; i++) {
                let d = getPeriodStart(now, i);
                labels.push(getLabel(d));
            }
            let dataByStatus = {};
            statusTypes.forEach(status => {
                dataByStatus[status] = Array(groupCount).fill(0);
            });
            incidents.forEach(incident => {
                let date = new Date(incident.timestamp);
                for (let i = 0; i < groupCount; i++) {
                    let d = getPeriodStart(now, i);
                    let match = false;
                    if (period === 'day') {
                        match = date.getDate() === d.getDate() && date.getMonth() === d.getMonth() && date.getFullYear() === d.getFullYear();
                    } else if (period === 'month') {
                        match = date.getMonth() === d.getMonth() && date.getFullYear() === d.getFullYear();
                    } else if (period === 'year') {
                        match = date.getFullYear() === d.getFullYear();
                    }
                    if (match) {
                        let status = incident.currentStatus || 'active';
                        if (!statusTypes.includes(status)) status = 'active';
                        dataByStatus[status][i]++;
                    }
                }
            });
            return { labels, dataByStatus, statusLabels, statusColors };
        }

        function getPieChartData(incidents) {
            let statusTypes = ['active', 'resolved', 'false_alarm', 'non_incident'];
            let statusLabels = {
                'active': 'Incident',
                'resolved': 'Responded',
                'false_alarm': 'False Alarm',
                'non_incident': 'Non-Incident'
            };
            let statusColors = {
                'active': '#ff9800',
                'resolved': '#4caf50',
                'false_alarm': '#ffc107',
                'non_incident': '#2196f3'
            };
            let counts = statusTypes.map(status => incidents.filter(i => (i.currentStatus || 'active') === status).length);
            return {
                labels: statusTypes.map(s => statusLabels[s]),
                data: counts,
                backgroundColor: statusTypes.map(s => statusColors[s])
            };
        }

        function updateReportsAnalyticsCharts() {
            const incidents = [
                ...Array.from(liveIncidentsData.values()),
                ...Array.from(resolvedIncidentsData.values())
            ];
            const period = document.getElementById('analyticsPeriodSelect').value;
            const summaryDiv = document.getElementById('reportsAnalyticsSummary');
            const barCtx = document.getElementById('reportsAnalyticsBarChart').getContext('2d');
            const pieCtx = document.getElementById('reportsAnalyticsPieChart').getContext('2d');
            if (reportsAnalyticsBarChart) reportsAnalyticsBarChart.destroy();
            if (reportsAnalyticsPieChart) reportsAnalyticsPieChart.destroy();
            if (incidents.length === 0) {
                summaryDiv.textContent = 'No incident data available. The charts will update when new incidents are reported.';
                reportsAnalyticsBarChart = new Chart(barCtx, {
                    type: 'bar',
                    data: { labels: [], datasets: [] },
                    options: { responsive: true, plugins: { legend: { display: true } }, scales: { y: { beginAtZero: true } } }
                });
                reportsAnalyticsPieChart = new Chart(pieCtx, {
                    type: 'pie',
                    data: { labels: [], datasets: [] },
                    options: { responsive: true, plugins: { legend: { display: true } } }
                });
                return;
            }
            const { labels, dataByStatus, statusLabels, statusColors } = groupIncidentsByPeriodAndStatus(incidents, period);
            const datasets = Object.keys(dataByStatus).map(status => ({
                label: statusLabels[status],
                data: dataByStatus[status],
                backgroundColor: statusColors[status],
                stack: 'Stack 0'
            }));
            reportsAnalyticsBarChart = new Chart(barCtx, {
                type: 'bar',
                data: { labels, datasets },
                options: {
                    responsive: true,
                    plugins: { legend: { display: true } },
                    scales: { y: { beginAtZero: true } }
                }
            });
            // Pie chart
            const pieData = getPieChartData(incidents);
            reportsAnalyticsPieChart = new Chart(pieCtx, {
                type: 'pie',
                data: { labels: pieData.labels, datasets: [{ data: pieData.data, backgroundColor: pieData.backgroundColor }] },
                options: { responsive: true, plugins: { legend: { display: true } } }
            });
            // Summary
            const total = incidents.length;
            const summary = pieData.labels.map((label, i) => `${label}: ${pieData.data[i]} (${((pieData.data[i]/total)*100).toFixed(1)}%)`).join(' | ');
            summaryDiv.textContent = summary;
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateReportsAnalyticsCharts();
            document.getElementById('analyticsPeriodSelect').addEventListener('change', updateReportsAnalyticsCharts);
        });
        // Also update after adding/removing incidents
        function refreshAllIncidentDisplays() {
            updateQuickStatsDisplay();
            renderHistoryTable();
            renderActiveIncidentsList();
            updateReportsAnalyticsCharts();
        }

        function updateConnectionStatus(status) {
            const apiStatusEl = document.getElementById('systemApiStatusValue');
            const actorStatusEl = document.getElementById('actorStatusValue');
            const lastRunStatusEl = document.getElementById('lastRunStatusValue');
            const connectionStatusEl = document.getElementById('connectionStatusValue');
            const connectionDetailsEl = document.getElementById('connectionDetails');
            const systemStatusBtn = document.getElementById('openSystemStatusModal');

            // Helper to title case a word
            function toTitleCase(str) {
                return str.replace(/\w\S*/g, (txt) => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
            }

            if (status.isConnected) {
                apiStatusEl.textContent = toTitleCase('connected');
                apiStatusEl.style.color = 'var(--success)';
                actorStatusEl.textContent = toTitleCase((status.actorStatus || 'Unknown').replace('SUCCEEDED', 'Succeeded').replace('FAILED', 'Failed'));
                actorStatusEl.style.color = status.actorStatus === 'SUCCEEDED' ? 'var(--success)' : 
                                          status.actorStatus === 'ERROR' ? 'var(--danger)' : 
                                          'var(--warning)';
                lastRunStatusEl.textContent = toTitleCase((status.lastRunStatus || 'No Runs').replace('SUCCEEDED', 'Succeeded').replace('FAILED', 'Failed'));
                lastRunStatusEl.style.color = status.lastRunStatus === 'SUCCEEDED' ? 'var(--success)' : 
                                            status.lastRunStatus === 'ERROR' ? 'var(--danger)' : 
                                            'var(--warning)';
                connectionStatusEl.textContent = toTitleCase('connected');
                connectionStatusEl.style.color = 'var(--success)';
                connectionDetailsEl.textContent = `Last checked: ${new Date(status.lastCheck).toLocaleString()}`;
                if (systemStatusBtn) {
                    systemStatusBtn.classList.remove('btn-system-status-danger');
                    systemStatusBtn.classList.add('btn-system-status-success');
                }
            } else {
                apiStatusEl.textContent = toTitleCase('disconnected');
                apiStatusEl.style.color = 'var(--danger)';
                actorStatusEl.textContent = toTitleCase('error');
                actorStatusEl.style.color = 'var(--danger)';
                lastRunStatusEl.textContent = toTitleCase('error');
                lastRunStatusEl.style.color = 'var(--danger)';
                connectionStatusEl.textContent = toTitleCase('disconnected');
                connectionStatusEl.style.color = 'var(--danger)';
                connectionDetailsEl.textContent = `Last error: ${status.error || 'Unknown error'}`;
                if (systemStatusBtn) {
                    systemStatusBtn.classList.remove('btn-system-status-success');
                    systemStatusBtn.classList.add('btn-system-status-danger');
                }
            }
        }

        let apifyPollingInterval = null;
        let apifyPollingMinutes = 1; // default

        function setApifyPollingInterval(minutes) {
            if (apifyPollingInterval) clearInterval(apifyPollingInterval);
            apifyPollingMinutes = minutes;
            // Removed call to window.apifyService.setPollingInterval
            apifyPollingInterval = setInterval(() => {
                if (window.apifyService) {
                    // Call runActorAndProcess instead of fetchAndProcessData
                    window.apifyService.runActorAndProcess().catch(error => {
                         console.error('Error during Apify polling run:', error);
                    });
                }
            }, minutes * 60 * 1000);
        }

        // On page load, set up the dropdown and polling
        document.addEventListener('DOMContentLoaded', function() {
            const filterSelect = document.getElementById('incidentFilter');
            if (filterSelect) {
                filterSelect.addEventListener('change', renderActiveIncidentsList);
            }
            // Set initial polling interval (e.g., 1 minute)
            setApifyPollingInterval(1);
        });

        let notificationSoundEnabled = true; // default

        function setNotificationSound(enabled) {
            notificationSoundEnabled = enabled;
            localStorage.setItem('bfpNotificationSound', enabled ? 'enabled' : 'disabled');
            createNotification('Success', `Notification sound ${enabled ? 'enabled' : 'disabled'}.`, 'success');
        }

        // On page load, set up the dropdown and sound setting
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.getElementById('notificationSoundSelect');
            if (!select) return;

            // Load saved setting or default to enabled
            const saved = localStorage.getItem('bfpNotificationSound');
            if (saved) {
                select.value = saved;
                setNotificationSound(saved === 'enabled');
            } else {
                setNotificationSound(true);
            }

            select.addEventListener('change', function() {
                setNotificationSound(this.value === 'enabled');
            });
        });

        // Update the Apify posts event listener to play sound if enabled
        // REMOVED: Duplicate event listener - keeping only the one in the module script below
        // The event listener is now handled in the module script section to prevent conflicts

        // Add event listener for the filter dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const filterSelect = document.getElementById('incidentFilter');
            if (filterSelect) {
                filterSelect.addEventListener('change', renderActiveIncidentsList);
            }
        });

        // User Management Functions
        let userCounter = {
            admin: 1,
            user: 1
        };

        function generateUserId(type) {
            const prefix = type === 'admin' ? 'BFPK' : 'BFPU';
            const number = userCounter[type].toString().padStart(4, '0');
            userCounter[type]++;
            return `${prefix}${number}`;
        }

        function generatePassword() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
            let password = '';
            // Ensure at least one of each required character type
            password += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[Math.floor(Math.random() * 26)]; // Uppercase
            password += 'abcdefghijklmnopqrstuvwxyz'[Math.floor(Math.random() * 26)]; // Lowercase
            password += '0123456789'[Math.floor(Math.random() * 10)]; // Number
            password += '!@#$%^&*'[Math.floor(Math.random() * 8)]; // Special character
            
            // Fill the rest randomly
            for (let i = 4; i < 8; i++) {
                password += chars[Math.floor(Math.random() * chars.length)];
            }
            
            // Shuffle the password
            return password.split('').sort(() => Math.random() - 0.5).join('');
        }


        function openChangePasswordModal(userId) {
            const modal = document.getElementById('changePasswordModal');
            modal.style.display = 'block';
            modal.dataset.userId = userId;
        }

        // Event Listeners
        document.addEventListener('DOMContentLoaded', function() {
            // User Management Modal
            const userManagementBtn = document.getElementById('openUserManagementBtn');
            const userManagementModal = document.getElementById('userManagementModal');
            const closeUserManagementModal = document.getElementById('closeUserManagementModal');
            
            userManagementBtn.onclick = function() {
                userManagementModal.style.display = 'block';
                renderUserListPHP();
            }
            
            closeUserManagementModal.onclick = function() {
                userManagementModal.style.display = 'none';
            }
            
            // Change Password Modal
            const changePasswordModal = document.getElementById('changePasswordModal');
            const closeChangePasswordModal = document.getElementById('closeChangePasswordModal');
            const changePasswordBtn = document.getElementById('changePasswordBtn');
            
            closeChangePasswordModal.onclick = function() {
                changePasswordModal.style.display = 'none';
            }
            
            // Create User Button
            document.getElementById('createUserBtn').onclick = createUserHandler;
            
            // Close modals when clicking outside
            window.onclick = function(event) {
                if (event.target === userManagementModal) {
                    userManagementModal.style.display = 'none';
                }
                if (event.target === changePasswordModal) {
                    changePasswordModal.style.display = 'none';
                }
            }
        });

        // Load user ID in sidebar
        window.addEventListener('load', function() {
            const userId = localStorage.getItem('userId');
            if (userId) {
                document.getElementById('sidebarUserId').textContent = userId;
            }
        });

        // --- USER MANAGEMENT (PHP/DB) ---
        async function fetchUsers() {
            const res = await fetch('api/users.php');
            const data = await res.json();
            if (data.success) return data.data;
            else throw new Error(data.message);
        }

        async function createUserPHP(name, type, password, email) {
            const res = await fetch('api/users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, type, password, email })
            });
            return await res.json();
        }

        async function deleteUserPHP(id) {
            const res = await fetch('api/users.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            return await res.json();
        }

        function generatePassword() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
            let password = '';
            password += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[Math.floor(Math.random() * 26)];
            password += 'abcdefghijklmnopqrstuvwxyz'[Math.floor(Math.random() * 26)];
            password += '0123456789'[Math.floor(Math.random() * 10)];
            password += '!@#$%^&*'[Math.floor(Math.random() * 8)];
            for (let i = 4; i < 8; i++) {
                password += chars[Math.floor(Math.random() * chars.length)];
            }
            return password.split('').sort(() => Math.random() - 0.5).join('');
        }

        async function renderUserListPHP() {
            const tbody = document.getElementById('userListBody');
            if (!tbody) return;
            
            tbody.innerHTML = '<tr><td colspan="5">Loading...</td></tr>';
            try {
                const users = await fetchUsers();
                if (!users || users.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No users found</td></tr>';
                    return;
                }
            
            tbody.innerHTML = users.map(user => `
                <tr>
                    <td>${user.id}</td>
                    <td>${user.name}</td>
                    <td><span class="user-type-badge user-type-${user.type}">${user.type}</span></td>
                        <td>••••••••</td>
                    <td class="user-actions">
                        <button class="btn-change-password" onclick="openChangePasswordModal('${user.id}')">Change Password</button>
                            <button class="btn-delete-user" onclick="deleteUserHandler('${user.id}')">Delete</button>
                    </td>
                </tr>
            `).join('');
            } catch (error) {
                console.error('Error rendering user list:', error);
                tbody.innerHTML = `<tr><td colspan="5" style="text-align: center;">Error loading users: ${error.message}</td></tr>`;
            }
        }

        async function createUserHandler() {
            const userType = document.getElementById('userTypeSelect').value;
            const userName = document.getElementById('newUserName').value.trim();
            const userEmail = document.getElementById('newUserEmail').value.trim();
            if (!userName || !userEmail) {
                createNotification('Error', 'Please enter a name and email', 'error');
                return;
            }
            
            const password = generatePassword();
            try {
                const res = await createUserPHP(userName, userType, password, userEmail);
                if (res.success) {
                    // Show credentials modal
                    const modal = document.getElementById('newUserCredentialsModal');
                    const infoDiv = document.getElementById('newUserCredentialsInfo');
                    infoDiv.innerHTML = `<b>User ID:</b> <span id='newUserId'>${res.data.id}</span><br><b>Password:</b> <span id='newUserPassword'>${password}</span>`;
                    modal.style.display = 'block';
                    // Copy button
                    document.getElementById('copyCredentialsBtn').onclick = function() {
                        const text = `User ID: ${res.data.id}\nPassword: ${password}`;
                        navigator.clipboard.writeText(text);
                        createNotification('Success', 'Credentials copied to clipboard', 'success');
                    };
                    // Close button
                    document.getElementById('closeNewUserCredentialsModal').onclick = function() {
                        modal.style.display = 'none';
                    };
                    // Also close on outside click
                window.onclick = function(event) {
                    if (event.target === modal) {
                            modal.style.display = 'none';
                        }
                    };
                    document.getElementById('newUserName').value = '';
                    document.getElementById('newUserEmail').value = '';
                    await renderUserListPHP();
                } else {
                    createNotification('Error', res.message || 'Failed to create user', 'error');
                }
            } catch (error) {
                createNotification('Error', 'Failed to create user: ' + error.message, 'error');
            }
        }

        async function deleteUserHandler(id) {
            if (!confirm('Are you sure you want to delete this user?')) return;
            try {
                const res = await deleteUserPHP(id);
                if (res.success) {
                createNotification('Success', 'User deleted successfully', 'success');
                    // Refresh the user list immediately after deletion
                    await renderUserListPHP();
                } else {
                    createNotification('Error', res.message || 'Failed to delete user', 'error');
                }
            } catch (error) {
                console.error('Error deleting user:', error);
                createNotification('Error', 'Failed to delete user: ' + error.message, 'error');
            }
        }

        // Attach event listeners for user management
         document.addEventListener('DOMContentLoaded', function() {
            const openUserManagementBtn = document.getElementById('openUserManagementBtn');
            if (openUserManagementBtn) {
                openUserManagementBtn.onclick = function() {
                    const userManagementModal = document.getElementById('userManagementModal');
                    if (userManagementModal) {
                        userManagementModal.style.display = 'block';
                        renderUserListPHP();
                    }
                };
            }
            const createUserBtn = document.getElementById('createUserBtn');
            if (createUserBtn) {
                createUserBtn.onclick = createUserHandler;
            }
            const closeUserManagementModal = document.getElementById('closeUserManagementModal');
            if (closeUserManagementModal) {
                closeUserManagementModal.onclick = function() {
                    const userManagementModal = document.getElementById('userManagementModal');
                    if (userManagementModal) userManagementModal.style.display = 'none';
                };
            }
            window.deleteUserHandler = deleteUserHandler;
            window.openChangePasswordModal = openChangePasswordModal;
            const changePasswordBtn = document.getElementById('changePasswordBtn');
            if (changePasswordBtn) {
                changePasswordBtn.onclick = changePasswordHandler;
            }
         });

        // ... existing code ...
        async function changeUserPasswordPHP(userId, newPassword) {
            const res = await fetch('api/user-password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ userId, newPassword })
            });
            return await res.json();
        }

        function openChangePasswordModal(userId) {
            const modal = document.getElementById('changePasswordModal');
            modal.style.display = 'block';
            modal.dataset.userId = userId;
        }

        async function changePasswordHandler() {
            const userId = document.getElementById('changePasswordModal').dataset.userId;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
                    if (newPassword !== confirmPassword) {
                createNotification('Error', 'New passwords do not match', 'error');
                        return;
                    }
                    if (newPassword.length < 8) {
                        createNotification('Error', 'Password must be at least 8 characters long', 'error');
                        return;
                    }
            const res = await changeUserPasswordPHP(userId, newPassword);
            if (res.success) {
                document.getElementById('changePasswordModal').style.display = 'none';
                    createNotification('Success', 'Password changed successfully', 'success');
            } else {
                createNotification('Error', res.message, 'error');
            }
        }

        // Update renderUserListPHP to add change password button
        async function renderUserListPHP() {
            const tbody = document.getElementById('userListBody');
            tbody.innerHTML = '<tr><td colspan="5">Loading...</td></tr>';
            try {
                const users = await fetchUsers();
                if (!users.length) {
                    tbody.innerHTML = '<tr><td colspan="5">No users found.</td></tr>';
                return;
            }
                tbody.innerHTML = users.map(user => `
                    <tr>
                        <td>${user.id}</td>
                        <td>${user.name}</td>
                        <td><span class="user-type-badge user-type-${user.type}">${user.type}</span></td>
                        <td>••••••••</td>
                        <td class="user-actions">
                            <button class="btn-change-password" onclick="openChangePasswordModal('${user.id}')">Change Password</button>
                            <button class="btn-delete-user" onclick="deleteUserHandler('${user.id}')">Delete</button>
                        </td>
                    </tr>
                `).join('');
            } catch (e) {
                tbody.innerHTML = `<tr><td colspan="5">Error: ${e.message}</td></tr>`;
            }
        }

        // Attach event listeners for user management
        document.addEventListener('DOMContentLoaded', function() {
            const openUserManagementBtn = document.getElementById('openUserManagementBtn');
            if (openUserManagementBtn) {
                openUserManagementBtn.onclick = function() {
            const userManagementModal = document.getElementById('userManagementModal');
                    if (userManagementModal) {
                userManagementModal.style.display = 'block';
                        renderUserListPHP();
                    }
                };
            }
            const createUserBtn = document.getElementById('createUserBtn');
            if (createUserBtn) {
                createUserBtn.onclick = createUserHandler;
            }
            const closeUserManagementModal = document.getElementById('closeUserManagementModal');
            if (closeUserManagementModal) {
            closeUserManagementModal.onclick = function() {
                    const userManagementModal = document.getElementById('userManagementModal');
                    if (userManagementModal) userManagementModal.style.display = 'none';
                };
            }
            window.deleteUserHandler = deleteUserHandler;
            window.openChangePasswordModal = openChangePasswordModal;
            const changePasswordBtn = document.getElementById('changePasswordBtn');
            if (changePasswordBtn) {
                changePasswordBtn.onclick = changePasswordHandler;
            }
         });

        // ... existing code ...
        async function fetchActiveIncidents(retryCount = 0) {
            try {
                // Clear all existing incident data to prevent stale states on refresh
                liveIncidentsData.clear();
                resolvedIncidentsData.clear();
                if (markerCluster) {
                    markerCluster.clearLayers();
                }
                markers.clear();

                const response = await fetch(`api/incidents.php?cache_bust=${Date.now()}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();

                // Gather new active incident IDs
                const newActiveIncidentIds = new Set();
                if (data.success && data.incidents.length > 0) {
                    data.incidents.forEach(incident => {
                        incident.currentStatus = incident.status;
                        incident.timestamp = new Date(incident.timestamp).getTime();
                        
                        if (incident.status === 'active') {
                            if (!liveIncidentsData.has(incident.id)) {
                            liveIncidentsData.set(incident.id, incident);
                            addIncidentMarker(incident, false);
                            }
                            newActiveIncidentIds.add(incident.id);
                        } else {
                            // Add to resolved incidents for reports only
                            resolvedIncidentsData.set(incident.id, incident);
                        }
                    });
                }
                
                // Only play sound if not the initial load and there are new incidents
                if (!isInitialIncidentLoad && notificationSoundEnabled) {
                    for (const id of newActiveIncidentIds) {
                        if (!previousActiveIncidentIds.has(id)) {
                            const sound = document.getElementById('notificationSound');
                            if (sound) sound.play();
                            break; // Play sound only once per fetch
                        }
                    }
                }
                isInitialIncidentLoad = false;
                previousActiveIncidentIds = newActiveIncidentIds;
                
                // Set the initial stats based on the fresh data from the server
                dashboardReportStats.activeCases = liveIncidentsData.size;
                dashboardReportStats.totalReportedThisSession = liveIncidentsData.size + resolvedIncidentsData.size;
                
                // Update stats and reports table, but NOT the active incident list on the dashboard
                updateQuickStatsDisplay();
                renderHistoryTable();
                updateReportsAnalyticsCharts();
                
            } catch (error) {
                console.error('Error fetching active incidents:', error);
                
                // Retry logic for cache/resource issues
                if (retryCount < 3 && (error.message.includes('cached') || error.message.includes('unavailable'))) {
                    console.log(`Retrying fetchActiveIncidents (attempt ${retryCount + 1}/3)...`);
                    await new Promise(resolve => setTimeout(resolve, 1000 * (retryCount + 1))); // Exponential backoff
                    return fetchActiveIncidents(retryCount + 1);
                }
                
                createNotification('Error', 'Failed to load incident data: ' + error.message, 'error');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            fetchActiveIncidents().then(() => {
                isInitialLoad = false; // Allow rendering after initial load is complete
            });
            // ...existing code...
        });

        // ... existing code ...
        window.deleteSliderImage = async function(imageId) {
            if (!confirm('Are you sure you want to delete this image?')) return;
            try {
                const response = await fetch('api/delete-slider-image.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ imageId })
                });
                const data = await response.json();
                if (data.success) {
                    createNotification('Success', 'Image deleted successfully', 'success');
                    loadSliderImages();
                } else {
                    createNotification('Error', data.message || 'Failed to delete image', 'error');
                }
            } catch (error) {
                console.error('Error deleting image:', error);
                createNotification('Error', 'Failed to delete image', 'error');
            }
        };

        window.moveSliderImage = async function(imageId, direction) {
            try {
                const response = await fetch('api/move-slider-image.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ imageId, direction })
                });
                const data = await response.json();
                if (data.success) {
                    loadSliderImages();
                } else {
                    createNotification('Error', data.message || 'Failed to move image', 'error');
                }
            } catch (error) {
                console.error('Error moving image:', error);
                createNotification('Error', 'Failed to move image', 'error');
            }
        };
        // ... existing code ...
    </script>
    <script type="module">
        import ApifyService from './js/apify-service.js';
        
        // Initialize Apify service
        const apifyService = new ApifyService();
        window.apifyService = apifyService; // Make it globally accessible
        
        // Helper to convert status to proper case
        function toProperCase(str) {
            if (!str) return '';
            return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
        }
        
        // Update connection status display
        function updateConnectionStatus(status) {
            const apiStatusEl = document.getElementById('systemApiStatusValue');
            const actorStatusEl = document.getElementById('actorStatusValue');
            const lastRunStatusEl = document.getElementById('lastRunStatusValue');
            const connectionStatusEl = document.getElementById('connectionStatusValue');
            const connectionDetailsEl = document.getElementById('connectionDetails');
            const systemStatusBtn = document.getElementById('openSystemStatusModal');

            if (status.isConnected) {
                apiStatusEl.textContent = toProperCase('connected');
                apiStatusEl.style.color = 'var(--success)';
                actorStatusEl.textContent = toProperCase(status.actorStatus || 'Unknown');
                actorStatusEl.style.color = status.actorStatus === 'SUCCEEDED' ? 'var(--success)' : 
                                          status.actorStatus === 'ERROR' ? 'var(--danger)' : 
                                          'var(--warning)';
                lastRunStatusEl.textContent = toProperCase(status.lastRunStatus || 'No runs');
                lastRunStatusEl.style.color = status.lastRunStatus === 'SUCCEEDED' ? 'var(--success)' : 
                                            status.lastRunStatus === 'ERROR' ? 'var(--danger)' : 
                                            'var(--warning)';
                connectionStatusEl.textContent = toProperCase('connected');
                connectionStatusEl.style.color = 'var(--success)';
                connectionDetailsEl.textContent = `Last checked: ${new Date(status.lastCheck).toLocaleString()}`;
                if (systemStatusBtn) {
                    systemStatusBtn.classList.remove('btn-system-status-danger');
                    systemStatusBtn.classList.add('btn-system-status-success');
                }
                } else {
                apiStatusEl.textContent = toProperCase('disconnected');
                apiStatusEl.style.color = 'var(--danger)';
                actorStatusEl.textContent = toProperCase('error');
                actorStatusEl.style.color = 'var(--danger)';
                lastRunStatusEl.textContent = toProperCase('error');
                lastRunStatusEl.style.color = 'var(--danger)';
                connectionStatusEl.textContent = toProperCase('disconnected');
                connectionStatusEl.style.color = 'var(--danger)';
                connectionDetailsEl.textContent = `Last error: ${status.error || 'Unknown error'}`;
                if (systemStatusBtn) {
                    systemStatusBtn.classList.remove('btn-system-status-success');
                    systemStatusBtn.classList.add('btn-system-status-danger');
                }
            }
        }
        
        // Listen for Apify status updates
        window.addEventListener('apifyStatusUpdate', (event) => {
            updateConnectionStatus(event.detail);
        });
        
        // Listen for Apify posts
        window.addEventListener('apifyPostsFound', (event) => {
            console.log('Apify posts received:', event.detail);
            const { posts, runStatus, timestamp } = event.detail;
            
            // Only play sound if there are new posts
            if (Array.isArray(posts) && posts.length > 0 && notificationSoundEnabled) {
                const sound = document.getElementById('notificationSound');
                if (sound) sound.play();
            }

            // Process new posts
            if (Array.isArray(posts) && posts.length > 0) {
                console.log('Processing posts:', posts);
                posts.forEach(post => {
                    const postUrl = post.url || post.permalink || '';
                    
                    // Check if this incident already exists in liveIncidentsData by ID
                    if (liveIncidentsData.has(post.id)) {
                        console.log('Skipping post - already exists in active incidents:', post.id);
                        return; // Already present, skip to avoid duplicate
                    }
                    
                    // Check if this URL already exists in resolved incidents
                    const isResolved = Array.from(resolvedIncidentsData.values()).some(incident => 
                        incident.url === postUrl || incident.id === post.id
                    );
                    
                    if (isResolved) {
                        console.log('Skipping post - already exists as resolved incident:', postUrl);
                        return; // Already resolved, skip to avoid re-adding
                    }
                    
                    // Send to backend for DB insert
                    fetch('api/insert_incident.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            id: post.id,
                            type: 'fire',
                            location: post.location,
                            message: post.text || post.message,
                            source: typeof post.source === 'string' ? post.source : (post.source?.pageName || ''),
                            url: post.url,
                            timestamp: new Date(post.date).getTime(),
                            confidence: post.confidence,
                            status: 'active'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            let incidentDataToAdd = {
                                id: post.id,
                                type: 'fire',
                                location: post.location,
                                message: post.text || post.message,
                                source: typeof post.source === 'string' ? post.source : (post.source?.pageName || ''),
                                url: post.url,
                                timestamp: new Date(post.date),
                                confidence: post.confidence,
                                currentStatus: 'active'
                            };
                            // Check if this incident is already in our live data and is resolved
                            const existingIncident = liveIncidentsData.get(post.id);
                            if (existingIncident && existingIncident.currentStatus !== 'active') {
                                // Do not re-add closed incidents
                                return;
                            }
                            addIncidentMarker(incidentDataToAdd);
                            refreshAllIncidentDisplays();
                        } else if (data.message && data.message.includes('already closed')) {
                            // Do not add to dashboard if backend says it's closed
                            console.log('Incident not added (already closed):', post.id);
                        } else {
                            console.error('Failed to insert incident:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error inserting incident:', error);
                    });
                });
            } else {
                console.log('No new posts to process');
            }
        });

        // Listen for Apify errors
        window.addEventListener('apifyError', (event) => {
            const { error, timestamp } = event.detail;
            console.error('Apify error:', error);
            // Retry after 5 minutes
            setTimeout(() => {
                if (window.apifyService) window.apifyService.fetchAndProcessData();
            }, 5 * 60 * 1000);
        });
        
        // Start Apify monitoring when dashboard loads
        document.addEventListener('DOMContentLoaded', () => {
            apifyService.startMonitoring().catch(error => {
                console.error('Failed to start Apify monitoring:', error);
                createNotification('Apify Service Error', 'Failed to start monitoring', 'error');
            });
        });
    </script>
    <!-- Only show Manage Users button to admins -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hide Manage Users button if not admin
        const userType = '<?php echo isset($_SESSION['user_type']) ? $_SESSION['user_type'] : ""; ?>';
        const manageBtn = document.getElementById('openUserManagementBtn');
        if (manageBtn && userType !== 'admin') {
            manageBtn.style.display = 'none';
        }
        });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var input = document.getElementById('sliderImageUpload');
        if (!input) {
            alert('sliderImageUpload input NOT found at end of DOM!');
        } else {
            // alert('sliderImageUpload input FOUND at end of DOM!'); // Removed as requested
            input.addEventListener('change', function() {
                alert('File input changed at end of DOM!');
            });
        }
        });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure notificationSoundEnabled is declared
        if (typeof notificationSoundEnabled === 'undefined') {
            window.notificationSoundEnabled = true;
        }
        // Ensure sliderImageUpload input exists before attaching event listeners
        var sliderImageInput = document.getElementById('sliderImageUpload');
        if (sliderImageInput) {
            // Example: sliderImageInput.addEventListener('change', handleSliderImageUpload);
            console.log('sliderImageUpload input found, ready for event handlers.');
        } else {
            console.error('Error: sliderImageUpload input not found in DOM!');
        }
    });
    // ... existing code ...
    </script>
    <script>
    // ... existing code ...
    window.openFeedbackResponse = openFeedbackResponse;
    window.viewFeedbackDetails = viewFeedbackDetails;
    // ... existing code ...
    </script>
    <script>
        function viewEvidenceDetails(evidenceId) {
            const evidence = evidenceData.find(e => e.id === evidenceId);
            if (!evidence) return;

            const details = `
                Evidence ID: ${evidence.id}
                Incident ID: ${evidence.incident_id}
                User: ${evidence.user_name || evidence.user_id}
                Date: ${new Date(evidence.created_at).toLocaleString()}
                ${evidence.comment ? `Comment: ${evidence.comment}` : ''}
                ${evidence.incident_message ? `Incident: ${evidence.incident_message}` : ''}
                ${evidence.incident_location ? `Location: ${evidence.incident_location}` : ''}
                Image: ${evidence.image_path}
            `;

            alert(details);
        }

        // ... existing code ...
    </script>
    <script>
        // Debug function for evidence system
        function debugEvidenceSystem() {
            const debugInfo = document.getElementById('debugInfo');
            const debugContent = document.getElementById('debugContent');
            
            debugInfo.style.display = debugInfo.style.display === 'none' ? 'block' : 'none';
            
            const debugData = {
                evidenceData: evidenceData,
                evidenceStats: evidenceStats,
                timestamp: new Date().toISOString(),
                userAgent: navigator.userAgent
            };
            
            debugContent.innerHTML = `<pre>${JSON.stringify(debugData, null, 2)}</pre>`;
        }

        // Add missing event listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Debug button
            const debugBtn = document.getElementById('debugEvidenceBtn');
            if (debugBtn) {
                debugBtn.addEventListener('click', debugEvidenceSystem);
            }

            // Update database button
            const updateDbBtn = document.getElementById('updateDatabaseBtn');
            if (updateDbBtn) {
                updateDbBtn.addEventListener('click', async () => {
                    try {
                        const response = await fetch('api/update_evidence_database.php');
                        const data = await response.json();
                        if (data.success) {
                            createNotification('Success', 'Database updated successfully: ' + data.message, 'success');
                            await loadEvidenceData(); // Reload data after update
                        } else {
                            createNotification('Error', 'Database update failed: ' + data.message, 'error');
                        }
                    } catch (error) {
                        console.error('Database update error:', error);
                        createNotification('Error', 'Database update failed: ' + error.message, 'error');
                    }
                });
            }
        });
    </script>

    <!-- Evidence Upload Modal -->
    <div id="evidenceUploadModal" class="modal" style="display:none;">
      <div class="modal-content" style="max-width: 400px;">
        <span class="close" onclick="closeEvidenceUpload()">&times;</span>
        <h2>Upload Photo of Post</h2>
        <form id="evidenceUploadForm" enctype="multipart/form-data">
          <input type="hidden" id="evidenceIncidentId" name="incident_id">
          <div class="form-group">
            <label for="evidenceImage">Photo (screenshot of post)</label>
            <input type="file" id="evidenceImage" name="image" accept="image/*" required>
          </div>
          <div class="form-group">
            <label for="evidenceComment">Comment (optional)</label>
            <textarea id="evidenceComment" name="comment"></textarea>
          </div>
          <button type="submit" class="btn btn-success">Upload</button>
        </form>
      </div>
    </div>

    <script>
    function openEvidenceUpload(incidentId) {
        document.getElementById('evidenceIncidentId').value = incidentId;
        document.getElementById('evidenceUploadModal').style.display = 'block';
    }

    function closeEvidenceUpload() {
        document.getElementById('evidenceUploadModal').style.display = 'none';
    }

    document.getElementById('evidenceUploadForm').onsubmit = async function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const response = await fetch('api/upload_evidence.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            alert('Evidence uploaded successfully!');
            closeEvidenceUpload();
            // Optionally refresh evidence list here
        } else {
            alert('Upload failed: ' + result.message);
        }
    };
    </script>
    <!-- End Evidence Upload Modal and Script -->

    <script>
    // --- Apify & NLP Settings JS ---
    async function loadSettings() {
        const res = await fetch('api/get_settings.php');
        const data = await res.json();
        document.getElementById('apifyQueryInput').value = data.apify_query || '';
        renderNlpLocations(data.locations || []);
    }
    function renderNlpLocations(locations) {
        const box = document.getElementById('nlpLocationsBox');
        box.innerHTML = '';
        // Hardcoded locations (should not be removable)
        const hardcoded = [
            'Dasmariñas City', 'General Trias', 'Imus', 'Bacoor', 'Cavite City',
            'Tanza', 'Trece Martires', 'Tagaytay', 'Silang', 'Rosario', 'Naic',
            'Indang', 'Alfonso', 'Amadeo', 'Carmona', 'Gen. Mariano Alvarez',
            'Kawit', 'Magallanes', 'Maragondon', 'Mendez', 'Noveleta', 'Ternate'
        ];
        locations.forEach(loc => {
            const tag = document.createElement('span');
            tag.textContent = loc;
            tag.style.background = 'var(--primary-light)';
            tag.style.color = 'var(--text-light)';
            tag.style.padding = '4px 10px';
            tag.style.borderRadius = '12px';
            tag.style.margin = '2px';
            tag.style.display = 'inline-block';
            tag.style.fontSize = '0.95rem';
            if (!hardcoded.includes(loc)) {
                // Add remove button for non-hardcoded
                const removeBtn = document.createElement('button');
                removeBtn.textContent = '✕';
                removeBtn.style.marginLeft = '8px';
                removeBtn.style.background = 'var(--danger)';
                removeBtn.style.color = 'white';
                removeBtn.style.border = 'none';
                removeBtn.style.borderRadius = '50%';
                removeBtn.style.width = '22px';
                removeBtn.style.height = '22px';
                removeBtn.style.cursor = 'pointer';
                removeBtn.onclick = async function() {
                    if (!confirm('Remove this location?')) return;
                    await fetch('api/remove_nlp_location.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ location: loc })
                    });
                    loadSettings();
                };
                tag.appendChild(removeBtn);
            }
            box.appendChild(tag);
        });
    }
    document.getElementById('saveApifyQueryBtn').onclick = async function() {
        const query = document.getElementById('apifyQueryInput').value.trim();
        const res = await fetch('api/save_settings.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ apify_query: query })
        });
        const data = await res.json();
        const msg = document.getElementById('apifyQuerySaveMsg');
        msg.textContent = data.success ? 'Saved!' : (data.message || 'Error');
        msg.style.color = data.success ? 'var(--success)' : 'var(--danger)';
        msg.style.display = 'block';
        setTimeout(() => { msg.style.display = 'none'; }, 2000);
        // Restart Apify monitoring to use the new query immediately
        if (window.apifyService && typeof window.apifyService.restartMonitoring === 'function') {
            window.apifyService.restartMonitoring();
        }
    };
    document.getElementById('addNlpLocationBtn').onclick = async function() {
        const input = document.getElementById('nlpLocationInput');
        const loc = input.value.trim();
        if (!loc) return;
        const res = await fetch('api/save_nlp_location.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ location: loc })
        });
        const data = await res.json();
        const msg = document.getElementById('nlpLocationMsg');
        msg.textContent = data.success ? 'Location added!' : (data.message || 'Error');
        msg.style.color = data.success ? 'var(--success)' : 'var(--danger)';
        msg.style.display = 'block';
        input.value = '';
        loadSettings();
        setTimeout(() => { msg.style.display = 'none'; }, 2000);
    };
    document.addEventListener('DOMContentLoaded', loadSettings);
    // ... existing code ...
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Apify Token Management
        const apifyTokenInput = document.getElementById('apifyTokenInput');
        const saveApifyTokenBtn = document.getElementById('saveApifyTokenBtn');
        const resetApifyTokenBtn = document.getElementById('resetApifyTokenBtn');
        const apifyTokenSaveMsg = document.getElementById('apifyTokenSaveMsg');

        // Load token from localStorage
        const savedToken = localStorage.getItem('apifyToken');
        if (savedToken) {
            apifyTokenInput.value = savedToken;
        }

        saveApifyTokenBtn.onclick = function() {
            const token = apifyTokenInput.value.trim();
            localStorage.setItem('apifyToken', token);
            apifyTokenSaveMsg.textContent = 'Saved!';
            apifyTokenSaveMsg.style.color = 'var(--success)';
            apifyTokenSaveMsg.style.display = 'block';
            setTimeout(() => { apifyTokenSaveMsg.style.display = 'none'; }, 2000);
        };

        resetApifyTokenBtn.onclick = function() {
            localStorage.removeItem('apifyToken');
            apifyTokenInput.value = '';
            apifyTokenSaveMsg.textContent = 'Token reset!';
            apifyTokenSaveMsg.style.color = 'var(--danger)';
            apifyTokenSaveMsg.style.display = 'block';
            setTimeout(() => { apifyTokenSaveMsg.style.display = 'none'; }, 2000);
        };
    });
    </script>
</body>
</html>