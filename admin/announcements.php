<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$page_title = 'Announcements & Calendar Management';
require_once '../api/config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<style>
    /* Statistics cards */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        border-radius: var(--radius-md);
        padding: 20px;
        box-shadow: var(--shadow-sm);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .stat-card.primary { background: linear-gradient(135deg, #007bff, #0056b3); color: white; }
    .stat-card.danger { background: linear-gradient(135deg, #dc3545, #a71e2a); color: white; }
    .stat-card.info { background: linear-gradient(135deg, #17a2b8, #117a8b); color: white; }
    .stat-card.success { background: linear-gradient(135deg, #28a745, #1e7e34); color: white; }
    
    .stat-content h5 { margin: 0; font-size: 14px; opacity: 0.9; }
    .stat-content h2 { margin: 0; font-size: 32px; font-weight: 700; }
    .stat-icon { font-size: 32px; opacity: 0.8; }
    
    /* View toggle buttons */
    .view-toggle {
        display: flex;
        gap: 4px;
        margin-bottom: 20px;
        border-bottom: 2px solid var(--pru-border);
    }
    
    .view-tab {
        background: none;
        border: none;
        padding: 12px 20px;
        font-size: 14px;
        font-weight: 600;
        color: var(--pru-muted);
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .view-tab:hover {
        color: var(--pru-text);
        background: rgba(213, 0, 50, 0.05);
    }
    
    .view-tab.active {
        color: var(--pru-red);
        border-bottom-color: var(--pru-red);
        background: rgba(213, 0, 50, 0.08);
    }
    
    /* Calendar styles */
    .calendar-container {
        background: white;
        border-radius: var(--radius-md);
        padding: 20px;
        box-shadow: var(--shadow-sm);
    }
    
    .calendar-legend {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
    }
    
    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 3px;
    }
    
    .fc-event {
        border: none !important;
        border-radius: 6px !important;
        padding: 2px 6px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
    }
    
    .fc-event-urgent { background: #dc3545 !important; color: white !important; }
    .fc-event-event { background: #17a2b8 !important; color: white !important; }
    .fc-event-reminder { background: #ffc107 !important; color: #212529 !important; }
    .fc-event-general { background: #6c757d !important; color: white !important; }
    
    /* Announcement cards */
    .announcement-card {
        background: white;
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        margin-bottom: 15px;
        overflow: hidden;
    }
    
    .announcement-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid var(--pru-border);
    }
    
    .announcement-type-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .type-urgent { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
    .type-event { background: rgba(23, 162, 184, 0.1); color: #17a2b8; }
    .type-reminder { background: rgba(255, 193, 7, 0.1); color: #e6a800; }
    .type-general { background: rgba(108, 117, 125, 0.1); color: #6c757d; }
    
    .announcement-actions {
        display: flex;
        gap: 5px;
    }
    
    .btn-sm-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        border: 1px solid;
        background: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-sm-icon:hover {
        transform: translateY(-1px);
    }
</style>

<main class="pru-main">
    <div class="page-header">
        <h2>Announcements & Calendar Management</h2>
        <p>Create and manage announcements for agents. Use calendar view for scheduling.</p>
        <div style="margin-top: 15px;">
            <button class="btn-pru btn-pru-sm" onclick="console.log('Button clicked'); openCreateModal();">
                <i class="fas fa-plus"></i> New Announcement
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-row">
        <div class="stat-card primary">
            <div class="stat-content">
                <h5>Total</h5>
                <h2 id="totalCount">0</h2>
            </div>
            <div class="stat-icon">
                <i class="fas fa-bullhorn"></i>
            </div>
        </div>
        <div class="stat-card danger">
            <div class="stat-content">
                <h5>Urgent</h5>
                <h2 id="urgentCount">0</h2>
            </div>
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="stat-card info">
            <div class="stat-content">
                <h5>Events</h5>
                <h2 id="eventCount">0</h2>
            </div>
            <div class="stat-icon">
                <i class="fas fa-calendar-day"></i>
            </div>
        </div>
        <div class="stat-card success">
            <div class="stat-content">
                <h5>Active</h5>
                <h2 id="activeCount">0</h2>
            </div>
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>

    <!-- View Toggle Tabs -->
    <div class="view-toggle">
        <button class="view-tab active" id="listViewTab" onclick="toggleView('list')">
            <i class="fas fa-list"></i> List View
        </button>
        <button class="view-tab" id="calendarViewTab" onclick="toggleView('calendar')">
            <i class="fas fa-calendar"></i> Calendar View
        </button>
    </div>
    <!-- Calendar View -->
    <div id="calendarView" style="display: none;">
        <div class="calendar-container">
            <div class="calendar-legend">
                <div class="legend-item">
                    <div class="legend-color" style="background: #dc3545;"></div>
                    <span>Urgent</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #17a2b8;"></div>
                    <span>Event</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ffc107;"></div>
                    <span>Reminder</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #6c757d;"></div>
                    <span>General</span>
                </div>
            </div>
            <div id="calendar"></div>
        </div>
    </div>
    
    <!-- List View -->
    <div id="listView">
        <div class="table-wrapper">
            <div class="table-toolbar">
                <h5 style="margin-right:auto;">All Announcements</h5>
                <div class="table-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search announcements...">
                </div>
                <select class="form-select" id="typeFilter" style="width: auto; margin: 0 10px;">
                    <option value="">All Types</option>
                    <option value="urgent">Urgent</option>
                    <option value="event">Event</option>
                    <option value="reminder">Reminder</option>
                    <option value="general">General</option>
                </select>
                <select class="form-select" id="statusFilter" style="width: auto;">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            
            <div id="announcementsList">
                <div style="text-align: center; padding: 40px; color: var(--pru-muted);">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p style="margin-top: 15px;">Loading announcements...</p>
                </div>
            </div>
        </div>
    </div>
</main>
<!-- Create/Edit Modal -->
<div class="modal-overlay" id="announcementModal">
    <div class="modal-box modal-lg">
        <div class="modal-head">
            <h5 id="modalTitle">Create Announcement</h5>
            <button class="modal-close" onclick="closeModal('announcementModal')">&times;</button>
        </div>
        <form id="announcementForm">
            <div class="modal-body-inner">
                <input type="hidden" id="announcementId">
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="announcementType">Type *</label>
                        <select id="announcementType" class="form-control" required>
                            <option value="general">General</option>
                            <option value="urgent">Urgent</option>
                            <option value="event">Event</option>
                            <option value="reminder">Reminder</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="message">Message *</label>
                    <textarea id="message" class="form-control" rows="4" required></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label for="startDate">Start Date (Optional)</label>
                        <input type="date" id="startDate" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="endDate">End Date (Optional)</label>
                        <input type="date" id="endDate" class="form-control">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group">
                        <label for="startTime">Start Time (Optional)</label>
                        <input type="time" id="startTime" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="endTime">End Time (Optional)</label>
                        <input type="time" id="endTime" class="form-control">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="isActive" checked>
                        <span class="checkmark"></span>
                        Active (visible to agents)
                    </label>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-pru btn-pru-outline" onclick="closeModal('announcementModal')">Cancel</button>
                <button type="submit" class="btn-pru btn-pru-sm">
                    <i class="fas fa-save"></i> Save Announcement
                </button>
            </div>
        </form>
    </div>
</div>
<script src="../assets/js/scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
let announcements = [];
let editingId = null;
let calendar = null;
let currentView = 'list';

// Load announcements on page load
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing...');
    checkDatabaseSetup();
    initializeCalendar();
    
    // Initialize modal system
    if (typeof openModal === 'undefined') {
        console.error('Modal functions not found! Make sure scripts.js is loaded.');
    } else {
        console.log('Modal functions available');
    }
    
    // Event listeners
    document.getElementById('searchInput').addEventListener('input', filterAnnouncements);
    document.getElementById('typeFilter').addEventListener('change', filterAnnouncements);
    document.getElementById('statusFilter').addEventListener('change', filterAnnouncements);
    document.getElementById('announcementForm').addEventListener('submit', saveAnnouncement);
});

async function checkDatabaseSetup() {
    try {
        const response = await fetch('../api/announcements/check-setup.php');
        const result = await response.json();
        
        if (result.success) {
            if (result.table_created) {
                showSuccess('Database setup completed automatically');
            }
            loadAnnouncements();
        } else {
            showError('Database setup error: ' + result.message);
            console.error('Setup error details:', result);
        }
    } catch (error) {
        console.error('Setup check error:', error);
        showError('Failed to check database setup');
        loadAnnouncements();
    }
}

function initializeCalendar() {
    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek'
        },
        height: 'auto',
        events: [],
        eventClick: function(info) {
            const announcementId = parseInt(info.event.id);
            editAnnouncement(announcementId);
        },
        dateClick: function(info) {
            openCreateModal(info.dateStr);
        },
        eventDidMount: function(info) {
            const type = info.event.extendedProps.announcement_type;
            info.el.classList.add(`fc-event-${type}`);
        }
    });
}

function toggleView(view) {
    currentView = view;
    
    // Update tab states
    document.getElementById('listViewTab').classList.remove('active');
    document.getElementById('calendarViewTab').classList.remove('active');
    
    if (view === 'calendar') {
        document.getElementById('calendarViewTab').classList.add('active');
        document.getElementById('calendarView').style.display = 'block';
        document.getElementById('listView').style.display = 'none';
        
        if (calendar) {
            calendar.render();
            updateCalendarEvents();
        }
    } else {
        document.getElementById('listViewTab').classList.add('active');
        document.getElementById('calendarView').style.display = 'none';
        document.getElementById('listView').style.display = 'block';
    }
}
function updateCalendarEvents() {
    if (!calendar) return;
    
    const events = announcements.map(announcement => {
        const startDate = announcement.start_date || announcement.created_at.split(' ')[0];
        const endDate = announcement.end_date || startDate;
        
        return {
            id: announcement.id,
            title: announcement.title,
            start: startDate,
            end: endDate,
            extendedProps: {
                announcement_type: announcement.announcement_type,
                message: announcement.message,
                is_active: announcement.is_active
            },
            display: announcement.is_active ? 'block' : 'background'
        };
    });
    
    calendar.removeAllEvents();
    calendar.addEventSource(events);
}

async function loadAnnouncements() {
    try {
        const response = await fetch('../api/announcements/get-admin.php');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('Load announcements result:', result);
        
        if (result.success) {
            announcements = result.data || [];
            updateStatistics();
            renderAnnouncements();
            updateCalendarEvents();
        } else {
            showError('Failed to load announcements: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Load error:', error);
        showError('Network error loading announcements: ' + error.message);
        
        document.getElementById('announcementsList').innerHTML = `
            <div style="text-align: center; padding: 40px; color: var(--pru-danger);">
                <i class="fas fa-exclamation-triangle fa-3x"></i>
                <h4 style="margin: 15px 0 10px;">Failed to Load Announcements</h4>
                <p style="color: var(--pru-muted); margin-bottom: 20px;">There was an error loading the announcements.</p>
                <button class="btn-pru btn-pru-sm" onclick="loadAnnouncements()">
                    <i class="fas fa-refresh"></i> Try Again
                </button>
            </div>
        `;
    }
}

function updateStatistics() {
    const total = announcements.length;
    const urgent = announcements.filter(a => a.announcement_type === 'urgent').length;
    const events = announcements.filter(a => a.announcement_type === 'event').length;
    const active = announcements.filter(a => a.is_active == 1).length;
    
    document.getElementById('totalCount').textContent = total;
    document.getElementById('urgentCount').textContent = urgent;
    document.getElementById('eventCount').textContent = events;
    document.getElementById('activeCount').textContent = active;
}
function renderAnnouncements() {
    const container = document.getElementById('announcementsList');
    
    if (announcements.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 40px; color: var(--pru-muted);">
                <i class="fas fa-bullhorn fa-3x"></i>
                <h4 style="margin: 15px 0 10px;">No Announcements</h4>
                <p style="margin-bottom: 20px;">Create your first announcement to get started.</p>
                <button class="btn-pru btn-pru-sm" onclick="openCreateModal()">
                    <i class="fas fa-plus"></i> Create Announcement
                </button>
            </div>
        `;
        return;
    }
    
    container.innerHTML = announcements.map(announcement => `
        <div class="announcement-card">
            <div class="announcement-header">
                <div>
                    <h5 style="margin: 0 0 8px; font-size: 16px; font-weight: 600;">${escapeHtml(announcement.title)}</h5>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="announcement-type-badge type-${announcement.announcement_type}">
                            ${announcement.announcement_type}
                        </span>
                        <small style="color: var(--pru-muted); font-size: 12px;">
                            Created ${formatDate(announcement.created_at)}
                            ${announcement.created_by_name ? 'by ' + escapeHtml(announcement.created_by_name) : ''}
                        </small>
                    </div>
                </div>
                <div class="announcement-actions">
                    <button class="btn-sm-icon" style="color: #007bff; border-color: #007bff;" 
                            onclick="editAnnouncement(${announcement.id})" 
                            title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-sm-icon" style="color: ${announcement.is_active ? '#ffc107' : '#28a745'}; border-color: ${announcement.is_active ? '#ffc107' : '#28a745'};" 
                            onclick="toggleStatus(${announcement.id})" 
                            title="${announcement.is_active ? 'Deactivate' : 'Activate'}">
                        <i class="fas fa-${announcement.is_active ? 'eye-slash' : 'eye'}"></i>
                    </button>
                    <button class="btn-sm-icon" style="color: #dc3545; border-color: #dc3545;" 
                            onclick="deleteAnnouncement(${announcement.id})" 
                            title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div style="padding: 15px 20px;">
                <p style="margin: 0 0 10px; line-height: 1.5;">${escapeHtml(announcement.message)}</p>
                ${announcement.start_date || announcement.end_date || announcement.start_time || announcement.end_time ? `
                    <div style="font-size: 12px; color: var(--pru-muted);">
                        <i class="fas fa-calendar"></i>
                        ${announcement.start_date ? formatDate(announcement.start_date) : 'No start date'}
                        ${announcement.start_time ? ' at ' + formatTime(announcement.start_time) : ''}
                        ${announcement.end_date ? ' - ' + formatDate(announcement.end_date) : ''}
                        ${announcement.end_time ? ' at ' + formatTime(announcement.end_time) : ''}
                    </div>
                ` : ''}
            </div>
        </div>
    `).join('');
}
function filterAnnouncements() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const typeFilter = document.getElementById('typeFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    let filtered = announcements;
    
    if (search) {
        filtered = filtered.filter(a => 
            a.title.toLowerCase().includes(search) || 
            a.message.toLowerCase().includes(search)
        );
    }
    
    if (typeFilter) {
        filtered = filtered.filter(a => a.announcement_type === typeFilter);
    }
    
    if (statusFilter !== '') {
        filtered = filtered.filter(a => a.is_active == statusFilter);
    }
    
    const originalAnnouncements = announcements;
    announcements = filtered;
    renderAnnouncements();
    announcements = originalAnnouncements;
}

function openCreateModal(selectedDate = null) {
    console.log('openCreateModal called with date:', selectedDate);
    editingId = null;
    document.getElementById('modalTitle').textContent = 'Create Announcement';
    document.getElementById('announcementForm').reset();
    document.getElementById('announcementId').value = '';
    document.getElementById('isActive').checked = true;
    
    if (selectedDate) {
        document.getElementById('startDate').value = selectedDate;
    }
    
    console.log('Opening modal...');
    openModal('announcementModal');
}

function editAnnouncement(id) {
    const announcement = announcements.find(a => a.id === id);
    if (!announcement) return;
    
    editingId = id;
    document.getElementById('modalTitle').textContent = 'Edit Announcement';
    document.getElementById('announcementId').value = id;
    document.getElementById('title').value = announcement.title;
    document.getElementById('message').value = announcement.message;
    document.getElementById('announcementType').value = announcement.announcement_type;
    document.getElementById('startDate').value = announcement.start_date || '';
    document.getElementById('endDate').value = announcement.end_date || '';
    document.getElementById('startTime').value = announcement.start_time || '';
    document.getElementById('endTime').value = announcement.end_time || '';
    document.getElementById('isActive').checked = announcement.is_active == 1;
    
    openModal('announcementModal');
}
async function saveAnnouncement(event) {
    event.preventDefault();
    
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    submitBtn.disabled = true;
    
    const formData = {
        id: document.getElementById('announcementId').value,
        title: document.getElementById('title').value.trim(),
        message: document.getElementById('message').value.trim(),
        announcement_type: document.getElementById('announcementType').value,
        start_date: document.getElementById('startDate').value || null,
        end_date: document.getElementById('endDate').value || null,
        start_time: document.getElementById('startTime').value || null,
        end_time: document.getElementById('endTime').value || null,
        is_active: document.getElementById('isActive').checked ? 1 : 0
    };
    
    if (!formData.title) {
        showError('Title is required');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        return;
    }
    
    if (!formData.message) {
        showError('Message is required');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        return;
    }
    
    try {
        const url = editingId ? '../api/announcements/update.php' : '../api/announcements/create.php';
        const response = await fetch(url, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            closeModal('announcementModal');
            showSuccess(result.message);
            loadAnnouncements();
        } else {
            showError(result.message || 'Unknown error occurred');
        }
    } catch (error) {
        console.error('Save error:', error);
        showError('Network error: ' + error.message);
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}
async function toggleStatus(id) {
    const announcement = announcements.find(a => a.id === id);
    if (!announcement) return;
    
    const newStatus = announcement.is_active ? 0 : 1;
    
    try {
        const response = await fetch('../api/announcements/update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, is_active: newStatus })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess(`Announcement ${newStatus ? 'activated' : 'deactivated'}`);
            loadAnnouncements();
        } else {
            showError(result.message);
        }
    } catch (error) {
        console.error('Toggle error:', error);
        showError('Failed to update status');
    }
}

async function deleteAnnouncement(id) {
    if (!confirm('Are you sure you want to delete this announcement?')) return;
    
    try {
        const response = await fetch('../api/announcements/delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccess('Announcement deleted successfully');
            loadAnnouncements();
        } else {
            showError(result.message);
        }
    } catch (error) {
        console.error('Delete error:', error);
        showError('Failed to delete announcement');
    }
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatTime(timeString) {
    if (!timeString) return '';
    const time = new Date(`2000-01-01T${timeString}`);
    return time.toLocaleTimeString('en-PH', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showSuccess(message) {
    // Simple success notification - you can enhance this
    alert('✓ ' + message);
}

function showError(message) {
    // Simple error notification - you can enhance this
    alert('✗ ' + message);
}
</script>