# Feedback System Enhancements

## Overview
Enhanced the agent feedback system with two new features:
1. **Subject Dropdown** - Predefined subject categories for better organization
2. **Emoji Mood Rating** - Visual mood indicator to capture agent sentiment

## Features Implemented

### 1. Emoji Mood Rating System
- **Location**: Agent sidebar feedback form (first field)
- **Question**: "How are you feeling today?"
- **Options**:
  - 😢 Very Unhappy (value: 1)
  - 😟 Unhappy (value: 2)
  - 😐 Neutral (value: 3)
  - 🙂 Happy (value: 4)
  - 😄 Very Happy (value: 5)
- **Behavior**: 
  - Emojis are grayscale and faded by default
  - Hover effect shows partial color and slight scale
  - Selected emoji becomes full color with scale and background highlight
  - Required field - must be selected before submission

### 2. Subject Dropdown
- **Location**: Agent sidebar feedback form (second field)
- **Type**: Select dropdown (replaced text input)
- **Options**:
  1. Product Inquiry
  2. Technical Issue
  3. Account Support
  4. Payment Concern
  5. Guidelines Question
  6. Portal Access
  7. Training Request
  8. System Feedback
  9. Other
- **Behavior**: Required field with placeholder "-- Select a subject --"

## Database Changes

### New Column
```sql
ALTER TABLE feedbacks ADD COLUMN IF NOT EXISTS mood_rating INT DEFAULT NULL;
```

**Column Details**:
- **Name**: `mood_rating`
- **Type**: INT
- **Default**: NULL
- **Values**: 1-5 (corresponding to emoji ratings)
- **Auto-created**: Column is automatically added by `api/feedbacks/create.php` if it doesn't exist

## Files Modified

### 1. `includes/agent-sidebar.php`
**Changes**:
- Added emoji rating selector with 5 mood options
- Changed subject input from text to select dropdown
- Added CSS styles for emoji rating (hover, selected states)
- Updated `submitQuickFeedback()` to validate and send mood_rating
- Updated `loadFbInbox()` to display mood emoji next to subject in inbox

**New CSS Classes**:
- `.emoji-rating-container` - Container for emoji options
- `.emoji-option` - Individual emoji label
- `.fb-mood-emoji` - Mood emoji display in inbox

### 2. `api/feedbacks/create.php`
**Changes**:
- Added automatic database migration (adds mood_rating column if missing)
- Accepts `mood_rating` from request body
- Validates and stores mood_rating value (1-5)

### 3. `api/feedbacks/get.php`
**Changes**:
- No changes needed - automatically returns mood_rating column

### 4. `admin/feedbacks.php`
**Changes**:
- Added "Mood" column to feedbacks table (between Agent and Subject)
- Displays emoji based on mood_rating value
- Shows tooltip with mood label on hover
- Updated colspan values for empty states

## User Experience

### Agent Side
1. **Submitting Feedback**:
   - Agent clicks "Submit Feedback" in sidebar
   - Selects mood emoji (required)
   - Selects subject from dropdown (required)
   - Types message (required)
   - Clicks "Send Feedback"

2. **Viewing Feedback Inbox**:
   - Mood emoji appears next to subject line
   - Shows status (Pending/Replied)
   - Displays admin reply if available

### Admin Side
1. **Viewing Feedbacks**:
   - New "Mood" column shows emoji
   - Hover over emoji to see mood label
   - Subject shows selected category
   - Can reply and delete as before

## Validation
- Mood rating is required (validated in JavaScript)
- Subject must be selected from dropdown
- Message must not be empty
- All three fields must be filled before submission

## Styling Details

### Emoji Rating
- **Default State**: 40% opacity, grayscale filter
- **Hover State**: 70% opacity, 50% grayscale, 1.1x scale
- **Selected State**: 100% opacity, no grayscale, 1.2x scale, red background tint
- **Size**: 32px font-size
- **Container**: Light gray background (#f8f9fa), centered layout

### Subject Dropdown
- Uses standard `.form-control` styling
- Matches existing form inputs
- 9 predefined options + placeholder

## Technical Notes

1. **Database Migration**: The mood_rating column is added automatically on first feedback submission after update. No manual SQL execution required.

2. **Backward Compatibility**: Existing feedbacks without mood_rating will show "-" in admin table.

3. **Emoji Display**: Uses Unicode emojis (no image dependencies).

4. **Validation Order**: Mood → Subject → Message (user-friendly error messages).

## Testing Checklist

- [x] Emoji rating selector displays correctly
- [x] Emoji hover and selection states work
- [x] Subject dropdown shows all 9 options
- [x] Form validation prevents submission without mood rating
- [x] Form validation prevents submission without subject
- [x] Feedback submits successfully with mood_rating
- [x] Mood emoji displays in agent inbox
- [x] Mood emoji displays in admin feedbacks table
- [x] Database column auto-creates on first submission
- [x] Existing feedbacks display correctly (with "-" for no mood)

## Future Enhancements (Optional)

1. **Mood Analytics**: Dashboard showing agent mood trends over time
2. **Subject Filtering**: Filter feedbacks by subject category in admin
3. **Mood-based Prioritization**: Automatically prioritize very unhappy feedbacks
4. **Custom Subjects**: Allow agents to add custom subjects (with admin approval)
5. **Mood History**: Track mood changes for individual agents

## Summary

The feedback system now captures both structured data (subject categories) and emotional context (mood ratings), providing administrators with better insights into agent concerns and overall team sentiment. The emoji-based mood rating makes it quick and intuitive for agents to express how they're feeling, while the subject dropdown ensures consistent categorization of feedback topics.
