# "How Are You Feeling Today?" - Visual Guide

## 🎨 What It Looks Like

### Modal Design

```
┌─────────────────────────────────────────────────┐
│  ╔═══════════════════════════════════════════╗  │
│  ║   How are you feeling today?              ║  │
│  ║   Let's start your day with a quick       ║  │
│  ║   check-in                                ║  │
│  ╚═══════════════════════════════════════════╝  │
│                                                  │
│  ┌──────┐  ┌──────┐  ┌──────┐                  │
│  │  😊  │  │  🙂  │  │  😐  │                  │
│  │Great │  │ Good │  │ Okay │                  │
│  └──────┘  └──────┘  └──────┘                  │
│                                                  │
│  ┌──────┐  ┌──────┐  ┌──────┐                  │
│  │  😴  │  │  😰  │  │  😔  │                  │
│  │Tired │  │Stress│  │ Sad  │                  │
│  └──────┘  └──────┘  └──────┘                  │
│                                                  │
│  ┌────────────────────────────────────────┐    │
│  │ 🌟 That's wonderful! Keep that         │    │
│  │ positive energy going...                │    │
│  └────────────────────────────────────────┘    │
│                                                  │
│  [ Skip ]              [ ✓ Continue ]           │
└─────────────────────────────────────────────────┘
```

---

## 📱 User Flow Diagram

```
┌─────────────┐
│ Agent Logs  │
│    In       │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Check Last  │
│ Feeling     │
│ Check Date  │
└──────┬──────┘
       │
       ├─── Already checked today? ──► Skip modal
       │
       ▼ No
┌─────────────┐
│ Show Modal  │
│ (1 sec      │
│  delay)     │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Agent       │
│ Selects     │
│ Feeling     │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Show        │
│ Personalized│
│ Message     │
└──────┬──────┘
       │
       ├─── Skip? ──► Close modal (don't save)
       │
       ▼ Continue
┌─────────────┐
│ Save to     │
│ LocalStorage│
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Show Toast  │
│ "Thank you!"│
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Close Modal │
│ Continue to │
│ Dashboard   │
└─────────────┘
```

---

## 🎭 Feeling States

### 1. Great 😊
```
┌────────────────────────────────────┐
│ Selected: Great                     │
│                                     │
│ ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓ │
│ ┃  😊                           ┃ │
│ ┃  Great                        ┃ │
│ ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛ │
│                                     │
│ Message:                            │
│ 🌟 That's wonderful! Keep that     │
│ positive energy going. You're       │
│ going to have an amazing day!       │
└────────────────────────────────────┘
```

### 2. Good 🙂
```
┌────────────────────────────────────┐
│ Selected: Good                      │
│                                     │
│ ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓ │
│ ┃  🙂                           ┃ │
│ ┃  Good                         ┃ │
│ ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛ │
│                                     │
│ Message:                            │
│ 😊 Great to hear! Let's make       │
│ today even better. You've got       │
│ this!                               │
└────────────────────────────────────┘
```

### 3. Okay 😐
```
┌────────────────────────────────────┐
│ Selected: Okay                      │
│                                     │
│ ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓ │
│ ┃  😐                           ┃ │
│ ┃  Okay                         ┃ │
│ ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛ │
│                                     │
│ Message:                            │
│ 👍 That's alright! Sometimes okay  │
│ is perfectly fine. Take it one      │
│ step at a time.                     │
└────────────────────────────────────┘
```

### 4. Tired 😴
```
┌────────────────────────────────────┐
│ Selected: Tired                     │
│                                     │
│ ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓ │
│ ┃  😴                           ┃ │
│ ┃  Tired                        ┃ │
│ ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛ │
│                                     │
│ Message:                            │
│ 💪 We all have those days.         │
│ Remember to take breaks and stay    │
│ hydrated. You're doing great!       │
└────────────────────────────────────┘
```

### 5. Stressed 😰
```
┌────────────────────────────────────┐
│ Selected: Stressed                  │
│                                     │
│ ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓ │
│ ┃  😰                           ┃ │
│ ┃  Stressed                     ┃ │
│ ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛ │
│                                     │
│ Message:                            │
│ 🧘 Take a deep breath. You're      │
│ stronger than you think. Break      │
│ tasks into smaller steps and        │
│ tackle them one by one.             │
└────────────────────────────────────┘
```

### 6. Sad 😔
```
┌────────────────────────────────────┐
│ Selected: Sad                       │
│                                     │
│ ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓ │
│ ┃  😔                           ┃ │
│ ┃  Sad                          ┃ │
│ ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛ │
│                                     │
│ Message:                            │
│ 💙 It's okay to not be okay.       │
│ Remember, tough times don't last,   │
│ but tough people do. We're here     │
│ for you!                            │
└────────────────────────────────────┘
```

---

## 🎬 Animation Sequence

### Step 1: Modal Appears (1 second after login)
```
Opacity: 0 → 1
Position: translateY(30px) → translateY(0)
Duration: 0.4s
Effect: Fade in + Slide up
```

### Step 2: Hover Over Feeling Card
```
Border: #e0e0e0 → #D50032 (PRU Red)
Background: #fafafa → rgba(213, 0, 50, 0.05)
Transform: translateY(0) → translateY(-2px)
Duration: 0.2s
Effect: Lift up
```

### Step 3: Select Feeling
```
Border: 2px solid #D50032
Background: rgba(213, 0, 50, 0.1)
Box-shadow: 0 4px 16px rgba(213, 0, 50, 0.2)
Duration: 0.2s
Effect: Highlight
```

### Step 4: Message Appears
```
Opacity: 0 → 1
Duration: 0.3s
Effect: Fade in
```

### Step 5: Submit Button Hover
```
Background: #D50032 → #a00028
Transform: translateY(0) → translateY(-1px)
Box-shadow: 0 4px 14px → 0 6px 20px
Duration: 0.2s
Effect: Lift + Glow
```

### Step 6: Modal Closes
```
Opacity: 1 → 0
Duration: 0.3s
Effect: Fade out
```

---

## 📊 State Diagram

```
┌─────────────────────────────────────────────┐
│                                             │
│  INITIAL STATE                              │
│  • Modal hidden                             │
│  • No feeling selected                      │
│  • Submit button disabled                   │
│                                             │
└──────────────┬──────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────┐
│                                             │
│  MODAL SHOWN                                │
│  • Modal visible                            │
│  • Waiting for selection                    │
│  • Submit button disabled                   │
│                                             │
└──────────────┬──────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────┐
│                                             │
│  FEELING SELECTED                           │
│  • Card highlighted                         │
│  • Message displayed                        │
│  • Submit button enabled                    │
│                                             │
└──────────────┬──────────────────────────────┘
               │
               ├─── Skip ──► MODAL CLOSED (not saved)
               │
               ▼ Continue
┌─────────────────────────────────────────────┐
│                                             │
│  SAVING                                     │
│  • Button shows spinner                     │
│  • Saving to localStorage                   │
│                                             │
└──────────────┬──────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────┐
│                                             │
│  SAVED                                      │
│  • Toast notification shown                 │
│  • Modal closing                            │
│  • Data saved for today                     │
│                                             │
└──────────────┬──────────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────────┐
│                                             │
│  COMPLETE                                   │
│  • Modal hidden                             │
│  • Dashboard active                         │
│  • Won't show again today                   │
│                                             │
└─────────────────────────────────────────────┘
```

---

## 🎨 Color Palette

### Header
- **Background:** Linear gradient
  - Start: `#D50032` (PRU Red)
  - End: `#a00028` (Darker Red)
- **Text:** `#FFFFFF` (White)

### Body
- **Background:** `#FFFFFF` (White)
- **Text:** `var(--pru-text)` (Dark Gray)

### Feeling Cards
- **Default Border:** `#e0e0e0` (Light Gray)
- **Default Background:** `#fafafa` (Off White)
- **Hover Border:** `#D50032` (PRU Red)
- **Hover Background:** `rgba(213, 0, 50, 0.05)` (Light Red)
- **Selected Border:** `#D50032` (PRU Red)
- **Selected Background:** `rgba(213, 0, 50, 0.1)` (Light Red)

### Message Box
- **Background:** `rgba(213, 0, 50, 0.05)` (Light Red)
- **Border Left:** `4px solid #D50032` (PRU Red)
- **Text:** `var(--pru-text)` (Dark Gray)

### Buttons
- **Skip:**
  - Background: `#f5f5f5` (Light Gray)
  - Text: `#555` (Dark Gray)
  - Hover: `#e0e0e0` (Darker Gray)
- **Submit:**
  - Background: `#D50032` (PRU Red)
  - Text: `#FFFFFF` (White)
  - Hover: `#a00028` (Darker Red)
  - Shadow: `0 4px 14px rgba(213, 0, 50, 0.3)`

---

## 📐 Layout Specifications

### Modal
- **Width:** 100% (max 500px)
- **Border Radius:** 24px
- **Box Shadow:** `0 32px 80px rgba(0, 0, 0, 0.4)`

### Header
- **Padding:** 32px 28px
- **Title Font Size:** 24px
- **Title Font Weight:** 800
- **Subtitle Font Size:** 14px

### Body
- **Padding:** 32px 28px

### Feeling Options Grid
- **Columns:** 3
- **Gap:** 12px
- **Card Padding:** 20px 12px
- **Card Border Radius:** 16px
- **Emoji Size:** 48px
- **Label Size:** 13px

### Message Box
- **Padding:** 16px
- **Border Radius:** 12px
- **Border Left:** 4px solid
- **Margin Bottom:** 24px

### Buttons
- **Padding:** 14px 24px
- **Border Radius:** 12px
- **Font Size:** 14px
- **Font Weight:** 700
- **Gap:** 12px

---

## 🔧 Technical Specifications

### LocalStorage
```javascript
// Keys
lastFeelingCheck: "Fri May 08 2026"
todayFeeling: "great"

// Check
const lastCheck = localStorage.getItem('lastFeelingCheck');
const today = new Date().toDateString();

// Save
localStorage.setItem('lastFeelingCheck', today);
localStorage.setItem('todayFeeling', feeling);
```

### Timing
```javascript
// Modal appearance delay
setTimeout(() => {
    showModal();
}, 1000); // 1 second

// Toast duration
setTimeout(() => {
    closeModal();
}, 500); // 0.5 seconds
```

### Animations
```css
/* Fade in */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Slide up */
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
```

---

## 📱 Responsive Behavior

### Desktop (> 900px)
- Modal: 500px wide, centered
- Grid: 3 columns
- All features visible

### Tablet (600px - 900px)
- Modal: 90% width, centered
- Grid: 3 columns
- Slightly smaller padding

### Mobile (< 600px)
- Modal: 95% width, centered
- Grid: 3 columns (stacked if needed)
- Reduced padding
- Larger tap targets

---

## ✅ Success Indicators

### Visual Feedback
1. **Selection:** Card border turns red, background highlights
2. **Message:** Personalized message fades in
3. **Button:** Submit button becomes enabled (red)
4. **Saving:** Button shows spinner
5. **Success:** Toast notification appears
6. **Close:** Modal fades out smoothly

### User Confirmation
- ✅ "Thank you for sharing! Have a great day! 🌟"
- ✅ Modal closes automatically
- ✅ Dashboard becomes active
- ✅ Won't show again today

---

## 🎯 Key Interactions

### 1. Hover Over Card
```
Before: Gray border, light background
After:  Red border, pink background, lifted
```

### 2. Click Card
```
Before: No selection
After:  Red border, pink background, message appears, button enabled
```

### 3. Click Skip
```
Result: Modal closes immediately, no save
```

### 4. Click Continue
```
Result: Spinner shows, saves to localStorage, toast appears, modal closes
```

---

## 🌟 Best Practices

### For Agents:
1. **Be Honest:** Select how you truly feel
2. **Take a Moment:** Read the personalized message
3. **Don't Rush:** It only takes 5 seconds
4. **Use Daily:** Make it part of your routine

### For Developers:
1. **Test All Feelings:** Ensure all messages work
2. **Check Timing:** Verify 1-second delay
3. **Test Daily Check:** Confirm it only shows once per day
4. **Verify Storage:** Check localStorage in DevTools

---

## 📖 User Guide

### How to Use:
1. **Log in** to your agent dashboard
2. **Wait** for the modal to appear (1 second)
3. **Click** on how you're feeling
4. **Read** the personalized message
5. **Click "Continue"** to save and close
6. **Start** your day with a positive mindset!

### Tips:
- ✅ Be honest about your feelings
- ✅ Take the message to heart
- ✅ Use it as a daily check-in
- ✅ Skip if you're in a hurry (but try not to!)

---

**Status:** ✅ Complete and Working
**Last Updated:** May 8, 2026
