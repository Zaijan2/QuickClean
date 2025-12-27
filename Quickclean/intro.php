<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Welcome to QuickClean</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        margin: 0;
        padding: 0;
        background: linear-gradient(135deg, #FFDB58 0%, #FFC107 100%);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        font-family: "Poppins", sans-serif;
        overflow: hidden;
        position: relative;
    }

    /* Animated background bubbles */
    .bubble {
        position: absolute;
        bottom: -100px;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        animation: rise 15s infinite ease-in;
    }

    .bubble:nth-child(1) {
        width: 80px;
        height: 80px;
        left: 10%;
        animation-duration: 12s;
        animation-delay: 0s;
    }

    .bubble:nth-child(2) {
        width: 60px;
        height: 60px;
        left: 25%;
        animation-duration: 10s;
        animation-delay: 2s;
    }

    .bubble:nth-child(3) {
        width: 100px;
        height: 100px;
        left: 50%;
        animation-duration: 14s;
        animation-delay: 1s;
    }

    .bubble:nth-child(4) {
        width: 70px;
        height: 70px;
        left: 75%;
        animation-duration: 11s;
        animation-delay: 3s;
    }

    .bubble:nth-child(5) {
        width: 90px;
        height: 90px;
        left: 85%;
        animation-duration: 13s;
        animation-delay: 4s;
    }

    @keyframes rise {
        0% {
            bottom: -100px;
            opacity: 0;
            transform: translateX(0) rotate(0deg);
        }
        50% {
            opacity: 0.6;
        }
        100% {
            bottom: 110vh;
            opacity: 0;
            transform: translateX(100px) rotate(360deg);
        }
    }

    /* Sparkle effect */
    .sparkle {
        position: absolute;
        width: 4px;
        height: 4px;
        background: white;
        border-radius: 50%;
        animation: sparkle 3s infinite;
        opacity: 0;
    }

    @keyframes sparkle {
        0%, 100% { opacity: 0; transform: scale(0); }
        50% { opacity: 1; transform: scale(1); }
    }

    .container {
        text-align: center;
        opacity: 0;
        animation: fadeInUp 1.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        z-index: 10;
        position: relative;
    }

    @keyframes fadeInUp {
        from { 
            opacity: 0; 
            transform: translateY(40px) scale(0.95);
        }
        to { 
            opacity: 1; 
            transform: translateY(0) scale(1);
        }
    }

    .logo-wrapper {
        position: relative;
        display: inline-block;
        margin-bottom: 30px;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
    }

    .logo {
        width: 220px;
        height: 220px;
        border-radius: 50%;
        object-fit: cover;
        border: 6px solid white;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        animation: pulse 2s ease-in-out infinite;
        position: relative;
        z-index: 2;
    }

    @keyframes pulse {
        0%, 100% { 
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2),
                        0 0 0 0 rgba(255, 255, 255, 0.7);
        }
        50% { 
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.3),
                        0 0 0 15px rgba(255, 255, 255, 0);
        }
    }

    /* Glow effect behind logo */
    .logo-glow {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 250px;
        height: 250px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.4) 0%, transparent 70%);
        border-radius: 50%;
        animation: glow 2s ease-in-out infinite;
        z-index: 1;
    }

    @keyframes glow {
        0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.5; }
        50% { transform: translate(-50%, -50%) scale(1.1); opacity: 0.8; }
    }

    .tagline {
        font-size: 28px;
        font-weight: 800;
        color: #2E89F0;
        margin-bottom: 35px;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        animation: slideIn 1s cubic-bezier(0.4, 0, 0.2, 1) 0.3s backwards;
        letter-spacing: 0.5px;
    }

    @keyframes slideIn {
        from { 
            opacity: 0; 
            transform: translateX(-30px);
        }
        to { 
            opacity: 1; 
            transform: translateX(0);
        }
    }

    .tagline .highlight {
        color: #FF6B6B;
        display: inline-block;
        animation: bounce 1s ease-in-out 1.5s;
    }

    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        25% { transform: translateY(-10px); }
        50% { transform: translateY(0); }
        75% { transform: translateY(-5px); }
    }

    .enter-btn {
        background: linear-gradient(135deg, #2E89F0 0%, #1c6ed6 100%);
        color: white;
        padding: 16px 48px;
        font-size: 20px;
        font-weight: 600;
        border: none;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        display: inline-block;
        box-shadow: 0 10px 30px rgba(46, 137, 240, 0.3);
        animation: buttonPop 1s cubic-bezier(0.4, 0, 0.2, 1) 0.6s backwards;
        position: relative;
        overflow: hidden;
    }

    @keyframes buttonPop {
        0% { 
            opacity: 0; 
            transform: scale(0.8) translateY(20px);
        }
        60% { 
            transform: scale(1.05) translateY(0);
        }
        100% { 
            opacity: 1; 
            transform: scale(1) translateY(0);
        }
    }

    .enter-btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .enter-btn:hover::before {
        width: 300px;
        height: 300px;
    }

    .enter-btn:hover {
        transform: translateY(-5px) scale(1.05);
        box-shadow: 0 15px 40px rgba(46, 137, 240, 0.5);
    }

    .enter-btn:active {
        transform: translateY(-2px) scale(1.02);
    }

    .enter-btn span {
        position: relative;
        z-index: 1;
    }

    /* Shine effect on button */
    .enter-btn::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -100%;
        width: 100%;
        height: 200%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transform: rotate(45deg);
        animation: shine 3s infinite;
    }

    @keyframes shine {
        0% { left: -100%; }
        50%, 100% { left: 150%; }
    }

    /* Confetti effect */
    .confetti {
        position: absolute;
        width: 10px;
        height: 10px;
        background: #FF6B6B;
        animation: confettiFall 4s linear infinite;
        opacity: 0;
    }

    @keyframes confettiFall {
        0% {
            opacity: 1;
            transform: translateY(-100px) rotate(0deg);
        }
        100% {
            opacity: 0;
            transform: translateY(100vh) rotate(720deg);
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .logo {
            width: 180px;
            height: 180px;
        }

        .tagline {
            font-size: 22px;
            padding: 0 20px;
        }

        .enter-btn {
            padding: 14px 36px;
            font-size: 18px;
        }
    }
</style>

</head>
<body>

<!-- Animated bubbles -->
<div class="bubble"></div>
<div class="bubble"></div>
<div class="bubble"></div>
<div class="bubble"></div>
<div class="bubble"></div>

<div class="container">
    <!-- Logo with glow effect -->
    <div class="logo-wrapper">
        <div class="logo-glow"></div>
        <img src="logo.png" class="logo" alt="QuickClean Logo">
    </div>

    <!-- Tagline -->
    <div class="tagline">
        QuickClean: <span class="highlight">Clean Spaces, Happy Faces</span>.
    </div>

    <!-- Enter Button -->
    <a href="register.php" class="enter-btn">
        <span>🚀 Get Started!</span>
    </a>
</div>

<script>
// Generate random sparkles
function createSparkle() {
    const sparkle = document.createElement('div');
    sparkle.className = 'sparkle';
    sparkle.style.left = Math.random() * 100 + '%';
    sparkle.style.top = Math.random() * 100 + '%';
    sparkle.style.animationDelay = Math.random() * 3 + 's';
    document.body.appendChild(sparkle);
}

// Create 15 sparkles
for (let i = 0; i < 15; i++) {
    createSparkle();
}

// Generate random confetti
function createConfetti() {
    const confetti = document.createElement('div');
    confetti.className = 'confetti';
    confetti.style.left = Math.random() * 100 + '%';
    confetti.style.animationDelay = Math.random() * 4 + 's';
    confetti.style.animationDuration = (Math.random() * 3 + 3) + 's';
    
    const colors = ['#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8'];
    confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
    
    document.body.appendChild(confetti);
}

// Create 20 confetti pieces
for (let i = 0; i < 20; i++) {
    createConfetti();
}

// Add click effect on button
document.querySelector('.enter-btn').addEventListener('click', function(e) {
    const ripple = document.createElement('div');
    ripple.style.position = 'absolute';
    ripple.style.width = '20px';
    ripple.style.height = '20px';
    ripple.style.background = 'rgba(255, 255, 255, 0.6)';
    ripple.style.borderRadius = '50%';
    ripple.style.transform = 'scale(0)';
    ripple.style.animation = 'ripple 0.6s ease-out';
    ripple.style.left = (e.clientX - this.offsetLeft) + 'px';
    ripple.style.top = (e.clientY - this.offsetTop) + 'px';
    
    this.appendChild(ripple);
    
    setTimeout(() => ripple.remove(), 600);
});

// Add ripple animation
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
</script>

</body>
</html>