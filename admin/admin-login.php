<!DOCTYPE html>

<html class="light" lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Admin Login - DentalCare Pro</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-background": "#0b1c30",
                        "on-primary-fixed": "#001b3d",
                        "surface-container-high": "#dce9ff",
                        "on-secondary-fixed": "#191c1e",
                        "on-surface": "#0b1c30",
                        "secondary-fixed-dim": "#c4c7c9",
                        "on-primary-fixed-variant": "#00468c",
                        "secondary": "#5c5f61",
                        "surface-container-lowest": "#ffffff",
                        "background": "#f8f9ff",
                        "surface-tint": "#005db6",
                        "on-tertiary-fixed": "#002113",
                        "error": "#ba1a1a",
                        "primary-container": "#005eb8",
                        "inverse-primary": "#a9c7ff",
                        "surface-container": "#e5eeff",
                        "on-tertiary-container": "#65f2b5",
                        "surface-container-highest": "#d3e4fe",
                        "outline": "#727783",
                        "on-primary": "#ffffff",
                        "outline-variant": "#c2c6d4",
                        "inverse-on-surface": "#eaf1ff",
                        "primary-fixed-dim": "#a9c7ff",
                        "surface-bright": "#f8f9ff",
                        "on-primary-container": "#c8daff",
                        "on-tertiary-fixed-variant": "#005236",
                        "primary": "#00478d",
                        "error-container": "#ffdad6",
                        "on-secondary": "#ffffff",
                        "surface-container-low": "#eff4ff",
                        "tertiary-fixed": "#6ffbbe",
                        "on-surface-variant": "#424752",
                        "on-tertiary": "#ffffff",
                        "tertiary-fixed-dim": "#4edea3",
                        "tertiary": "#005237",
                        "surface": "#f8f9ff",
                        "on-error-container": "#93000a",
                        "surface-variant": "#d3e4fe",
                        "surface-dim": "#cbdbf5",
                        "on-secondary-fixed-variant": "#444749",
                        "primary-fixed": "#d6e3ff",
                        "inverse-surface": "#213145",
                        "on-error": "#ffffff",
                        "secondary-fixed": "#e0e3e5",
                        "secondary-container": "#e0e3e5",
                        "tertiary-container": "#006d4a",
                        "on-secondary-container": "#626567"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "gutter": "24px",
                        "md": "24px",
                        "margin-mobile": "16px",
                        "xs": "4px",
                        "lg": "48px",
                        "xl": "80px",
                        "margin-desktop": "64px",
                        "sm": "12px",
                        "base": "8px"
                    },
                    "fontFamily": {
                        "label-sm": ["Inter"],
                        "body-md": ["Inter"],
                        "headline-md": ["Inter"],
                        "headline-xl": ["Inter"],
                        "body-lg": ["Inter"],
                        "headline-lg-mobile": ["Inter"],
                        "headline-lg": ["Inter"],
                        "label-md": ["Inter"]
                    },
                    "fontSize": {
                        "label-sm": ["12px", { "lineHeight": "16px", "fontWeight": "600" }],
                        "body-md": ["16px", { "lineHeight": "24px", "fontWeight": "400" }],
                        "headline-md": ["24px", { "lineHeight": "32px", "fontWeight": "600" }],
                        "headline-xl": ["48px", { "lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                        "body-lg": ["18px", { "lineHeight": "28px", "fontWeight": "400" }],
                        "headline-lg-mobile": ["28px", { "lineHeight": "36px", "fontWeight": "600" }],
                        "headline-lg": ["32px", { "lineHeight": "40px", "letterSpacing": "-0.01em", "fontWeight": "600" }],
                        "label-md": ["14px", { "lineHeight": "20px", "letterSpacing": "0.01em", "fontWeight": "500" }]
                    }
                }
            }
        }
    </script>
<style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-background text-on-background min-h-screen flex items-center justify-center p-margin-mobile md:p-margin-desktop relative overflow-hidden">
<!-- Decorative Background Elements -->
<div class="absolute top-[-10%] left-[-5%] w-[40vw] h-[40vw] bg-surface-container-high rounded-full blur-3xl opacity-50 z-0 pointer-events-none"></div>
<div class="absolute bottom-[-10%] right-[-5%] w-[30vw] h-[30vw] bg-primary-container rounded-full blur-3xl opacity-20 z-0 pointer-events-none"></div>
<!-- Main Content Canvas -->
<main class="w-full max-w-[480px] bg-surface-container-lowest rounded-xl shadow-[0px_10px_30px_rgba(0,0,0,0.08)] border border-outline-variant/30 relative z-10 overflow-hidden">
<!-- Header Section -->
<div class="p-lg pb-md text-center bg-surface-container-low border-b border-outline-variant/30">
<div class="w-16 h-16 mx-auto bg-primary text-on-primary rounded-xl flex items-center justify-center mb-md shadow-sm">
<span class="material-symbols-outlined text-4xl" data-icon="medical_services">medical_services</span>
</div>
<h1 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-base">Admin Portal</h1>
<p class="font-body-md text-body-md text-on-surface-variant">Sign in to manage DentalCare Pro</p>
</div>
<!-- Login Form -->
<div class="p-lg pt-md">
<form class="space-y-md" id="loginForm">
<!-- Email Field -->
<div>
<label class="block font-label-md text-label-md text-on-surface-variant mb-base" for="email">Email Address</label>
<div class="relative">
<span class="absolute inset-y-0 left-0 flex items-center pl-sm text-outline">
<span class="material-symbols-outlined" data-icon="mail">mail</span>
</span>
<input class="w-full pl-xl pr-sm py-sm bg-surface rounded-lg border border-outline-variant text-on-background font-body-md text-body-md focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all outline-none" id="email" name="email" placeholder="admin@dentalcarepro.com" required="" type="email"/>
</div>
</div>
<!-- Password Field -->
<div>
<div class="flex justify-between items-center mb-base">
<label class="block font-label-md text-label-md text-on-surface-variant" for="password">Password</label>
<a class="font-label-sm text-label-sm text-primary hover:text-primary-container transition-colors" href="#">Forgot password?</a>
</div>
<div class="relative">
<span class="absolute inset-y-0 left-0 flex items-center pl-sm text-outline">
<span class="material-symbols-outlined" data-icon="lock">lock</span>
</span>
<input class="w-full pl-xl pr-xl py-sm bg-surface rounded-lg border border-outline-variant text-on-background font-body-md text-body-md focus:border-primary focus:ring-4 focus:ring-primary/20 transition-all outline-none" id="password" name="password" placeholder="••••••••" required="" type="password"/>
<button class="absolute inset-y-0 right-0 flex items-center pr-sm text-outline hover:text-on-surface-variant transition-colors focus:outline-none" id="togglePassword" type="button">
<span class="material-symbols-outlined" data-icon="visibility" id="toggleIcon">visibility</span>
</button>
</div>
</div>
<!-- Remember Me -->
<div class="flex items-center">
<input class="w-4 h-4 text-primary bg-surface border-outline-variant rounded focus:ring-primary focus:ring-2" id="remember" type="checkbox" value=""/>
<label class="ml-sm font-label-md text-label-md text-on-surface-variant" for="remember">Remember me for 30 days</label>
</div>
<!-- Submit Button -->
<button class="w-full bg-primary text-on-primary font-label-md text-label-md py-sm rounded-lg hover:bg-primary-container transition-all duration-200 active:scale-[0.98] shadow-sm flex items-center justify-center gap-base mt-lg" type="submit">
                    Sign In
                    <span class="material-symbols-outlined" data-icon="arrow_forward">arrow_forward</span>
</button>
<!-- Error Message Container (Hidden by default) -->
<div class="hidden mt-sm p-sm bg-error-container text-on-error-container font-label-sm text-label-sm rounded-lg flex items-center gap-base" id="errorMessage">
<span class="material-symbols-outlined" data-icon="error">error</span>
                    Invalid credentials. Please try again.
                </div>
</form>
</div>
<!-- Footer Info -->
<div class="px-lg py-md bg-surface-container-low border-t border-outline-variant/30 text-center">
<p class="font-label-sm text-label-sm text-on-surface-variant flex items-center justify-center gap-xs">
<span class="material-symbols-outlined text-[16px]" data-icon="shield">shield</span>
                Secure 256-bit Encrypted Connection
            </p>
</div>
</main>
<script>
        document.addEventListener('DOMContentLoaded', () => {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.getElementById('togglePassword');
            const toggleIcon = document.getElementById('toggleIcon');
            const loginForm = document.getElementById('loginForm');
            const errorMessage = document.getElementById('errorMessage');

            // Password Toggle Logic
            toggleButton.addEventListener('click', () => {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                const newIcon = type === 'password' ? 'visibility' : 'visibility_off';
                toggleIcon.textContent = newIcon;
                toggleIcon.setAttribute('data-icon', newIcon);
            });

            // Mock Login Logic
            loginForm.addEventListener('submit', (e) => {
                e.preventDefault();
                
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                const submitBtn = loginForm.querySelector('button[type="submit"]');

                // Simulate loading state
                submitBtn.disabled = true;
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="material-symbols-outlined animate-spin" data-icon="progress_activity">progress_activity</span> Authenticating...';

                // Mock API call delay
                setTimeout(() => {
                    // Simple mock validation (accept any email containing 'admin' and password > 4 chars)
                    if (email.includes('admin') && password.length > 4) {
                        errorMessage.classList.add('hidden');
                        // In a real app, this would redirect to the dashboard
                        submitBtn.innerHTML = '<span class="material-symbols-outlined" data-icon="check_circle">check_circle</span> Success Redirecting...';
                        submitBtn.classList.remove('bg-primary', 'hover:bg-primary-container');
                        submitBtn.classList.add('bg-tertiary-container', 'text-on-tertiary-container');
                        
                        setTimeout(() => {
                            alert('Login Successful! Redirecting to Dashboard...');
                            // window.location.href = '/dashboard.html';
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                            submitBtn.className = "w-full bg-primary text-on-primary font-label-md text-label-md py-sm rounded-lg hover:bg-primary-container transition-all duration-200 active:scale-[0.98] shadow-sm flex items-center justify-center gap-base mt-lg";
                        }, 1000);
                    } else {
                        // Show error
                        errorMessage.classList.remove('hidden');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                        
                        // Shake animation for error
                        loginForm.classList.add('animate-shake');
                        setTimeout(() => {
                            loginForm.classList.remove('animate-shake');
                        }, 500);
                    }
                }, 1500);
            });
        });
    </script>
<style>
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        .animate-shake {
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }
    </style>
</body></html>