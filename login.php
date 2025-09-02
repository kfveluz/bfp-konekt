<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFP Konekt - Login</title>
    <style>
        :root {
            --primary: #FF8C00;
            --primary-light: #FFB84D;
            --primary-dark: #E67A00;
            --secondary: #1A237E;
            --secondary-light: #534BAE;
            --secondary-dark: #000051;
            --bg-main: #F8F9FA;
            --bg-light: #FFFFFF;
            --bg-dark: #343A40;
            --text-dark: #333333;
            --text-light: #FFFFFF;
            --text-muted: #6C757D;
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
            min-height: 100vh;
            background-color: #1a1a1a;
            color: white;
        }
        
        .login-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .login-form-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
        }

        .slider-section {
            flex: 1;
            position: relative;
            overflow: hidden;
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
        }

        .slider-container {
            width: 100%;
            height: 100%;
            position: relative;
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            background: #fff;
        }

        .slider {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .slider-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            left: 0; top: 0;
            opacity: 0;
            transition: opacity 0.5s;
            z-index: 1;
        }

        .slider-image.active {
            opacity: 1;
            z-index: 2;
            position: relative;
        }

        .slider-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.4);
            color: #fff;
            border: none;
            font-size: 2rem;
            padding: 0 12px;
            cursor: pointer;
            border-radius: 50%;
            z-index: 10;
            transition: background 0.2s;
        }

        .slider-arrow.left { left: 10px; }
        .slider-arrow.right { right: 10px; }
        .slider-arrow:hover { background: rgba(0,0,0,0.7); }

        .slider-dots {
            position: absolute;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
            z-index: 10;
        }

        .slider-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #bbb;
            cursor: pointer;
            transition: background 0.2s;
        }

        .slider-dot.active {
            background: #333;
        }

        .login-form {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            background: var(--bg-light);
            border-radius: 15px;
            box-shadow: 0 4px 15px var(--shadow);
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo-container img {
            max-width: 200px;
            height: auto;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #4d4d4d;
            border-radius: 8px;
            transition: all 0.3s ease;
            background-color: #3d3d3d;
            color: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(255, 140, 0, 0.1);
            background-color: #3d3d3d;
            color: white;
        }

        .form-control::placeholder {
            color: #aaa;
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #007bff;
            color: var(--text-light);
        }

        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
        }

        .error-message {
            color: var(--danger);
            margin-top: 1rem;
            text-align: center;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            .slider-section {
                display: none;
            }

            .login-form-section {
                padding: 1rem;
            }

            .login-form {
                padding: 1.5rem;
            }
        }

        .card {
            background-color: #2d2d2d;
            border: none;
        }

        .text-muted {
            color: #aaa !important;
        }

        a {
            color: #007bff;
        }

        a:hover {
            color: #0056b3;
        }
    </style>
</head>
<body>
        <div class="login-container">
        <div class="login-form-section">
            <div class="login-form">
        <div class="logo-container">
                    <img id="loginLogo" src="Konekt (1).png" alt="BFP Konekt Logo">
                        </div>
                <form id="loginForm" onsubmit="return handleLogin(event)">
                <div class="form-group">
                <label for="userId">User ID</label>
                        <input type="text" id="userId" name="userId" class="form-control" required>
                </div>
                <div class="form-group">
                <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                </div>
            <button type="submit" class="btn btn-primary">Login</button>
                    <div id="errorMessage" class="error-message"></div>
            </form>
            <!-- Emergency Alert Button -->
            <button id="emergencyAlertBtn" class="btn btn-danger" style="margin-top: 1rem; width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px; background-color: #dc3545; border: none; padding: 12px; color: #fff;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" style="display:inline-block;vertical-align:middle;" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="12" cy="12" r="12" fill="white"/>
                  <polygon points="12,4 20,20 4,20" fill="#dc3545"/>
                  <rect x="11" y="10" width="2" height="5" rx="1" fill="white"/>
                  <rect x="11" y="17" width="2" height="2" rx="1" fill="white"/>
                </svg>
                Emergency Alert
            </button>
            </div>
        </div>
        <div class="slider-section">
            <div class="slider-container" id="sliderContainer">
                <div class="slider" id="slider"></div>
                <button class="slider-arrow left" id="sliderPrev" aria-label="Previous">&#10094;</button>
                <button class="slider-arrow right" id="sliderNext" aria-label="Next">&#10095;</button>
                <div class="slider-dots" id="sliderDots"></div>
            </div>
        </div>
    </div>
    
    <!-- Emergency Alert Modal -->
    <div id="emergencyModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#fff; color:#222; max-width:450px; width:90vw; margin:auto; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.2); padding:2rem; position:relative;">
            <button id="closeEmergencyModal" style="position:absolute; top:10px; right:10px; background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
            <h2 style="margin-bottom:1rem; color:#dc3545;">🚨 Emergency Alert</h2>
            <form id="emergencyForm">
                <div class="form-group">
                    <label for="emergencyPhone">Phone Number <span style="color:#dc3545;">*</span></label>
                    <input type="tel" id="emergencyPhone" class="form-control" placeholder="09XXXXXXXXX (11 digits)" 
                           pattern="[0-9]{11}" maxlength="11" required>
                    <small style="color:#666; font-size:0.85em;">Enter your 11-digit Philippine mobile number (e.g., 09123456789)</small>
                    <div id="phoneValidation" style="font-size:0.85em; margin-top:0.25rem;"></div>
                </div>
                <div class="form-group">
                    <label for="emergencyType">Type of Emergency <span style="color:#dc3545;">*</span></label>
                    <select id="emergencyType" class="form-control" required>
                        <option value="">Select emergency type...</option>
                        <option value="Fire">🔥 Fire Incident</option>
                        <option value="Medical">🏥 Medical Emergency</option>
                        <option value="Accident">🚗 Accident</option>
                        <option value="Crime">🚨 Crime/Police</option>
                        <option value="Other">⚠️ Other Emergency</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="emergencyMessage">Situation / Message <span style="color:#dc3545;">*</span></label>
                    <textarea id="emergencyMessage" class="form-control" rows="3" 
                              placeholder="Describe the situation, location details, and any immediate dangers..." 
                              maxlength="500" required></textarea>
                    <small style="color:#666; font-size:0.85em;">Maximum 500 characters</small>
                    <div id="messageCounter" style="font-size:0.85em; margin-top:0.25rem; text-align:right;"></div>
                </div>
                <div class="form-group">
                    <label>Location (auto-detected)</label>
                    <div id="gpsStatus" style="font-size:0.95em; color:#888;">Detecting location...</div>
                </div>
                
                <!-- SMS Confirmation Options -->
                <div class="form-group">
                    <label style="display:flex; align-items:center; gap:0.5rem;">
                        <input type="checkbox" id="smsConfirmation" checked>
                        <span>📱 Send FREE SMS confirmation when alert is received</span>
                    </label>
                    <label style="display:flex; align-items:center; gap:0.5rem; margin-top:0.5rem;">
                        <input type="checkbox" id="smsResponse" checked>
                        <span>📨 Receive FREE SMS updates on response status</span>
                    </label>
                    <small style="color:#28a745; font-size:0.85em; margin-top:0.5rem; display:block;">
                        💡 SMS alerts are completely FREE using email-to-SMS gateways
                    </small>
                </div>
                
                <button type="submit" id="sendEmergencyBtn" class="btn btn-danger" style="width:100%; margin-top:1rem;" disabled>
                    <span id="sendBtnText">🚨 Send Emergency Alert</span>
                    <span id="sendBtnLoading" style="display:none;">⏳ Sending...</span>
                </button>
                <div id="emergencyError" style="color:#dc3545; margin-top:0.5rem; display:none;"></div>
                <div id="emergencySuccess" style="color:#28a745; margin-top:0.5rem; display:none;"></div>
            </form>
        </div>
    </div>
    
    <script>
        // Clear all localStorage/sessionStorage on login page load
        localStorage.clear();
        sessionStorage.clear();

        // Function to handle login
        async function handleLogin(event) {
            event.preventDefault();
            
            const userId = document.getElementById('userId').value;
            const password = document.getElementById('password').value;
            const errorMessage = document.getElementById('errorMessage');
            
            try {
                const response = await fetch('api/auth.php', {
                method: 'POST',
                headers: {
                        'Content-Type': 'application/json'
                },
                    body: JSON.stringify({ userId, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Redirect to dashboard (reload page to get PHP session)
                    window.location = 'index.php';
                } else {
                    errorMessage.textContent = data.message || 'Invalid credentials';
                    errorMessage.classList.add('show');
                }
            } catch (error) {
                errorMessage.textContent = 'An error occurred. Please try again.';
                errorMessage.classList.add('show');
            }
            
            return false;
        }

        // Advanced slider logic
        async function loadSliderImages() {
            try {
                const response = await fetch('api/get-slider-images.php');
                const data = await response.json();
                const slider = document.getElementById('slider');
                const dotsContainer = document.getElementById('sliderDots');
                const prevBtn = document.getElementById('sliderPrev');
                const nextBtn = document.getElementById('sliderNext');
                let current = 0;
                let timer;
                slider.innerHTML = '';
                dotsContainer.innerHTML = '';
                if (data.success && data.images.length > 0) {
                    data.images.forEach((img, idx) => {
                        const imgEl = document.createElement('img');
                        imgEl.src = img.url;
                        imgEl.alt = 'Slider Image';
                        imgEl.className = 'slider-image' + (idx === 0 ? ' active' : '');
                        slider.appendChild(imgEl);
                        const dot = document.createElement('span');
                        dot.className = 'slider-dot' + (idx === 0 ? ' active' : '');
                        dot.addEventListener('click', () => showSlide(idx));
                        dotsContainer.appendChild(dot);
                    });
                } else {
                    // Fallback default image
                    const imgEl = document.createElement('img');
                    imgEl.src = 'bfp-anime-landscape.png';
                    imgEl.alt = 'Default Slider Image';
                    imgEl.className = 'slider-image active';
                    slider.appendChild(imgEl);
                    const dot = document.createElement('span');
                    dot.className = 'slider-dot active';
                    dotsContainer.appendChild(dot);
                }
                const images = slider.querySelectorAll('.slider-image');
                const dots = dotsContainer.querySelectorAll('.slider-dot');
                function showSlide(idx) {
                    images[current].classList.remove('active');
                    dots[current].classList.remove('active');
                    current = idx;
                    images[current].classList.add('active');
                    dots[current].classList.add('active');
                    resetTimer();
                }
                function nextSlide() {
                    showSlide((current + 1) % images.length);
                }
                function prevSlide() {
                    showSlide((current - 1 + images.length) % images.length);
                }
                function resetTimer() {
                    clearInterval(timer);
                    timer = setInterval(nextSlide, 5000);
                }
                nextBtn.addEventListener('click', nextSlide);
                prevBtn.addEventListener('click', prevSlide);
                timer = setInterval(nextSlide, 5000);
            } catch (error) {
                // Fallback default image on error
                const slider = document.getElementById('slider');
                const dotsContainer = document.getElementById('sliderDots');
                slider.innerHTML = '';
                dotsContainer.innerHTML = '';
                const imgEl = document.createElement('img');
                imgEl.src = 'bfp-anime-landscape.png';
                imgEl.alt = 'Default Slider Image';
                imgEl.className = 'slider-image active';
                slider.appendChild(imgEl);
                const dot = document.createElement('span');
                dot.className = 'slider-dot active';
                dotsContainer.appendChild(dot);
            }
        }
        document.addEventListener('DOMContentLoaded', loadSliderImages);

        // Emergency Alert Modal Logic
        let emergencyLat = null;
        let emergencyLng = null;
        let reporterIP = null;

        // Philippine phone number validation
        function validatePhilippinePhone(phone) {
            const phoneRegex = /^09\d{9}$/;
            return phoneRegex.test(phone);
        }

        // Update message counter
        function updateMessageCounter() {
            const message = document.getElementById('emergencyMessage').value;
            const counter = document.getElementById('messageCounter');
            const remaining = 500 - message.length;
            counter.textContent = `${remaining} characters remaining`;
            counter.style.color = remaining < 50 ? '#dc3545' : remaining < 100 ? '#ffc107' : '#666';
        }

        // Update phone validation display
        function updatePhoneValidation(phone) {
            const validationDiv = document.getElementById('phoneValidation');
            if (!phone) {
                validationDiv.textContent = '';
                validationDiv.style.color = '#666';
                return false;
            }
            
            if (phone.length !== 11) {
                validationDiv.textContent = '❌ Phone number must be exactly 11 digits';
                validationDiv.style.color = '#dc3545';
                return false;
            }
            
            if (!validatePhilippinePhone(phone)) {
                validationDiv.textContent = '❌ Invalid Philippine mobile number format (should start with 09)';
                validationDiv.style.color = '#dc3545';
                return false;
            }
            
            validationDiv.textContent = '✅ Valid Philippine mobile number';
            validationDiv.style.color = '#28a745';
            return true;
        }

        function showEmergencyModal() {
            document.getElementById('emergencyModal').style.display = 'flex';
            document.getElementById('emergencyError').style.display = 'none';
            document.getElementById('emergencySuccess').style.display = 'none';
            document.getElementById('emergencyForm').reset();
            document.getElementById('sendEmergencyBtn').disabled = true;
            document.getElementById('gpsStatus').textContent = 'Detecting location...';
            emergencyLat = null;
            emergencyLng = null;
            
            // Get GPS
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    emergencyLat = pos.coords.latitude;
                    emergencyLng = pos.coords.longitude;
                    document.getElementById('gpsStatus').textContent = `📍 Location: ${emergencyLat.toFixed(5)}, ${emergencyLng.toFixed(5)}`;
                    checkEmergencyFormReady();
                }, function(err) {
                    document.getElementById('gpsStatus').textContent = '⚠️ Location unavailable. Please enable GPS for better response.';
                });
            } else {
                document.getElementById('gpsStatus').textContent = '⚠️ Geolocation not supported by your browser.';
            }
            
            // Get IP
            fetch('https://api.ipify.org?format=json').then(r=>r.json()).then(data=>{ 
                reporterIP = data.ip; 
            }).catch(() => {
                reporterIP = 'unknown';
            });
        }

        function hideEmergencyModal() {
            document.getElementById('emergencyModal').style.display = 'none';
        }

        function checkEmergencyFormReady() {
            const phone = document.getElementById('emergencyPhone').value.trim();
            const type = document.getElementById('emergencyType').value;
            const message = document.getElementById('emergencyMessage').value.trim();
            const isPhoneValid = updatePhoneValidation(phone);
            
            const isReady = isPhoneValid && type && message && emergencyLat && emergencyLng;
            document.getElementById('sendEmergencyBtn').disabled = !isReady;
        }

        // Event listeners
        document.getElementById('emergencyAlertBtn').onclick = showEmergencyModal;
        document.getElementById('closeEmergencyModal').onclick = hideEmergencyModal;
        document.getElementById('emergencyPhone').oninput = checkEmergencyFormReady;
        document.getElementById('emergencyType').onchange = checkEmergencyFormReady;
        document.getElementById('emergencyMessage').oninput = function() {
            updateMessageCounter();
            checkEmergencyFormReady();
        };

        // Form submission
        document.getElementById('emergencyForm').onsubmit = async function(e) {
            e.preventDefault();
            
            const phone = document.getElementById('emergencyPhone').value.trim();
            const type = document.getElementById('emergencyType').value;
            const message = document.getElementById('emergencyMessage').value.trim();
            const smsConfirmation = document.getElementById('smsConfirmation').checked;
            const smsResponse = document.getElementById('smsResponse').checked;
            
            const errorDiv = document.getElementById('emergencyError');
            const successDiv = document.getElementById('emergencySuccess');
            const sendBtn = document.getElementById('sendEmergencyBtn');
            const sendBtnText = document.getElementById('sendBtnText');
            const sendBtnLoading = document.getElementById('sendBtnLoading');
            
            // Validation
            if (!phone || !type || !message || !emergencyLat || !emergencyLng) {
                errorDiv.textContent = 'All fields and location are required.';
                errorDiv.style.display = 'block';
                successDiv.style.display = 'none';
                return;
            }
            
            if (!validatePhilippinePhone(phone)) {
                errorDiv.textContent = 'Please enter a valid 11-digit Philippine mobile number.';
                errorDiv.style.display = 'block';
                successDiv.style.display = 'none';
                return;
            }
            
            // Show loading state
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';
            sendBtn.disabled = true;
            sendBtnText.style.display = 'none';
            sendBtnLoading.style.display = 'inline';
            
            try {
                const res = await fetch('api/report-emergency.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        phone, 
                        type, 
                        message,
                        lat: emergencyLat, 
                        lng: emergencyLng,
                        ip: reporterIP,
                        smsConfirmation,
                        smsResponse
                    })
                });
                
                const data = await res.json();
                
                if (data.success) {
                    successDiv.innerHTML = `
                        <div style="text-align:center; padding:1rem;">
                            <div style="font-size:3rem; margin-bottom:0.5rem;">✅</div>
                            <h3 style="color:#28a745; margin-bottom:0.5rem;">Emergency Alert Sent!</h3>
                            <p><strong>Reference ID:</strong> ${data.custom_id}</p>
                            <p>BFP has been notified and will respond immediately.</p>
                            ${smsConfirmation ? '<p>📱 You will receive an SMS confirmation shortly.</p>' : ''}
                            ${smsResponse ? '<p>📨 You will receive SMS updates on response status.</p>' : ''}
                        </div>
                    `;
                    successDiv.style.display = 'block';
                    
                    // Auto-close modal after 5 seconds
                    setTimeout(() => {
                        hideEmergencyModal();
                    }, 5000);
                } else {
                    errorDiv.textContent = data.message || 'Failed to send emergency report. Please try again.';
                    errorDiv.style.display = 'block';
                }
            } catch (err) {
                errorDiv.textContent = 'Network error. Please check your connection and try again.';
                errorDiv.style.display = 'block';
            }
            
            // Reset button state
            sendBtn.disabled = false;
            sendBtnText.style.display = 'inline';
            sendBtnLoading.style.display = 'none';
        };

        // Logo Management for Login Page
        document.addEventListener('DOMContentLoaded', function() {
            const loginLogo = document.getElementById('loginLogo');
            
            // Load saved main logo if exists
            const savedMainLogo = localStorage.getItem('bfpLogo');
            if (savedMainLogo && loginLogo) {
                loginLogo.src = savedMainLogo;
            }
        });
    </script>
</body>
</html>