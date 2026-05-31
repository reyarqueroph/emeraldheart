# Agent Tutorial System - Implementation Guide

## Overview
An interactive walkthrough tutorial that guides new agents through the eHeart system features and navigation after their first login.

## Features

### 🎯 **Tutorial Steps**

The tutorial includes **11 comprehensive steps**:

1. **Welcome Screen** - Introduction and start button
2. **Dashboard Overview** - Main dashboard features
3. **Products Section** - Insurance products and primers
4. **Guidelines** - Underwriting and policy guidelines
5. **Services** - Forms and procedures
6. **Submit Feedback** - Feedback system
7. **Your Account** - Profile management
8. **Email Directories** - Contact information
9. **Accredited Clinics** - Medical clinic list
10. **PRU Portals** - External portal links
11. **Completion Screen** - Finish and get started

### ✨ **Interactive Features**

- **Spotlight Effect** - Highlights the current feature being explained
- **Step-by-Step Navigation** - Next, Back, and Skip buttons
- **Progress Indicator** - Visual dots showing current step
- **Smooth Animations** - Slide-in and fade effects
- **Responsive Design** - Works on desktop, tablet, and mobile
- **Auto-Scroll** - Automatically scrolls to highlighted elements
- **Smart Positioning** - Card positions itself based on screen space

## How It Works

### First Login Experience

1. Agent logs in for the first time
2. After 1 second delay, tutorial automatically starts
3. Welcome screen appears with "Start Tour" button
4. Agent can choose to start or skip the tutorial
5. Tutorial guides through each feature with highlights
6. Completion screen appears at the end
7. Tutorial is marked as completed in localStorage

### Subsequent Logins

- Tutorial does NOT appear again automatically
- Agent can restart tutorial from About modal
- "Restart Tutorial" button available in About section

## Visual Design

### Welcome Screen
```
┌─────────────────────────────────────────┐
│                                         │
│           ┌────────────┐                │
│           │     🚀     │                │ ← Animated icon
│           └────────────┘                │
│                                         │
│      Welcome to eHeart! 👋              │
│                                         │
│  Let's take a quick tour of your       │
│  dashboard and show you around...      │
│                                         │
│      [  Start Tour  →  ]                │
│                                         │
│         Skip tutorial                   │
└─────────────────────────────────────────┘
```

### Tutorial Step
```
┌─────────────────────────────────────────┐
│ STEP 2 OF 9                             │ ← Gradient header
│ Dashboard Overview                      │
├─────────────────────────────────────────┤
│                                         │
│    ┌──────┐                             │
│    │  🏠  │                             │ ← Feature icon
│    └──────┘                             │
│                                         │
│  This is your main dashboard where     │
│  you can see important information...  │
│                                         │
│  ✓ View your daily Bible verse         │
│  ✓ Quick access to Products            │
│  ✓ Check admin announcements           │
│  ✓ Access PRU portals directly         │
│                                         │
├─────────────────────────────────────────┤
│ ● ━ ○ ○ ○ ○ ○ ○ ○     [Skip] [Next →] │ ← Progress & buttons
└─────────────────────────────────────────┘
```

### Spotlight Effect
```
Screen is darkened with blur effect
     ↓
┌─────────────────────────────────────────┐
│ ████████████████████████████████████    │
│ ████████████████████████████████████    │
│ ████┏━━━━━━━━━━━━━━━━━━━━┓████████    │
│ ████┃  Highlighted Item  ┃████████    │ ← Red border + glow
│ ████┗━━━━━━━━━━━━━━━━━━━━┛████████    │
│ ████████████████████████████████████    │
│ ████████████████████████████████████    │
└─────────────────────────────────────────┘
```

## Files Created

### CSS
**File:** `assets/css/tutorial.css`
- Tutorial overlay and backdrop
- Spotlight effect with red border
- Tutorial card styling
- Welcome and finish screens
- Progress indicators
- Button styles
- Responsive breakpoints
- Animations (slide-in, pulse, fade)

### JavaScript
**File:** `assets/js/tutorial.js`
- Tutorial class with step management
- Element highlighting logic
- Card positioning algorithm
- Navigation controls (next, back, skip)
- LocalStorage integration
- Auto-scroll functionality
- Responsive positioning

### Integration
**Modified:** `agent/dashboard.php`
- Added tutorial.css link
- Added tutorial.js script
- Tutorial auto-starts on first login

**Modified:** `includes/agent-footer.php`
- Added "Restart Tutorial" button in About modal

## Tutorial Steps Details

### Step 1: Welcome
- **Icon:** 🚀 Rocket
- **Purpose:** Welcome message and introduction
- **Action:** Start Tour or Skip

### Step 2: Dashboard Overview
- **Target:** Main content area
- **Highlights:** 
  - Daily Bible verse
  - Quick action cards
  - Announcements
  - PRU portals

### Step 3: Products Section
- **Target:** Products sidebar link
- **Highlights:**
  - VUL products
  - Traditional Life Insurance
  - Product guides
  - Search and filter

### Step 4: Guidelines
- **Target:** Guidelines sidebar link
- **Highlights:**
  - Underwriting guidelines
  - Policy guidelines
  - Health calculator
  - BMI tools

### Step 5: Services
- **Target:** Services sidebar link
- **Highlights:**
  - New business forms
  - After-sales services
  - Claims processing
  - Form downloads

### Step 6: Submit Feedback
- **Target:** Feedback button
- **Highlights:**
  - Submit suggestions
  - Report issues
  - View feedback history
  - Admin responses

### Step 7: Your Account
- **Target:** Account sidebar link
- **Highlights:**
  - Update profile
  - Change password
  - Upload photo
  - View details

### Step 8: Email Directories
- **Target:** Email Directories link
- **Highlights:**
  - Department emails
  - Contact support
  - Search function
  - Quick copy

### Step 9: Accredited Clinics
- **Target:** Accredited Clinics link
- **Highlights:**
  - Browse clinics
  - View locations
  - Download PDF
  - Find by area

### Step 10: PRU Portals
- **Target:** Portals section
- **Highlights:**
  - PruExpert training
  - PruShoppe merchandise
  - PruOne portal
  - PruForce tools

### Step 11: Completion
- **Icon:** ✅ Check Circle
- **Purpose:** Congratulations and finish
- **Action:** Get Started button

## User Controls

### Navigation Buttons

**Skip Button**
- Available on all steps
- Shows confirmation dialog
- Marks tutorial as completed
- Closes tutorial immediately

**Back Button**
- Available from step 2 onwards
- Returns to previous step
- Maintains spotlight position

**Next Button**
- Available on all regular steps
- Advances to next step
- Smooth transition animation

**Start Tour Button**
- Only on welcome screen
- Begins the tutorial
- Advances to step 2

**Get Started Button**
- Only on completion screen
- Marks tutorial as completed
- Closes tutorial
- Shows success toast

### Progress Indicator
- Dots represent each step (excluding welcome and finish)
- Active step shown as elongated red bar
- Inactive steps shown as gray circles
- Updates automatically on navigation

## LocalStorage

### Key: `eheart_tutorial_completed`

**Values:**
- `null` or not set - Tutorial not completed (will show on login)
- `"true"` - Tutorial completed (will not show automatically)

**Functions:**
- `tutorial.init()` - Checks if tutorial should start
- `tutorial.finish()` - Sets completed flag
- `tutorial.reset()` - Removes completed flag
- `restartTutorial()` - Resets and reloads page

## Responsive Behavior

### Desktop (>768px)
- Card positioned next to highlighted element
- Full-size spotlight effect
- All features visible

### Tablet (768px)
- Card positioned at bottom of screen
- Adjusted spotlight size
- Wrapped button layout

### Mobile (<768px)
- Card fixed at bottom
- Full-width layout
- Stacked buttons
- Progress bar below buttons
- Smaller spotlight padding

## Customization

### Adding New Steps

Edit `assets/js/tutorial.js` and add to `tutorialSteps` array:

```javascript
{
    title: "New Feature",
    description: "Description of the feature",
    icon: "fa-icon-name",
    target: ".css-selector",
    position: "right", // or "left", "bottom", "center"
    features: [
        "Feature point 1",
        "Feature point 2",
        "Feature point 3"
    ]
}
```

### Changing Colors

Edit `assets/css/tutorial.css`:
- Primary color: `#D50032` (PRU Red)
- Success color: `#28a745` (Green)
- Background: `rgba(0, 0, 0, 0.85)` (Dark overlay)

### Adjusting Timing

Edit `assets/js/tutorial.js`:
```javascript
// Initial delay before showing tutorial
setTimeout(() => {
    tutorial.init();
}, 1000); // Change this value (milliseconds)
```

## Accessibility

### Keyboard Support
- Tab navigation through buttons
- Enter/Space to activate buttons
- Escape key to close (can be added)

### Screen Readers
- Semantic HTML structure
- ARIA labels on buttons
- Clear step indicators
- Descriptive text

### Visual
- High contrast colors
- Large touch targets (44px minimum)
- Clear focus states
- Readable font sizes

## Browser Compatibility

✅ Chrome/Edge (latest)
✅ Firefox (latest)
✅ Safari (latest)
✅ Mobile browsers (iOS Safari, Chrome Mobile)

## Performance

- **Lightweight:** ~15KB CSS + ~8KB JS (uncompressed)
- **No Dependencies:** Uses vanilla JavaScript
- **Smooth Animations:** Hardware-accelerated CSS transforms
- **Lazy Loading:** Only loads when needed
- **Memory Efficient:** Cleans up after completion

## Testing Checklist

- [x] Tutorial starts on first login
- [x] Welcome screen displays correctly
- [x] All 11 steps navigate properly
- [x] Spotlight highlights correct elements
- [x] Card positions correctly on all steps
- [x] Back button works
- [x] Next button works
- [x] Skip button shows confirmation
- [x] Finish button completes tutorial
- [x] Progress indicator updates
- [x] LocalStorage saves completion
- [x] Tutorial doesn't show on second login
- [x] Restart button works from About modal
- [x] Responsive on mobile
- [x] Responsive on tablet
- [x] No console errors

## Troubleshooting

### Tutorial Not Showing
1. Check localStorage: `localStorage.getItem('eheart_tutorial_completed')`
2. Clear it: `localStorage.removeItem('eheart_tutorial_completed')`
3. Refresh page

### Element Not Highlighting
1. Check if selector exists in DOM
2. Verify element is visible
3. Check console for errors
4. Element might load after tutorial starts

### Card Positioning Issues
1. Check viewport size
2. Verify target element position
3. Adjust position parameter in step definition
4. Check for CSS conflicts

## Future Enhancements

1. **Video Tutorials** - Embed video explanations
2. **Interactive Demos** - Let users try features during tutorial
3. **Tooltips** - Add persistent tooltips for first-time actions
4. **Progress Saving** - Save current step, resume later
5. **Multiple Languages** - Translate tutorial content
6. **Analytics** - Track which steps users skip
7. **Contextual Help** - Show relevant tutorial step when clicking features
8. **Keyboard Shortcuts** - Add keyboard navigation
9. **Voice Narration** - Audio guide option
10. **Gamification** - Add badges/rewards for completion

---

**Status:** ✅ Complete and Tested
**Version:** 1.0.0
**Last Updated:** May 7, 2026
**Compatibility:** All modern browsers
