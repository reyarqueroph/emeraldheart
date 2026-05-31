/* ============================================
   AGENT TUTORIAL / WALKTHROUGH
   ============================================ */

const tutorialSteps = [
    {
        title: "Welcome to eHeart! 👋",
        description: "Let's take a quick tour of your dashboard and show you around the key features.",
        icon: "fa-rocket",
        isWelcome: true
    },
    {
        title: "Dashboard Overview",
        description: "This is your main dashboard where you can see important information at a glance.",
        icon: "fa-home",
        target: ".ad-content",
        position: "center",
        features: [
            "View your daily Bible verse for inspiration",
            "Quick access to Products, Guidelines, and Services",
            "Check admin announcements and calendar events",
            "Access PRU portals directly"
        ]
    },
    {
        title: "Products Section",
        description: "Access all insurance products, primers, and product guides here.",
        icon: "fa-box-open",
        target: "#productsAccordion",
        position: "right",
        features: [
            "Browse VUL and Traditional Life Insurance products",
            "View product primers and details",
            "Access product guides and references",
            "Search and filter products easily"
        ]
    },
    {
        title: "Guidelines",
        description: "Important underwriting and policy guidelines for your reference.",
        icon: "fa-book",
        target: "#guidelinesAccordion",
        position: "right",
        features: [
            "Underwriting guidelines and requirements",
            "Policy guidelines and procedures",
            "Health calculator and BMI tools",
            "Standard height and weight references"
        ]
    },
    {
        title: "Services",
        description: "Forms and procedures for new business, after-sales, and claims.",
        icon: "fa-concierge-bell",
        target: "#servicesAccordion",
        position: "right",
        features: [
            "New business forms and procedures",
            "After-sales service forms",
            "Claims processing guides",
            "Download required forms"
        ]
    },
    {
        title: "Submit Feedback",
        description: "Have suggestions or concerns? Send feedback directly to the admin team.",
        icon: "fa-comment-dots",
        target: "#navFeedbackBtn",
        position: "right",
        features: [
            "Submit feedback or suggestions",
            "Report issues or concerns",
            "View your feedback history",
            "Get responses from admin"
        ]
    },
    {
        title: "Your Account",
        description: "Manage your profile, change password, and update your information.",
        icon: "fa-user-circle",
        target: "a[href*='account.php']",
        position: "right",
        features: [
            "Update your profile information",
            "Change your password",
            "Upload your profile photo",
            "View your agent details"
        ]
    },
    {
        title: "Email Directories",
        description: "Quick access to important PRU Life U.K. email contacts.",
        icon: "fa-envelope-open-text",
        target: "a[href*='email-directories']",
        position: "right",
        features: [
            "Find department email addresses",
            "Contact support teams",
            "Search by department",
            "Copy emails quickly"
        ]
    },
    {
        title: "Accredited Clinics",
        description: "View the list of accredited medical clinics for client referrals.",
        icon: "fa-clinic-medical",
        target: "a[href*='accredited-clinics']",
        position: "right",
        features: [
            "Browse accredited clinics",
            "View clinic locations",
            "Download clinic list PDF",
            "Find clinics by area"
        ]
    },
    {
        title: "PRU Portals",
        description: "Direct links to important PRU Life U.K. online portals and resources.",
        icon: "fa-external-link-alt",
        target: ".ad-nav-label:contains('Portals')",
        position: "right",
        features: [
            "PruExpert - Training and learning",
            "PruShoppe - Agent merchandise",
            "PruOne - Agent portal",
            "PruForce - Sales tools"
        ]
    },
    {
        title: "You're All Set! 🎉",
        description: "You're ready to start using eHeart! Remember, you can always access the About section for more information.",
        icon: "fa-check-circle",
        isFinish: true
    }
];

class Tutorial {
    constructor() {
        this.currentStep = 0;
        this.overlay = null;
        this.spotlight = null;
        this.card = null;
        this.isActive = false;
    }

    init() {
        // Check if tutorial has been completed
        if (localStorage.getItem('eheart_tutorial_completed') === 'true') {
            return;
        }

        // Wait a bit before showing tutorial
        setTimeout(() => {
            this.createElements();
            this.show();
        }, 1000);
    }

    createElements() {
        // Create overlay
        this.overlay = document.createElement('div');
        this.overlay.className = 'tutorial-overlay';
        this.overlay.addEventListener('click', (e) => {
            if (e.target === this.overlay) {
                this.skip();
            }
        });

        // Create spotlight
        this.spotlight = document.createElement('div');
        this.spotlight.className = 'tutorial-spotlight';

        // Create card
        this.card = document.createElement('div');
        this.card.className = 'tutorial-card';

        document.body.appendChild(this.overlay);
        document.body.appendChild(this.spotlight);
        document.body.appendChild(this.card);
    }

    show() {
        this.isActive = true;
        this.overlay.style.display = 'block';
        // Force reflow to ensure display change is applied
        void this.overlay.offsetHeight;
        this.overlay.classList.add('active');
        // Prevent body scroll when tutorial is active
        document.body.style.overflow = 'hidden';
        this.renderStep();
    }

    hide() {
        this.isActive = false;
        this.overlay.classList.remove('active');
        // Restore body scroll
        document.body.style.overflow = '';
        setTimeout(() => {
            if (this.overlay) {
                this.overlay.style.display = 'none';
                this.overlay.remove();
            }
            if (this.spotlight) this.spotlight.remove();
            if (this.card) this.card.remove();
        }, 300);
    }

    renderStep() {
        const step = tutorialSteps[this.currentStep];
        
        // Render welcome screen
        if (step.isWelcome) {
            this.card.innerHTML = `
                <div class="tutorial-welcome">
                    <div class="tutorial-welcome-icon">
                        <i class="fas ${step.icon}"></i>
                    </div>
                    <h2>${step.title}</h2>
                    <p>${step.description}</p>
                    <button class="tutorial-welcome-btn" onclick="tutorial.next()">
                        Start Tour <i class="fas fa-arrow-right"></i>
                    </button>
                    <a href="#" class="tutorial-skip-link" onclick="tutorial.skip(); return false;">
                        Skip tutorial
                    </a>
                </div>
            `;
            this.positionCard('center');
            this.spotlight.style.display = 'none';
            return;
        }

        // Render finish screen
        if (step.isFinish) {
            this.card.innerHTML = `
                <div class="tutorial-welcome">
                    <div class="tutorial-welcome-icon">
                        <i class="fas ${step.icon}"></i>
                    </div>
                    <h2>${step.title}</h2>
                    <p>${step.description}</p>
                    <button class="tutorial-welcome-btn tutorial-btn-finish" onclick="tutorial.finish()">
                        Get Started <i class="fas fa-check"></i>
                    </button>
                </div>
            `;
            this.positionCard('center');
            this.spotlight.style.display = 'none';
            return;
        }

        // Render regular step
        const featuresHTML = step.features ? `
            <ul class="tutorial-features">
                ${step.features.map(f => `<li><i class="fas fa-check-circle"></i> ${f}</li>`).join('')}
            </ul>
        ` : '';

        this.card.innerHTML = `
            <div class="tutorial-header">
                <div class="tutorial-step-indicator">Step ${this.currentStep} of ${tutorialSteps.length - 2}</div>
                <h3 class="tutorial-title">${step.title}</h3>
            </div>
            <div class="tutorial-body">
                <div class="tutorial-icon">
                    <i class="fas ${step.icon}"></i>
                </div>
                <p class="tutorial-description">${step.description}</p>
                ${featuresHTML}
            </div>
            <div class="tutorial-footer">
                <div class="tutorial-progress">
                    ${tutorialSteps.map((_, i) => {
                        if (i === 0 || i === tutorialSteps.length - 1) return '';
                        return `<div class="tutorial-dot ${i === this.currentStep ? 'active' : ''}"></div>`;
                    }).join('')}
                </div>
                <div class="tutorial-buttons">
                    <button class="tutorial-btn tutorial-btn-skip" onclick="tutorial.skip()">
                        Skip
                    </button>
                    ${this.currentStep > 1 ? `
                        <button class="tutorial-btn tutorial-btn-prev" onclick="tutorial.prev()">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                    ` : ''}
                    <button class="tutorial-btn tutorial-btn-next" onclick="tutorial.next()">
                        Next <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        `;

        // Highlight target element
        if (step.target) {
            this.highlightElement(step.target, step.position);
        } else {
            this.spotlight.style.display = 'none';
            this.positionCard('center');
        }
    }

    highlightElement(selector, position) {
        const element = document.querySelector(selector);
        
        if (!element) {
            this.spotlight.style.display = 'none';
            this.positionCard('center');
            return;
        }

        // Scroll element into view
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });

        setTimeout(() => {
            const rect = element.getBoundingClientRect();
            
            // Position spotlight
            this.spotlight.style.display = 'block';
            this.spotlight.style.top = `${rect.top - 8}px`;
            this.spotlight.style.left = `${rect.left - 8}px`;
            this.spotlight.style.width = `${rect.width + 16}px`;
            this.spotlight.style.height = `${rect.height + 16}px`;

            // Position card
            this.positionCard(position, rect);
        }, 300);
    }

    positionCard(position, targetRect = null) {
        // Reset all positioning styles first
        this.card.style.top = '';
        this.card.style.left = '';
        this.card.style.right = '';
        this.card.style.bottom = '';
        this.card.style.transform = '';
        
        // Always center for welcome and finish screens
        if (position === 'center' || !targetRect) {
            this.card.style.top = '50%';
            this.card.style.left = '50%';
            this.card.style.transform = 'translate(-50%, -50%)';
            return;
        }

        // For mobile, always position at bottom
        if (window.innerWidth <= 768) {
            this.card.style.position = 'fixed';
            this.card.style.bottom = '20px';
            this.card.style.left = '20px';
            this.card.style.right = '20px';
            this.card.style.top = 'auto';
            this.card.style.transform = 'none';
            return;
        }

        // For desktop with target element, try to position near it
        const cardRect = this.card.getBoundingClientRect();
        const padding = 20;
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;

        // Calculate position based on target
        let top = targetRect.top;
        let left = targetRect.right + padding;

        // Check if card would go off-screen on the right
        if (left + cardRect.width > viewportWidth - padding) {
            // Try positioning on the left
            left = targetRect.left - cardRect.width - padding;
            
            // If still off-screen, center it
            if (left < padding) {
                this.card.style.top = '50%';
                this.card.style.left = '50%';
                this.card.style.transform = 'translate(-50%, -50%)';
                return;
            }
        }

        // Check if card would go off-screen vertically
        if (top + cardRect.height > viewportHeight - padding) {
            top = viewportHeight - cardRect.height - padding;
        }
        if (top < padding) {
            top = padding;
        }

        // Apply calculated position
        this.card.style.position = 'fixed';
        this.card.style.top = `${top}px`;
        this.card.style.left = `${left}px`;
        this.card.style.transform = 'none';
    }

    next() {
        if (this.currentStep < tutorialSteps.length - 1) {
            this.currentStep++;
            this.renderStep();
        }
    }

    prev() {
        if (this.currentStep > 0) {
            this.currentStep--;
            this.renderStep();
        }
    }

    skip() {
        if (confirm('Are you sure you want to skip the tutorial? You can always access help from the About section.')) {
            this.finish();
        }
    }

    finish() {
        localStorage.setItem('eheart_tutorial_completed', 'true');
        this.hide();
        
        // Show completion toast
        if (typeof showToast === 'function') {
            showToast('Tutorial completed! Welcome to eHeart! 🎉', 'success');
        }
    }

    reset() {
        localStorage.removeItem('eheart_tutorial_completed');
        this.currentStep = 0;
    }
}

// Initialize tutorial
const tutorial = new Tutorial();

// Auto-start on page load (only for agent dashboard)
if (window.location.pathname.includes('agent/dashboard.php')) {
    document.addEventListener('DOMContentLoaded', () => {
        tutorial.init();
    });
}

// Add restart tutorial function to window
window.restartTutorial = function() {
    tutorial.reset();
    window.location.reload();
};
