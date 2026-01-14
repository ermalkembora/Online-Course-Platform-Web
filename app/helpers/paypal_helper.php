<?php
/**
 * PayPal Helper Functions
 * 
 * Helper functions for PayPal API integration.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/paypal.php';

/**
 * Get PayPal OAuth access token
 * 
 * @return array Returns ['success' => true, 'token' => '...'] or ['success' => false, 'error' => '...', 'http_code' => ..., 'response' => ...]
 */
function paypal_get_access_token() {
    // Check if cURL is available
    if (!function_exists('curl_version')) {
        $errorData = [
            'success' => false,
            'error' => 'cURL not enabled',
            'http_code' => 0,
            'response' => null,
        ];
        error_log("PAYPAL TOKEN ERROR: PHP cURL extension is not enabled on this server.");
        return $errorData;
    }
    
    // Verify configuration
    if (!defined('PAYPAL_API_BASE') || !defined('PAYPAL_CLIENT_ID') || !defined('PAYPAL_SECRET')) {
        $errorData = [
            'success' => false,
            'error' => 'PayPal configuration missing',
            'http_code' => 0,
            'response' => null,
        ];
        error_log("PAYPAL TOKEN ERROR: PayPal configuration constants not defined.");
        return $errorData;
    }
    
    // Build URL
    $url = PAYPAL_API_BASE . '/v1/oauth2/token';
    
    // Verify we're using sandbox URL
    if (PAYPAL_MODE === 'sandbox' && strpos($url, 'sandbox') === false) {
        error_log("PAYPAL TOKEN WARNING: PAYPAL_MODE is 'sandbox' but URL doesn't contain 'sandbox': $url");
    }
    
    // Prepare credentials
    $clientId = trim(PAYPAL_CLIENT_ID);
    $secret = trim(PAYPAL_SECRET);
    
    // Check for empty credentials
    if (empty($clientId) || empty($secret)) {
        $errorData = [
            'success' => false,
            'error' => 'PayPal credentials are empty',
            'http_code' => 0,
            'response' => null,
        ];
        error_log("PAYPAL TOKEN ERROR: Client ID or Secret is empty. Client ID length: " . strlen($clientId) . ", Secret length: " . strlen($secret));
        return $errorData;
    }
    
    // Initialize cURL
    $ch = curl_init($url);
    
    if ($ch === false) {
        $errorData = [
            'success' => false,
            'error' => 'Failed to initialize cURL',
            'http_code' => 0,
            'response' => null,
        ];
        error_log("PAYPAL TOKEN ERROR: Failed to initialize cURL handle.");
        return $errorData;
    }
    
    // Configure cURL options
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_USERPWD => $clientId . ':' . $secret,
        CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
            'Accept-Language: en_US',
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    
    // Execute request
    $responseBody = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    curl_close($ch);
    
    // Log errors only (not successful requests)
    if ($httpCode !== 200 || $curlError) {
        error_log("PAYPAL TOKEN ERROR: HTTP $httpCode, CURL: " . ($curlError ?: 'None'));
    }
    
    // Log to database if Payment model is available
    try {
        require_once __DIR__ . '/../models/Payment.php';
        $paymentModel = new Payment();
        
        $logData = [
            'service_name' => 'paypal',
            'user_id' => null,
            'course_id' => null,
            'transaction_id' => null,
            'request_type' => 'oauth_token',
            'request_data' => json_encode([
                'url' => $url,
                'method' => 'POST',
                'grant_type' => 'client_credentials',
                'client_id_length' => strlen($clientId),
            ]),
            'response_data' => json_encode([
                'http_code' => $httpCode,
                'curl_error' => $curlError ?: null,
                'curl_errno' => $curlErrno ?: null,
                'response_body' => $responseBody ?: null,
            ]),
            'status_code' => $httpCode ?: 0,
            'status' => ($httpCode === 200 && !$curlError) ? 'success' : 'error',
            'error_message' => $curlError ?: ($httpCode !== 200 ? "HTTP $httpCode" : null),
        ];
        
        $paymentModel->logThirdParty($logData);
    } catch (Exception $e) {
        error_log("Failed to log PayPal token request to database: " . $e->getMessage());
    }
    
    // Handle cURL errors
    if ($curlError) {
        $errorData = [
            'success' => false,
            'error' => 'cURL Error: ' . $curlError,
            'http_code' => 0,
            'response' => null,
            'curl_errno' => $curlErrno,
        ];
        return $errorData;
    }
    
    // Handle HTTP errors
    if ($httpCode !== 200) {
        $errorData = [
            'success' => false,
            'error' => 'HTTP ' . $httpCode,
            'http_code' => $httpCode,
            'response' => $responseBody,
        ];
        
        // Try to parse error response
        $errorJson = json_decode($responseBody, true);
        if ($errorJson) {
            if (isset($errorJson['error'])) {
                $errorData['error'] = $errorJson['error'];
            }
            if (isset($errorJson['error_description'])) {
                $errorData['error_description'] = $errorJson['error_description'];
            }
            $errorData['response'] = $errorJson;
        }
        
        return $errorData;
    }
    
    // Parse JSON response
    $data = json_decode($responseBody, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $errorData = [
            'success' => false,
            'error' => 'Invalid JSON response: ' . json_last_error_msg(),
            'http_code' => $httpCode,
            'response' => $responseBody,
        ];
        error_log("PAYPAL TOKEN JSON ERROR: " . json_last_error_msg());
        return $errorData;
    }
    
    // Check for access token
    if (!isset($data['access_token'])) {
        $errorMessage = isset($data['error']) ? $data['error'] : 'No access_token in response';
        $errorDescription = isset($data['error_description']) ? $data['error_description'] : 'Unknown error';
        
        $errorData = [
            'success' => false,
            'error' => $errorMessage,
            'error_description' => $errorDescription,
            'http_code' => $httpCode,
            'response' => $data,
        ];
        error_log("PAYPAL TOKEN ERROR: $errorMessage - $errorDescription");
        return $errorData;
    }
    
    // Success
    
    return [
        'success' => true,
        'token' => $data['access_token'],
        'expires_in' => $data['expires_in'] ?? null,
        'token_type' => $data['token_type'] ?? 'Bearer',
    ];
}

/**
 * Create PayPal order
 * 
 * @param array $course Course data
 * @param array $user User data
 * @return array Order result with success flag, order_id, approve_url, or error details
 */
function paypal_create_order($course, $user) {
    // Get access token
    $tokenResult = paypal_get_access_token();
    
    if (!$tokenResult['success']) {
        return [
            'success' => false,
            'error' => 'Failed to get access token: ' . ($tokenResult['error'] ?? 'Unknown error'),
            'error_type' => 'oauth_error',
            'http_code' => $tokenResult['http_code'] ?? 0,
            'response' => $tokenResult['response'] ?? null,
            'token_error_details' => $tokenResult,
        ];
    }
    
    $accessToken = $tokenResult['token'];
    $url = PAYPAL_API_BASE . '/v2/checkout/orders';
    
    $orderData = [
        'intent' => 'CAPTURE',
        'purchase_units' => [
            [
                'amount' => [
                    'currency_code' => PAYPAL_CURRENCY,
                    'value' => number_format($course['price'], 2, '.', ''),
                ],
                'description' => mb_substr(strip_tags($course['description'] ?? ''), 0, 127),
            ],
        ],
        'application_context' => [
            'return_url' => BASE_URL . PAYPAL_SUCCESS_URL,
            'cancel_url' => BASE_URL . PAYPAL_CANCEL_URL . '?course_id=' . $course['id'],
            'brand_name' => SITE_NAME,
            'user_action' => 'PAY_NOW',
        ],
    ];
    
    $ch = curl_init($url);
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($orderData),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json',
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("PayPal create order cURL error: $error");
        return [
            'success' => false,
            'error' => 'cURL Error: ' . $error,
            'error_type' => 'curl_error',
            'http_code' => 0,
            'response' => null,
        ];
    }
    
    $data = json_decode($response, true);
    
    if ($httpCode === 201 && isset($data['id']) && isset($data['status']) && $data['status'] === 'CREATED') {
        // Find approve URL in links
        $approveUrl = null;
        if (isset($data['links'])) {
            foreach ($data['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    $approveUrl = $link['href'];
                    break;
                }
            }
        }
        
        return [
            'success' => true,
            'order_id' => $data['id'],
            'status' => $data['status'],
            'approve_url' => $approveUrl,
            'response' => $data,
        ];
    }
    
    // Parse error details
    $errorMessage = 'Unknown error';
    $errorName = null;
    $errorDetails = null;
    
    if (isset($data['name'])) {
        $errorName = $data['name'];
        $errorMessage = $data['name'];
    }
    
    if (isset($data['message'])) {
        $errorMessage = $data['message'];
    }
    
    if (isset($data['details'])) {
        $errorDetails = $data['details'];
    }
    
    error_log("PayPal create order error: HTTP $httpCode - $errorName: $errorMessage");
    
    return [
        'success' => false,
        'error' => $errorMessage,
        'error_name' => $errorName,
        'error_details' => $errorDetails,
        'http_code' => $httpCode,
        'response' => $data,
    ];
}

/**
 * Capture PayPal order
 * 
 * @param string $accessToken PayPal OAuth access token
 * @param string $orderId PayPal order ID
 * @return array Structured result with success, http_code, curl_error, raw_body, and data
 */
function paypal_capture_order($accessToken, $orderId) {
    // Verify configuration
    if (!defined('PAYPAL_API_BASE')) {
        error_log("PAYPAL CAPTURE ERROR: PAYPAL_API_BASE not defined");
        return [
            'success' => false,
            'http_code' => 0,
            'curl_error' => 'Configuration missing: PAYPAL_API_BASE',
            'raw_body' => null,
            'data' => null,
        ];
    }
    
    // Build URL - ensure we're using sandbox
    $url = PAYPAL_API_BASE . '/v2/checkout/orders/' . urlencode($orderId) . '/capture';
    
    // Verify sandbox URL
    if (PAYPAL_MODE === 'sandbox' && strpos($url, 'sandbox') === false) {
        error_log("PAYPAL CAPTURE WARNING: PAYPAL_MODE is 'sandbox' but URL doesn't contain 'sandbox': $url");
    }
    
    // Prepare request payload (empty JSON object)
    $requestPayload = '{}';
    
    // Initialize cURL
    $ch = curl_init($url);
    
    if ($ch === false) {
        error_log("PAYPAL CAPTURE ERROR: Failed to initialize cURL handle");
        return [
            'success' => false,
            'http_code' => 0,
            'curl_error' => 'Failed to initialize cURL',
            'raw_body' => null,
            'data' => null,
        ];
    }
    
    // Configure cURL options
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $requestPayload,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    
    // Execute request
    $responseBody = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    curl_close($ch);
    
    // Log errors only (not successful requests)
    if ($httpCode !== 200 || $curlError) {
        error_log("PAYPAL CAPTURE ERROR: HTTP $httpCode, CURL: " . ($curlError ?: 'None'));
    }
    
    // Parse JSON response
    $decoded = null;
    $jsonError = null;
    
    if ($responseBody) {
        $decoded = json_decode($responseBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $jsonError = json_last_error_msg();
            error_log("PAYPAL CAPTURE JSON ERROR: $jsonError");
        }
    }
    
    // Log to database
    try {
        require_once __DIR__ . '/../models/Payment.php';
        $paymentModel = new Payment();
        
        $logData = [
            'service_name' => 'paypal',
            'user_id' => null,
            'course_id' => null,
            'transaction_id' => $orderId,
            'request_type' => 'capture_order',
            'request_data' => json_encode([
                'url' => $url,
                'method' => 'POST',
                'order_id' => $orderId,
                'request_payload' => $requestPayload,
            ]),
            'response_data' => json_encode([
                'http_code' => $httpCode,
                'curl_error' => $curlError ?: null,
                'curl_errno' => $curlErrno ?: null,
                'response_body' => $responseBody ?: null,
                'json_error' => $jsonError ?: null,
                'decoded_data' => $decoded,
            ]),
            'status_code' => $httpCode ?: 0,
            'status' => ($httpCode >= 200 && $httpCode < 300 && !$curlError && !$jsonError) ? 'success' : 'error',
            'error_message' => $curlError ?: ($jsonError ? "JSON Error: $jsonError" : ($httpCode < 200 || $httpCode >= 300 ? "HTTP $httpCode" : null)),
        ];
        
        $paymentModel->logThirdParty($logData);
    } catch (Exception $e) {
        error_log("Failed to log PayPal capture request to database: " . $e->getMessage());
    }
    
    // Determine success: HTTP 200-299, no cURL error, valid JSON
    $success = ($httpCode >= 200 && $httpCode < 300 && empty($curlError) && $jsonError === null);
    
    // Return structured result
    return [
        'success' => $success,
        'http_code' => $httpCode,
        'curl_error' => $curlError ?: '',
        'raw_body' => $responseBody ?: '',
        'data' => $decoded,
    ];
}

/**
 * Get PayPal order details
 * 
 * @param string $orderId PayPal order ID
 * @return array|false Order data on success, false on failure
 */
function paypal_get_order($orderId) {
    $tokenResult = paypal_get_access_token();
    
    if (!$tokenResult['success']) {
        return false;
    }
    
    $accessToken = $tokenResult['token'];
    $url = PAYPAL_API_BASE . '/v2/checkout/orders/' . urlencode($orderId);
    
    $ch = curl_init($url);
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json',
        ],
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error || $httpCode !== 200) {
        error_log("PayPal get order error: HTTP $httpCode - $error");
        return false;
    }
    
    $data = json_decode($response, true);
    
    if (isset($data['id'])) {
        return $data;
    }
    
    return false;
}
