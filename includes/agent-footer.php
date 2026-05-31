<!-- About Modal -->
<div class="modal-overlay" id="aboutModal">
    <div class="modal-box" style="max-width:600px;">
        <div class="modal-head" style="background:linear-gradient(135deg,#1C1C1C 0%,#2a0010 60%,#D50032 100%);color:white;">
            <h5 style="margin:0;display:flex;align-items:center;gap:10px;">
                <div style="width:36px;height:36px;background:rgba(255,255,255,0.15);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;letter-spacing:-1px;">eH</div>
                <span>About eHeart</span>
            </h5>
            <button class="modal-close" onclick="closeModal('aboutModal')" style="color:white;opacity:0.8;"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body-inner" style="padding:28px;">
            <div style="text-align:center;margin-bottom:24px;">
                <div style="width:80px;height:80px;background:linear-gradient(135deg,#D50032,#a8002a);border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;box-shadow:0 8px 24px rgba(213,0,50,0.3);">
                    <span style="font-size:32px;font-weight:900;color:white;letter-spacing:-2px;">eH</span>
                </div>
                <h3 style="font-size:24px;font-weight:900;color:#1C1C1C;margin-bottom:6px;">eHeart System</h3>
                <div style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;background:rgba(213,0,50,0.08);border-radius:20px;font-size:12px;font-weight:700;color:#D50032;">
                    <i class="fas fa-code-branch"></i> Version 1.0.0
                </div>
            </div>

            <div style="background:#f8f9fa;border-radius:12px;padding:20px;margin-bottom:20px;">
                <h6 style="font-size:14px;font-weight:800;color:#1C1C1C;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-heart" style="color:#D50032;"></i> Official Website
                </h6>
                <p style="font-size:13px;color:#555;line-height:1.7;margin:0;">
                    This is the <strong>Official Website for Emerald Heart Branch</strong> of PRU Life U.K. 
                    The eHeart system is designed to streamline agent management, product information, 
                    and client services for our dedicated team.
                </p>
            </div>

            <div style="background:#f8f9fa;border-radius:12px;padding:20px;margin-bottom:20px;">
                <h6 style="font-size:14px;font-weight:800;color:#1C1C1C;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-rocket" style="color:#D50032;"></i> Features
                </h6>
                <ul style="font-size:13px;color:#555;line-height:1.8;margin:0;padding-left:20px;">
                    <li>Agent Management & Registration</li>
                    <li>Product Catalog & Guidelines</li>
                    <li>Payment Processing via GCash</li>
                    <li>Health Calculator & BMI Tools</li>
                    <li>Announcements & Calendar</li>
                    <li>Feedback System</li>
                </ul>
            </div>

            <div style="background:#f8f9fa;border-radius:12px;padding:20px;margin-bottom:20px;">
                <h6 style="font-size:14px;font-weight:800;color:#1C1C1C;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-lightbulb" style="color:#D50032;"></i> Future Updates
                </h6>
                <p style="font-size:13px;color:#555;line-height:1.7;margin:0;">
                    We're continuously working on improvements and new features. 
                    Stay tuned for updates that will enhance your experience and productivity!
                </p>
            </div>

            <div style="background:#f8f9fa;border-radius:12px;padding:20px;margin-bottom:20px;">
                <h6 style="font-size:14px;font-weight:800;color:#1C1C1C;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-code" style="color:#D50032;"></i> Developed By
                </h6>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <div style="font-size:13px;color:#555;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-user-circle" style="color:#D50032;"></i>
                        <span><strong style="color:#1C1C1C;">John Rey Arquero</strong> - Front-End Developer</span>
                    </div>
                    <div style="font-size:13px;color:#555;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-user-circle" style="color:#D50032;"></i>
                        <span><strong style="color:#1C1C1C;">Mark Christian Baylon</strong> - Back-End Developer</span>
                    </div>
                    <div style="font-size:13px;color:#555;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-user-circle" style="color:#D50032;"></i>
                        <span><strong style="color:#1C1C1C;">Jon Calamaan</strong> - UI/UX Designer</span>
                    </div>
                    <div style="font-size:13px;color:#555;display:flex;align-items:center;gap:8px;">
                        <i class="fas fa-user-circle" style="color:#D50032;"></i>
                        <span><strong style="color:#1C1C1C;">Justin Angelo Eleria</strong> - Data Analyst</span>
                    </div>
                    <div style="font-size:12px;color:#777;margin-top:6px;padding-top:8px;border-top:1px solid #e0e0e0;display:flex;align-items:center;gap:6px;">
                        <i class="fas fa-graduation-cap" style="color:#D50032;"></i>
                        <span>IT Interns from Pateros Technological College</span>
                    </div>
                </div>
            </div>

            <div style="background:#f8f9fa;border-radius:12px;padding:20px;">
                <h6 style="font-size:14px;font-weight:800;color:#1C1C1C;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-graduation-cap" style="color:#D50032;"></i> Need Help?
                </h6>
                <button onclick="closeModal('aboutModal'); restartTutorial();" style="width:100%;padding:10px;background:white;border:2px solid #D50032;color:#D50032;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:8px;">
                    <i class="fas fa-redo"></i> Restart Tutorial
                </button>
            </div>

            <div style="margin-top:24px;padding-top:20px;border-top:1px solid #e0e0e0;text-align:center;">
                <p style="font-size:11px;color:#aaa;margin:0;">
                    © <?php echo date('Y'); ?> eHeart · Emerald Heart Branch · PRU Life U.K.<br>
                    All rights reserved.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function openAboutModal() {
    openModal('aboutModal');
}
</script>
<script src="../assets/js/theme-toggle.js"></script>
</body>
</html>
