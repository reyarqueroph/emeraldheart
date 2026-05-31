# AI Chatbot Testing Guide 🧪

## Quick Start Testing

### 1. Open the Chatbot
1. Navigate to: `http://localhost/pru_life_system/agent/products.php`
2. Login as an agent if not already logged in
3. Click the **robot icon** 🤖 in the bottom-right corner
4. Chatbot panel will slide open

---

## Test Scenarios

### ✅ Test 1: Complete Information (All at Once)

**What to Type:**
```
I have a client 25 years old, can pay 2,500 per month, regular payment
```

**Expected Behavior:**
1. Bot immediately recognizes all three pieces of information
2. Shows client profile summary:
   - Age: 25 years old
   - Budget: ₱2,500/month
   - Payment: Regular
3. Displays matching products (VUL and Traditional only)
4. Shows suggestion buttons: "Start over", "Show VUL", "Show Traditional", "Compare"
5. Product cards are clickable and open PDF viewer

**What to Check:**
- ✅ No follow-up questions asked
- ✅ Products displayed immediately
- ✅ Only VUL and Traditional products shown (no Product Guides or Stand-Alone)
- ✅ Product cards show: name, category, min premium, payment type
- ✅ Clicking a product card opens the PDF viewer on the right

---

### ✅ Test 2: Different Format Variations

Try each of these and verify bot extracts information correctly:

**Variation A - Casual:**
```
Got a 35 yo client, can afford 5k monthly, limited pay
```
Expected: Age=35, Budget=5000, Payment=Limited

**Variation B - Formal:**
```
Client is 45 years old, monthly budget of ₱10,000, prefers single payment
```
Expected: Age=45, Budget=10000, Payment=Single

**Variation C - Short:**
```
28 yrs, P3000/mo, regular
```
Expected: Age=28, Budget=3000, Payment=Regular

**Variation D - With Commas:**
```
50 year old, budget 15,000 per month, limited payment
```
Expected: Age=50, Budget=15000, Payment=Limited

**What to Check:**
- ✅ All variations are recognized correctly
- ✅ Bot shows correct profile summary
- ✅ Products match the criteria

---

### ✅ Test 3: Partial Information

**What to Type:**
```
My client is 30 years old
```

**Expected Behavior:**
1. Bot recognizes age: 30
2. Bot asks: "What's your client's monthly budget?"
3. Shows suggestion buttons: "1000", "2500", "5000", "10000"

**Then Type:**
```
5000
```

**Expected Behavior:**
1. Bot recognizes budget: 5000
2. Bot shows profile so far (Age + Budget)
3. Bot asks: "How would your client prefer to pay?"
4. Shows suggestion buttons: "Regular", "Limited", "Single"

**Then Click:**
Click the "Regular" suggestion button

**Expected Behavior:**
1. Bot shows complete profile
2. Displays matching products

**What to Check:**
- ✅ Bot only asks for missing information
- ✅ Bot remembers previously provided information
- ✅ Suggestion buttons work correctly
- ✅ Progress bar updates (0% → 33% → 66% → 100%)

---

### ✅ Test 4: Browse Commands

**Test 4A - Show VUL:**
```
Show VUL products
```

**Expected Behavior:**
1. Bot explains what VUL is (Variable Unit-Linked Insurance)
2. Lists benefits and features
3. Shows ALL VUL products (regardless of budget)
4. Suggestion buttons: "Show Traditional", "Compare VUL vs Traditional", "Start recommendation"

**Test 4B - Show Traditional:**
```
Show Traditional products
```

**Expected Behavior:**
1. Bot explains Traditional Life Insurance
2. Lists benefits and features
3. Shows ALL Traditional products
4. Suggestion buttons: "Show VUL", "Compare VUL vs Traditional", "Start recommendation"

**Test 4C - Compare:**
```
Compare VUL and Traditional
```

**Expected Behavior:**
1. Bot provides side-by-side comparison
2. Explains key differences
3. Gives recommendation guidance
4. Suggestion buttons: "Show VUL", "Show Traditional", "Start recommendation"

**What to Check:**
- ✅ Responses are conversational and natural
- ✅ Information is accurate and helpful
- ✅ Products displayed match the category
- ✅ No Product Guides or Stand-Alone products shown

---

### ✅ Test 5: Restart Flow

**What to Type:**
```
Start over
```

**Expected Behavior:**
1. Bot resets all context (age, budget, payment type cleared)
2. Bot asks: "How old is your client?"
3. Shows suggestion buttons: "25", "35", "45", "55"
4. Progress bar resets to 0%

**What to Check:**
- ✅ Previous context is cleared
- ✅ Flow starts from beginning
- ✅ Suggestion buttons appear

---

### ✅ Test 6: Greeting

**What to Type:**
```
Hi
```

**Expected Behavior:**
1. Bot gives welcome message
2. Explains what it can do
3. Lists options (Quick Match, Browse VUL, Browse Traditional, Ask Questions)
4. Asks: "How old is your client?"
5. Shows suggestion buttons: "25", "35", "45", "55"

**What to Check:**
- ✅ Friendly, welcoming tone
- ✅ Clear explanation of capabilities
- ✅ Starts recommendation flow

---

### ✅ Test 7: Product Card Interaction

**Setup:**
First, get some products displayed:
```
25 years old, 5000 monthly, regular payment
```

**Then:**
1. Click on any product card in the chat

**Expected Behavior:**
1. Product card in the left panel highlights (becomes active)
2. Right panel shows product details
3. PDF viewer loads the Product Primer
4. PDF controls work (zoom, page navigation, download)

**What to Check:**
- ✅ Product card click works
- ✅ Left panel updates (active state)
- ✅ Right panel shows correct product
- ✅ PDF loads and displays correctly
- ✅ PDF controls are functional

---

### ✅ Test 8: Suggestion Buttons

**Test all suggestion button types:**

**Age Buttons:**
Click "25" → Should send "25" as message

**Budget Buttons:**
Click "5000" → Should send "5000" as message

**Payment Buttons:**
Click "Regular" → Should send "Regular" as message

**Action Buttons:**
Click "Show VUL" → Should send "Show VUL" as message

**What to Check:**
- ✅ Clicking button sends the message
- ✅ Bot processes the message correctly
- ✅ No need to type manually
- ✅ Buttons disappear after clicking (new suggestions appear)

---

## Edge Cases to Test

### Edge Case 1: Invalid Age
```
Client is 150 years old, budget 5000, regular
```

**Expected:** Bot should handle gracefully (may show no products or closest matches)

### Edge Case 2: Very Low Budget
```
25 years old, 50 pesos monthly, regular
```

**Expected:** Bot should indicate budget is too low and ask for realistic amount

### Edge Case 3: Typos
```
Clint is 25 yrs old, buget 5000, regulr pay
```

**Expected:** Bot should still extract: Age=25, Budget=5000, Payment=Regular (typos in other words ignored)

### Edge Case 4: Multiple Ages
```
Client is 25 or maybe 30 years old, budget 5000, regular
```

**Expected:** Bot extracts first age found (25)

### Edge Case 5: No Products Match
```
70 years old, budget 100000 monthly, single payment
```

**Expected:** Bot shows "closest alternatives" or "most popular products"

---

## Performance Checks

### Response Time
- ✅ Bot should respond within 1-2 seconds
- ✅ No long delays or timeouts
- ✅ Typing indicator shows while processing

### Product Loading
- ✅ Product cards render smoothly
- ✅ No layout breaks or overlaps
- ✅ Images/icons load correctly

### PDF Viewer
- ✅ PDF loads within 2-3 seconds
- ✅ No errors in console
- ✅ Zoom and navigation work smoothly

---

## Browser Console Checks

### Open Developer Tools (F12)

**Check for Errors:**
- ✅ No JavaScript errors in Console tab
- ✅ No failed network requests in Network tab
- ✅ API calls return 200 status code

**Expected API Calls:**
1. `POST /api/chatbot/recommend-ai.php` → Returns JSON with success=true
2. `GET /api/products/get.php` → Returns product list
3. `GET /api/products/serve-pdf.php?file=...` → Returns PDF file

**What to Check:**
- ✅ All API calls succeed (status 200)
- ✅ No CORS errors
- ✅ No authentication errors
- ✅ Response times are reasonable

---

## Mobile Testing (Optional)

### Responsive Design
1. Open on mobile device or use browser DevTools mobile emulation
2. Test chatbot toggle button
3. Test chatbot panel (should slide in from bottom or side)
4. Test suggestion buttons (should be tappable)
5. Test product cards (should be scrollable)

**What to Check:**
- ✅ Chatbot is accessible on mobile
- ✅ Text is readable (not too small)
- ✅ Buttons are tappable (not too small)
- ✅ Layout doesn't break

---

## Troubleshooting

### Problem: Bot doesn't respond
**Check:**
1. Browser console for errors
2. Network tab for failed API calls
3. PHP error logs: `C:\xampp\php\logs\php_error_log`
4. Apache error logs: `C:\xampp\apache\logs\error.log`

### Problem: Products don't show
**Check:**
1. Database has products with category 'VUL' or 'Traditional Life Insurance'
2. Products have `is_active = 1` or `is_active IS NULL`
3. Products match the budget criteria

### Problem: PDF doesn't load
**Check:**
1. Product has `primer_file` value in database
2. PDF file exists in `uploads/primers/` directory
3. File permissions allow reading
4. Browser console for 404 errors

### Problem: Suggestion buttons don't work
**Check:**
1. JavaScript console for errors
2. `sendSuggestion()` function is defined
3. Button onclick handlers are attached

---

## Success Criteria

### ✅ All Tests Pass When:

1. **Keyword Extraction Works**
   - Bot recognizes age, budget, payment type from single message
   - Bot skips unnecessary questions
   - Bot shows products immediately

2. **Conversational Responses**
   - Bot sounds natural and friendly
   - Bot provides helpful explanations
   - Bot acknowledges user input

3. **Product Filtering**
   - Only VUL and Traditional products shown
   - No Product Guides or Stand-Alone products
   - Products match criteria (age, budget, payment)

4. **UI/UX Smooth**
   - No errors in console
   - Fast response times
   - Buttons work correctly
   - Product cards are clickable
   - PDF viewer loads properly

5. **Edge Cases Handled**
   - Invalid input handled gracefully
   - No crashes or errors
   - Helpful error messages

---

## Reporting Issues

If you find any issues during testing, note:

1. **What you did** (exact message sent)
2. **What you expected** (expected behavior)
3. **What happened** (actual behavior)
4. **Browser console errors** (if any)
5. **Screenshots** (if helpful)

---

**Testing Date:** May 8, 2026
**Version:** Keyword Extraction v1.0
**Status:** Ready for Testing ✅
