/* ============================================
   eHeart Chatbot — Product Advisor
   ============================================ */

(function () {
    'use strict';

    let chatContext = {};
    let isOpen      = false;
    let currentProductContext = null; // Track currently viewed product

    // Function to update chatbot context when product is selected
    window.updateChatbotContext = function(product) {
        currentProductContext = product;
        console.log('Chatbot context updated:', product.product_name);
    };

    // ── Toggle ────────────────────────────────────────────────
    window.toggleChat = function () {
        const panel = document.getElementById('chatbotPanel');
        isOpen = !isOpen;
        panel.classList.toggle('open', isOpen);
        document.getElementById('chatBadge').style.display = 'none';
        if (isOpen) {
            document.getElementById('chatInput').focus();
            scrollChat();
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('chatbotToggle');
        if (toggle) toggle.addEventListener('click', window.toggleChat);

        const input = document.getElementById('chatInput');
        if (input) {
            input.addEventListener('keydown', e => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendChatMessage();
                }
            });
        }
    });

    // ── Send suggestion ───────────────────────────────────────
    window.sendSuggestion = function (text) {
        const input = document.getElementById('chatInput');
        if (input) { input.value = text; sendChatMessage(); }
    };

    // ── Main send ─────────────────────────────────────────────
    async function sendChatMessage() {
        const input = document.getElementById('chatInput');
        const btn   = document.getElementById('chatSendBtn');
        const msg   = (input.value || '').trim();
        if (!msg) return;

        appendMsg(msg, 'user');
        input.value = '';
        if (btn) btn.disabled = true;

        // Clear suggestions
        const suggEl = document.getElementById('chatSuggestions');
        if (suggEl) suggEl.innerHTML = '';

        const typing = appendTyping();

        try {
            // Include current product context if available
            const payload = {
                message: msg,
                context: chatContext
            };
            
            // Add current product context if viewing a product
            if (currentProductContext) {
                payload.current_product = {
                    id: currentProductContext.id,
                    name: currentProductContext.product_name,
                    category: currentProductContext.category,
                    min_premium: currentProductContext.min_premium_monthly,
                    payment_type: currentProductContext.payment_type,
                    age_range: currentProductContext.age_range,
                    description: currentProductContext.description
                };
            }
            
            const res  = await fetch('../api/chatbot/recommend-ai.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(payload)
            });
            const data = await res.json();
            typing.remove();

            if (data.success) {
                chatContext = data.context || {};
                
                // Update progress bar if available
                if (data.progress !== undefined) {
                    updateProgress(data.progress);
                }
                
                appendMsg(data.reply, 'bot');

                // Product cards
                if (data.products && data.products.length) {
                    appendProductCards(data.products);
                }

                // Suggestions
                if (data.suggestions && data.suggestions.length && suggEl) {
                    suggEl.innerHTML = '';
                    data.suggestions.forEach(s => {
                        const btn = document.createElement('button');
                        btn.textContent = s;
                        btn.onclick = function() { sendSuggestion(s); };
                        suggEl.appendChild(btn);
                    });
                }
            } else {
                appendMsg("Sorry, I couldn't process that. Please try again.", 'bot');
            }
        } catch (_) {
            typing.remove();
            appendMsg("Connection error. Please check your connection and try again.", 'bot');
        } finally {
            if (btn) btn.disabled = false;
            scrollChat();
        }
    }

    window.sendChatMessage = sendChatMessage;

    // ── Append message ────────────────────────────────────────
    function appendMsg(text, role) {
        const msgs = document.getElementById('chatMessages');
        if (!msgs) return null;
        const div  = document.createElement('div');
        div.className = `chat-msg ${role}`;
        // Convert markdown-like bold and newlines
        const html = esc(text)
            .replace(/\n/g, '<br>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/_(.*?)_/g, '<em>$1</em>');
        div.innerHTML = `<div class="msg-bubble">${html}</div>`;
        msgs.appendChild(div);
        scrollChat();
        return div;
    }

    // ── Append product recommendation cards ───────────────────
    function appendProductCards(products) {
        const msgs = document.getElementById('chatMessages');
        if (!msgs) return;
        const wrap = document.createElement('div');
        wrap.className = 'chat-msg bot';
        const inner = document.createElement('div');
        inner.className = 'chat-product-cards';

        products.forEach(p => {
            const card = document.createElement('div');
            card.className = 'chat-product-card';
            card.innerHTML = `
                <div class="cpn">${esc(p.product_name)}</div>
                <div class="cpm">${esc(p.category)} · Min. ₱${Number(p.min_premium_monthly).toLocaleString()}/mo · ${esc(p.payment_type)}</div>
                ${p.primer_file ? '<div class="cpdf"><i class="fas fa-file-pdf"></i> PDF available</div>' : ''}
            `;
            card.addEventListener('click', () => {
                // Use the page's viewProductById if available
                if (typeof window.viewProductById === 'function') {
                    window.viewProductById(p.id);
                } else if (typeof window.getProducts === 'function') {
                    const full = window.getProducts().find(x => x.id == p.id);
                    if (full && typeof window.viewProduct === 'function') window.viewProduct(full.id);
                }
            });
            inner.appendChild(card);
        });

        wrap.appendChild(inner);
        msgs.appendChild(wrap);
        scrollChat();
    }

    // ── Typing indicator ──────────────────────────────────────
    function appendTyping() {
        const msgs = document.getElementById('chatMessages');
        if (!msgs) return { remove: () => {} };
        const div  = document.createElement('div');
        div.className = 'chat-msg bot';
        div.innerHTML = `<div class="msg-bubble" style="padding:10px 14px;">
            <div class="chat-typing"><span></span><span></span><span></span></div>
        </div>`;
        msgs.appendChild(div);
        scrollChat();
        return div;
    }

    // ── Scroll to bottom ──────────────────────────────────────
    function scrollChat() {
        const msgs = document.getElementById('chatMessages');
        if (msgs) msgs.scrollTop = msgs.scrollHeight;
    }
    
    // ── Update progress bar ───────────────────────────────────
    function updateProgress(percent) {
        const progressBar = document.getElementById('chatProgressBar');
        const progressText = document.getElementById('chatProgressText');
        
        if (progressBar) {
            progressBar.style.width = percent + '%';
        }
        
        if (progressText) {
            if (percent === 0) {
                progressText.textContent = 'Starting...';
            } else if (percent === 33) {
                progressText.textContent = 'Step 1 of 3 complete';
            } else if (percent === 66) {
                progressText.textContent = 'Step 2 of 3 complete';
            } else if (percent === 100) {
                progressText.textContent = 'Finding products...';
                // Hide progress after a delay
                setTimeout(() => {
                    const container = document.getElementById('chatProgressContainer');
                    if (container) container.style.display = 'none';
                }, 2000);
            }
        }
        
        // Show progress container
        const container = document.getElementById('chatProgressContainer');
        if (container && percent < 100) {
            container.style.display = 'block';
        }
    }

    // ── Safe HTML escape ──────────────────────────────────────
    function esc(str) {
        const d = document.createElement('div');
        d.appendChild(document.createTextNode(str ?? ''));
        return d.innerHTML;
    }

})();
