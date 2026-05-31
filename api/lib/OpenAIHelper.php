<?php
/**
 * OpenAI Helper
 * Handles communication with OpenAI API for intelligent responses
 */

class OpenAIHelper {
    
    private $apiKey;
    private $model;
    private $maxTokens;
    private $temperature;
    private $apiUrl;
    
    public function __construct() {
        $config = require_once dirname(__DIR__) . '/config/openai-config.php';
        
        $this->apiKey = $config['api_key'];
        $this->model = $config['model'];
        $this->maxTokens = $config['max_tokens'];
        $this->temperature = $config['temperature'];
        $this->apiUrl = $config['api_url'];
    }
    
    /**
     * Ask OpenAI a question about a product based on PDF content
     * 
     * @param string $question User's question
     * @param string $pdfContent Extracted PDF text content
     * @param array $productInfo Product metadata (name, category, etc.)
     * @return array Response with 'success', 'answer', and optional 'error'
     */
    public function askAboutProduct($question, $pdfContent, $productInfo) {
        // Check if API key is configured
        if (empty($this->apiKey) || $this->apiKey === 'YOUR_OPENAI_API_KEY_HERE') {
            return [
                'success' => false,
                'error' => 'OpenAI API key not configured. Please update api/config/openai-config.php',
                'answer' => $this->getFallbackAnswer($question, $productInfo)
            ];
        }
        
        // Build the system prompt
        $systemPrompt = $this->buildSystemPrompt($productInfo);
        
        // Build the user prompt with PDF content
        $userPrompt = $this->buildUserPrompt($question, $pdfContent, $productInfo);
        
        // Call OpenAI API
        $response = $this->callOpenAI($systemPrompt, $userPrompt);
        
        if ($response['success']) {
            return [
                'success' => true,
                'answer' => $response['answer']
            ];
        } else {
            return [
                'success' => false,
                'error' => $response['error'],
                'answer' => $this->getFallbackAnswer($question, $productInfo)
            ];
        }
    }
    
    /**
     * Build system prompt for OpenAI
     */
    private function buildSystemPrompt($productInfo) {
        $productName = $productInfo['name'] ?? 'this product';
        $category = $productInfo['category'] ?? 'insurance product';
        
        return "You are an expert insurance agent for Pru Life UK, specializing in {$category} products. 
You are currently helping a client understand **{$productName}**.

Your role:
- Answer questions accurately based on the Product Primer PDF content provided
- Be conversational, warm, and professional like a real insurance agent
- Use simple language that clients can understand
- Highlight key benefits and features
- Address concerns with empathy
- If information is not in the PDF, say so honestly
- Keep responses concise but informative (2-4 paragraphs)
- Use bullet points for lists
- Add relevant emojis sparingly for warmth (🛡️ 💰 📈 ✅)

Remember: You're not just providing information, you're building trust and helping clients make informed decisions.";
    }
    
    /**
     * Build user prompt with question and PDF content
     */
    private function buildUserPrompt($question, $pdfContent, $productInfo) {
        $productName = $productInfo['name'] ?? 'this product';
        $category = $productInfo['category'] ?? '';
        $minPremium = isset($productInfo['min_premium']) ? '₱' . number_format($productInfo['min_premium']) : 'N/A';
        $paymentType = $productInfo['payment_type'] ?? 'N/A';
        $ageRange = $productInfo['age_range'] ?? 'N/A';
        
        // Truncate PDF content if too long (OpenAI has token limits)
        $maxPdfLength = 8000; // Approximately 2000 tokens
        if (strlen($pdfContent) > $maxPdfLength) {
            $pdfContent = substr($pdfContent, 0, $maxPdfLength) . "\n\n[Content truncated for length...]";
        }
        
        return "PRODUCT INFORMATION:
Product Name: {$productName}
Category: {$category}
Minimum Premium: {$minPremium}/month
Payment Type: {$paymentType}
Age Range: {$ageRange}

PRODUCT PRIMER PDF CONTENT:
{$pdfContent}

CLIENT QUESTION:
{$question}

Please answer the client's question based on the Product Primer PDF content above. Be specific, accurate, and conversational. If the PDF doesn't contain the information needed to answer the question, say so honestly and provide general guidance if possible.";
    }
    
    /**
     * Call OpenAI API
     */
    private function callOpenAI($systemPrompt, $userPrompt) {
        $data = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt
                ],
                [
                    'role' => 'user',
                    'content' => $userPrompt
                ]
            ],
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature
        ];
        
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'Network error: ' . $error
            ];
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? 'Unknown error';
            
            return [
                'success' => false,
                'error' => "OpenAI API error ({$httpCode}): {$errorMessage}"
            ];
        }
        
        $result = json_decode($response, true);
        
        if (!isset($result['choices'][0]['message']['content'])) {
            return [
                'success' => false,
                'error' => 'Invalid response from OpenAI'
            ];
        }
        
        return [
            'success' => true,
            'answer' => trim($result['choices'][0]['message']['content'])
        ];
    }
    
    /**
     * Get fallback answer when OpenAI is not available
     */
    private function getFallbackAnswer($question, $productInfo) {
        $productName = $productInfo['name'] ?? 'this product';
        
        return "I'd love to give you detailed information about **{$productName}**, but I'm currently unable to access the AI system to read the Product Primer PDF. 

However, I can tell you that all the information you need is in the Product Primer PDF you're currently viewing! 

**Please check the PDF for:**
• Detailed benefits and coverage
• Premium illustrations and examples
• Age-specific information
• Investment performance (for VUL products)
• Guaranteed values (for Traditional products)
• Terms and conditions

If you have specific questions, I recommend:
1. Scrolling through the PDF to find the relevant section
2. Contacting your Pru Life UK representative for personalized assistance
3. Visiting a Pru Life UK office for face-to-face consultation

I apologize for the inconvenience! 🙏";
    }
    
    /**
     * Test OpenAI connection
     * 
     * @return array Response with 'success' and 'message'
     */
    public function testConnection() {
        if (empty($this->apiKey) || $this->apiKey === 'YOUR_OPENAI_API_KEY_HERE') {
            return [
                'success' => false,
                'message' => 'OpenAI API key not configured'
            ];
        }
        
        $response = $this->callOpenAI(
            'You are a helpful assistant.',
            'Say "Hello! OpenAI is working correctly." in a friendly way.'
        );
        
        if ($response['success']) {
            return [
                'success' => true,
                'message' => 'OpenAI connection successful!',
                'response' => $response['answer']
            ];
        } else {
            return [
                'success' => false,
                'message' => $response['error']
            ];
        }
    }
}
?>
