<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['user_role'] === 'admin' ? 'admin/dashboard.php' : 'agent/dashboard.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eHeart – PRU LIFE U.K. Agent Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --red: #D50032;
            --red-dark: #a8002a;
            --dark: #1C1C1C;
            --dark2: #2a2a2a;
            --white: #ffffff;
            --light: #F5F6FA;
            --border: #E0E0E0;
            --text: #2C2C2C;
            --muted: #777777;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: var(--text);
            background: var(--white);
            overflow-x: hidden;
        }

        /* ── NAVBAR ── */
        .pru-nav {
            position: fixed; top: 0; left: 0; right: 0;
            z-index: 1000;
            padding: 0 40px;
            height: 68px;
            display: flex; align-items: center; justify-content: space-between;
            background: rgba(28,28,28,0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            transition: all 0.3s;
        }

        .pru-nav .nav-brand {
            display: flex; align-items: center; gap: 12px;
            text-decoration: none;
        }

        .pru-nav .nav-brand .logo-box {
            width: 38px; height: 38px;
            background: var(--red);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; font-weight: 900; color: white;
            box-shadow: 0 4px 12px rgba(213,0,50,0.4);
        }

        .pru-nav .nav-brand .brand-name {
            color: white;
            font-size: 15px; font-weight: 800;
            letter-spacing: -0.3px;
        }

        .pru-nav .nav-brand .brand-sub {
            color: rgba(255,255,255,0.35);
            font-size: 10px;
            display: block;
            margin-top: -2px;
        }

        .pru-nav .nav-links {
            display: flex; align-items: center; gap: 6px;
        }

        .pru-nav .nav-links a {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 13px; font-weight: 500;
            padding: 7px 14px;
            border-radius: 7px;
            transition: all 0.2s;
        }

        .pru-nav .nav-links a:hover {
            color: white;
            background: rgba(255,255,255,0.07);
        }

        .btn-nav-login {
            background: var(--red);
            color: white !important;
            border-radius: 8px !important;
            padding: 8px 20px !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            box-shadow: 0 4px 12px rgba(213,0,50,0.3);
            transition: all 0.2s !important;
        }

        .btn-nav-login:hover {
            background: var(--red-dark) !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(213,0,50,0.4) !important;
        }

        /* ── HERO ── */
        .hero {
            min-height: 100vh;
            background: var(--dark);
            display: flex; align-items: center;
            position: relative;
            overflow: hidden;
            padding-top: 68px;
        }

        .hero-bg {
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse at 70% 50%, rgba(213,0,50,0.18) 0%, transparent 60%),
                radial-gradient(ellipse at 10% 80%, rgba(213,0,50,0.08) 0%, transparent 50%);
        }

        .hero-grid {
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 60px 60px;
        }

        .hero-content {
            position: relative; z-index: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 80px 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
            width: 100%;
        }

        .hero-left .eyebrow {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(213,0,50,0.12);
            border: 1px solid rgba(213,0,50,0.25);
            color: #ff6b8a;
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1.5px;
            padding: 6px 14px;
            border-radius: 20px;
            margin-bottom: 24px;
        }

        .hero-left h1 {
            font-size: 52px; font-weight: 900;
            color: white; line-height: 1.1;
            margin-bottom: 20px;
            letter-spacing: -1.5px;
            animation: slideUp 0.8s ease;
        }

        .hero-left h1 span {
            color: var(--red);
            position: relative;
            animation: glow 2s ease-in-out infinite;
        }

        .hero-left p {
            font-size: 16px;
            color: rgba(255,255,255,0.55);
            line-height: 1.7;
            margin-bottom: 36px;
            max-width: 460px;
            animation: fadeIn 1s ease 0.3s;
            animation-fill-mode: backwards;
        }

        .hero-cta {
            display: flex; gap: 14px; flex-wrap: wrap;
            animation: fadeIn 1s ease 0.5s;
            animation-fill-mode: backwards;
        }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes glow {
            0%, 100% { text-shadow: 0 0 10px rgba(213, 0, 50, 0.5); }
            50% { text-shadow: 0 0 20px rgba(213, 0, 50, 0.8), 0 0 30px rgba(213, 0, 50, 0.6); }
        }

        .btn-hero-primary {
            display: inline-flex; align-items: center; gap: 9px;
            background: var(--red);
            color: white;
            padding: 14px 28px;
            border-radius: 10px;
            font-size: 14px; font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 6px 20px rgba(213,0,50,0.35);
        }

        .btn-hero-primary:hover {
            background: var(--red-dark);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(213,0,50,0.45);
        }

        .btn-hero-outline {
            display: inline-flex; align-items: center; gap: 9px;
            background: transparent;
            color: rgba(255,255,255,0.7);
            padding: 14px 28px;
            border-radius: 10px;
            font-size: 14px; font-weight: 600;
            text-decoration: none;
            border: 1.5px solid rgba(255,255,255,0.15);
            transition: all 0.2s;
        }

        .btn-hero-outline:hover {
            color: white;
            border-color: rgba(255,255,255,0.4);
            background: rgba(255,255,255,0.05);
        }

        .hero-stats {
            display: flex; gap: 32px;
            margin-top: 48px;
            padding-top: 32px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .hero-stat .num {
            font-size: 28px; font-weight: 900;
            color: white; line-height: 1;
        }

        .hero-stat .num span { color: var(--red); }

        .hero-stat .lbl {
            font-size: 11px; color: rgba(255,255,255,0.35);
            margin-top: 4px; text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        /* Hero right — login selection card */
        .hero-right {
            display: flex; justify-content: center;
        }

        .login-select-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 36px 32px;
            width: 100%; max-width: 380px;
            backdrop-filter: blur(20px);
        }

        .login-select-card .lsc-title {
            font-size: 18px; font-weight: 800;
            color: white; margin-bottom: 6px;
        }

        .login-select-card .lsc-sub {
            font-size: 12px; color: rgba(255,255,255,0.35);
            margin-bottom: 28px;
        }

        .login-option {
            display: flex; align-items: center; gap: 16px;
            background: rgba(255,255,255,0.05);
            border: 1.5px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            padding: 18px 20px;
            text-decoration: none;
            transition: all 0.25s;
            margin-bottom: 12px;
            cursor: pointer;
        }

        .login-option:last-of-type { margin-bottom: 0; }

        .login-option:hover {
            background: rgba(213,0,50,0.12);
            border-color: rgba(213,0,50,0.4);
            transform: translateX(4px);
        }

        .login-option .opt-icon {
            width: 46px; height: 46px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .login-option.admin .opt-icon {
            background: rgba(213,0,50,0.15);
            color: #ff6b8a;
        }

        .login-option.agent .opt-icon {
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.7);
        }

        .login-option .opt-text h6 {
            font-size: 14px; font-weight: 700;
            color: white; margin: 0 0 3px;
        }

        .login-option .opt-text p {
            font-size: 11px;
            color: rgba(255,255,255,0.35);
            margin: 0;
        }

        .login-option .opt-arrow {
            margin-left: auto;
            color: rgba(255,255,255,0.2);
            font-size: 13px;
            transition: all 0.2s;
        }

        .login-option:hover .opt-arrow {
            color: var(--red);
            transform: translateX(3px);
        }

        .lsc-divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }

        .lsc-divider::before {
            content: '';
            position: absolute; top: 50%; left: 0; right: 0;
            height: 1px; background: rgba(255,255,255,0.08);
        }

        .lsc-divider span {
            background: transparent;
            padding: 0 12px;
            font-size: 11px; color: rgba(255,255,255,0.25);
            position: relative;
        }

        .lsc-register {
            text-align: center;
            font-size: 12px; color: rgba(255,255,255,0.3);
            margin-top: 20px;
        }

        .lsc-register a {
            color: #ff6b8a; font-weight: 700;
            text-decoration: none;
        }

        .lsc-register a:hover { text-decoration: underline; }

        /* ── FEATURES SECTION ── */
        .features-section {
            padding: 100px 40px;
            background: var(--light);
        }

        .section-label {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(213,0,50,0.08);
            color: var(--red);
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1.5px;
            padding: 6px 14px;
            border-radius: 20px;
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 36px; font-weight: 900;
            color: var(--dark); line-height: 1.2;
            margin-bottom: 12px;
            letter-spacing: -0.8px;
        }

        .section-sub {
            font-size: 15px; color: var(--muted);
            max-width: 520px; line-height: 1.7;
        }

        .feature-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
            height: 100%;
            transition: all 0.25s;
        }

        .feature-card:hover {
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            transform: translateY(-4px);
            border-color: rgba(213,0,50,0.2);
        }

        .feature-card .fc-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            margin-bottom: 18px;
        }

        .feature-card h5 {
            font-size: 16px; font-weight: 800;
            color: var(--dark); margin-bottom: 8px;
        }

        .feature-card p {
            font-size: 13px; color: var(--muted);
            line-height: 1.6; margin: 0;
        }

        /* ── PORTALS SECTION ── */
        .portals-section {
            padding: 100px 40px;
            background: var(--dark);
            position: relative; overflow: hidden;
        }

        .portals-section::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(ellipse at 50% 0%, rgba(213,0,50,0.12) 0%, transparent 60%);
        }

        .portals-section .section-title { color: white; }
        .portals-section .section-sub   { color: rgba(255,255,255,0.4); }
        .portals-section .section-label {
            background: rgba(213,0,50,0.15);
            color: #ff6b8a;
        }

        .portal-tile {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            padding: 24px 20px;
            text-align: center;
            text-decoration: none;
            display: block;
            transition: all 0.25s;
        }

        .portal-tile:hover {
            background: rgba(213,0,50,0.1);
            border-color: rgba(213,0,50,0.3);
            transform: translateY(-4px);
        }

        .portal-tile .pt-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            background: rgba(255,255,255,0.06);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            margin: 0 auto 14px;
            transition: background 0.2s;
        }

        .portal-tile:hover .pt-icon { background: rgba(213,0,50,0.2); }

        .portal-tile .pt-name {
            font-size: 13px; font-weight: 700;
            color: white; margin-bottom: 4px;
        }

        .portal-tile .pt-desc {
            font-size: 11px; color: rgba(255,255,255,0.3);
        }

        /* ── FOOTER ── */
        .pru-footer {
            background: #111;
            padding: 40px;
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 16px;
            border-top: 1px solid rgba(255,255,255,0.06);
        }

        .pru-footer .footer-brand {
            display: flex; align-items: center; gap: 10px;
        }

        .pru-footer .footer-brand .fb-icon {
            width: 32px; height: 32px;
            background: var(--red);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 900; color: white;
        }

        .pru-footer .footer-brand span {
            color: rgba(255,255,255,0.5);
            font-size: 13px;
        }

        .pru-footer .footer-copy {
            font-size: 12px; color: rgba(255,255,255,0.2);
        }

        /* ── REGISTER MODAL ── */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.6);
            z-index: 2000;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
            opacity: 0; pointer-events: none;
            transition: opacity 0.2s;
            backdrop-filter: blur(4px);
        }

        .modal-overlay.show { opacity: 1; pointer-events: all; }

        .modal-box {
            background: white;
            border-radius: 18px;
            width: 100%; max-width: 680px;
            max-height: 90vh; overflow-y: auto;
            box-shadow: 0 32px 80px rgba(0,0,0,0.4);
            transform: translateY(24px) scale(0.98);
            transition: transform 0.25s;
        }

        .modal-overlay.show .modal-box { transform: translateY(0) scale(1); }

        .modal-head {
            padding: 22px 26px;
            border-bottom: 1px solid #f0f0f0;
            display: flex; align-items: center; justify-content: space-between;
        }

        .modal-head h5 {
            font-size: 17px; font-weight: 800;
            color: var(--dark); margin: 0;
            display: flex; align-items: center; gap: 10px;
        }

        .modal-head h5 .mh-icon {
            width: 34px; height: 34px;
            background: rgba(213,0,50,0.08);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            color: var(--red); font-size: 14px;
        }

        .modal-close {
            background: none; border: none;
            width: 32px; height: 32px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: var(--muted); cursor: pointer; font-size: 14px;
            transition: all 0.2s;
        }

        .modal-close:hover { background: #f5f5f5; color: var(--red); }

        .modal-body-inner { padding: 26px; }

        .modal-foot {
            padding: 18px 26px;
            border-top: 1px solid #f0f0f0;
            display: flex; justify-content: flex-end; gap: 10px;
        }

        /* Step Indicator */
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            position: relative;
        }

        .step-indicator::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 2px;
            background: #e0e0e0;
            z-index: 1;
        }

        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            position: relative;
            z-index: 2;
        }

        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #999;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            transition: all 0.3s;
        }

        .step-item.active .step-number {
            background: var(--red);
            color: white;
        }

        .step-item.completed .step-number {
            background: #28a745;
            color: white;
        }

        .step-label {
            font-size: 11px;
            color: #999;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .step-item.active .step-label {
            color: var(--red);
        }

        /* Registration Steps */
        .registration-step {
            display: none;
        }

        .registration-step.active {
            display: block;
        }

        /* Payment Options */
        .payment-options {
            margin-bottom: 20px;
        }

        .payment-choice {
            margin-bottom: 12px;
        }

        .payment-choice input[type="radio"] {
            display: none;
        }

        .payment-option-card {
            display: block;
            padding: 16px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            background: #fafafa;
        }

        .payment-choice input[type="radio"]:checked + .payment-option-card {
            border-color: var(--red);
            background: rgba(213,0,50,0.05);
        }

        .option-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 4px;
        }

        .option-header i {
            color: var(--red);
            font-size: 16px;
        }

        .option-header span {
            font-weight: 600;
            font-size: 14px;
        }

        .option-desc {
            font-size: 12px;
            color: #666;
            margin-left: 26px;
        }

        /* Payment Details */
        .payment-details {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border: 1px solid #e0e0e0;
        }

        .gcash-info-card {
            background: linear-gradient(135deg, #007DFF 0%, #0062CC 100%);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 4px 16px rgba(0, 125, 255, 0.2);
        }

        .gcash-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 14px;
        }

        .gcash-details .gcash-name {
            font-weight: 700;
            font-size: 15px;
            color: white;
        }

        .gcash-details .gcash-number {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: 700;
            color: white;
        }

        .amount-display {
            text-align: center;
            padding: 14px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            font-weight: 600;
            color: white;
            backdrop-filter: blur(10px);
        }

        .fee-amount {
            color: white;
            font-size: 22px;
            font-weight: 800;
        }

        /* File Upload */
        .file-upload-area {
            position: relative;
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: all 0.2s;
            cursor: pointer;
        }

        .file-upload-area:hover {
            border-color: var(--red);
            background: rgba(213,0,50,0.02);
        }

        .file-upload-area input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .upload-placeholder i {
            font-size: 24px;
            color: #ccc;
            margin-bottom: 8px;
            display: block;
        }

        .upload-placeholder span {
            display: block;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .upload-placeholder small {
            color: #999;
            font-size: 11px;
        }

        /* Form fields inside modal */
        .f-group { margin-bottom: 16px; }

        .f-group label {
            display: block;
            font-size: 11px; font-weight: 700;
            color: #555; text-transform: uppercase;
            letter-spacing: 0.5px; margin-bottom: 7px;
        }

        .f-wrap { position: relative; }

        .f-wrap .fi {
            position: absolute; left: 13px; top: 50%;
            transform: translateY(-50%);
            color: #bbb; font-size: 13px; pointer-events: none;
        }

        .f-wrap input, .f-wrap select {
            width: 100%;
            padding: 11px 14px 11px 38px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 13px; color: var(--dark);
            outline: none; background: #fafafa;
            transition: border-color 0.2s, box-shadow 0.2s;
            appearance: none;
        }

        .f-wrap input:focus, .f-wrap select:focus {
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(213,0,50,0.08);
            background: white;
        }

        .f-wrap .pw-eye {
            position: absolute; right: 11px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: #bbb; cursor: pointer; font-size: 13px;
            padding: 4px; transition: color 0.2s;
        }

        .f-wrap .pw-eye:hover { color: var(--red); }

        .btn-submit {
            padding: 13px 20px;
            background: var(--red); color: white;
            border: none; border-radius: 10px;
            font-size: 14px; font-weight: 700;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: all 0.2s;
        }

        .btn-submit:hover {
            background: var(--red-dark);
            box-shadow: 0 6px 20px rgba(213,0,50,0.35);
            transform: translateY(-1px);
        }

        .btn-submit:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

        .btn-pru {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-pru-outline {
            background: #f5f5f5;
            color: #555;
        }

        .btn-pru-outline:hover {
            background: #eee;
        }

        .btn-cancel {
            padding: 10px 20px;
            background: #f5f5f5; color: #555;
            border: none; border-radius: 8px;
            font-size: 13px; font-weight: 600;
            cursor: pointer; transition: all 0.2s;
        }

        .btn-cancel:hover { background: #eee; }

        /* Toast */
        .toast-stack {
            position: fixed; top: 20px; right: 20px;
            z-index: 9999;
            display: flex; flex-direction: column; gap: 10px;
        }

        .pru-toast {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            padding: 14px 18px;
            display: flex; align-items: flex-start; gap: 12px;
            min-width: 280px; max-width: 360px;
            border-left: 4px solid var(--red);
            animation: toastIn 0.3s ease;
        }

        .pru-toast.success { border-left-color: #28a745; }
        .pru-toast.error   { border-left-color: #dc3545; }
        .pru-toast.warning { border-left-color: #ffc107; }

        .pru-toast .ti { font-size: 15px; margin-top: 1px; flex-shrink: 0; }
        .pru-toast.success .ti { color: #28a745; }
        .pru-toast.error   .ti { color: #dc3545; }
        .pru-toast.warning .ti { color: #ffc107; }
        .pru-toast .tm { font-size: 13px; color: #333; flex: 1; line-height: 1.4; }
        .pru-toast .tc { background: none; border: none; color: #bbb; cursor: pointer; font-size: 13px; padding: 0; flex-shrink: 0; }
        .pru-toast .tc:hover { color: #555; }

        @keyframes toastIn {
            from { transform: translateX(100%); opacity: 0; }
            to   { transform: translateX(0); opacity: 1; }
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 991px) {
            .hero-content { grid-template-columns: 1fr; gap: 48px; padding: 60px 24px; }
            .hero-left h1 { font-size: 38px; }
            .hero-right { justify-content: flex-start; }
            .login-select-card { max-width: 100%; }
            .pru-nav { padding: 0 20px; }
            .pru-nav .nav-links { display: none; }
            .features-section, .portals-section { padding: 60px 24px; }
            .section-title { font-size: 28px; }
            .pru-footer { padding: 24px; }
        }

        @media (max-width: 576px) {
            .hero-left h1 { font-size: 30px; }
            .hero-stats { gap: 20px; }
            .hero-stat .num { font-size: 22px; }
        }
    </style>
</head>
<body>

<!-- ── NAVBAR ── -->
<nav class="pru-nav" id="pruNav">
    <a href="#" class="nav-brand">
        <div class="logo-box" style="font-size:13px;font-weight:900;letter-spacing:-1px;">eH</div>
        <div>
            <div class="brand-name">eHeart</div>
            <span class="brand-sub">PRU LIFE U.K. · Agent System</span>
        </div>
    </a>
    <div class="nav-links">
        <a href="#features">Features</a>
        <a href="#portals">Portals</a>
        <a href="#" onclick="openModal('registerModal');return false;">Register</a>
        <a href="agent/login.php" class="btn-nav-login">Sign In</a>
    </div>
</nav>

<!-- ── HERO ── -->
<section class="hero" id="home">
    <div class="hero-bg"></div>
    <div class="hero-grid"></div>

    <div class="hero-content">
        <!-- Left -->
        <div class="hero-left">
            <div class="eyebrow">
                <i class="fas fa-heart"></i>
                eHeart · PRU LIFE U.K.
            </div>
            <h1>Welcome to <span>eHeart</span> — PRU LIFE U.K. Agent System</h1>
            <p>Your complete platform for managing insurance products, tracking performance, and connecting with all PRU Life U.K. portals — all in one place.</p>

            <div class="hero-cta">
                <a href="#features" class="btn-hero-outline">
                    <i class="fas fa-info-circle"></i> Learn More
                </a>
            </div>

            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="num">30<span>+</span></div>
                    <div class="lbl">Years of Service</div>
                </div>
                <div class="hero-stat">
                    <div class="num">1M<span>+</span></div>
                    <div class="lbl">Policyholders</div>
                </div>
                <div class="hero-stat">
                    <div class="num">100<span>%</span></div>
                    <div class="lbl">Secure Platform</div>
                </div>
            </div>
        </div>

        <!-- Right — Login Selection -->
        <div class="hero-right">
            <div class="login-select-card">
                <div class="lsc-title">
                    <span style="display:inline-flex;align-items:center;gap:8px;">
                        <span style="width:28px;height:28px;background:#D50032;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:900;color:white;letter-spacing:-0.5px;">eH</span>
                        eHeart Portal Access
                    </span>
                </div>
                <div class="lsc-sub">Choose your role to sign in</div>

                <a href="admin/login.php" class="login-option admin">
                    <div class="opt-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="opt-text">
                        <h6>Admin Portal</h6>
                        <p>Manage agents, products & system settings</p>
                    </div>
                    <i class="fas fa-chevron-right opt-arrow"></i>
                </a>

                <a href="agent/login.php" class="login-option agent">
                    <div class="opt-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="opt-text">
                        <h6>Agent Portal</h6>
                        <p>Access products, guidelines & services</p>
                    </div>
                    <i class="fas fa-chevron-right opt-arrow"></i>
                </a>

                <div class="lsc-divider"><span>New to the system?</span></div>

                <div class="lsc-register">
                    Don't have an account?
                    <a href="#" onclick="openModal('registerModal');return false;">Create one here</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── FEATURES ── -->
<section class="features-section" id="features">
    <div style="max-width:1200px;margin:0 auto;">
        <div style="text-align:center;margin-bottom:56px;">
            <div class="section-label"><i class="fas fa-star"></i> Platform Features</div>
            <h2 class="section-title">Everything You Need in One Place</h2>
            <p class="section-sub" style="margin:0 auto;">A complete management system built for PRU Life U.K. agents and administrators.</p>
        </div>

        <div class="row g-4">
            <?php
            $features = [
                ['fa-users','rgba(213,0,50,0.08)','#D50032','Agent Management','Add, edit, and manage all registered agents with full profile control and status tracking.'],
                ['fa-box-open','rgba(40,167,69,0.08)','#28a745','Product Catalog','Browse and manage VUL, Traditional Life, and Personal Accident insurance products.'],
                ['fa-key','rgba(255,193,7,0.1)','#e6a800','Password Requests','Agents can request password resets which admins can approve or decline securely.'],
                ['fa-comments','rgba(23,162,184,0.08)','#17a2b8','Agent Feedbacks','Two-way communication between agents and admins with reply functionality.'],
                ['fa-file-export','rgba(108,117,125,0.08)','#6c757d','Data Export','Export agents, products, and feedback data to CSV for reporting and analysis.'],
                ['fa-book','rgba(111,66,193,0.08)','#6f42c1','Guidelines','Access underwriting, policy, and sales guidelines with categorized navigation.'],
            ];
            foreach ($features as [$icon, $bg, $color, $title, $desc]):
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="fc-icon" style="background:<?php echo $bg; ?>;">
                        <i class="fas <?php echo $icon; ?>" style="color:<?php echo $color; ?>;"></i>
                    </div>
                    <h5><?php echo $title; ?></h5>
                    <p><?php echo $desc; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── PORTALS ── -->
<section class="portals-section" id="portals">
    <div style="max-width:1200px;margin:0 auto;position:relative;z-index:1;">
        <div style="text-align:center;margin-bottom:56px;">
            <div class="section-label"><i class="fas fa-external-link-alt"></i> Quick Access</div>
            <h2 class="section-title">PRU Life U.K. Portals</h2>
            <p class="section-sub" style="margin:0 auto;color:rgba(255,255,255,0.4);">Direct links to all official PRU Life U.K. platforms and services.</p>
        </div>

        <div class="row g-3">
            <?php
            $portals = [
                ['PruExpert',   'https://pruexpertph.docebosaas.com/learn',        'fa-graduation-cap', '#2980b9', 'Training and learning platform'],
                ['PruShoppe',   'https://www.prushoppe.com/',                      'fa-shopping-cart',  '#e67e22', 'Agent merchandise store'],
                ['PruOne',      'https://pruone.prulifeuk.com.ph/web/',            'fa-desktop',        '#27ae60', 'Agent portal'],
                ['PruServices', 'https://www.prulifeuk.com.ph/en/pruservices/',    'fa-cogs',           '#16a085', 'Customer service hub'],
                ['PruForce',    'https://pruforce.prulifeuk.com.ph/',              'fa-users-cog',      '#f39c12', 'Sales tools and resources'],
                ['PRISM',       'https://prism.prulifeuk.com.ph/',                 'fa-gem',            '#9333ea', 'Policy management system'],
                ['JoinPru',     'https://www.joinpru.com.ph/',                     'fa-user-plus',      '#D50032', 'Recruitment platform'],
                ['PruLife UK',  'https://www.prulifeuk.com.ph/en/',                'fa-globe',          '#8e44ad', 'Official website'],
            ];
            foreach ($portals as [$name, $url, $icon, $color, $desc]):
            ?>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?php echo $url; ?>" target="_blank" rel="noopener noreferrer" class="portal-tile">
                    <div class="pt-icon">
                        <i class="fas <?php echo $icon; ?>" style="color:<?php echo $color; ?>;"></i>
                    </div>
                    <div class="pt-name"><?php echo $name; ?></div>
                    <div class="pt-desc"><?php echo $desc; ?></div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── FOOTER ── -->
<footer class="pru-footer">
    <div class="footer-brand">
        <div class="fb-icon" style="font-size:10px;font-weight:900;letter-spacing:-1px;">eH</div>
        <span>eHeart · PRU LIFE U.K. Agent Management System</span>
    </div>
    <div class="footer-copy">
        © <?php echo date('Y'); ?> eHeart · PRU Life U.K. All rights reserved.
    </div>
    <div style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(255,255,255,0.05);">
        <div style="font-size:11px;color:rgba(255,255,255,0.3);margin-bottom:8px;font-weight:600;text-transform:uppercase;letter-spacing:0.8px;">
            <i class="fas fa-code"></i> Developed By
        </div>
        <div style="display:flex;flex-direction:column;gap:6px;">
            <div style="font-size:12px;color:rgba(255,255,255,0.4);display:flex;align-items:center;gap:8px;">
                <i class="fas fa-user-circle" style="color:#D50032;"></i>
                <span><strong style="color:rgba(255,255,255,0.6);">John Rey Arquero</strong> - Front-End Developer</span>
            </div>
            <div style="font-size:12px;color:rgba(255,255,255,0.4);display:flex;align-items:center;gap:8px;">
                <i class="fas fa-user-circle" style="color:#D50032;"></i>
                <span><strong style="color:rgba(255,255,255,0.6);">Mark Christian Baylon</strong> - Back-End Developer</span>
            </div>
            <div style="font-size:12px;color:rgba(255,255,255,0.4);display:flex;align-items:center;gap:8px;">
                <i class="fas fa-user-circle" style="color:#D50032;"></i>
                <span><strong style="color:rgba(255,255,255,0.6);">Jon Calamaan</strong> - UI/UX Designer</span>
            </div>
            <div style="font-size:12px;color:rgba(255,255,255,0.4);display:flex;align-items:center;gap:8px;">
                <i class="fas fa-user-circle" style="color:#D50032;"></i>
                <span><strong style="color:rgba(255,255,255,0.6);">Justin Angelo Eleria</strong> - Data Analyst</span>
            </div>
            <div style="font-size:11px;color:rgba(255,255,255,0.25);margin-top:4px;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-graduation-cap"></i>
                <span>IT Interns from Pateros Technological College</span>
            </div>
        </div>
    </div>
</footer>

<!-- ── REGISTER MODAL ── -->
<div class="modal-overlay" id="registerModal">
    <div class="modal-box">
        <div class="modal-head">
            <h5>
                <span class="mh-icon"><i class="fas fa-user-plus"></i></span>
                <span id="modalTitle">Create Agent Account</span>
            </h5>
            <button class="modal-close" onclick="closeModal('registerModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body-inner">
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step-item active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Account Info</div>
                </div>
                <div class="step-item" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Payment</div>
                </div>
            </div>

            <!-- Step 1: Account Information -->
            <div id="step1" class="registration-step active">
                <p style="font-size:13px;color:#888;margin-bottom:20px;line-height:1.6;">
                    Fill in your details below. Your account will be reviewed by the admin before activation.
                </p>
                <form id="registerForm" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="f-group" style="margin-bottom:0;">
                                <label>Agent Code *</label>
                                <div class="f-wrap">
                                    <i class="fas fa-id-badge fi"></i>
                                    <input type="text" id="regCode" placeholder="e.g. AG-00123" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="f-group" style="margin-bottom:0;">
                                <label>PLUK Email *</label>
                                <div class="f-wrap">
                                    <i class="fas fa-envelope fi"></i>
                                    <input type="email" id="regEmail" placeholder="you@prulifeuk.com.ph" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="f-group" style="margin-bottom:0;">
                                <label>Full Name *</label>
                                <div class="f-wrap">
                                    <i class="fas fa-user fi"></i>
                                    <input type="text" id="regName" placeholder="Juan Dela Cruz" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="f-group" style="margin-bottom:0;">
                                <label>Position *</label>
                                <div class="f-wrap">
                                    <i class="fas fa-briefcase fi"></i>
                                    <select id="regPosition">
                                        <option value="Agent">Agent</option>
                                        <option value="OM">OM – Office Manager</option>
                                        <option value="UM">UM – Unit Manager</option>
                                        <option value="BM">BM – Branch Manager</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="f-group" style="margin-bottom:0;">
                                <label>Password *</label>
                                <div class="f-wrap">
                                    <i class="fas fa-lock fi"></i>
                                    <input type="password" id="regPassword" placeholder="Min. 8 characters" required style="padding-right:40px;">
                                    <button type="button" class="pw-eye" onclick="togglePw('regPassword',this)"><i class="fas fa-eye"></i></button>
                                </div>
                                <div style="margin-top:6px;height:4px;border-radius:2px;background:#eee;overflow:hidden;">
                                    <div id="pwBar" style="height:100%;width:0;border-radius:2px;transition:all 0.3s;"></div>
                                </div>
                                <div id="pwHint" style="font-size:11px;margin-top:4px;color:#aaa;min-height:16px;"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="f-group" style="margin-bottom:0;">
                                <label>Confirm Password *</label>
                                <div class="f-wrap">
                                    <i class="fas fa-lock fi"></i>
                                    <input type="password" id="regConfirm" placeholder="Repeat password" required style="padding-right:40px;">
                                    <button type="button" class="pw-eye" onclick="togglePw('regConfirm',this)"><i class="fas fa-eye"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:16px;padding:12px 14px;background:#fff8f9;border:1px solid rgba(213,0,50,0.15);border-radius:8px;display:flex;align-items:flex-start;gap:10px;">
                        <i class="fas fa-info-circle" style="color:#D50032;margin-top:1px;flex-shrink:0;font-size:13px;"></i>
                        <span style="font-size:11px;color:#888;line-height:1.5;">Password must contain at least 8 characters including uppercase, lowercase, a number, and a special character.</span>
                    </div>
                </form>
            </div>

            <!-- Step 2: Payment Information -->
            <div id="step2" class="registration-step">
                <div class="payment-info-header">
                    <h6 style="display:flex;align-items:center;gap:8px;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;background:#007DFF;border-radius:6px;font-size:11px;font-weight:900;color:white;letter-spacing:-0.5px;">G</span>
                        Pay via GCash
                    </h6>
                    <p style="font-size:13px;color:#888;margin-bottom:20px;">
                        Complete your registration by submitting the required GCash payment below.
                    </p>
                </div>

                <!-- Payment Details -->
                <div id="paymentDetails" class="payment-details">
                    <div class="gcash-info-card">
                        <div class="gcash-header">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;background:rgba(255,255,255,0.2);border-radius:8px;font-size:16px;font-weight:900;color:white;letter-spacing:-0.5px;">G</span>
                                <span style="font-size:18px;font-weight:800;color:white;letter-spacing:-0.5px;">GCash</span>
                            </div>
                            <div class="gcash-details">
                                <div class="gcash-name" id="gcashName">PRU LIFE UK</div>
                                <div class="gcash-number" id="gcashNumber">09123456789</div>
                            </div>
                        </div>
                        <div class="amount-display">
                            Registration Fee: <span class="fee-amount" id="registrationFee">₱500.00</span>
                        </div>
                    </div>

                    <form id="paymentForm" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="f-group">
                                    <label>GCash Reference Number</label>
                                    <div class="f-wrap">
                                        <i class="fas fa-hashtag fi"></i>
                                        <input type="text" id="gcashReference" name="gcash_reference" placeholder="e.g. 1234567890">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="f-group">
                                    <label>Amount Paid *</label>
                                    <div class="f-wrap">
                                        <i class="fas fa-peso-sign fi"></i>
                                        <input type="number" id="paymentAmount" name="payment_amount" step="0.01" value="500.00" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="f-group">
                                    <label>Payment Receipt/Screenshot *</label>
                                    <div class="file-upload-area">
                                        <input type="file" id="paymentProof" name="payment_proof" accept="image/*" required>
                                        <div class="upload-placeholder">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <span>Click to upload or drag & drop</span>
                                            <small>JPG, PNG, GIF (Max 5MB)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="modal-foot">
            <button type="button" class="btn-pru btn-pru-outline" onclick="closeModal('registerModal')">Cancel</button>
            <button type="button" id="prevBtn" class="btn-pru btn-pru-outline" onclick="prevStep()" style="display:none;">
                <i class="fas fa-arrow-left"></i> Previous
            </button>
            <button type="button" id="nextBtn" class="btn-submit" onclick="nextStep()">
                Next <i class="fas fa-arrow-right"></i>
            </button>
            <button type="button" id="submitBtn" class="btn-submit" onclick="submitRegistration()" style="display:none;">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </div>
    </div>
</div>

<div class="toast-stack" id="toastStack"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Global Variables ──
let currentStep = 1;
let paymentSettings = {};

// ── Modal ──
function openModal(id) {
    const m = document.getElementById(id);
    if (m) { 
        m.classList.add('show'); 
        document.body.style.overflow = 'hidden';
        if (id === 'registerModal') {
            loadPaymentSettings();
        }
    }
}

function closeModal(id) {
    const m = document.getElementById(id);
    if (m) { 
        m.classList.remove('show'); 
        document.body.style.overflow = '';
        if (id === 'registerModal') {
            resetRegistrationForm();
        }
    }
}

document.addEventListener('click', e => {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('show');
        document.body.style.overflow = '';
        if (e.target.id === 'registerModal') {
            resetRegistrationForm();
        }
    }
});

// ── Registration Steps ──
function nextStep() {
    if (currentStep === 1) {
        if (validateStep1()) {
            currentStep = 2;
            updateStepDisplay();
        }
    }
}

function prevStep() {
    if (currentStep === 2) {
        currentStep = 1;
        updateStepDisplay();
    }
}

function updateStepDisplay() {
    // Update step indicator
    document.querySelectorAll('.step-item').forEach((item, index) => {
        item.classList.remove('active', 'completed');
        if (index + 1 === currentStep) {
            item.classList.add('active');
        } else if (index + 1 < currentStep) {
            item.classList.add('completed');
        }
    });

    // Update step content
    document.querySelectorAll('.registration-step').forEach((step, index) => {
        step.classList.remove('active');
        if (index + 1 === currentStep) {
            step.classList.add('active');
        }
    });

    // Update buttons
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const modalTitle = document.getElementById('modalTitle');

    if (currentStep === 1) {
        prevBtn.style.display = 'none';
        nextBtn.style.display = 'inline-flex';
        submitBtn.style.display = 'none';
        modalTitle.textContent = 'Create Agent Account';
    } else if (currentStep === 2) {
        prevBtn.style.display = 'inline-flex';
        nextBtn.style.display = 'none';
        submitBtn.style.display = 'inline-flex';
        modalTitle.textContent = 'Registration Payment';
    }
}

function validateStep1() {
    const code = document.getElementById('regCode').value.trim();
    const email = document.getElementById('regEmail').value.trim();
    const name = document.getElementById('regName').value.trim();
    const password = document.getElementById('regPassword').value;
    const confirm = document.getElementById('regConfirm').value;

    if (!code || !email || !name || !password || !confirm) {
        showToast('Please fill in all required fields.', 'error');
        return false;
    }

    if (password !== confirm) {
        showToast('Passwords do not match.', 'error');
        return false;
    }

    if (checkStrength(password) === 'weak') {
        showToast('Password is too weak.', 'warning');
        return false;
    }

    return true;
}

function resetRegistrationForm() {
    currentStep = 1;
    updateStepDisplay();
    document.getElementById('registerForm').reset();
    document.getElementById('paymentForm').reset();
    document.getElementById('pwBar').style.width = '0';
    document.getElementById('pwHint').textContent = '';
}

// ── Payment Functions ──
async function loadPaymentSettings() {
    try {
        const response = await fetch('api/payment/get-settings.php');
        const data = await response.json();
        
        if (data.success) {
            paymentSettings = data.settings;
            
            // Update UI with settings
            document.getElementById('gcashName').textContent = paymentSettings.gcash_name || 'PRU LIFE UK';
            document.getElementById('gcashNumber').textContent = paymentSettings.gcash_number || '09123456789';
            document.getElementById('registrationFee').textContent = '₱' + parseFloat(paymentSettings.registration_fee || 500).toFixed(2);
            document.getElementById('paymentAmount').value = parseFloat(paymentSettings.registration_fee || 500).toFixed(2);
        }
    } catch (error) {
        console.error('Failed to load payment settings:', error);
    }
}

function togglePaymentDetails() {
    // GCash payment is always required — kept for compatibility
}

// ── Registration Submission ──
async function submitRegistration() {
    if (!validatePaymentForm()) {
        return;
    }

    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating account...';

    try {
        const formData = new FormData();
        
        // Add account data
        formData.append('agent_code', document.getElementById('regCode').value);
        formData.append('email', document.getElementById('regEmail').value);
        formData.append('full_name', document.getElementById('regName').value);
        formData.append('username', document.getElementById('regCode').value);
        formData.append('position', document.getElementById('regPosition').value);
        formData.append('password', document.getElementById('regPassword').value);
        
        // Add payment data
        formData.append('gcash_reference', document.getElementById('gcashReference').value);
        formData.append('payment_amount', document.getElementById('paymentAmount').value);
        
        const paymentProof = document.getElementById('paymentProof').files[0];
        if (paymentProof) {
            formData.append('payment_proof', paymentProof);
        }

        const response = await fetch('api/auth/register.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        if (result.success) {
            closeModal('registerModal');
            showToast(result.message, 'success');
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Registration error:', error);
        showToast('Registration failed. Please try again.', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

function validatePaymentForm() {
    const amount = document.getElementById('paymentAmount').value;
    const proof = document.getElementById('paymentProof').files[0];
    
    if (!amount || parseFloat(amount) <= 0) {
        showToast('Please enter a valid payment amount.', 'error');
        return false;
    }
    
    if (!proof) {
        showToast('Please upload your payment receipt.', 'error');
        return false;
    }
    
    // Validate file type and size
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!allowedTypes.includes(proof.type)) {
        showToast('Please upload a valid image file (JPG, PNG, GIF).', 'error');
        return false;
    }
    
    if (proof.size > 5 * 1024 * 1024) { // 5MB
        showToast('File size too large. Maximum 5MB allowed.', 'error');
        return false;
    }
    
    return true;
}

// ── Event Listeners ──
document.addEventListener('DOMContentLoaded', function() {
    // Auto-open register modal if coming from agent login
    if (window.location.hash === '#register') {
        setTimeout(() => {
            openModal('registerModal');
            // Remove hash from URL
            history.replaceState(null, null, ' ');
        }, 500);
    }
    
    // File upload preview
    document.getElementById('paymentProof').addEventListener('change', function() {
        const file = this.files[0];
        const placeholder = this.parentElement.querySelector('.upload-placeholder');
        
        if (file) {
            placeholder.innerHTML = `
                <i class="fas fa-check-circle" style="color: #28a745;"></i>
                <span style="color: #28a745;">${file.name}</span>
                <small>File selected successfully</small>
            `;
        } else {
            placeholder.innerHTML = `
                <i class="fas fa-cloud-upload-alt"></i>
                <span>Click to upload or drag & drop</span>
                <small>JPG, PNG, GIF (Max 5MB)</small>
            `;
        }
    });
});

// ── Toast ──
function showToast(msg, type = 'info') {
    const stack = document.getElementById('toastStack');
    const icons = { success:'fa-check-circle', error:'fa-times-circle', warning:'fa-exclamation-triangle', info:'fa-info-circle' };
    const t = document.createElement('div');
    t.className = `pru-toast ${type}`;
    t.innerHTML = `<i class="fas ${icons[type]||icons.info} ti"></i><div class="tm">${msg}</div><button class="tc" onclick="this.closest('.pru-toast').remove()"><i class="fas fa-times"></i></button>`;
    stack.appendChild(t);
    setTimeout(() => { t.style.transition='all 0.3s'; t.style.opacity='0'; t.style.transform='translateX(100%)'; setTimeout(()=>t.remove(),300); }, 4500);
}

// ── Password strength ──
function checkStrength(pw) {
    let s = 0;
    if (pw.length >= 8) s++;
    if (/[A-Z]/.test(pw)) s++;
    if (/[0-9]/.test(pw)) s++;
    if (/[^A-Za-z0-9]/.test(pw)) s++;
    return s <= 1 ? 'weak' : s <= 2 ? 'medium' : 'strong';
}

document.getElementById('regPassword').addEventListener('input', function() {
    const bar  = document.getElementById('pwBar');
    const hint = document.getElementById('pwHint');
    if (!this.value) { bar.style.width='0'; hint.textContent=''; return; }
    const s = checkStrength(this.value);
    const map = { weak:['33%','#dc3545','Weak – add uppercase, numbers & symbols'], medium:['66%','#ffc107','Medium – add more variety'], strong:['100%','#28a745','Strong password ✓'] };
    bar.style.width = map[s][0]; bar.style.background = map[s][1];
    hint.textContent = map[s][2]; hint.style.color = map[s][1];
});

// ── Toggle password ──
function togglePw(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    input.type  = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

// ── Navbar scroll effect ──
window.addEventListener('scroll', () => {
    const nav = document.getElementById('pruNav');
    if (window.scrollY > 40) {
        nav.style.background = 'rgba(28,28,28,0.98)';
        nav.style.boxShadow  = '0 4px 24px rgba(0,0,0,0.3)';
    } else {
        nav.style.background = 'rgba(28,28,28,0.95)';
        nav.style.boxShadow  = 'none';
    }
});
</script>
</body>
</html>
