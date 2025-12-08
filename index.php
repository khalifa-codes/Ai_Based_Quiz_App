<?php
// QuizAura - AI-Powered Quiz & Exam System Landing Page
// Set HTTP status and headers for performance
http_response_code(200);
header('Cache-Control: public, max-age=31536000, immutable');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Security Headers -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="SAMEORIGIN">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
    <meta http-equiv="Permissions-Policy" content="geolocation=(), microphone=(), camera=()">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://unpkg.com; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src 'self' data: https:; connect-src 'self'; frame-ancestors 'self';">
    <meta http-equiv="Cross-Origin-Opener-Policy" content="same-origin">
    <meta http-equiv="Strict-Transport-Security" content="max-age=31536000; includeSubDomains">
    
    <!-- Resource Hints for Performance -->
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link rel="dns-prefetch" href="https://unpkg.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://unpkg.com" crossorigin>
    
    <!-- Primary Meta Tags -->
    <title>QuizAura - AI-Powered Quiz & Exam System | Smart Assessment Platform</title>
    <meta name="title" content="QuizAura - AI-Powered Quiz & Exam System | Smart Assessment Platform">
    <meta name="description" content="QuizAura is an advanced AI-powered quiz and exam system that enables teachers to create, manage, and evaluate assessments with intelligent AI evaluation. Features include anti-cheat security, multi-organization support, white-label customization, and comprehensive analytics.">
    <meta name="keywords" content="quiz system, exam platform, AI assessment, online quiz, exam management, quiz creator, AI evaluation, educational technology, assessment platform, quiz software, exam software, online assessment, quiz maker, AI grading, automated assessment">
    <meta name="author" content="QuizAura">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">
    <meta name="bingbot" content="index, follow">
    <meta name="language" content="English">
    <meta name="revisit-after" content="7 days">
    <meta name="theme-color" content="#6366f1">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="https://quizaura.hamzax.me/">
    
    <!-- Sitemap -->
    <link rel="sitemap" type="application/xml" href="https://quizaura.hamzax.me/sitemap.xml">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://quizaura.hamzax.me/">
    <meta property="og:title" content="QuizAura - AI-Powered Quiz & Exam System | Smart Assessment Platform">
    <meta property="og:description" content="Create, manage, and evaluate assessments with intelligent AI evaluation. Features anti-cheat security, multi-organization support, and comprehensive analytics.">
    <meta property="og:image" content="https://quizaura.hamzax.me/assets/images/logo-removebg-preview.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="QuizAura">
    <meta property="og:locale" content="en_US">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://quizaura.hamzax.me/">
    <meta name="twitter:title" content="QuizAura - AI-Powered Quiz & Exam System">
    <meta name="twitter:description" content="Create, manage, and evaluate assessments with intelligent AI evaluation. Features anti-cheat security and comprehensive analytics.">
    <meta name="twitter:image" content="https://quizaura.hamzax.me/assets/images/logo-removebg-preview.png">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/logo-removebg-preview.png">
    <link rel="apple-touch-icon" href="assets/images/logo-removebg-preview.png">
    
    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "@id": "https://quizaura.hamzax.me/#software",
        "name": "QuizAura",
        "applicationCategory": "EducationalApplication",
        "operatingSystem": "Web",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.8",
            "ratingCount": "150"
        },
        "description": "AI-powered quiz and exam system with intelligent evaluation, anti-cheat security, and comprehensive analytics.",
        "url": "https://quizaura.hamzax.me/",
        "screenshot": "https://quizaura.hamzax.me/assets/images/logo-removebg-preview.png",
        "featureList": [
            "AI-Powered Evaluation",
            "Anti-Cheat Security",
            "Multi-Organization Support",
            "White-Label Customization",
            "Comprehensive Analytics",
            "Real-Time Assessment",
            "DOCX Upload Support"
        ]
    }
    </script>
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "@id": "https://quizaura.hamzax.me/#organization",
        "name": "QuizAura",
        "url": "https://quizaura.hamzax.me/",
        "logo": {
            "@type": "ImageObject",
            "url": "https://quizaura.hamzax.me/assets/images/logo-removebg-preview.png"
        },
        "description": "AI-powered quiz and exam system for educational institutions",
        "sameAs": [
            "https://www.facebook.com/quizaura",
            "https://www.twitter.com/quizaura",
            "https://www.linkedin.com/company/quizaura"
        ],
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "Customer Support",
            "email": "support@quizaura.com",
            "availableLanguage": "English"
        }
    }
    </script>
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "@id": "https://quizaura.hamzax.me/#website",
        "name": "QuizAura",
        "url": "https://quizaura.hamzax.me/",
        "potentialAction": {
            "@type": "SearchAction",
            "target": {
                "@type": "EntryPoint",
                "urlTemplate": "https://quizaura.hamzax.me/search?q={search_term_string}"
            },
            "query-input": "required name=search_term_string"
        },
        "inLanguage": "en-US",
        "isAccessibleForFree": true
    }
    </script>
    
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "@id": "https://quizaura.hamzax.me/#faq",
        "mainEntity": [{
            "@type": "Question",
            "name": "How accurate is AI evaluation?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Our AI evaluation system uses advanced natural language processing models to assess subjective answers with high accuracy. The system evaluates based on multiple criteria including accuracy, completeness, clarity, logic, examples, and structure."
            }
        }, {
            "@type": "Question",
            "name": "What security features does QuizAura offer?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "QuizAura includes comprehensive anti-cheat features including tab switching detection, minimize detection, back button prevention, refresh prevention, keyboard shortcut blocking, developer tools detection, and copy-paste prevention."
            }
        }, {
            "@type": "Question",
            "name": "Can I customize the platform for my organization?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Yes! QuizAura offers white-label customization including custom branding, themes, logos, and domain names. You can fully customize the platform to match your organization's identity."
            }
        }, {
            "@type": "Question",
            "name": "What AI models are available?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "QuizAura offers multiple AI models based on your plan. Basic plans include standard AI models, while professional and enterprise plans include advanced AI models with faster processing and higher accuracy."
            }
        }, {
            "@type": "Question",
            "name": "How does the postpone feature work?",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "Students can postpone questions during a quiz. After answering all other questions, postponed questions will reappear for completion, allowing students to manage their time effectively."
            }
        }]
    }
    </script>
    
    <!-- Critical CSS Inline for Performance (FCP) -->
    <style>
        /* Critical above-the-fold styles to prevent NO_FCP */
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; }
        .navbar { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 1rem 0; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1); }
        .hero-section { min-height: 100vh; display: flex; align-items: center; background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); padding: 2rem 0; }
        .hero-title { color: white; font-size: 3rem; font-weight: 700; margin: 0; line-height: 1.2; }
        .hero-subtitle { color: rgba(255, 255, 255, 0.9); font-size: 1.25rem; margin: 1.5rem 0; line-height: 1.6; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 1rem; }
        /* Font display optimization */
        @font-face { font-family: 'Poppins'; font-display: swap; }
        @font-face { font-family: 'Inter'; font-display: swap; }
    </style>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" media="print" onload="this.media='all'">
    <noscript><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous"></noscript>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" crossorigin="anonymous">
    <!-- Google Fonts with font-display swap -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet"></noscript>
    
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --accent-color: #ec4899;
            --dark-bg: #0f172a;
            --dark-card: #1e293b;
            --light-bg: #f8fafc;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --transition: all 0.3s ease;
        }

        [data-theme="dark"] {
            --light-bg: #0f172a;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --dark-card: #1e293b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', 'Inter', sans-serif;
            color: var(--text-primary);
            background-color: var(--light-bg);
            transition: var(--transition);
            overflow-x: hidden;
        }

        /* Navigation Bar */
        .navbar {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            padding: 0.75rem 0;
            transition: var(--transition);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            overflow: visible;
        }

        [data-theme="dark"] .navbar {
            background: rgba(15, 23, 42, 0.7);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .navbar .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            max-width: 100%;
            position: relative;
            padding-left: 1rem;
            padding-right: 1rem;
            overflow: visible;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 700;
            font-size: 1.6rem;
            color: var(--primary-color) !important;
            text-decoration: none;
            flex-shrink: 0;
            margin-right: 1rem;
        }

        .navbar-brand:hover,
        .navbar-brand:focus,
        .navbar-brand:active {
            color: var(--primary-color) !important;
        }

        .navbar-brand img {
            height: 60px;
            width: auto;
            max-width: 140px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            flex-shrink: 0;
        }

        [data-theme="dark"] .navbar-brand img {
            filter: drop-shadow(0 2px 4px rgba(255, 255, 255, 0.1)) brightness(1.1);
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .nav-link {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--text-primary) !important;
            margin: 0 0.5rem;
            padding: 0.5rem 1rem !important;
            transition: var(--transition);
            white-space: nowrap;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
            transform: translateY(-2px);
        }

        .navbar-nav {
            display: flex;
            align-items: center;
            flex-wrap: nowrap;
            margin: 0;
            padding: 0;
            flex: 0 1 auto;
            min-width: 0;
        }

        .navbar-collapse {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            flex-grow: 1;
            width: auto;
            flex-basis: auto;
            gap: 1rem;
        }

        @media (min-width: 992px) {
            .navbar-collapse {
                display: flex !important;
            }
            
            .navbar-nav {
                margin-right: auto;
            }
        }

        /* Theme Toggle Button */
        .theme-toggle {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            flex-shrink: 0;
            margin-right: 0.5rem;
        }

        .theme-toggle:hover {
            transform: scale(1.1) rotate(180deg);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
        }

        /* Hero Section */
        .hero-section {
            min-height: 90vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            position: relative;
            overflow: hidden;
            padding: 100px 0;
        }

        [data-theme="dark"] .hero-section {
            background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #475569 100%);
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            color: white;
        }

        .hero-image-wrapper {
            margin-bottom: 1.5rem;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.95;
            font-weight: 400;
        }

        .btn-hero {
            padding: 15px 35px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            margin: 0.5rem;
            transition: var(--transition);
            border: none;
        }

        .btn-primary-hero {
            background: white;
            color: var(--primary-color);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .btn-primary-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }

        .btn-outline-hero {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-outline-hero:hover {
            background: white;
            color: var(--primary-color);
            transform: translateY(-3px);
        }

        .tagline {
            margin-top: 1rem;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .hero-graphic {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .hero-graphic-placeholder {
            width: 450px;
            height: 450px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            padding: 2.5rem;
            margin: 0 auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3), 
                        0 0 40px rgba(99, 102, 241, 0.2),
                        inset 0 0 30px rgba(255, 255, 255, 0.1);
            animation: smoothFloat 4s ease-in-out infinite;
        }

        .hero-graphic-placeholder img {
            width: 80%;
            height: 80%;
            object-fit: contain;
            filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.4));
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        @keyframes smoothFloat {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        /* Section Styling */
        .section {
            padding: 80px 0;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-subtitle {
            text-align: center;
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 3rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Glass Cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2.5rem;
            height: 100%;
            transition: var(--transition);
            box-shadow: 0 8px 32px rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        [data-theme="dark"] .glass-card {
            background: rgba(30, 41, 59, 0.7);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .glass-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(99, 102, 241, 0.2);
        }

        /* Features Section */
        .feature-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2.5rem;
            height: 100%;
            transition: var(--transition);
            box-shadow: 0 8px 32px rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        [data-theme="dark"] .feature-card {
            background: rgba(30, 41, 59, 0.7);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(99, 102, 241, 0.2);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 2rem;
            color: white;
        }

        .feature-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }

        .feature-description {
            color: var(--text-secondary);
            line-height: 1.7;
        }

        /* Demo Section */
        .demo-section {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        [data-theme="dark"] .demo-section {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        }

        .demo-video-placeholder {
            width: 100%;
            height: 400px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(99, 102, 241, 0.3);
        }

        .play-button {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: var(--primary-color);
            cursor: pointer;
            transition: var(--transition);
            z-index: 2;
        }

        .play-button:hover {
            transform: scale(1.1);
        }

        /* Pricing Section */
        .pricing-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 2.5rem;
            height: 100%;
            transition: var(--transition);
            box-shadow: 0 8px 32px rgba(99, 102, 241, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        [data-theme="dark"] .pricing-card {
            background: rgba(30, 41, 59, 0.7);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .pricing-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(99, 102, 241, 0.3);
            border-color: var(--primary-color);
        }

        .pricing-card.featured {
            border: 2px solid var(--primary-color);
            transform: scale(1.05);
        }

        .pricing-card.featured::before {
            height: 8px;
        }

        .pricing-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .pricing-price {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 1.5rem 0;
        }

        .pricing-features {
            list-style: none;
            padding: 0;
            margin: 2rem 0;
        }

        .pricing-features li {
            padding: 0.8rem 0;
            color: var(--text-secondary);
            border-bottom: 1px solid rgba(99, 102, 241, 0.1);
        }

        .pricing-features li:last-child {
            border-bottom: none;
        }

        .pricing-features li i {
            color: var(--primary-color);
            margin-right: 0.5rem;
        }

        .btn-pricing {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
        }

        .btn-pricing:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
        }

        /* Organization Section */
        .org-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        [data-theme="dark"] .org-section {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        }

        /* Ensure org-section headings are always visible */
        .org-section .section-title {
            color: white !important;
            -webkit-background-clip: unset !important;
            -webkit-text-fill-color: white !important;
            background: none !important;
            background-clip: unset !important;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
        }

        .org-section .section-subtitle,
        .org-section h2,
        .org-section h3,
        .org-section h4,
        .org-section h5,
        .org-section p {
            color: white !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .org-preview {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .before-after {
            display: flex;
            gap: 2rem;
            margin-top: 2rem;
        }

        .before-after-item {
            flex: 1;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: var(--transition);
            cursor: pointer;
        }

        .before-after-item:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-5px);
        }

        .before-after-item.active {
            background: rgba(255, 255, 255, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .before-after-preview {
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1rem;
            border-radius: 10px;
            overflow: hidden;
        }

        .before-preview {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
        }

        .after-preview {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            padding: 2rem;
        }

        /* Color Picker Styles */
        .color-picker-container {
            margin-top: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .color-picker-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .color-picker-wrapper {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .color-option {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            cursor: pointer;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: var(--transition);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .color-option:hover {
            transform: scale(1.1);
            border-color: rgba(255, 255, 255, 0.6);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .color-option.active {
            border-color: white;
            border-width: 3px;
            transform: scale(1.15);
        }

        .custom-color-input {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            cursor: pointer;
            background: transparent;
            padding: 0;
        }

        .custom-color-input::-webkit-color-swatch-wrapper {
            padding: 0;
        }

        .custom-color-input::-webkit-color-swatch {
            border: none;
            border-radius: 6px;
        }

        /* Testimonials Section */
        .testimonial-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2.5rem;
            height: 100%;
            box-shadow: 0 8px 32px rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: var(--transition);
        }

        [data-theme="dark"] .testimonial-card {
            background: rgba(30, 41, 59, 0.7);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(99, 102, 241, 0.2);
        }

        .testimonial-rating {
            color: #fbbf24;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .testimonial-text {
            color: var(--text-secondary);
            font-style: italic;
            margin-bottom: 1.5rem;
            line-height: 1.8;
        }

        .testimonial-author {
            font-weight: 600;
            color: var(--text-primary);
        }

        .testimonial-role {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* FAQ Section */
        .faq-item {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 15px;
            margin-bottom: 1rem;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(99, 102, 241, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: var(--transition);
        }

        [data-theme="dark"] .faq-item {
            background: rgba(30, 41, 59, 0.7);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .faq-question {
            padding: 1.5rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            color: var(--text-primary);
            transition: var(--transition);
        }

        .faq-question:hover {
            background: rgba(99, 102, 241, 0.05);
        }

        .faq-answer {
            padding: 0 1.5rem;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
            color: var(--text-secondary);
            line-height: 1.7;
        }

        .faq-answer.active {
            padding: 1.5rem;
            max-height: 500px;
        }

        .faq-icon {
            transition: transform 0.3s ease;
            color: var(--primary-color);
        }

        .faq-item.active .faq-icon {
            transform: rotate(180deg);
        }

        /* Contact Section */
        .contact-section {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        [data-theme="dark"] .contact-section {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        }

        .contact-form {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 8px 32px rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        [data-theme="dark"] .contact-form {
            background: rgba(30, 41, 59, 0.7);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .form-control {
            padding: 15px;
            border-radius: 10px;
            border: 2px solid rgba(99, 102, 241, 0.2);
            margin-bottom: 1.5rem;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.5);
            color: var(--text-primary);
        }

        .form-control::placeholder {
            color: var(--text-secondary);
            opacity: 0.7;
        }

        [data-theme="dark"] .form-control {
            background: rgba(30, 41, 59, 0.5);
            color: var(--text-primary);
        }

        [data-theme="dark"] .form-control::placeholder {
            color: rgba(203, 213, 225, 0.8);
            opacity: 1;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
            background: rgba(255, 255, 255, 0.8);
            color: var(--text-primary);
        }

        [data-theme="dark"] .form-control:focus {
            background: rgba(30, 41, 59, 0.8);
            color: var(--text-primary);
        }

        [data-theme="dark"] .form-control:focus::placeholder {
            color: rgba(203, 213, 225, 0.6);
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
        }

        .whatsapp-btn {
            background: #25d366;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            transition: var(--transition);
            margin-top: 1rem;
        }

        .whatsapp-btn:hover {
            background: #20ba5a;
            transform: translateY(-3px);
            color: white;
        }

        /* Footer */
        .footer {
            background: var(--dark-bg);
            color: white;
            padding: 4rem 0 2rem;
        }

        .footer-title {
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
        }

        .footer-link {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            display: block;
            margin-bottom: 0.8rem;
            transition: var(--transition);
        }

        .footer-link:hover {
            color: white;
            transform: translateX(5px);
        }

        .social-icons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-icon {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: var(--transition);
        }

        .social-icon:hover {
            background: var(--primary-color);
            transform: translateY(-5px);
            color: white;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 3rem;
            padding-top: 2rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
        }

        /* Navbar Buttons - Sign Up & Login */
        .navbar .d-flex.align-items-center {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-shrink: 0;
            margin-left: auto;
            padding-left: 0.5rem;
        }

        .navbar .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white;
            padding: 8px 18px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .navbar .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }

        .navbar .btn-outline-primary {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 8px 18px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: var(--transition);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .navbar .btn-outline-primary:hover {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-color: transparent;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .pricing-card.featured {
                transform: scale(1);
            }

            .before-after {
                flex-direction: column;
            }

            .nav-link {
                font-size: 1.1rem;
                margin: 0 0.5rem;
                padding: 0.5rem 1rem !important;
            }

            .navbar-brand img {
                height: 55px;
            }

            .navbar-brand {
                font-size: 1.5rem;
            }

            .hero-graphic-placeholder {
                width: 300px;
                height: 300px;
                padding: 2rem;
                box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3), 
                            0 0 30px rgba(99, 102, 241, 0.2),
                            inset 0 0 20px rgba(255, 255, 255, 0.1);
            }

            .navbar .container {
                flex-wrap: wrap;
            }

            .navbar-brand {
                margin-right: auto;
            }

            .navbar-toggler {
                order: 2;
            }

            .navbar-collapse {
                flex-direction: column !important;
                align-items: flex-start !important;
                width: 100%;
                margin-top: 1rem;
                order: 3;
            }

            .navbar-nav {
                position: relative;
                left: auto;
                transform: none;
                width: 100%;
                margin: 1rem 0;
                flex-direction: column;
                align-items: flex-start !important;
            }

            .navbar-collapse {
                margin-left: 0;
                margin-right: 0;
            }

            .navbar .d-flex.align-items-center {
                width: 100%;
                justify-content: flex-end;
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid rgba(0, 0, 0, 0.1);
                margin-left: 0;
            }

            [data-theme="dark"] .navbar .d-flex.align-items-center {
                border-top-color: rgba(255, 255, 255, 0.1);
            }
        }

        /* Smooth Scroll */
        html {
            scroll-behavior: smooth;
        }
        
        /* Skip Link Styles */
        .skip-link:focus {
            left: 0 !important;
            top: 0 !important;
            position: fixed !important;
        }
        
        /* Focus indicators for accessibility */
        *:focus-visible {
            outline: 3px solid var(--primary-color);
            outline-offset: 2px;
        }
        a:focus, button:focus, input:focus, textarea:focus, select:focus {
            outline: 3px solid var(--primary-color);
            outline-offset: 2px;
        }
        
        /* Ensure all interactive elements are keyboard accessible */
        button, a, input, textarea, select, [tabindex] {
            cursor: pointer;
        }
        
        /* Hide decorative elements from screen readers */
        [aria-hidden="true"] {
            pointer-events: none;
        }
        
        /* Visually hidden but accessible to screen readers */
        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0;
        }
        
        /* Prevent layout shift */
        img { max-width: 100%; height: auto; }
        .hero-section { min-height: 100vh; }

        /* Sticky Navbar */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1000;
        }
    </style>
</head>
<body data-theme="light" itemscope itemtype="https://schema.org/WebPage">
    <!-- Skip to Main Content Link (Accessibility) -->
    <a href="#main-content" class="skip-link" style="position: absolute; left: -9999px; z-index: 9999; padding: 1em; background: var(--primary-color); color: white; text-decoration: none; font-weight: bold;">Skip to main content</a>
    
    <!-- Header with Navigation -->
    <header role="banner">
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg" role="navigation" aria-label="Main navigation">
        <div class="container">
            <a class="navbar-brand" href="#home" aria-label="QuizAura Home">
                <img src="assets/images/logo-removebg-preview.png" alt="QuizAura - AI-Powered Quiz and Exam System Logo" width="40" height="40" loading="eager" fetchpriority="high" style="display: block !important; visibility: visible !important; opacity: 1 !important;" />
                <span>QuizAura</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation menu" tabindex="0">
                <span class="navbar-toggler-icon" aria-hidden="true"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav" role="menubar">
                    <li class="nav-item" role="none">
                        <a class="nav-link" href="#home" role="menuitem" tabindex="0">Home</a>
                    </li>
                    <li class="nav-item" role="none">
                        <a class="nav-link" href="#features" role="menuitem" tabindex="0">Features</a>
                    </li>
                    <li class="nav-item" role="none">
                        <a class="nav-link" href="#pricing" role="menuitem" tabindex="0">Pricing</a>
                    </li>
                    <li class="nav-item" role="none">
                        <a class="nav-link" href="#organization" role="menuitem" tabindex="0">Organization</a>
                    </li>
                    <li class="nav-item" role="none">
                        <a class="nav-link" href="#contact" role="menuitem" tabindex="0">Contact</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <button class="theme-toggle" id="themeToggle" title="Toggle Theme" aria-label="Toggle dark and light theme" type="button" tabindex="0" role="button" aria-pressed="false">
                        <i class="bi bi-moon-fill" id="themeIcon" aria-hidden="true"></i>
                    </button>
                    <a href="register.php" class="btn btn-primary" rel="noopener noreferrer" tabindex="0" role="button">Sign Up</a>
                    <a href="login.php" class="btn btn-outline-primary" rel="noopener noreferrer" tabindex="0" role="button">Login</a>
                </div>
            </div>
        </div>
    </nav>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero-section" role="region" aria-labelledby="hero-title">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content" data-aos="fade-right">
                    <div class="hero-image-wrapper">
                        <h1 class="hero-title" id="hero-title">AI-Powered Quiz & Exam System</h1>
                    </div>
                    <p class="hero-subtitle">
                        Revolutionize education with intelligent assessment. Evaluate subjective answers using advanced AI, 
                        prevent cheating with smart monitoring, and scale effortlessly with flexible plans.
                    </p>
                    <div role="group" aria-label="Hero action buttons">
                        <button class="btn btn-hero btn-primary-hero" id="startFreeTrialBtn" aria-label="Start free trial" type="button" tabindex="0">Start Free Trial</button>
                        <button class="btn btn-hero btn-outline-hero" id="bookDemoBtn" aria-label="Book a demo" type="button" tabindex="0">Book a Demo</button>
                    </div>
            
                </div>
                <div class="col-lg-6 hero-graphic" data-aos="fade-left" role="img" aria-label="QuizAura platform visualization">
                    <div class="hero-graphic-placeholder">
                        <img src="assets/images/logo-removebg-preview.png" alt="QuizAura AI-Powered Assessment Platform - Intelligent Quiz and Exam System" width="450" height="450" loading="eager" fetchpriority="high" style="width: 100%; height: 100%; object-fit: contain; filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.3)); display: block !important; visibility: visible !important; opacity: 1 !important;" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Start -->
    <main id="main-content" role="main">
    <!-- Features Section -->
    <section id="features" class="section" role="region" aria-labelledby="features-title">
        <div class="container">
            <h2 class="section-title" id="features-title" data-aos="fade-up">Key Features</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Everything you need for modern, intelligent assessment
            </p>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card glass-card">
                        <div class="feature-icon">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                        <h3 class="feature-title">AI Subjective Evaluation</h3>
                        <p class="feature-description">
                            Advanced AI models evaluate written answers with human-like understanding, 
                            providing detailed feedback and accurate scoring.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card glass-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3 class="feature-title">Anti-Cheating & IP Blocking</h3>
                        <p class="feature-description">
                            Comprehensive anti-cheating measures including IP tracking, 
                            browser monitoring, and real-time suspicious activity detection.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-card glass-card">
                        <div class="feature-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h3 class="feature-title">Smart Plans & User Limits</h3>
                        <p class="feature-description">
                            Flexible subscription plans with customizable teacher and student limits 
                            based on your organization's needs.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-card glass-card">
                        <div class="feature-icon">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                        <h3 class="feature-title">Multiple AI Models by Plan</h3>
                        <p class="feature-description">
                            Access to different AI models based on your subscription tier, 
                            from basic to advanced GPT models for superior evaluation.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="feature-card glass-card">
                        <div class="feature-icon">
                            <i class="bi bi-palette"></i>
                        </div>
                        <h3 class="feature-title">Organization White-Label Branding</h3>
                        <p class="feature-description">
                            Fully customizable branding with your logo, colors, theme, 
                            and domain for a completely personalized experience.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="700">
                    <div class="feature-card glass-card">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <h3 class="feature-title">Real-Time Analytics Dashboard</h3>
                        <p class="feature-description">
                            Comprehensive analytics and insights with real-time monitoring 
                            of student performance, quiz statistics, and AI evaluation metrics.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Demo Section -->
    <section id="demo" class="section demo-section" role="region" aria-labelledby="demo-title">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">See It In Action</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Watch how our platform transforms the assessment experience
            </p>
            <div class="row">
                <div class="col-lg-10 mx-auto" data-aos="zoom-in" data-aos-delay="200">
                    <div class="demo-video-placeholder">
                        <div class="play-button">
                            <i class="bi bi-play-fill"></i>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <button class="btn btn-primary btn-lg">Watch Demo</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="section" role="region" aria-labelledby="pricing-title">
        <div class="container">
            <h2 class="section-title" id="pricing-title" data-aos="fade-up">Pricing Plans</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Choose the perfect plan for your organization
            </p>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="pricing-card glass-card">
                        <h3 class="pricing-title">Starter</h3>
                        <div class="pricing-price">$29<span style="font-size: 1rem;">/mo</span></div>
                        <ul class="pricing-features">
                            <li><i class="bi bi-check-circle-fill"></i> 1 Teacher</li>
                            <li><i class="bi bi-check-circle-fill"></i> 50 Students</li>
                            <li><i class="bi bi-check-circle-fill"></i> Basic AI Model</li>
                            <li><i class="bi bi-check-circle-fill"></i> Standard Support</li>
                            <li><i class="bi bi-check-circle-fill"></i> Basic Analytics</li>
                        </ul>
                        <button class="btn btn-pricing" data-action="choose-plan" aria-label="Choose this plan">Choose Plan</button>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="pricing-card glass-card featured">
                        <div class="badge bg-primary position-absolute top-0 end-0 m-3">Popular</div>
                        <h3 class="pricing-title">Professional</h3>
                        <div class="pricing-price">$99<span style="font-size: 1rem;">/mo</span></div>
                        <ul class="pricing-features">
                            <li><i class="bi bi-check-circle-fill"></i> 5 Teachers</li>
                            <li><i class="bi bi-check-circle-fill"></i> 300 Students</li>
                            <li><i class="bi bi-check-circle-fill"></i> Pro AI Model</li>
                            <li><i class="bi bi-check-circle-fill"></i> Priority Support</li>
                            <li><i class="bi bi-check-circle-fill"></i> Advanced Analytics</li>
                            <li><i class="bi bi-check-circle-fill"></i> IP Blocking</li>
                        </ul>
                        <button class="btn btn-pricing" data-action="choose-plan" aria-label="Choose this plan">Choose Plan</button>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="400">
                    <div class="pricing-card glass-card">
                        <h3 class="pricing-title">Enterprise</h3>
                        <div class="pricing-price">$299<span style="font-size: 1rem;">/mo</span></div>
                        <ul class="pricing-features">
                            <li><i class="bi bi-check-circle-fill"></i> Unlimited Teachers</li>
                            <li><i class="bi bi-check-circle-fill"></i> Unlimited Students</li>
                            <li><i class="bi bi-check-circle-fill"></i> Advanced AI Model</li>
                            <li><i class="bi bi-check-circle-fill"></i> 24/7 Support</li>
                            <li><i class="bi bi-check-circle-fill"></i> Real-Time Analytics</li>
                            <li><i class="bi bi-check-circle-fill"></i> Advanced Anti-Cheat</li>
                            <li><i class="bi bi-check-circle-fill"></i> Fast AI Processing</li>
                        </ul>
                        <button class="btn btn-pricing" data-action="choose-plan" aria-label="Choose this plan">Choose Plan</button>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="500">
                    <div class="pricing-card glass-card">
                        <h3 class="pricing-title">White-Label</h3>
                        <div class="pricing-price">Custom</div>
                        <ul class="pricing-features">
                            <li><i class="bi bi-check-circle-fill"></i> Everything in Enterprise</li>
                            <li><i class="bi bi-check-circle-fill"></i> Custom Branding</li>
                            <li><i class="bi bi-check-circle-fill"></i> Custom Theme & Logo</li>
                            <li><i class="bi bi-check-circle-fill"></i> Custom Domain</li>
                            <li><i class="bi bi-check-circle-fill"></i> Dedicated Support</li>
                            <li><i class="bi bi-check-circle-fill"></i> API Access</li>
                        </ul>
                        <button class="btn btn-pricing">Contact Sales</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Organization Section -->
    <section id="organization" class="section org-section" role="region" aria-labelledby="organization-title">
        <div class="container">
            <h2 class="section-title text-white" id="organization-title" data-aos="fade-up">White-Label Organization</h2>
            <p class="section-subtitle text-white" style="opacity: 0.9;" data-aos="fade-up" data-aos-delay="100">
                Make it yours with complete branding customization
            </p>
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right">
                    <div class="org-preview">
                        <h3 class="mb-4">Customize Everything</h3>
                        <ul style="list-style: none; padding: 0;">
                            <li class="mb-3"><i class="bi bi-check-circle-fill me-2"></i> Upload Your Logo</li>
                            <li class="mb-3"><i class="bi bi-check-circle-fill me-2"></i> Set Brand Colors</li>
                            <li class="mb-3"><i class="bi bi-check-circle-fill me-2"></i> Custom Theme UI</li>
                            <li class="mb-3"><i class="bi bi-check-circle-fill me-2"></i> Custom Domain</li>
                            <li class="mb-3"><i class="bi bi-check-circle-fill me-2"></i> Email Branding</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="before-after">
                        <button type="button" class="before-after-item" id="beforeItem" onclick="toggleBeforeAfter('before')" aria-label="Show before customization" tabindex="0" role="button" aria-pressed="false">
                            <h4 class="mb-3">Before</h4>
                            <div class="before-after-preview before-preview" id="beforePreview">
                                <div>
                                    <h5>Generic Platform</h5>
                                    <p style="opacity: 0.8; margin-top: 1rem;">Standard QuizAura Interface</p>
                                    <p style="opacity: 0.6; font-size: 0.9rem;">Default branding and colors</p>
                                </div>
                            </div>
                        </button>
                        <button type="button" class="before-after-item active" id="afterItem" onclick="toggleBeforeAfter('after')" aria-label="Show after customization" tabindex="0" role="button" aria-pressed="true">
                            <h4 class="mb-3">After</h4>
                            <div class="before-after-preview after-preview" id="afterPreview">
                                <div>
                                    <h5>Your Brand</h5>
                                    <p style="opacity: 0.8; margin-top: 1rem;">Custom Logo & Colors</p>
                                    <p style="opacity: 0.6; font-size: 0.9rem;">Fully branded experience</p>
                                    <div class="color-picker-container">
                                        <div class="color-picker-label">Choose Your Brand Color:</div>
                                        <div class="color-picker-wrapper">
                                            <div class="color-option active" data-color="#6366f1" style="background: #6366f1;" onclick="selectColor('#6366f1', event)"></div>
                                            <div class="color-option" data-color="#8b5cf6" style="background: #8b5cf6;" onclick="selectColor('#8b5cf6', event)"></div>
                                            <div class="color-option" data-color="#ec4899" style="background: #ec4899;" onclick="selectColor('#ec4899', event)"></div>
                                            <div class="color-option" data-color="#10b981" style="background: #10b981;" onclick="selectColor('#10b981', event)"></div>
                                            <div class="color-option" data-color="#f59e0b" style="background: #f59e0b;" onclick="selectColor('#f59e0b', event)"></div>
                                            <div class="color-option" data-color="#ef4444" style="background: #ef4444;" onclick="selectColor('#ef4444', event)"></div>
                                            <input type="color" class="custom-color-input" id="customColorPicker" value="#6366f1" onchange="selectCustomColor(this.value, event)" title="Custom Color">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="section" role="region" aria-labelledby="testimonials-title">
        <div class="container">
            <h2 class="section-title" id="testimonials-title" data-aos="fade-up">What Our Users Say</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Trusted by educators and institutions worldwide
            </p>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="testimonial-card glass-card">
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                            "The AI evaluation is incredibly accurate. It saves us hours of grading time while providing 
                            detailed feedback to students."
                        </p>
                        <div class="testimonial-author">Dr. Sarah Johnson</div>
                        <div class="testimonial-role">Professor, University of Tech</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="testimonial-card glass-card">
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                            "Anti-cheating features give us peace of mind. The IP blocking and monitoring tools are 
                            exactly what we needed."
                        </p>
                        <div class="testimonial-author">Michael Chen</div>
                        <div class="testimonial-role">Director, Online Academy</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="400">
                    <div class="testimonial-card glass-card">
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                            "The white-label option allowed us to maintain our brand identity. Our students don't even 
                            know they're using a third-party platform."
                        </p>
                        <div class="testimonial-author">Emily Rodriguez</div>
                        <div class="testimonial-role">CEO, EduTech Solutions</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="500">
                    <div class="testimonial-card glass-card">
                        <div class="testimonial-rating">
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <p class="testimonial-text">
                            "Scalable plans that grow with us. The analytics dashboard provides insights we never had before."
                        </p>
                        <div class="testimonial-author">James Wilson</div>
                        <div class="testimonial-role">Principal, Global School</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="section" style="padding-bottom: 100px;" role="region" aria-labelledby="faq-title">
        <div class="container">
            <h2 class="section-title" id="faq-title" data-aos="fade-up">Frequently Asked Questions</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Everything you need to know about our platform
            </p>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="faq-item" data-aos="fade-up" data-aos-delay="200">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>How accurate is AI evaluation?</span>
                            <i class="bi bi-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            Our AI models are trained on millions of educational assessments and achieve over 95% accuracy 
                            compared to human evaluators. The system continuously learns and improves from feedback.
                        </div>
                    </div>
                    <div class="faq-item" data-aos="fade-up" data-aos-delay="300">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>How does anti-cheating work?</span>
                            <i class="bi bi-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            We use multiple layers of protection including IP address tracking, browser fingerprinting, 
                            tab switching detection, copy-paste monitoring, and real-time behavior analysis to identify 
                            suspicious activities.
                        </div>
                    </div>
                    <div class="faq-item" data-aos="fade-up" data-aos-delay="400">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Can we bulk import students?</span>
                            <i class="bi bi-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            Yes! You can import students via CSV files or integrate with your existing student information 
                            systems through our API. Bulk operations are available in all plans.
                        </div>
                    </div>
                    <div class="faq-item" data-aos="fade-up" data-aos-delay="500">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Can we customize themes?</span>
                            <i class="bi bi-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            Absolutely! Professional plans and above include theme customization. White-Label plans offer 
                            complete branding control including logo, colors, fonts, and custom domain.
                        </div>
                    </div>
                    <div class="faq-item" data-aos="fade-up" data-aos-delay="600">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>What AI models are available?</span>
                            <i class="bi bi-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            Starter plans use our optimized basic AI model. Professional plans access GPT-4 level models, 
                            and Enterprise plans get the fastest, most advanced models with priority processing.
                        </div>
                    </div>
                    <div class="faq-item" data-aos="fade-up" data-aos-delay="700">
                        <div class="faq-question" onclick="toggleFaq(this)">
                            <span>Is there a free trial?</span>
                            <i class="bi bi-chevron-down faq-icon"></i>
                        </div>
                        <div class="faq-answer">
                            Yes! We offer a 14-day free trial with full access to all features. No credit card required. 
                            You can start using the platform immediately after signup.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section contact-section" role="region" aria-labelledby="contact-title">
        <div class="container">
            <h2 class="section-title" id="contact-title" data-aos="fade-up">Get In Touch</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">
                Have questions? We'd love to hear from you
            </p>
            <div class="row">
                <div class="col-lg-8 mx-auto" data-aos="zoom-in" data-aos-delay="200">
                    <div class="contact-form glass-card">
                        <form id="contactForm" role="form" aria-label="Contact form">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="contact-name" class="visually-hidden">Your Name</label>
                                    <input type="text" id="contact-name" name="name" class="form-control" placeholder="Your Name" required aria-required="true" aria-label="Your Name" tabindex="0">
                                </div>
                                <div class="col-md-6">
                                    <label for="contact-email" class="visually-hidden">Your Email</label>
                                    <input type="email" id="contact-email" name="email" class="form-control" placeholder="Your Email" required aria-required="true" aria-label="Your Email" tabindex="0">
                                </div>
                            </div>
                            <label for="contact-org" class="visually-hidden">Organization Name</label>
                            <input type="text" id="contact-org" name="organization" class="form-control" placeholder="Organization Name" aria-label="Organization Name" tabindex="0">
                            <label for="contact-message" class="visually-hidden">Your Message</label>
                            <textarea id="contact-message" name="message" class="form-control" rows="5" placeholder="Your Message" required aria-required="true" aria-label="Your Message" tabindex="0"></textarea>
                            <div class="text-center">
                                <button type="submit" class="btn btn-submit" aria-label="Submit contact form" tabindex="0">Send Message</button>
                                <div>
                                    <a href="https://wa.me/3109950325" class="whatsapp-btn" target="_blank" rel="noopener noreferrer" aria-label="Chat on WhatsApp" tabindex="0">
                                        <i class="bi bi-whatsapp" aria-hidden="true"></i> Chat on WhatsApp
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h4 class="footer-title">
                        <img src="assets/images/logo-removebg-preview.png" alt="QuizAura Logo - Educational Assessment Platform" style="height: 40px; margin-right: 10px; display: inline-block !important; visibility: visible !important; opacity: 1 !important;" />
                        QuizAura
                    </h4>
                    <p style="color: rgba(255,255,255,0.7); line-height: 1.8;">
                        Revolutionizing education with AI-powered assessment solutions. 
                        Smart, secure, and scalable.
                    </p>
                    <div class="social-icons">
                        <a href="https://www.facebook.com/quizaura" class="social-icon" rel="noopener noreferrer" aria-label="Facebook" tabindex="0"><i class="bi bi-facebook" aria-hidden="true"></i></a>
                        <a href="https://www.twitter.com/quizaura" class="social-icon" rel="noopener noreferrer" aria-label="Twitter" tabindex="0"><i class="bi bi-twitter" aria-hidden="true"></i></a>
                        <a href="https://www.linkedin.com/company/quizaura" class="social-icon" rel="noopener noreferrer" aria-label="LinkedIn" tabindex="0"><i class="bi bi-linkedin" aria-hidden="true"></i></a>
                        <a href="https://www.instagram.com/quizaura" class="social-icon" rel="noopener noreferrer" aria-label="Instagram" tabindex="0"><i class="bi bi-instagram" aria-hidden="true"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="footer-title">Company</h5>
                    <a href="/about" class="footer-link" tabindex="0">About Us</a>
                    <a href="/careers" class="footer-link" tabindex="0">Careers</a>
                    <a href="/blog" class="footer-link" tabindex="0">Blog</a>
                    <a href="/partners" class="footer-link" tabindex="0">Partners</a>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="footer-title">Product</h5>
                    <a href="#features" class="footer-link">Features</a>
                    <a href="#pricing" class="footer-link">Pricing</a>
                    <a href="#organization" class="footer-link">White-Label</a>
                    <a href="#demo" class="footer-link">Demo</a>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="footer-title">Legal</h5>
                    <a href="/privacy" class="footer-link" tabindex="0">Privacy Policy</a>
                    <a href="/terms" class="footer-link" tabindex="0">Terms of Service</a>
                    <a href="/cookies" class="footer-link" tabindex="0">Cookie Policy</a>
                    <a href="/gdpr" class="footer-link" tabindex="0">GDPR</a>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="footer-title">Support</h5>
                    <a href="#contact" class="footer-link" tabindex="0">Contact</a>
                    <a href="#faq" class="footer-link" tabindex="0">FAQ</a>
                    <a href="/help" class="footer-link" tabindex="0">Help Center</a>
                    <a href="/docs" class="footer-link" tabindex="0">Documentation</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 QuizAura. All rights reserved.</p>
            </div>
        </div>
    </footer>

    </main>
    <!-- Main Content End -->

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous" defer></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js" defer></script>
    
    <script defer>
        // Initialize AOS (Animate On Scroll)
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    duration: 1000,
                    once: true,
                    offset: 100
                });
            }
        });

        // Theme Toggle Functionality
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const body = document.body;
        const currentTheme = localStorage.getItem('theme') || 'light';

        // Set initial theme
        body.setAttribute('data-theme', currentTheme);
        updateThemeIcon(currentTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = body.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
            
            // Update org section color if "After" is active
            setTimeout(() => {
                const activeColor = document.querySelector('.color-option.active');
                if (activeColor && document.getElementById('afterItem').classList.contains('active')) {
                    const color = activeColor.getAttribute('data-color');
                    if (color) {
                        updateOrgSectionColor(color);
                    }
                } else if (document.getElementById('afterItem').classList.contains('active')) {
                    // Use custom color if selected
                    const customColor = document.getElementById('customColorPicker').value;
                    if (customColor) {
                        updateOrgSectionColor(customColor);
                    }
                }
            }, 100);
        });

        function updateThemeIcon(theme) {
            if (theme === 'dark') {
                themeIcon.className = 'bi bi-sun-fill';
            } else {
                themeIcon.className = 'bi bi-moon-fill';
            }
        }

        // FAQ Toggle Functionality
        function toggleFaq(element) {
            const faqItem = element.parentElement;
            const faqAnswer = faqItem.querySelector('.faq-answer');
            const isActive = faqItem.classList.contains('active');
            const isExpanded = element.getAttribute('aria-expanded') === 'true';

            // Close all FAQ items
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
                const answer = item.querySelector('.faq-answer');
                if (answer) answer.classList.remove('active');
                const button = item.querySelector('.faq-question');
                if (button) {
                    button.setAttribute('aria-expanded', 'false');
                }
            });

            // Open clicked item if it wasn't active
            if (!isActive) {
                faqItem.classList.add('active');
                faqAnswer.classList.add('active');
                element.setAttribute('aria-expanded', 'true');
                // Focus management for accessibility
                setTimeout(() => {
                    faqAnswer.setAttribute('tabindex', '-1');
                    faqAnswer.focus();
                }, 100);
            } else {
                element.setAttribute('aria-expanded', 'false');
            }
        }

        // Start Free Trial Functionality
        function startFreeTrial() {
            window.location.href = 'register.php';
        }

        // Book a Demo Functionality
        function bookDemo() {
            // Scroll to contact section
            const contactSection = document.getElementById('contact');
            if (contactSection) {
                contactSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                // Focus on the first input after a short delay
                setTimeout(() => {
                    const firstInput = contactSection.querySelector('input[type="text"]');
                    if (firstInput) {
                        firstInput.focus();
                    }
                }, 500);
            }
        }

        // Add event listeners for buttons (replacing onclick handlers)
        document.addEventListener('DOMContentLoaded', function() {
            const startFreeTrialBtn = document.getElementById('startFreeTrialBtn');
            const bookDemoBtn = document.getElementById('bookDemoBtn');
            const watchDemoBtn = document.getElementById('watchDemoBtn');
            
            if (startFreeTrialBtn) {
                startFreeTrialBtn.addEventListener('click', startFreeTrial);
            }
            
            if (bookDemoBtn) {
                bookDemoBtn.addEventListener('click', bookDemo);
            }
            
            // Choose Plan buttons
            document.querySelectorAll('[data-action="choose-plan"]').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    choosePlan();
                });
            });
            
            // Watch Demo button
            if (watchDemoBtn) {
                watchDemoBtn.addEventListener('click', function() {
                    const playButton = document.querySelector('.play-button');
                    if (playButton) {
                        playButton.click();
                    }
                });
            }
            
            // Play Button Click Handler
            const playButton = document.querySelector('.play-button');
            if (playButton) {
                playButton.addEventListener('click', function() {
                    alert('Demo video would play here. In production, this would open a video modal or navigate to a video page.');
                });
                // Add keyboard support for accessibility
                playButton.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        playButton.click();
                    }
                });
            }
        });

        // Choose Plan Functionality
        function choosePlan() {
            window.location.href = 'register.php';
        }

        // Color Selection Functionality for White Label Organization
        function selectColor(color, event) {
            if (event) {
                event.stopPropagation();
            }
            
            // Update active state
            document.querySelectorAll('.color-option').forEach(option => {
                option.classList.remove('active');
            });
            if (event && event.target.closest('.color-option')) {
                event.target.closest('.color-option').classList.add('active');
            }
            
            // Update custom color picker
            document.getElementById('customColorPicker').value = color;
            
            // Update org-section background
            updateOrgSectionColor(color);
            
            // Store selected color
            localStorage.setItem('selectedOrgColor', color);
        }

        function selectCustomColor(color, event) {
            if (event) {
                event.stopPropagation();
            }
            
            // Remove active from all color options
            document.querySelectorAll('.color-option').forEach(option => {
                option.classList.remove('active');
            });
            
            // Update org-section background
            updateOrgSectionColor(color);
            
            // Store custom color
            localStorage.setItem('selectedOrgColor', color);
        }

        function updateOrgSectionColor(color) {
            const orgSection = document.getElementById('organization');
            if (!orgSection) return;
            
            // Convert hex to RGB for gradient
            const hex = color.replace('#', '');
            const r = parseInt(hex.substr(0, 2), 16);
            const g = parseInt(hex.substr(2, 2), 16);
            const b = parseInt(hex.substr(4, 2), 16);
            
            // Calculate brightness to determine if color is light or dark
            const brightness = (r * 299 + g * 587 + b * 114) / 1000;
            const isLightColor = brightness > 128;
            
            // Create gradient with selected color
            const isDark = document.body.getAttribute('data-theme') === 'dark';
            
            if (isDark) {
                // Dark mode gradient
                const darker1 = `rgb(${Math.max(0, r - 40)}, ${Math.max(0, g - 40)}, ${Math.max(0, b - 40)})`;
                const darker2 = `rgb(${Math.max(0, r - 60)}, ${Math.max(0, g - 60)}, ${Math.max(0, b - 60)})`;
                orgSection.style.background = `linear-gradient(135deg, ${darker1} 0%, ${darker2} 100%)`;
                // In dark mode, always use white text
                orgSection.style.color = 'white';
            } else {
                // Light mode gradient
                const lighter1 = `rgb(${Math.min(255, r + 30)}, ${Math.min(255, g + 30)}, ${Math.min(255, b + 30)})`;
                orgSection.style.background = `linear-gradient(135deg, ${color} 0%, ${lighter1} 100%)`;
                // In light mode, use dark text for light colors, white for dark colors
                if (isLightColor) {
                    orgSection.style.color = '#1e293b';
                    // Update text colors
                    orgSection.querySelectorAll('.section-title, .section-subtitle, h2, h3, h4, h5, p').forEach(el => {
                        el.style.color = '#1e293b';
                        el.style.textShadow = '0 1px 2px rgba(255, 255, 255, 0.5)';
                    });
                } else {
                    orgSection.style.color = 'white';
                    // Update text colors
                    orgSection.querySelectorAll('.section-title, .section-subtitle, h2, h3, h4, h5, p').forEach(el => {
                        el.style.color = 'white';
                        el.style.textShadow = '0 2px 4px rgba(0, 0, 0, 0.3)';
                    });
                }
            }
        }

        // Before/After Toggle Functionality
        function toggleBeforeAfter(type) {
            const beforeItem = document.getElementById('beforeItem');
            const afterItem = document.getElementById('afterItem');
            
            if (type === 'before') {
                beforeItem.classList.add('active');
                afterItem.classList.remove('active');
                // Reset to default colors
                const orgSection = document.getElementById('organization');
                if (orgSection) {
                    const isDark = document.body.getAttribute('data-theme') === 'dark';
                    if (isDark) {
                        orgSection.style.background = 'linear-gradient(135deg, #1e293b 0%, #334155 100%)';
                    } else {
                        orgSection.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                    }
                    // Reset text colors to white
                    orgSection.style.color = 'white';
                    orgSection.querySelectorAll('.section-title, .section-subtitle, h2, h3, h4, h5, p').forEach(el => {
                        el.style.color = 'white';
                        el.style.textShadow = '0 2px 4px rgba(0, 0, 0, 0.3)';
                    });
                }
            } else {
                afterItem.classList.add('active');
                beforeItem.classList.remove('active');
                // Restore selected color if any
                const savedColor = localStorage.getItem('selectedOrgColor');
                const activeColor = document.querySelector('.color-option.active');
                
                if (savedColor) {
                    // Use saved color
                    const matchingOption = document.querySelector(`.color-option[data-color="${savedColor}"]`);
                    if (matchingOption) {
                        matchingOption.classList.add('active');
                        document.querySelectorAll('.color-option').forEach(opt => {
                            if (opt !== matchingOption) opt.classList.remove('active');
                        });
                    }
                    document.getElementById('customColorPicker').value = savedColor;
                    updateOrgSectionColor(savedColor);
                } else if (activeColor) {
                    const color = activeColor.getAttribute('data-color');
                    if (color) {
                        updateOrgSectionColor(color);
                    }
                } else {
                    // Use default purple and set it as active
                    const defaultOption = document.querySelector('.color-option[data-color="#6366f1"]');
                    if (defaultOption) {
                        defaultOption.classList.add('active');
                    }
                    document.getElementById('customColorPicker').value = '#6366f1';
                    updateOrgSectionColor('#6366f1');
                }
            }
        }


        // Smooth Scrolling for Navigation Links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Contact Form Submission
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for your message! We will get back to you soon.');
            this.reset();
        });

        // Navbar Background on Scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.15)';
            } else {
                navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            }
        });

        // Play Button Click Handler (moved to DOMContentLoaded)
        // This is now handled in the DOMContentLoaded event listener above

        // Initialize color on page load if "After" is active
        window.addEventListener('load', function() {
            const savedColor = localStorage.getItem('selectedOrgColor');
            if (savedColor && document.getElementById('afterItem').classList.contains('active')) {
                // Find matching color option
                const matchingOption = document.querySelector(`.color-option[data-color="${savedColor}"]`);
                if (matchingOption) {
                    matchingOption.classList.add('active');
                    document.querySelectorAll('.color-option').forEach(opt => {
                        if (opt !== matchingOption) opt.classList.remove('active');
                    });
                }
                document.getElementById('customColorPicker').value = savedColor;
                updateOrgSectionColor(savedColor);
            }
        });
        // Close mobile navbar when nav link is clicked
        document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
            link.addEventListener('click', function() {
                const navbarCollapse = document.getElementById('navbarNav');
                if (navbarCollapse && window.innerWidth < 992) {
                    const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    } else {
                        navbarCollapse.classList.remove('show');
                    }
                }
            });
        });

        // Close mobile navbar when buttons are clicked
        document.querySelectorAll('.navbar .btn-primary, .navbar .btn-outline-primary, .navbar .theme-toggle').forEach(btn => {
            btn.addEventListener('click', function() {
                const navbarCollapse = document.getElementById('navbarNav');
                if (navbarCollapse && window.innerWidth < 992) {
                    setTimeout(() => {
                        const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                        if (bsCollapse) {
                            bsCollapse.hide();
                        } else {
                            navbarCollapse.classList.remove('show');
                        }
                    }, 100);
                }
            });
        });
    </script>
</body>
</html>

