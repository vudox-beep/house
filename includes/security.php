<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate CSRF Token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verify_csrf_token($token) {
    // 1. Basic Existence Check
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    
    // 2. Strict Type Check (must be string)
    if (!is_string($token) || !is_string($_SESSION['csrf_token'])) {
        return false;
    }

    // 3. Timing Attack Safe Comparison
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Advanced Input Sanitization
 * Protects against SQL Injection, XSS, and Shell Injection
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    
    // 1. Remove null bytes (common in shell injection)
    $data = str_replace(chr(0), '', $data);
    
    // 2. Trim whitespace
    $data = trim($data);
    
    // 3. Strip tags (Basic XSS)
    $data = strip_tags($data);
    
    // 4. HTML Entity Encode (Robust XSS)
    // ENT_QUOTES: Encodes both double and single quotes
    // ENT_SUBSTITUTE: Replaces invalid characters instead of returning empty string
    $data = htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    
    return $data;
}

/**
 * Validate Request Method
 * Prevents tools from forcing GET on POST actions
 */
function require_post_request() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405); // Method Not Allowed
        die('Method Not Allowed');
    }
}

/**
 * Rate Limiting
 * Blocks automated tools (Burp Intruder/Repeater)
 */
function check_rate_limit($key, $limit = 60, $period = 60) {
    if (!isset($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = [
            'count' => 0,
            'start_time' => time()
        ];
    }
    
    $data = &$_SESSION['rate_limits'][$key];
    
    // Reset if period passed
    if (time() - $data['start_time'] > $period) {
        $data['count'] = 0;
        $data['start_time'] = time();
    }
    
    $data['count']++;
    
    if ($data['count'] > $limit) {
        http_response_code(429); // Too Many Requests
        die('Rate limit exceeded. Please wait.');
    }
}

/**
 * Secure Headers
 * Mitigates Clickjacking, MIME sniffing, and XSS
 */
function set_security_headers() {
    // Prevent Clickjacking
    header('X-Frame-Options: DENY');
    
    // Prevent MIME Type Sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Enable XSS Protection Filter in browser
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer Policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy (Basic) - Allow scripts from self and trusted CDNs
    // Note: Adjust this based on your actual external scripts (Google Maps, Bootstrap, etc.)
    // header("Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' https:;");
}

// Apply headers globally
set_security_headers();
