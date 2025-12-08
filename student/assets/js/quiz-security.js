/**
 * Quiz Security & Anti-Cheat Script
 * Clean, robust implementation with safe console handling
 */

(function() {
    'use strict';

    // ============================================
    // SAFE CONSOLE IMPLEMENTATION (MUST BE FIRST)
    // ============================================
    // CRITICAL: Create safe no-op console that NEVER crashes JavaScript
    (function() {
        try {
            // Store original console before tampering
            if (window.console && typeof window.console === 'object') {
                window._originalConsole = window.console;
            }
            
            // Create comprehensive safe console with ALL methods as no-ops
            const safeConsole = {
                log: function() { return; },
                error: function() { return; },
                warn: function() { return; },
                info: function() { return; },
                debug: function() { return; },
                trace: function() { return; },
                table: function() { return; },
                group: function() { return; },
                groupEnd: function() { return; },
                groupCollapsed: function() { return; },
                time: function() { return; },
                timeEnd: function() { return; },
                timeLog: function() { return; },
                clear: function() { return; },
                count: function() { return; },
                countReset: function() { return; },
                dir: function() { return; },
                dirxml: function() { return; },
                assert: function() { return; },
                profile: function() { return; },
                profileEnd: function() { return; },
                timeStamp: function() { return; },
                context: function() { return; }
            };
            
            // Ensure all methods are functions
            Object.keys(safeConsole).forEach(function(key) {
                if (typeof safeConsole[key] !== 'function') {
                    safeConsole[key] = function() { return; };
                }
            });
            
            // Freeze and replace console safely
            Object.freeze(safeConsole);
            
            try {
                Object.defineProperty(window, 'console', {
                    value: safeConsole,
                    writable: false,
                    configurable: false,
                    enumerable: true
                });
            } catch (defineError) {
                try {
                    window.console = safeConsole;
                } catch (assignError) {
                    // Fallback: ensure console methods exist
                    if (!window.console) {
                        window.console = {};
                    }
                    Object.keys(safeConsole).forEach(function(key) {
                        if (typeof window.console[key] !== 'function') {
                            window.console[key] = safeConsole[key];
                        }
                    });
                }
            }
        } catch (e) {
            // CRITICAL: Never let console tampering break the page
            if (!window.console || typeof window.console !== 'object') {
                window.console = {};
            }
            ['log', 'error', 'warn', 'info', 'debug'].forEach(function(method) {
                if (typeof window.console[method] !== 'function') {
                    window.console[method] = function() { return; };
                }
            });
        }
    })();

    // ============================================
    // SECURITY STATE
    // ============================================
    let hasSubmitted = false;
    let securityActive = false;
    let pageLoadTime = Date.now();
    const GRACE_PERIOD = 2000; // 2 seconds grace period
    const REFRESH_GRACE_PERIOD = 15000; // 15 seconds for refresh
    let validationInterval = null;
    const VALIDATION_INTERVAL = 30000; // 30 seconds
    
    // Warning counters for minimize and tab switch
    let minimizeWarningCount = 0;
    let tabSwitchWarningCount = 0;
    
    // Check if security should be active
    function isSecurityReady() {
        const quizStarted = sessionStorage.getItem('quizStarted') === 'true';
        if (!quizStarted) return false;
        
        const timeSinceLoad = Date.now() - pageLoadTime;
        if (timeSinceLoad < GRACE_PERIOD) return false;
        
        return securityActive;
    }
    
    // Activate security
    function activateSecurity() {
        if (!securityActive) {
            const quizStarted = sessionStorage.getItem('quizStarted') === 'true';
            const timeSinceLoad = Date.now() - pageLoadTime;
            if (quizStarted && timeSinceLoad >= GRACE_PERIOD) {
                securityActive = true;
                startPeriodicValidation();
            }
        }
    }
    
    // Initialize security after grace period
    setTimeout(activateSecurity, GRACE_PERIOD);
    
    // Periodic check for security activation
    let securityCheckInterval = setInterval(function() {
        if (hasSubmitted) {
            clearInterval(securityCheckInterval);
            return;
        }
        activateSecurity();
        if (securityActive) {
            clearInterval(securityCheckInterval);
        }
    }, 200);

    // ============================================
    // TAB SWITCH DETECTION (Warning first, submit on 2nd)
    // ============================================
    document.addEventListener('visibilitychange', function() {
        if (!isSecurityReady() || hasSubmitted) return;
        if (document.hidden) {
            tabSwitchWarningCount++;
            if (tabSwitchWarningCount === 1) {
                // First time: Show warning only
                alert('Warning: You switched tabs. If you do this again, your examination will be automatically submitted.');
            } else if (tabSwitchWarningCount >= 2) {
                // Second time: Submit quiz
                validateWithServerAndSubmit('tab_switch', 'You switched tabs again. The examination has been automatically submitted.');
            }
        }
    }, true);
    
    // ============================================
    // WINDOW MINIMIZE DETECTION (Warning first, submit on 2nd)
    // ============================================
    let windowBlurTime = null;
    window.addEventListener('blur', function() {
        if (!isSecurityReady() || hasSubmitted) return;
        windowBlurTime = Date.now();
    }, true);
    
    window.addEventListener('focus', function() {
        if (!isSecurityReady() || hasSubmitted || !windowBlurTime) return;
        
        // Check if window was minimized (blurred for more than 1 second)
        const blurDuration = Date.now() - windowBlurTime;
        if (blurDuration > 1000) {
            minimizeWarningCount++;
            if (minimizeWarningCount === 1) {
                // First time: Show warning only
                alert('Warning: You minimized the window. If you do this again, your examination will be automatically submitted.');
            } else if (minimizeWarningCount >= 2) {
                // Second time: Submit quiz
                validateWithServerAndSubmit('window_minimize', 'You minimized the window again. The examination has been automatically submitted.');
            }
        }
        windowBlurTime = null;
    }, true);

    // ============================================
    // BACK BUTTON PREVENTION (Block only, no submit, no warning)
    // ============================================
    (function() {
        history.replaceState(null, null, window.location.href);
        history.pushState({page: 'quiz', preventBack: true}, null, window.location.href);
        history.pushState({page: 'quiz', preventBack: true}, null, window.location.href);
    })();
    
    window.addEventListener('popstate', function(event) {
        if (!isSecurityReady() || hasSubmitted) {
            history.replaceState({page: 'quiz', preventBack: true}, null, window.location.href);
            history.pushState({page: 'quiz', preventBack: true}, null, window.location.href);
            history.pushState({page: 'quiz', preventBack: true}, null, window.location.href);
            return;
        }
        
        // Block back button - silent block, no warning
        history.replaceState({page: 'quiz', preventBack: true}, null, window.location.href);
        history.pushState({page: 'quiz', preventBack: true}, null, window.location.href);
        history.pushState({page: 'quiz', preventBack: true}, null, window.location.href);
    }, true);

    // ============================================
    // KEYBOARD SHORTCUTS DISABLING (Block only, no submit, no warning)
    // ============================================
    document.addEventListener('keydown', function(e) {
        if (!isSecurityReady()) return;
        
        // Block F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C (Inspect/DevTools) - silent block
        if (e.key === 'F12' || 
            ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C'))) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        
        // Block Ctrl+R / F5 (refresh) - but allow during grace period - silent block
        if (((e.ctrlKey || e.metaKey) && e.key === 'r') || e.key === 'F5') {
            const quizStartTime = parseInt(sessionStorage.getItem('quizStartTime') || '0');
            const timeSinceStart = Date.now() - quizStartTime;
            if (timeSinceStart < REFRESH_GRACE_PERIOD) {
                return; // Allow refresh during grace period
            }
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        
        // Block all other common shortcuts (Ctrl+W, Ctrl+N, Alt+F4, etc.) - silent block
        if ((e.ctrlKey || e.metaKey) && (e.key === 'w' || e.key === 'n' || e.key === 't' || e.key === 'Tab')) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        
        if (e.altKey && e.key === 'F4') {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }, true);

    // ============================================
    // RIGHT-CLICK PREVENTION (Block only, no submit, no warning)
    // ============================================
    document.addEventListener('contextmenu', function(e) {
        // ALWAYS allow right-click on interactive elements
        if (e.target.matches('input, textarea, button, .option-label, label, .btn') ||
            e.target.closest('button, .option-label, label, .btn, .quiz-actions')) {
            return;
        }
        
        if (!isSecurityReady()) return;
        
        // Block right click - silent block, no warning
        e.preventDefault();
        e.stopPropagation();
        return false;
    }, false);

    // ============================================
    // DEVELOPER TOOLS DETECTION (Block only, no submit, no warning)
    // ============================================
    let devtoolsDetectionCount = 0;
    const DEVMTOOLS_THRESHOLD = 2;
    let devtoolsCheckInterval = setInterval(function() {
        if (!isSecurityReady() || hasSubmitted) {
            if (hasSubmitted && devtoolsCheckInterval) {
                clearInterval(devtoolsCheckInterval);
            }
            return;
        }
        
        const heightDiff = window.outerHeight - window.innerHeight;
        const widthDiff = window.outerWidth - window.innerWidth;
        const threshold = 160;
        
        let devtoolsOpen = false;
        if (heightDiff > threshold || widthDiff > threshold) {
            devtoolsOpen = true;
        }
        
        if (devtoolsOpen) {
            devtoolsDetectionCount++;
            if (devtoolsDetectionCount >= DEVMTOOLS_THRESHOLD) {
                // Block devtools - silent block, no warning
                devtoolsDetectionCount = 0; // Reset counter
            }
        } else {
            devtoolsDetectionCount = 0;
        }
    }, 500);

    // ============================================
    // COPY/PASTE DISABLING
    // ============================================
    document.addEventListener('copy', function(e) {
        if (!isSecurityReady()) return;
        if (e.target.matches('input[type="text"], textarea')) return;
        e.preventDefault();
        e.stopPropagation();
        return false;
    }, true);

    document.addEventListener('paste', function(e) {
        if (!isSecurityReady()) return;
        if (e.target.matches('input[type="text"], textarea')) return;
        e.preventDefault();
        e.stopPropagation();
        return false;
    }, true);
    
    // ============================================
    // TEXT SELECTION DISABLING (Mouse selection)
    // ============================================
    document.addEventListener('selectstart', function(e) {
        if (!isSecurityReady()) return;
        // Allow selection in input fields and textareas
        if (e.target.matches('input, textarea')) return;
        e.preventDefault();
        e.stopPropagation();
        return false;
    }, true);
    
    document.addEventListener('mousedown', function(e) {
        if (!isSecurityReady()) return;
        // Allow mouse down on interactive elements
        if (e.target.matches('input, textarea, button, .option-label, label, .btn, .option-radio')) return;
        // Prevent text selection drag
        if (e.detail > 1) {
            e.preventDefault();
            return false;
        }
    }, true);
    
    // Disable text selection via CSS when security is active
    function disableTextSelection() {
        if (!isSecurityReady()) return;
        const style = document.createElement('style');
        style.id = 'quiz-security-no-select';
        style.textContent = `
            .quiz-container * {
                -webkit-user-select: none !important;
                -moz-user-select: none !important;
                -ms-user-select: none !important;
                user-select: none !important;
            }
            .quiz-container input,
            .quiz-container textarea,
            .quiz-container .option-label,
            .quiz-container .option-text {
                -webkit-user-select: text !important;
                -moz-user-select: text !important;
                -ms-user-select: text !important;
                user-select: text !important;
            }
        `;
        if (!document.getElementById('quiz-security-no-select')) {
            document.head.appendChild(style);
        }
    }
    
    // Apply text selection disable after security is active
    setTimeout(function() {
        if (isSecurityReady()) {
            disableTextSelection();
        }
    }, GRACE_PERIOD);
    
    // Re-check periodically
    let selectionCheckInterval = setInterval(function() {
        if (hasSubmitted) {
            clearInterval(selectionCheckInterval);
            return;
        }
        if (isSecurityReady()) {
            disableTextSelection();
        }
    }, 500);

    // ============================================
    // PERIODIC VALIDATION
    // ============================================
    function startPeriodicValidation() {
        if (validationInterval || hasSubmitted) return;
        
        validateSessionWithServer();
        validationInterval = setInterval(function() {
            if (hasSubmitted) {
                clearInterval(validationInterval);
                validationInterval = null;
                return;
            }
            validateSessionWithServer();
        }, VALIDATION_INTERVAL);
    }
    
    async function validateSessionWithServer() {
        if (hasSubmitted) return;
        
        let submissionId = sessionStorage.getItem('submissionId');
        if (typeof window.quizData !== 'undefined' && window.quizData && window.quizData.submissionId) {
            submissionId = window.quizData.submissionId;
        }
        
        if (!submissionId) return;
        
        try {
            let csrfToken = null;
            if (typeof window.quizData !== 'undefined' && window.quizData && window.quizData.csrfToken) {
                csrfToken = window.quizData.csrfToken;
            }
            
            let validateUrl = '../../api/student/quiz/validate-session.php';
            if (window.location.pathname.includes('/student/quizzes/')) {
                validateUrl = '../../api/student/quiz/validate-session.php';
            } else if (window.location.pathname.includes('/quizzes/')) {
                validateUrl = '../../../api/student/quiz/validate-session.php';
            }
            
            const response = await fetch(validateUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    submission_id: parseInt(submissionId),
                    csrf_token: csrfToken
                })
            });
            
            const data = await response.json();
            
            if (!data.success || !data.valid) {
                if (data.data && data.data.time_expired) {
                    validateWithServerAndSubmit('time_expired', 'Time has expired. The examination has been automatically submitted.');
                } else {
                    validateWithServerAndSubmit('session_invalid', 'Session expired. The examination has been automatically submitted.');
                }
            }
        } catch (error) {
            // Fail silently
        }
    }
    
    // ============================================
    // SECURITY VIOLATION HANDLING
    // ============================================
    async function validateWithServerAndSubmit(reason, message) {
        if (hasSubmitted) return;
        
        let submissionId = sessionStorage.getItem('submissionId');
        if (typeof window.quizData !== 'undefined' && window.quizData && window.quizData.submissionId) {
            submissionId = window.quizData.submissionId;
        }
        
        if (submissionId) {
            try {
                let csrfToken = null;
                if (typeof window.quizData !== 'undefined' && window.quizData && window.quizData.csrfToken) {
                    csrfToken = window.quizData.csrfToken;
                }
                
                let validateUrl = '../../api/student/quiz/validate-session.php';
                if (window.location.pathname.includes('/student/quizzes/')) {
                    validateUrl = '../../api/student/quiz/validate-session.php';
                } else if (window.location.pathname.includes('/quizzes/')) {
                    validateUrl = '../../../api/student/quiz/validate-session.php';
                }
                
                const validateResponse = await fetch(validateUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        submission_id: parseInt(submissionId),
                        csrf_token: csrfToken
                    })
                });
                
                const validateData = await validateResponse.json();
                
                if (!validateData.valid || (validateData.data && validateData.data.time_expired)) {
                    if (validateData.data && validateData.data.time_expired) {
                        reason = 'time_expired';
                        message = 'Time has expired. The examination has been automatically submitted.';
                    }
                }
            } catch (error) {
                // Proceed with submission anyway
            }
        }
        
        handleSecurityViolation(reason, message);
    }
    
    async function handleSecurityViolation(reason, message) {
        if (hasSubmitted) return;
        
        hasSubmitted = true;
        
        if (typeof window.saveCurrentAnswer === 'function') {
            try {
                window.saveCurrentAnswer();
            } catch (err) {
                // Ignore
            }
        }
        
        if (typeof window.timerInterval !== 'undefined' && window.timerInterval) {
            clearInterval(window.timerInterval);
        }
        
        if (validationInterval) {
            clearInterval(validationInterval);
            validationInterval = null;
        }
        
        const urlParams = new URLSearchParams(window.location.search);
        let quizId = urlParams.get('id') || urlParams.get('quiz_id') || '1';
        let submissionId = sessionStorage.getItem('submissionId');
        
        if (typeof window.quizData !== 'undefined' && window.quizData) {
            if (window.quizData.id) quizId = window.quizData.id;
            if (window.quizData.submissionId) submissionId = window.quizData.submissionId;
        }
        
        let answers = {};
        if (typeof window.quizData !== 'undefined' && window.quizData) {
            answers = Object.assign({}, window.quizData.submitted || {}, window.quizData.answers || {});
        }
        
        sessionStorage.setItem('quizSubmitted', 'true');
        sessionStorage.setItem('submitReason', reason);
        sessionStorage.setItem('submitMessage', message);
        sessionStorage.setItem('quizAnswers', JSON.stringify(answers));
        
        const submitUrl = 'submit_quiz.php?quiz_id=' + quizId + '&auto_submit=1&reason=' + reason;
        
        alert(message);
        
        try {
            let finalizeUrl = '../../api/student/quiz/finalize.php';
            let submitApiUrl = '../../api/student/submit_quiz.php';
            if (window.location.pathname.includes('/student/quizzes/')) {
                finalizeUrl = '../../api/student/quiz/finalize.php';
                submitApiUrl = '../../api/student/submit_quiz.php';
            } else if (window.location.pathname.includes('/quizzes/')) {
                finalizeUrl = '../../../api/student/quiz/finalize.php';
                submitApiUrl = '../../../api/student/submit_quiz.php';
            }
            
            if (submissionId) {
                try {
                    await fetch(finalizeUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ submission_id: parseInt(submissionId) })
                    });
                } catch (err) {
                    // Ignore
                }
            }
            
            try {
                await fetch(submitApiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        quiz_id: parseInt(quizId),
                        submission_id: submissionId ? parseInt(submissionId) : null,
                        answers: answers,
                        questions: (typeof window.quizData !== 'undefined' && window.quizData.questions) ? window.quizData.questions : [],
                        ai_provider: (typeof window.quizData !== 'undefined' && window.quizData.aiProvider) ? window.quizData.aiProvider : 'gemini',
                        ai_model: (typeof window.quizData !== 'undefined' && window.quizData.aiModel) ? window.quizData.aiModel : null,
                        auto_submit: true,
                        reason: reason
                    })
                });
            } catch (err) {
                // Ignore
            }
        } catch (error) {
            // Ignore
        }
        
        window.location.replace(submitUrl);
        
        setTimeout(function() {
            if (window.location.pathname.indexOf('submit_quiz.php') === -1) {
                window.location.replace(submitUrl);
            }
        }, 1000);
    }

})();
