# Quick Test Guide - Premium Products Fix 🧪

## How to Test the Fix

### 1. Open the Chatbot
1. Go to: `http://localhost/pru_life_system/agent/products.php`
2. Login as an agent
3. Click the robot icon 🤖 in bottom-right corner

---

## Test Scenario 1: Your Original Request ✅

**Type this exactly:**
```
age 67, can pay 1,000,000 limited pay
```

**Expected Result:**
```
Perfect! I got all the details from your message. Let me find the best matches... ✅

**Your Client's Profile:**
• Age: 67 years old
• Budget: ₱1,000,000/month (Premium tier 💎)
• Payment: Limited

━━━━━━━━━━━━━━━━━━━━━━━━

🌟 **Outstanding!** I found 5 premium products perfect for high-net-worth clients!

These are our top-tier offerings with exceptional coverage, wealth accumulation, 
and legacy planning benefits. Your client deserves the best, and these products deliver!

[Product Cards Displayed]
```

**Products Shown (in order):**
1. ✅ **PRUWealth 10** - ₱500,000/month, Limited
2. ✅ **PRUMillion Protect** - ₱250,000/month, Limited ⭐
3. ✅ **PRUMillion Flex** - ₱250,000/month, Limited
4. PRULink Exact Protector - ₱9,200/month, Limited
5. PRULink Elite Protector Series - ₱7,100/month, Limited

**What to Check:**
- ✅ PRUMillion Protect appears in the results
- ✅ Premium products shown first (highest premium first)
- ✅ Special "Premium tier 💎" label appears
- ✅ Special messaging for high-net-worth clients
- ✅ All products are Limited Pay
- ✅ Product cards are clickable

---

## Test Scenario 2: PRU Millionaire (Single Pay)

**Type this:**
```
age 45, budget 1000000, single payment
```

**Expected Result:**
- Shows **PRU Millionaire** (₱1,000,000, Single Pay)
- Shows **PruLove Wealth Direct** (₱500,000, Single Pay)
- Shows **PRULink Investor Account Plus** (₱100,000, Single Pay)
- Premium tier messaging (💎)
- All products are Single Pay

---

## Test Scenario 3: Medium Budget (Normal Tier)

**Type this:**
```
age 30, can pay 5000 monthly, regular
```

**Expected Result:**
- Shows affordable products (₱500-₱5,000 range)
- Products sorted lowest to highest (opposite of premium)
- NO premium tier label (💎)
- Standard messaging (not high-net-worth)
- All products are Regular Pay

---

## Test Scenario 4: Step-by-Step with Premium Budget

**Step 1 - Type:**
```
Hi
```

**Step 2 - Bot asks for age, type:**
```
50
```

**Step 3 - Bot asks for budget, type:**
```
250000
```

**Expected:** Bot recognizes premium budget and says:
```
Wow! With this budget, your client qualifies for our **premium products** 
like PRUMillion Protect, PRUMillion Flex, and PRU Millionaire! 
These are our top-tier offerings with exceptional coverage and benefits. 🌟
```

**Step 4 - Bot asks for payment type, click:**
```
Limited
```

**Expected:** Shows premium products with special messaging

---

## Visual Indicators to Look For

### Premium Tier Indicators (Budget ≥ ₱100,000)
- 💎 Diamond icon next to budget
- "Premium tier" label
- 🌟 Star emoji in response
- "Outstanding!" or "Excellent!" messaging
- "High-net-worth clients" mentioned
- "Top-tier offerings" mentioned
- "Exceptional coverage" mentioned

### Normal Tier (Budget < ₱100,000)
- No diamond icon
- No "Premium tier" label
- 🎯 Target emoji in response
- "Great news!" messaging
- Standard product descriptions

---

## Product Cards to Verify

### Premium Products (≥₱100K/month)
- ✅ PRU Millionaire - ₱1,000,000 (Single)
- ✅ PRUWealth 10 - ₱500,000 (Limited)
- ✅ PruLove Wealth Direct - ₱500,000 (Single)
- ✅ PRUMillion Protect - ₱250,000 (Limited)
- ✅ PRUMillion Flex - ₱250,000 (Limited)
- ✅ PRULink Investor Account Plus - ₱100,000 (Single)

### When to See Them
- **Limited Pay + High Budget** → PRUWealth 10, PRUMillion Protect, PRUMillion Flex
- **Single Pay + High Budget** → PRU Millionaire, PruLove Wealth Direct, PRULink Investor Account Plus
- **Regular Pay + High Budget** → (No premium products with Regular Pay)

---

## Troubleshooting

### Issue: No products shown
**Check:**
1. Database has products in VUL or Traditional categories
2. Products have correct payment_type values
3. Browser console for errors (F12)

### Issue: Wrong products shown
**Check:**
1. Budget amount extracted correctly (check bot's profile summary)
2. Payment type extracted correctly (Regular/Limited/Single)
3. Age extracted correctly

### Issue: No premium messaging
**Check:**
1. Budget is ≥ ₱100,000
2. Bot correctly identifies it as high budget
3. Look for "Premium tier 💎" label in profile summary

### Issue: Products in wrong order
**Check:**
1. High budget (≥₱100K): Should be DESC (highest first)
2. Normal budget (<₱100K): Should be ASC (lowest first)
3. Premium products should appear at top for high budgets

---

## Success Criteria

### ✅ Test Passes When:

1. **Premium Products Appear**
   - PRUMillion Protect shows for "1,000,000 limited pay"
   - PRU Millionaire shows for "1,000,000 single payment"
   - Premium products appear FIRST in results

2. **Special Messaging**
   - "Premium tier 💎" label appears for budgets ≥₱100K
   - "Outstanding!" or "Excellent!" messaging
   - Mentions "high-net-worth clients"
   - Mentions "top-tier offerings"

3. **Correct Sorting**
   - High budgets: Premium products first (DESC order)
   - Normal budgets: Affordable products first (ASC order)

4. **Payment Type Match**
   - All products match requested payment type
   - Limited → Shows Limited Pay products
   - Single → Shows Single Pay products
   - Regular → Shows Regular Pay products

5. **Age Eligibility**
   - All products are eligible for client's age
   - Products with age restrictions are filtered out

6. **Clickable Cards**
   - Product cards are clickable
   - Clicking opens PDF viewer
   - PDF loads correctly

---

## Quick Verification Commands

### Check if premium products exist:
```sql
SELECT product_name, min_premium_monthly, payment_type 
FROM products 
WHERE min_premium_monthly >= 100000 
  AND category IN ('VUL', 'Traditional Life Insurance')
ORDER BY min_premium_monthly DESC;
```

### Check specific product:
```sql
SELECT * FROM products WHERE product_name LIKE '%PRUMillion Protect%';
```

---

## Expected Behavior Summary

| Budget Range | Sort Order | Messaging | Products Shown |
|--------------|------------|-----------|----------------|
| < ₱100K | ASC (low→high) | Standard | Affordable options |
| ≥ ₱100K | DESC (high→low) | Premium 💎 | Premium products first |

| Payment Type | Products Shown |
|--------------|----------------|
| Regular | Regular Pay products |
| Limited | Limited Pay products (PRUMillion Protect, etc.) |
| Single | Single Pay products (PRU Millionaire, etc.) |

---

## Final Check

After testing, verify:
- ✅ PRUMillion Protect appears for high budget + Limited Pay
- ✅ PRU Millionaire appears for high budget + Single Pay
- ✅ Premium tier messaging appears for budgets ≥₱100K
- ✅ Products sorted correctly (premium first for high budgets)
- ✅ Age filtering works (only eligible products shown)
- ✅ Payment type matching works
- ✅ Product cards are clickable
- ✅ No JavaScript errors in console
- ✅ No PHP errors in response

---

**Status:** Ready to Test! 🚀

**Test the exact scenario you mentioned:**
```
age 67, can pay 1,000,000 limited pay
```

**You should now see PRUMillion Protect and other premium products!** ✅
