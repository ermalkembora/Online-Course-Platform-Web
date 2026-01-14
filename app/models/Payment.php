<?php
/**
 * Payment Model
 * 
 * Handles all database operations related to payments and third-party logs.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/auth_helper.php';

class Payment {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Create a payment record
     * 
     * @param array $data Payment data (user_id, course_id, provider_payment_id, amount, currency, status, provider)
     * @return int|false Payment ID on success, false on failure
     */
    public function createPayment($userId, $courseId, $provider, $providerPaymentId, $amount, $currency, $status) {
        try {
            // Use payment_method column to store provider (e.g., 'paypal', 'stripe')
            $this->db->query("
                INSERT INTO payments (user_id, course_id, transaction_id, amount, currency, status, payment_method) 
                VALUES (:user_id, :course_id, :transaction_id, :amount, :currency, :status, :payment_method)
            ")
            ->bind(':user_id', $userId)
            ->bind(':course_id', $courseId)
            ->bind(':transaction_id', $providerPaymentId)
            ->bind(':amount', $amount)
            ->bind(':currency', $currency)
            ->bind(':status', $status)
            ->bind(':payment_method', $provider);

            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            }

            return false;
        } catch (Exception $e) {
            error_log("Payment creation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a payment record (legacy method for backward compatibility)
     * 
     * @param array $data Payment data
     * @return int|false Payment ID on success, false on failure
     */
    public function create($data) {
        $provider = $data['provider'] ?? $data['payment_method'] ?? 'paypal';
        $providerPaymentId = $data['provider_payment_id'] ?? $data['transaction_id'] ?? '';
        
        return $this->createPayment(
            $data['user_id'],
            $data['course_id'],
            $provider,
            $providerPaymentId,
            $data['amount'],
            $data['currency'] ?? 'EUR',
            $data['status'] ?? 'created'
        );
    }

    /**
     * Update payment status
     * 
     * @param string $transactionId Provider payment ID (transaction_id)
     * @param string $status New status
     * @return bool Success status
     */
    public function updateStatus($transactionId, $status) {
        $this->db->query("
            UPDATE payments 
            SET status = :status 
            WHERE transaction_id = :transaction_id
        ")
        ->bind(':status', $status)
        ->bind(':transaction_id', $transactionId);

        return $this->db->execute();
    }

    /**
     * Update payment status by ID
     * 
     * @param int $paymentId Payment ID
     * @param string $status New status
     * @return bool Success status
     */
    public function updatePaymentStatus($paymentId, $status) {
        $this->db->query("
            UPDATE payments 
            SET status = :status 
            WHERE id = :id
        ")
        ->bind(':status', $status)
        ->bind(':id', $paymentId);

        return $this->db->execute();
    }

    /**
     * Find payment by transaction ID (provider payment ID)
     * 
     * @param string $transactionId Transaction ID
     * @return array|false Payment data or false
     */
    public function findByTransactionId($transactionId) {
        $this->db->query("
            SELECT * FROM payments 
            WHERE transaction_id = :transaction_id
        ")
        ->bind(':transaction_id', $transactionId);

        return $this->db->single();
    }

    /**
     * Find payment by provider payment ID
     * 
     * @param string $providerPaymentId Provider payment ID (e.g., PayPal order ID)
     * @return array|false Payment data or false
     */
    public function findByProviderPaymentId($providerPaymentId) {
        return $this->findByTransactionId($providerPaymentId);
    }

    /**
     * Create enrollment
     * 
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return bool Success status
     */
    public function createEnrollment($userId, $courseId) {
        try {
            // Check if already enrolled
            $this->db->query("
                SELECT id FROM enrollments 
                WHERE user_id = :user_id AND course_id = :course_id
            ")
            ->bind(':user_id', $userId)
            ->bind(':course_id', $courseId);

            if ($this->db->single()) {
                return true; // Already enrolled
            }

            // Create enrollment
            $this->db->query("
                INSERT INTO enrollments (user_id, course_id) 
                VALUES (:user_id, :course_id)
            ")
            ->bind(':user_id', $userId)
            ->bind(':course_id', $courseId);

            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Enrollment creation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log third-party API communication
     * 
     * @param array $data Log data
     * @return int|false Log ID on success, false on failure
     */
    public function logThirdParty($data) {
        try {
            $this->db->query("
                INSERT INTO third_party_logs (
                    service_name, user_id, course_id, transaction_id, 
                    request_type, request_data, response_data, 
                    status_code, status, error_message, ip_address
                ) 
                VALUES (
                    :service_name, :user_id, :course_id, :transaction_id,
                    :request_type, :request_data, :response_data,
                    :status_code, :status, :error_message, :ip_address
                )
            ")
            ->bind(':service_name', $data['service_name'] ?? 'paypal')
            ->bind(':user_id', $data['user_id'] ?? null)
            ->bind(':course_id', $data['course_id'] ?? null)
            ->bind(':transaction_id', $data['transaction_id'] ?? null)
            ->bind(':request_type', $data['request_type'] ?? null)
            ->bind(':request_data', $data['request_data'] ?? null)
            ->bind(':response_data', $data['response_data'] ?? null)
            ->bind(':status_code', $data['status_code'] ?? null)
            ->bind(':status', $data['status'] ?? null)
            ->bind(':error_message', $data['error_message'] ?? null)
            ->bind(':ip_address', $data['ip_address'] ?? (function_exists('get_client_ip') ? get_client_ip() : '0.0.0.0'));

            if ($this->db->execute()) {
                return $this->db->lastInsertId();
            }

            return false;
        } catch (Exception $e) {
            error_log("Third-party log error: " . $e->getMessage());
            return false;
        }
    }

}


