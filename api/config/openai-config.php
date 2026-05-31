<?php
/**
 * OpenAI Configuration
 * 
 * SETUP INSTRUCTIONS:
 * 1. Go to https://platform.openai.com/api-keys
 * 2. Create a new API key
 * 3. Copy the key and paste it below
 * 4. Make sure you have credits in your OpenAI account
 * 
 * RECOMMENDED MODEL: gpt-4o-mini (fast and cost-effective)
 * Alternative: gpt-4o (more powerful but more expensive)
 */

return [
    'api_key' => 'YOUR_OPENAI_API_KEY_HERE', // Replace with your actual OpenAI API key
    'model' => 'gpt-4o-mini', // or 'gpt-4o' for better quality
    'max_tokens' => 1000, // Maximum response length
    'temperature' => 0.7, // 0.0 = deterministic, 1.0 = creative
    'api_url' => 'https://api.openai.com/v1/chat/completions'
];
?>
