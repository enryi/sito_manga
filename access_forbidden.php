<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Accesso Negato</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="icon.webp" type="image/x-icon">
    <style>
        :root {
            --primary: #d4d4d4;
            --secondary: #404040;
            --background: #000000;
            --surface: #111111;
            --text: #e5e5e5;
            --text-secondary: #808080;
            --accent: #333333;
            --danger: #dc2626;
            --danger-glow: rgba(220, 38, 38, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--background);
            color: var(--text);
            padding: 20px;
            overflow: hidden;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(220, 38, 38, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 90% 80%, rgba(128, 128, 128, 0.03) 0%, transparent 50%);
        }

        .container {
            position: relative;
            text-align: center;
            max-width: 600px;
            padding: 3rem;
            background: rgba(17, 17, 17, 0.7);
            border: 1px solid rgba(220, 38, 38, 0.1);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(20px);
            animation: slideUp 0.6s ease-out;
        }

        .error-icon {
            position: relative;
            width: 130px;
            height: 130px;
            margin: 0 auto 2.5rem;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            border-radius: 65px;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: shake 3s infinite;
            box-shadow: 0 0 40px var(--danger-glow);
        }

        .error-icon::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 65px;
            background: inherit;
            filter: blur(25px);
            opacity: 0.5;
            z-index: -1;
        }

        .error-icon i {
            font-size: 3.8rem;
            color: #ffffff;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.4);
        }

        .error-code {
            font-size: 5rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -2px;
            text-shadow: 0 0 30px var(--danger-glow);
            line-height: 1;
        }

        h1 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #d4d4d4, #808080);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        p {
            font-size: 1.15rem;
            line-height: 1.7;
            color: var(--text-secondary);
            margin-bottom: 2.5rem;
        }

        .info-box {
            background: rgba(220, 38, 38, 0.05);
            border: 1px solid rgba(220, 38, 38, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
            text-align: left;
        }

        .info-box h3 {
            color: #dc2626;
            font-size: 1rem;
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-box ul {
            list-style: none;
            padding-left: 0;
        }

        .info-box li {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            padding-left: 1.5rem;
            position: relative;
        }

        .info-box li::before {
            content: '•';
            position: absolute;
            left: 0.5rem;
            color: #dc2626;
        }

        @keyframes shake {
            0%, 100% { transform: rotate(0deg); }
            10%, 30%, 50%, 70%, 90% { transform: rotate(-2deg); }
            20%, 40%, 60%, 80% { transform: rotate(2deg); }
        }

        @keyframes slideUp {
            from { 
                opacity: 0;
                transform: translateY(30px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
            100% { transform: translateY(0px) rotate(360deg); }
        }

        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            border-radius: 50%;
            opacity: 0.08;
            animation: float 15s infinite;
        }

        @media (max-width: 480px) {
            .container {
                padding: 2rem;
            }
            .error-code {
                font-size: 4rem;
            }
            h1 {
                font-size: 1.8rem;
            }
            p {
                font-size: 1rem;
            }
            .error-icon {
                width: 110px;
                height: 110px;
            }
            .error-icon i {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="particles"></div>
    <div class="container">
        <div class="error-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        <div class="error-code">403</div>
        <h1>Access Forbidden</h1>
        <p>You are not authorized to access this page.<br>
        The access is only available if you use an authorized domain.</p>
        
        <div class="info-box">
            <h3><i class="fas fa-info-circle"></i> Common issue:</h3>
            <ul>
                <li>You are trying to log with an unauthorized domain.</li>
                <li>Your IP is not whitelisted.</li>
                <li>You don't have the permission to view this page.</li>
            </ul>
        </div>
    </div>

    <script>
        function createParticles() {
            const particles = document.querySelector('.particles');
            for(let i = 0; i < 15; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                const size = Math.random() * 20 + 5;
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.animationDelay = (Math.random() * 10) + 's';
                particle.style.animationDuration = (Math.random() * 20 + 15) + 's';
                particles.appendChild(particle);
            }
        }
        createParticles();
    </script>
</body>
</html>