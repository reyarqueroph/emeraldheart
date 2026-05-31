/* ============================================
   DARK/LIGHT MODE THEME TOGGLE
   ============================================ */

(function() {
    'use strict';

    // Get saved theme from localStorage or default to 'light'
    const savedTheme = localStorage.getItem('eheart-theme') || 'light';
    
    // Apply theme immediately to prevent flash
    document.documentElement.setAttribute('data-theme', savedTheme);

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        initThemeToggle();
    });

    function initThemeToggle() {
        // Create theme toggle button
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'theme-toggle-btn';
        toggleBtn.setAttribute('aria-label', 'Toggle dark/light mode');
        toggleBtn.setAttribute('title', 'Toggle theme');
        toggleBtn.innerHTML = `
            <i class="fas fa-sun theme-icon theme-icon-sun"></i>
            <i class="fas fa-moon theme-icon theme-icon-moon"></i>
        `;

        // Add button to page
        document.body.appendChild(toggleBtn);

        // Add click event
        toggleBtn.addEventListener('click', toggleTheme);

        // Update button state
        updateThemeButton();
    }

    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        // Apply new theme
        document.documentElement.setAttribute('data-theme', newTheme);
        
        // Save to localStorage
        localStorage.setItem('eheart-theme', newTheme);
        
        // Update button
        updateThemeButton();
        
        // Show toast notification
        if (typeof showToast === 'function') {
            const message = newTheme === 'dark' 
                ? '🌙 Dark mode enabled' 
                : '☀️ Light mode enabled';
            showToast(message, 'success');
        }

        // Trigger custom event for other components
        window.dispatchEvent(new CustomEvent('themeChanged', { 
            detail: { theme: newTheme } 
        }));
    }

    function updateThemeButton() {
        const theme = document.documentElement.getAttribute('data-theme');
        const btn = document.querySelector('.theme-toggle-btn');
        
        if (btn) {
            if (theme === 'dark') {
                btn.setAttribute('title', 'Switch to light mode');
                btn.setAttribute('aria-label', 'Switch to light mode');
            } else {
                btn.setAttribute('title', 'Switch to dark mode');
                btn.setAttribute('aria-label', 'Switch to dark mode');
            }
        }
    }

    // Expose functions globally if needed
    window.themeToggle = {
        toggle: toggleTheme,
        setTheme: function(theme) {
            if (theme === 'dark' || theme === 'light') {
                document.documentElement.setAttribute('data-theme', theme);
                localStorage.setItem('eheart-theme', theme);
                updateThemeButton();
            }
        },
        getTheme: function() {
            return document.documentElement.getAttribute('data-theme');
        }
    };

})();
