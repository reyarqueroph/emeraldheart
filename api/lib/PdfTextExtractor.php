<?php
/**
 * PDF Text Extractor
 * Extracts text content from PDF files for AI processing
 */

class PdfTextExtractor {
    
    /**
     * Extract text from PDF file
     * Uses multiple methods to ensure compatibility
     * 
     * @param string $pdfPath Full path to PDF file
     * @return string Extracted text content
     */
    public static function extractText($pdfPath) {
        if (!file_exists($pdfPath)) {
            return "PDF file not found: {$pdfPath}";
        }
        
        // Method 1: Try using pdftotext command (if available)
        $text = self::extractUsingPdfToText($pdfPath);
        if (!empty($text)) {
            return $text;
        }
        
        // Method 2: Try using PHP's built-in PDF parser
        $text = self::extractUsingPhpParser($pdfPath);
        if (!empty($text)) {
            return $text;
        }
        
        // Method 3: Basic extraction (fallback)
        $text = self::extractBasic($pdfPath);
        if (!empty($text)) {
            return $text;
        }
        
        return "Unable to extract text from PDF. The PDF may be image-based or encrypted.";
    }
    
    /**
     * Extract using pdftotext command line tool
     */
    private static function extractUsingPdfToText($pdfPath) {
        // Check if pdftotext is available
        $command = "pdftotext -layout " . escapeshellarg($pdfPath) . " -";
        $output = @shell_exec($command);
        
        if (!empty($output)) {
            return trim($output);
        }
        
        return '';
    }
    
    /**
     * Extract using PHP's built-in PDF parser
     * This is a simple parser that works with basic PDFs
     */
    private static function extractUsingPhpParser($pdfPath) {
        $content = file_get_contents($pdfPath);
        
        if (empty($content)) {
            return '';
        }
        
        // Extract text between stream objects
        $text = '';
        
        // Method 1: Extract from text objects
        if (preg_match_all('/BT\s+(.*?)\s+ET/s', $content, $matches)) {
            foreach ($matches[1] as $match) {
                // Extract text from Tj and TJ operators
                if (preg_match_all('/\((.*?)\)\s*Tj/s', $match, $textMatches)) {
                    foreach ($textMatches[1] as $textMatch) {
                        $text .= self::decodeText($textMatch) . ' ';
                    }
                }
                
                // Extract from array notation [(text)]TJ
                if (preg_match_all('/\[(.*?)\]\s*TJ/s', $match, $arrayMatches)) {
                    foreach ($arrayMatches[1] as $arrayMatch) {
                        if (preg_match_all('/\((.*?)\)/', $arrayMatch, $innerMatches)) {
                            foreach ($innerMatches[1] as $innerMatch) {
                                $text .= self::decodeText($innerMatch) . ' ';
                            }
                        }
                    }
                }
            }
        }
        
        // Clean up the text
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        return $text;
    }
    
    /**
     * Basic extraction - reads raw PDF content
     */
    private static function extractBasic($pdfPath) {
        $content = file_get_contents($pdfPath);
        
        if (empty($content)) {
            return '';
        }
        
        // Remove binary data and extract readable text
        $text = '';
        
        // Split by stream objects
        $parts = preg_split('/stream\s*\n/s', $content);
        
        foreach ($parts as $part) {
            // Get content before endstream
            if (preg_match('/(.*?)endstream/s', $part, $match)) {
                $streamContent = $match[1];
                
                // Extract printable characters
                $printable = preg_replace('/[^\x20-\x7E\n\r\t]/', '', $streamContent);
                
                if (!empty(trim($printable))) {
                    $text .= $printable . ' ';
                }
            }
        }
        
        // Clean up
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        // If we got something meaningful, return it
        if (strlen($text) > 100) {
            return $text;
        }
        
        return '';
    }
    
    /**
     * Decode PDF text encoding
     */
    private static function decodeText($text) {
        // Handle escape sequences
        $text = str_replace('\\n', "\n", $text);
        $text = str_replace('\\r', "\r", $text);
        $text = str_replace('\\t', "\t", $text);
        $text = str_replace('\\(', '(', $text);
        $text = str_replace('\\)', ')', $text);
        $text = str_replace('\\\\', '\\', $text);
        
        return $text;
    }
    
    /**
     * Extract text and cache it for performance
     * 
     * @param string $pdfPath Full path to PDF file
     * @param string $cacheDir Directory to store cached text
     * @return string Extracted text content
     */
    public static function extractTextWithCache($pdfPath, $cacheDir = null) {
        if ($cacheDir === null) {
            $cacheDir = dirname(__DIR__) . '/cache/pdf_text';
        }
        
        // Create cache directory if it doesn't exist
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        
        // Generate cache filename
        $cacheFile = $cacheDir . '/' . md5($pdfPath) . '.txt';
        
        // Check if cache exists and is newer than PDF
        if (file_exists($cacheFile) && filemtime($cacheFile) >= filemtime($pdfPath)) {
            return file_get_contents($cacheFile);
        }
        
        // Extract text
        $text = self::extractText($pdfPath);
        
        // Save to cache
        @file_put_contents($cacheFile, $text);
        
        return $text;
    }
    
    /**
     * Get a summary of the PDF content (first N characters)
     * 
     * @param string $pdfPath Full path to PDF file
     * @param int $maxLength Maximum length of summary
     * @return string Summary text
     */
    public static function getSummary($pdfPath, $maxLength = 2000) {
        $text = self::extractTextWithCache($pdfPath);
        
        if (strlen($text) <= $maxLength) {
            return $text;
        }
        
        return substr($text, 0, $maxLength) . '...';
    }
}
?>
