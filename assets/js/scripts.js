/* ============================================
   PRU LIFE U.K. - Global Scripts
   ============================================ */

// ── Toast Notifications ──────────────────────
function showToast(message, type = 'info', title = null) {
    let stack = document.querySelector('.toast-stack');
    if (!stack) {
        stack = document.createElement('div');
        stack.className = 'toast-stack';
        document.body.appendChild(stack);
    }

    const icons = { success: 'fa-check-circle', error: 'fa-times-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle' };
    const titles = { success: 'Success', error: 'Error', warning: 'Warning', info: 'Info' };

    const toast = document.createElement('div');
    toast.className = `pru-toast ${type}`;
    toast.innerHTML = `
        <i class="fas ${icons[type] || icons.info} toast-icon"></i>
        <div class="toast-content">
            <div class="toast-title">${title || titles[type] || 'Notice'}</div>
            <div class="toast-msg">${message}</div>
        </div>
        <button class="toast-close" onclick="this.closest('.pru-toast').remove()">
            <i class="fas fa-times"></i>
        </button>`;

    stack.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ── Sidebar Toggle (Admin) ───────────────────
function initAdminSidebar() {
    const hamburger = document.getElementById('sidebarToggle');
    const collapseBtn = document.getElementById('sidebarCollapseBtn');
    const sidebar = document.querySelector('.pru-sidebar');
    let overlay = document.querySelector('.sidebar-overlay');

    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }

    // Mobile hamburger — show/hide sidebar
    if (hamburger && sidebar) {
        hamburger.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        });
    }

    overlay.addEventListener('click', () => {
        if (sidebar) sidebar.classList.remove('open');
        overlay.classList.remove('show');
    });

    // Desktop collapse button — collapse to icon-only
    if (collapseBtn && sidebar) {
        // Restore saved state
        const saved = localStorage.getItem('sidebarCollapsed');
        if (saved === 'true') applySidebarCollapse(true, sidebar);

        collapseBtn.addEventListener('click', () => {
            const isCollapsed = sidebar.classList.contains('collapsed');
            applySidebarCollapse(!isCollapsed, sidebar);
            localStorage.setItem('sidebarCollapsed', !isCollapsed);
        });
    }
}

function applySidebarCollapse(collapse, sidebar) {
    const icon = document.getElementById('collapseIcon');
    const topbar = document.querySelector('.pru-topbar');
    const main = document.querySelector('.pru-main');

    sidebar.classList.toggle('collapsed', collapse);
    document.body.classList.toggle('sidebar-collapsed', collapse);

    if (icon) {
        icon.className = collapse ? 'fas fa-indent' : 'fas fa-outdent';
    }
}

// ── Agent Nav Toggle (mobile) ────────────────
function initAgentNav() {
    const toggle = document.getElementById('navToggle');
    const links = document.querySelector('.agent-topnav .nav-links');
    if (toggle && links) {
        toggle.addEventListener('click', () => links.classList.toggle('open'));
    }
}

// ── Logout ───────────────────────────────────
function initLogout() {
    document.querySelectorAll('[data-logout]').forEach(el => {
        el.addEventListener('click', async (e) => {
            e.preventDefault();
            try { await fetch('../api/auth/logout.php', { method: 'POST' }); } catch (_) { }
            const role = el.dataset.role;
            window.location.href = role === 'admin' ? '../admin/login.php' : '../agent/login.php';
        });
    });
}

// ── Modal helpers ────────────────────────────
function openModal(id) {
    const m = document.getElementById(id);
    if (m) { m.classList.add('show'); document.body.style.overflow = 'hidden'; }
}

function closeModal(id) {
    const m = document.getElementById(id);
    if (m) { m.classList.remove('show'); document.body.style.overflow = ''; }
}

// Close modal on overlay click
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('show');
        document.body.style.overflow = '';
    }
});

// ── Password Strength ────────────────────────
function checkPasswordStrength(pw) {
    let score = 0;
    if (pw.length >= 8) score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;
    if (score <= 1) return 'weak';
    if (score <= 2) return 'medium';
    return 'strong';
}

function attachPasswordStrength(inputId, barId, hintId) {
    const input = document.getElementById(inputId);
    const bar = document.getElementById(barId);
    const hint = document.getElementById(hintId);
    if (!input || !bar) return;

    input.addEventListener('input', () => {
        const strength = checkPasswordStrength(input.value);
        const colors = { weak: '#dc3545', medium: '#ffc107', strong: '#28a745' };
        const widths = { weak: '33%', medium: '66%', strong: '100%' };
        const msgs = { weak: 'Weak – add uppercase, numbers & symbols', medium: 'Medium – add more variety', strong: 'Strong password ✓' };

        // Support both class-based (dashboard pages) and inline-style bars (login page)
        if (bar.classList.contains('pw-strength')) {
            bar.className = `pw-strength ${strength}`;
        } else {
            bar.style.width = input.value ? widths[strength] : '0';
            bar.style.background = colors[strength];
        }

        if (hint) {
            hint.textContent = input.value ? msgs[strength] : '';
            hint.style.color = colors[strength] || '#aaa';
        }
    });
}

// ── Escape HTML ──────────────────────────────
function esc(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str ?? ''));
    return d.innerHTML;
}

// ── Format currency ──────────────────────────
function formatPHP(amount) {
    return '₱' + Number(amount).toLocaleString('en-PH', { minimumFractionDigits: 2 });
}

// ── Format date ──────────────────────────────
function formatDate(dateStr) {
    if (!dateStr) return 'Never';
    return new Date(dateStr).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
}

// ── Init on DOM ready ────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initAdminSidebar();
    initAgentNav();
    initLogout();
});
