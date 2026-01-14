<?php
/**
 * PayPal Configuration (Sandbox Mode)
 * 
 * PayPal API keys and configuration settings for sandbox/test mode.
 * 
 * To get your sandbox credentials:
 * 1. Go to: https://developer.paypal.com/
 * 2. Log in or create an account
 * 3. Navigate to Dashboard > My Apps & Credentials
 * 4. Under "Sandbox" tab, create a new app or use existing
 * 5. Copy the Client ID and Secret
 */

// PayPal Mode: 'sandbox' or 'live'
define('PAYPAL_MODE', 'sandbox');

// PayPal Sandbox API Credentials
// Get these from: https://developer.paypal.com/dashboard/applications/sandbox
define('PAYPAL_CLIENT_ID', 'Af1P43G0DEg2XcqIc6Gs1rstmob9Yyz4Hd7tUzgUDY_BkKcRlhNyYRxg5-zw9t2RuFyhTQeBvg4FLtQR');
define('PAYPAL_SECRET', 'EIY0AjK6RUfjzbWHJLXQGCZOAc5fZVClgp1dnzcipuKQoHYLWLmVosqqb1H5KXoRrZVcLsG1KQ6sfJX5');

// PayPal API Base URLs
if (PAYPAL_MODE === 'sandbox') {
    define('PAYPAL_API_BASE', 'https://api-m.sandbox.paypal.com');
    define('PAYPAL_WEB_BASE', 'https://www.sandbox.paypal.com');
} else {
    define('PAYPAL_API_BASE', 'https://api-m.paypal.com');
    define('PAYPAL_WEB_BASE', 'https://www.paypal.com');
}

// Currency (use EUR or USD - choose one and use consistently)
define('PAYPAL_CURRENCY', 'EUR');

// Success/Cancel URLs (relative to BASE_URL)
define('PAYPAL_SUCCESS_URL', 'checkout/paypal-success');
define('PAYPAL_CANCEL_URL', 'checkout/paypal-cancel');

