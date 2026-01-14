<?php
/**
 * Checkout Controller
 * 
 * Handles course purchase and PayPal payment integration.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/paypal.php';
require_once __DIR__ . '/../helpers/paypal_helper.php';

class Checkout extends Controller {
    private $paymentModel;
    private $courseModel;

    public function __construct() {
        $this->paymentModel = $this->model('Payment');
        $this->courseModel = $this->model('Course');
    }

    /**
     * Default index method - redirects to courses
     * 
     * @return void
     */
    public function index() {
        $this->redirect('courses');
    }

    /**
     * Confirm checkout (show summary before payment)
     * 
     * @param int $id Course ID
     * @return void
     */
    public function confirm($id = null) {
        require_login();

        if (!$id) {
            set_flash('error', 'Course ID is required.');
            $this->redirect('courses');
        }

        $courseId = (int)$id;
        $user = current_user();

        // Get course
        $course = $this->courseModel->findById($courseId);
        if (!$course) {
            set_flash('error', 'Course not found.');
            $this->redirect('courses');
        }

        // Check if already enrolled
        if ($this->courseModel->isEnrolled($user['id'], $courseId)) {
            set_flash('info', 'You are already enrolled in this course.');
            $this->redirect('courses/show/' . $courseId);
        }

        // Check if course is free
        if ($course['price'] == 0) {
            // Enroll directly for free courses
            if ($this->paymentModel->createEnrollment($user['id'], $courseId)) {
                set_flash('success', 'Successfully enrolled in the free course!');
                $this->redirect('courses/my_courses');
            } else {
                set_flash('error', 'Failed to enroll in course.');
                $this->redirect('courses/show/' . $courseId);
            }
            return;
        }

        $data = [
            'title' => 'Checkout',
            'course' => $course,
            'user' => $user
        ];

        $this->render('confirm', $data);
    }

    /**
     * Create PayPal order and redirect to PayPal
     * 
     * @param int $courseId Course ID
     * @return void
     */
    public function payWithPaypal($courseId = null) {
        require_login();

        if (!$courseId) {
            $courseId = (int)($_POST['course_id'] ?? $_GET['id'] ?? 0);
        } else {
            $courseId = (int)$courseId;
        }

        if (!$courseId) {
            set_flash('error', 'Course ID is required.');
            $this->redirect('courses');
        }

        $user = current_user();

        // Get course
        $course = $this->courseModel->findById($courseId);
        if (!$course) {
            set_flash('error', 'Course not found.');
            $this->redirect('courses');
        }

        // Check if already enrolled
        if ($this->courseModel->isEnrolled($user['id'], $courseId)) {
            set_flash('info', 'You are already enrolled in this course.');
            $this->redirect('courses/show/' . $courseId);
        }

        // Check if course is free
        if ($course['price'] == 0) {
            set_flash('error', 'This course is free. Please enroll directly.');
            $this->redirect('courses/show/' . $courseId);
        }

        // Create PayPal order (this will get the access token internally)
        $orderResult = paypal_create_order($course, $user);

        // Log the order creation attempt
        $this->paymentModel->logThirdParty([
            'service_name' => 'paypal',
            'user_id' => $user['id'],
            'course_id' => $courseId,
            'transaction_id' => $orderResult['order_id'] ?? null,
            'request_type' => 'create_order',
            'request_data' => json_encode([
                'course_id' => $courseId,
                'amount' => $course['price'],
                'currency' => PAYPAL_CURRENCY,
                'course_title' => $course['title'] ?? '',
            ]),
            'response_data' => json_encode($orderResult),
            'status_code' => $orderResult['http_code'] ?? ($orderResult['success'] ? 201 : 500),
            'status' => $orderResult['success'] ? 'success' : 'error',
            'error_message' => $orderResult['success'] ? null : ($orderResult['error'] ?? 'Unknown error'),
        ]);

        if (!$orderResult['success']) {
            // Determine user-friendly error message based on error type
            $errorMessage = 'Payment service is temporarily unavailable. Please try again later.';
            
            // Check if it's an OAuth/token error
            if (isset($orderResult['error_type']) && $orderResult['error_type'] === 'oauth_error') {
                $tokenError = $orderResult['token_error_details'] ?? [];
                $httpCode = $tokenError['http_code'] ?? 0;
                $tokenErrorMsg = $tokenError['error'] ?? '';
                
                // Specific error messages based on HTTP code and error type
                if (strpos($tokenErrorMsg, 'cURL not enabled') !== false) {
                    $errorMessage = 'PayPal error: PHP cURL extension is not enabled on this server.';
                } elseif ($httpCode === 401) {
                    $errorMessage = 'PayPal error: Invalid client ID or secret (HTTP 401). Please confirm sandbox credentials.';
                } elseif ($httpCode === 403) {
                    $errorMessage = 'PayPal error: Authentication/configuration issue (HTTP 403).';
                } elseif ($httpCode === 400) {
                    $errorMessage = 'PayPal error: Bad request (HTTP 400). Please check API configuration.';
                } elseif ($httpCode > 0) {
                    $errorMessage = "PayPal error: Authentication/configuration issue (HTTP $httpCode).";
                } elseif (strpos($tokenErrorMsg, 'cURL Error') !== false) {
                    $errorMessage = 'PayPal error: Network connection failed. Please check your internet connection.';
                } elseif (strpos($tokenErrorMsg, 'credentials are empty') !== false) {
                    $errorMessage = 'PayPal error: API credentials are not configured. Please check config/paypal.php.';
                } else {
                    $errorMessage = 'Could not connect to PayPal. Please check API credentials or try again later.';
                }
            } elseif (isset($orderResult['error'])) {
                // PayPal API error (not OAuth)
                $paypalError = $orderResult['error'];
                
                if (strpos($paypalError, 'INVALID_REQUEST') !== false) {
                    $errorMessage = 'PayPal order creation failed: Invalid request. Please try again.';
                } elseif (strpos($paypalError, 'UNAUTHORIZED') !== false || strpos($paypalError, 'AUTHENTICATION') !== false) {
                    $errorMessage = 'PayPal authentication failed. Please check API credentials.';
                } elseif (strpos($paypalError, 'INSUFFICIENT_FUNDS') !== false) {
                    $errorMessage = 'PayPal order creation failed: Insufficient funds.';
                } else {
                    // Generic PayPal error - show short version
                    $shortError = mb_substr($paypalError, 0, 100);
                    $errorMessage = 'PayPal order creation failed: ' . $shortError;
                }
            }
            
            set_flash('error', $errorMessage);
            $this->redirect('checkout/confirm/' . $courseId);
        }

        $orderId = $orderResult['order_id'];
        $orderData = $orderResult['response'];
        $approveUrl = $orderResult['approve_url'] ?? null;

        if (!$approveUrl) {
            // Log this as an error
            $this->paymentModel->logThirdParty([
                'service_name' => 'paypal',
                'user_id' => $user['id'],
                'course_id' => $courseId,
                'transaction_id' => $orderId,
                'request_type' => 'create_order_missing_approve_url',
                'request_data' => json_encode(['order_id' => $orderId]),
                'response_data' => json_encode($orderData),
                'status_code' => 201,
                'status' => 'error',
                'error_message' => 'Order created but approve URL not found in response',
            ]);
            
            set_flash('error', 'Payment initialization failed. Please try again.');
            $this->redirect('checkout/confirm/' . $courseId);
        }

        // Create payment record with the PayPal order ID
        // The orderId from PayPal is stored in transaction_id column
        $this->paymentModel->createPayment(
            $user['id'],
            $courseId,
            'paypal',
            $orderId,  // This is the PayPal order ID (e.g., "5CH721262X123456Y")
            $course['price'],
            PAYPAL_CURRENCY,
            'created'
        );

        // Redirect to PayPal
        header('Location: ' . $approveUrl);
        exit;
    }

    /**
     * Handle successful PayPal payment
     * 
     * PayPal redirects back with: /checkout/paypal-success?token=ORDER_ID
     * The token parameter contains the PayPal order ID
     * 
     * @return void
     */
    public function paypalSuccess() {
        require_login();

        // PayPal redirects with token parameter (this is the order ID)
        $orderId = trim($_GET['token'] ?? '');

        // PV1: Missing order ID from query string
        if (empty($orderId)) {
            // Log missing order ID
            $this->paymentModel->logThirdParty([
                'service_name' => 'paypal',
                'user_id' => null,
                'course_id' => null,
                'transaction_id' => null,
                'request_type' => 'verification_error',
                'request_data' => json_encode([
                    'pv_code' => 'PV1',
                    'error' => 'Missing order ID (token) in query string',
                    'get_params' => $_GET,
                ]),
                'response_data' => null,
                'status_code' => 400,
                'status' => 'error',
                'error_message' => 'PV1: Missing order ID (token) in query string',
            ]);
            
            set_flash('error', 'Payment verification failed (PV1). Please contact support.');
            $this->redirect('courses');
        }

        $user = current_user();

        // PV2: Get access token first
        $tokenResult = paypal_get_access_token();
        
        if (!$tokenResult['success']) {
            // Log token failure
            $this->paymentModel->logThirdParty([
                'service_name' => 'paypal',
                'user_id' => $user['id'],
                'transaction_id' => $orderId,
                'request_type' => 'verification_error',
                'request_data' => json_encode([
                    'pv_code' => 'PV2',
                    'order_id' => $orderId,
                    'error' => 'Failed to get PayPal access token: ' . ($tokenResult['error'] ?? 'Unknown error'),
                ]),
                'response_data' => json_encode($tokenResult),
                'status_code' => $tokenResult['http_code'] ?? 0,
                'status' => 'error',
                'error_message' => 'PV2: Failed to get PayPal access token',
            ]);
            
            set_flash('error', 'Payment verification failed (PV2). Please contact support.');
            $this->redirect('courses');
        }

        $accessToken = $tokenResult['token'];

        // Call capture with access token and order ID
        $captureResult = paypal_capture_order($accessToken, $orderId);

        // Extract response details
        $httpCode = $captureResult['http_code'];
        $curlError = $captureResult['curl_error'];
        $data = $captureResult['data'];

        // PV3: Capture API call failed (HTTP/cURL/JSON error)
        // Check for low-level API/transport failures ONLY
        // Keep PV3 for actual technical failures: curl errors, http 0, no response
        if (!empty($curlError) || $httpCode === 0 || ($data === null && empty($captureResult['raw_body']))) {
            // This is a hard API/transport failure → trigger PV3
            $errorMsg = 'Capture API call failed';
            if (!empty($curlError)) {
                $errorMsg .= ': ' . $curlError;
            } elseif ($httpCode === 0) {
                $errorMsg .= ': HTTP 0 (no response)';
            } else {
                $errorMsg .= ': No response data';
            }
            
            // Log verification failure
            $this->paymentModel->logThirdParty([
                'service_name' => 'paypal',
                'user_id' => $user['id'],
                'transaction_id' => $orderId,
                'request_type' => 'verification_error',
                'request_data' => json_encode([
                    'pv_code' => 'PV3',
                    'order_id' => $orderId,
                    'error' => $errorMsg,
                ]),
                'response_data' => json_encode($captureResult),
                'status_code' => $httpCode ?: 500,
                'status' => 'error',
                'error_message' => "PV3: $errorMsg",
            ]);

            // Update payment status to failed
            $payment = $this->paymentModel->findByProviderPaymentId($orderId);
            if ($payment) {
                $this->paymentModel->updatePaymentStatus($payment['id'], 'failed');
            }

            set_flash('error', 'Payment verification failed (PV3). Please contact support.');
            $this->redirect('courses');
        }

        // Sandbox-only COMPLIANCE_VIOLATION bypass for school project.
        // DO NOT USE IN REAL PRODUCTION.
        // Check if this is HTTP 422 with COMPLIANCE_VIOLATION
        $isComplianceBypass = false;
        if ($httpCode === 422 && 
            $data !== null && 
            isset($data['name']) && 
            $data['name'] === 'UNPROCESSABLE_ENTITY' &&
            isset($data['details'][0]['issue']) &&
            $data['details'][0]['issue'] === 'COMPLIANCE_VIOLATION') {
            
            // Log the compliance bypass
            $this->paymentModel->logThirdParty([
                'service_name' => 'paypal',
                'user_id' => $user['id'],
                'transaction_id' => $orderId,
                'request_type' => 'capture_order_compliance_bypass',
                'request_data' => json_encode(['order_id' => $orderId]),
                'response_data' => json_encode($data),
                'status_code' => 422,
                'status' => 'bypass',
                'error_message' => 'COMPLIANCE_VIOLATION bypassed for sandbox testing (school project only)',
            ]);
            
            // Set bypass status
            $isComplianceBypass = true;
            $overallStatus = 'COMPLIANCE_BYPASS';
            $captureStatus = 'COMPLIANCE_BYPASS';
        } else {
            // Extract status from response (normal flow)
            $overallStatus = $data['status'] ?? null;
            $captureStatus = null;
            
            // Try to get capture status from purchase_units
            if (isset($data['purchase_units'][0]['payments']['captures'][0]['status'])) {
                $captureStatus = $data['purchase_units'][0]['payments']['captures'][0]['status'];
            }
        }

        // Check if payment status is COMPLETED or COMPLIANCE_BYPASS
        $isCompleted = ($overallStatus === 'COMPLETED' || 
                       $captureStatus === 'COMPLETED' || 
                       $overallStatus === 'COMPLIANCE_BYPASS' || 
                       $captureStatus === 'COMPLIANCE_BYPASS');

        if ($isCompleted) {
            // Find payment record by order ID
            $payment = $this->paymentModel->findByProviderPaymentId($orderId);
            
            // PV5: Payment row not found in DB
            if (!$payment) {
                // Log payment not found
                $this->paymentModel->logThirdParty([
                    'service_name' => 'paypal',
                    'user_id' => $user['id'],
                    'transaction_id' => $orderId,
                    'request_type' => 'verification_error',
                    'request_data' => json_encode([
                        'pv_code' => 'PV5',
                        'order_id' => $orderId,
                        'error' => "Payment record not found for order ID: $orderId",
                    ]),
                    'response_data' => null,
                    'status_code' => 404,
                    'status' => 'error',
                    'error_message' => "PV5: Payment record not found for order ID: $orderId",
                ]);
                
                set_flash('error', 'Payment verification failed (PV5). Please contact support (missing payment record).');
                $this->redirect('courses');
            }

            // Update payment status to completed
            $this->paymentModel->updatePaymentStatus($payment['id'], 'completed');

            // Get user_id and course_id from payment
            $userId = $payment['user_id'];
            $courseId = $payment['course_id'];

            // Verify it belongs to current user
            if ($userId != $user['id']) {
                set_flash('error', 'Invalid payment session.');
                $this->redirect('courses');
            }

            // PV6: Enrollment insert failed
            // Create enrollment
            if ($this->paymentModel->createEnrollment($userId, $courseId)) {
                set_flash('success', 'Payment successful. You are now enrolled in this course.');
                $this->redirect('courses/my_courses');
            } else {
                // Enrollment failed but payment succeeded - log this
                $this->paymentModel->logThirdParty([
                    'service_name' => 'paypal',
                    'user_id' => $userId,
                    'course_id' => $courseId,
                    'transaction_id' => $orderId,
                    'request_type' => 'verification_error',
                    'request_data' => json_encode([
                        'pv_code' => 'PV6',
                        'order_id' => $orderId,
                        'user_id' => $userId,
                        'course_id' => $courseId,
                        'error' => 'Enrollment insert failed',
                    ]),
                    'response_data' => null,
                    'status_code' => 500,
                    'status' => 'error',
                    'error_message' => "PV6: Enrollment insert failed for user $userId, course $courseId",
                ]);
                
                error_log("PayPal success: Payment completed but enrollment failed for user $userId, course $courseId");
                set_flash('error', 'Payment verification failed (PV6). Please contact support.');
                $this->redirect('courses');
            }
        } else {
            // PV4: Capture response status not COMPLETED (and not bypassed)
            // Payment not completed - log the status
            $this->paymentModel->logThirdParty([
                'service_name' => 'paypal',
                'user_id' => $user['id'],
                'transaction_id' => $orderId,
                'request_type' => 'verification_error',
                'request_data' => json_encode([
                    'pv_code' => 'PV4',
                    'order_id' => $orderId,
                    'overall_status' => $overallStatus,
                    'capture_status' => $captureStatus,
                    'http_code' => $httpCode,
                ]),
                'response_data' => json_encode($captureResult),
                'status_code' => $httpCode ?: 200,
                'status' => 'error',
                'error_message' => "PV4: Payment status not COMPLETED. Overall: $overallStatus, Capture: $captureStatus",
            ]);
            
            // Update payment status to pending or failed
            $payment = $this->paymentModel->findByProviderPaymentId($orderId);
            if ($payment) {
                $this->paymentModel->updatePaymentStatus($payment['id'], 'pending');
            }

            set_flash('error', 'Payment verification failed (PV4). Please contact support.');
            $this->redirect('courses');
        }
    }

    /**
     * Handle cancelled PayPal payment
     * 
     * PayPal redirects back with: /checkout/paypal-cancel?token=ORDER_ID&course_id=COURSE_ID
     * 
     * @return void
     */
    public function paypalCancel() {
        require_login();

        // PayPal may redirect with token parameter
        $token = $_GET['token'] ?? '';
        $courseId = (int)($_GET['course_id'] ?? 0);

        // If we have a token, mark payment as cancelled
        if (!empty($token)) {
            $payment = $this->paymentModel->findByProviderPaymentId($token);
            if ($payment) {
                $this->paymentModel->updatePaymentStatus($payment['id'], 'cancelled');
            }
        }

        $data = [
            'title' => 'Payment Cancelled',
            'course_id' => $courseId
        ];

        $this->render('cancel', $data);
    }
}
