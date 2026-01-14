<?php
/**
 * User Model
 * 
 * Handles all database operations related to users.
 * 
 * REQUIRED DATABASE TABLES:
 * 
 * CREATE TABLE IF NOT EXISTS users (
 *     id INT AUTO_INCREMENT PRIMARY KEY,
 *     email VARCHAR(255) NOT NULL UNIQUE,
 *     password VARCHAR(255) NOT NULL,
 *     first_name VARCHAR(100),
 *     last_name VARCHAR(100),
 *     email_verified TINYINT(1) DEFAULT 0,
 *     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 *     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 * 
 * CREATE TABLE IF NOT EXISTS email_verifications (
 *     id INT AUTO_INCREMENT PRIMARY KEY,
 *     user_id INT NOT NULL,
 *     code VARCHAR(10) NOT NULL,
 *     expires_at DATETIME NOT NULL,
 *     used TINYINT(1) DEFAULT 0,
 *     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
 *     FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 *     INDEX idx_user_code (user_id, code),
 *     INDEX idx_expires (expires_at)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 * 
 * ALTER TABLE email_verifications ADD COLUMN IF NOT EXISTS used TINYINT(1) DEFAULT 0;
 */

require_once __DIR__ . '/../../config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Find user by ID
     * 
     * @param int $id User ID
     * @return array|false User data or false if not found
     */
    public function findById($id) {
        $this->db->query("SELECT * FROM users WHERE id = :id")
                 ->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Get user by ID (alias for findById)
     * 
     * @param int $id User ID
     * @return array|false User data or false if not found
     */
    public function getUserById($id) {
        return $this->findById($id);
    }

    /**
     * Find user by email
     * 
     * @param string $email Email address
     * @return array|false User data or false if not found
     */
    public function findByEmail($email) {
        $this->db->query("SELECT * FROM users WHERE email = :email")
                 ->bind(':email', $email);
        return $this->db->single();
    }

    /**
     * Check if email exists
     * 
     * @param string $email Email address
     * @return bool True if email exists
     */
    public function emailExists($email) {
        $this->db->query("SELECT id FROM users WHERE email = :email")
                 ->bind(':email', $email);
        return $this->db->single() !== false;
    }

    /**
     * Create a new user
     * 
     * @param array $data User data (email, password, first_name, last_name)
     * @return int|false User ID on success, false on failure
     */
    public function create($data) {
        try {
            $this->db->beginTransaction();

            // Insert user
            $this->db->query("
                INSERT INTO users (email, password, first_name, last_name, email_verified) 
                VALUES (:email, :password, :first_name, :last_name, 0)
            ")
            ->bind(':email', $data['email'])
            ->bind(':password', $data['password'])
            ->bind(':first_name', $data['first_name'])
            ->bind(':last_name', $data['last_name'])
            ->execute();

            $userId = $this->db->lastInsertId();

            // Assign default 'user' role (if roles table exists)
            try {
                $this->db->query("
                    INSERT INTO user_roles (user_id, role_id) 
                    SELECT :user_id, id FROM roles WHERE name = 'user'
                ")
                ->bind(':user_id', $userId)
                ->execute();
            } catch (Exception $e) {
                // Roles table might not exist, that's okay
                error_log("Note: Could not assign default role: " . $e->getMessage());
            }

            $this->db->commit();
            return $userId;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("User creation error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create email verification code
     * 
     * @param int $userId User ID
     * @param string $code 6-digit verification code
     * @param string $expiresAt Expiry datetime (Y-m-d H:i:s format)
     * @return bool Success status
     */
    public function createVerificationCode($userId, $code, $expiresAt) {
        // Check if $expiresAt is a SQL function (contains DATE_ADD) or a timestamp string
        $isRawSQL = (strpos($expiresAt, 'DATE_ADD') !== false || strpos($expiresAt, 'NOW()') !== false);
        
        // Try to insert with 'used' column first
        try {
            if ($isRawSQL) {
                // Use raw SQL for date calculation
                $this->db->query("
                    INSERT INTO email_verifications (user_id, code, expires_at, used) 
                    VALUES (:user_id, :code, {$expiresAt}, 0)
                ")
                ->bind(':user_id', $userId)
                ->bind(':code', $code);
            } else {
                // Use parameter binding for timestamp string
                $this->db->query("
                    INSERT INTO email_verifications (user_id, code, expires_at, used) 
                    VALUES (:user_id, :code, :expires_at, 0)
                ")
                ->bind(':user_id', $userId)
                ->bind(':code', $code)
                ->bind(':expires_at', $expiresAt);
            }
            
            return $this->db->execute();
        } catch (Exception $e) {
            // Fallback: if 'used' column doesn't exist, insert without it
            if ($isRawSQL) {
                // Use raw SQL for date calculation
                $this->db->query("
                    INSERT INTO email_verifications (user_id, code, expires_at) 
                    VALUES (:user_id, :code, {$expiresAt})
                ")
                ->bind(':user_id', $userId)
                ->bind(':code', $code);
            } else {
                // Use parameter binding for timestamp string
                $this->db->query("
                    INSERT INTO email_verifications (user_id, code, expires_at) 
                    VALUES (:user_id, :code, :expires_at)
                ")
                ->bind(':user_id', $userId)
                ->bind(':code', $code)
                ->bind(':expires_at', $expiresAt);
            }
            
            return $this->db->execute();
        }
    }

    /**
     * Find valid verification code for user
     * 
     * @param int $userId User ID
     * @param string $code Verification code
     * @return array|false Verification record or false if not found
     */
    public function findValidVerificationCode($userId, $code) {
        // Clean the code - remove any spaces and ensure it's a string
        $code = trim($code);
        $code = preg_replace('/[^0-9]/', '', $code);
        
        // First try with 'used' column
        try {
            $this->db->query("
                SELECT * FROM email_verifications
                WHERE user_id = :user_id
                  AND code = :code
                  AND used = 0
                  AND expires_at >= NOW()
                ORDER BY created_at DESC
                LIMIT 1
            ");
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':code', $code);
            $result = $this->db->single();
            
            if ($result) {
                return $result;
            }
        } catch (Exception $e) {
            // 'used' column doesn't exist, try without it
        }
        
        // Fallback: query without 'used' column
        $this->db->query("
            SELECT * FROM email_verifications
            WHERE user_id = :user_id
              AND code = :code
              AND expires_at >= NOW()
              AND (verified_at IS NULL OR verified_at = '')
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':code', $code);
        $result = $this->db->single();
        
        return $result ? $result : false;
    }

    /**
     * Mark verification code as used
     * 
     * @param int $id Verification record ID
     * @return bool Success status
     */
    public function markVerificationCodeUsed($id) {
        // Try to update with 'used' column first
        try {
            $this->db->query("UPDATE email_verifications SET used = 1, verified_at = NOW() WHERE id = :id");
            $this->db->bind(':id', $id);
            return $this->db->execute();
        } catch (Exception $e) {
            // Fallback: if 'used' column doesn't exist, just set verified_at
            $this->db->query("UPDATE email_verifications SET verified_at = NOW() WHERE id = :id");
            $this->db->bind(':id', $id);
            return $this->db->execute();
        }
    }

    /**
     * Mark user email as verified
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function markEmailVerified($userId) {
        $this->db->query("UPDATE users SET email_verified = 1 WHERE id = :id");
        $this->db->bind(':id', $userId);
        return $this->db->execute();
    }

    /**
     * Update user
     * 
     * @param int $id User ID
     * @param array $data Data to update
     * @return bool Success status
     */
    public function update($id, $data) {
        $fields = [];
        $allowedFields = ['email', 'password', 'first_name', 'last_name', 'profile_picture', 'email_verified', 'last_activity'];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "{$key} = :{$key}";
            }
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = NOW()";
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";

        $this->db->query($sql)->bind(':id', $id);

        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $this->db->bind(":{$key}", $value);
            }
        }

        return $this->db->execute();
    }

    /**
     * Get user roles
     * 
     * @param int $userId User ID
     * @return array Array of role names
     */
    public function getRoles($userId) {
        try {
            $this->db->query("
                SELECT r.name 
                FROM roles r
                INNER JOIN user_roles ur ON r.id = ur.role_id
                WHERE ur.user_id = :user_id
            ")
            ->bind(':user_id', $userId);

            $roles = $this->db->resultSet();
            return array_column($roles, 'name');
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Log login attempt
     * 
     * @param int|null $userId User ID (null if email not found)
     * @param string $email Email used in attempt
     * @param string $ipAddress IP address
     * @param string|null $userAgent User agent string
     * @param bool $success Whether login was successful
     * @return bool Success status
     */
    public function logLoginAttempt($userId, $email, $ipAddress, $userAgent = null, $success = false) {
        try {
            $this->db->query("
                INSERT INTO login_attempts (user_id, email, ip_address, user_agent, success) 
                VALUES (:user_id, :email, :ip_address, :user_agent, :success)
            ")
            ->bind(':user_id', $userId)
            ->bind(':email', $email)
            ->bind(':ip_address', $ipAddress)
            ->bind(':user_agent', $userAgent)
            ->bind(':success', $success ? 1 : 0);

            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error logging login attempt: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if account is locked out
     * 
     * @param string $email Email address
     * @param string $ipAddress IP address
     * @return array|false Returns array with 'locked' => true and 'unlock_time' if locked, false otherwise
     */
    public function isLockedOut($email, $ipAddress) {
        try {
            // Check failed attempts in the last LOCKOUT_DURATION seconds
            $this->db->query("
                SELECT COUNT(*) as failed_count, MAX(attempted_at) as last_attempt
                FROM login_attempts 
                WHERE (email = :email OR ip_address = :ip_address)
                AND success = 0
                AND attempted_at > DATE_SUB(NOW(), INTERVAL :lockout_duration SECOND)
            ")
            ->bind(':email', $email)
            ->bind(':ip_address', $ipAddress)
            ->bind(':lockout_duration', defined('LOCKOUT_DURATION') ? LOCKOUT_DURATION : 1800);

            $result = $this->db->single();

            if ($result && (int)$result['failed_count'] >= (defined('MAX_LOGIN_ATTEMPTS') ? MAX_LOGIN_ATTEMPTS : 7)) {
                // Calculate unlock time
                $lastAttempt = strtotime($result['last_attempt']);
                $unlockTime = $lastAttempt + (defined('LOCKOUT_DURATION') ? LOCKOUT_DURATION : 1800);
                $minutesRemaining = ceil(($unlockTime - time()) / 60);

                return [
                    'locked' => true,
                    'unlock_time' => $unlockTime,
                    'minutes_remaining' => max(0, $minutesRemaining)
                ];
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Clear failed login attempts for a given email/IP
     *
     * @param string $email Email address used in attempts
     * @param string $ipAddress IP address used in attempts
     * @return bool Success status
     */
    public function clearLoginAttempts($email, $ipAddress) {
        try {
            // Delete ONLY failed attempts (success = 0) for this email OR IP
            $this->db->query("
                DELETE FROM login_attempts
                WHERE (email = :email OR ip_address = :ip_address)
                  AND success = 0
            ")
            ->bind(':email', $email)
            ->bind(':ip_address', $ipAddress);

            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error clearing login attempts: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create remember-me token
     * 
     * @param int $userId User ID
     * @param string $token Plain token (will be hashed)
     * @param string $ipAddress IP address
     * @param string|null $userAgent User agent
     * @return bool Success status
     */
    public function createRememberToken($userId, $token, $ipAddress, $userAgent = null) {
        try {
            $hashedToken = hash('sha256', $token);
            $expiresAt = date('Y-m-d H:i:s', time() + (defined('REMEMBER_ME_LIFETIME') ? REMEMBER_ME_LIFETIME : 2592000));

            $this->db->query("
                INSERT INTO remember_tokens (user_id, token, expires_at) 
                VALUES (:user_id, :token, :expires_at)
            ")
            ->bind(':user_id', $userId)
            ->bind(':token', $hashedToken)
            ->bind(':expires_at', $expiresAt);

            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error creating remember token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find user by remember token
     * 
     * @param string $token Plain token (will be hashed for lookup)
     * @return array|false User data or false if not found or expired
     */
    public function findRememberToken($token) {
        try {
            $hashedToken = hash('sha256', $token);

            $this->db->query("
                SELECT u.* 
                FROM users u
                INNER JOIN remember_tokens rt ON u.id = rt.user_id
                WHERE rt.token = :token 
                AND rt.expires_at > NOW()
                LIMIT 1
            ")
            ->bind(':token', $hashedToken);

            return $this->db->single();
        } catch (Exception $e) {
            error_log("Error finding remember token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete remember-me token
     * 
     * @param string $token Plain token
     * @return bool Success status
     */
    public function deleteRememberToken($token) {
        try {
            $hashedToken = hash('sha256', $token);

            $this->db->query("
                DELETE FROM remember_tokens 
                WHERE token = :token
            ")
            ->bind(':token', $hashedToken);

            return $this->db->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get all users
     * 
     * @return array All users with their roles
     */
    public function getAllUsers() {
        try {
            $this->db->query("
                SELECT u.*, 
                       GROUP_CONCAT(r.name SEPARATOR ', ') as roles
                FROM users u
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                GROUP BY u.id
                ORDER BY u.created_at DESC
            ");

            return $this->db->resultSet();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Delete user
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function delete($userId) {
        try {
            $this->db->query("DELETE FROM users WHERE id = :id")
                     ->bind(':id', $userId);
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create password reset token
     * 
     * @param int $userId User ID
     * @param string $token Reset token
     * @param string $expiresAt Expiry datetime (Y-m-d H:i:s format or SQL expression like DATE_ADD(NOW(), INTERVAL 60 MINUTE))
     * @return bool Success status
     */
    public function createPasswordResetToken($userId, $token, $expiresAt) {
        // Check if $expiresAt is a SQL function (contains DATE_ADD) or a timestamp string
        $isRawSQL = (strpos($expiresAt, 'DATE_ADD') !== false || strpos($expiresAt, 'NOW()') !== false);
        
        // Trim token to ensure no whitespace
        $token = trim($token);
        
        try {
            if ($isRawSQL) {
                // Use raw SQL for date calculation (same pattern as createVerificationCode)
                $this->db->query("
                    INSERT INTO password_resets (user_id, token, expires_at) 
                    VALUES (:user_id, :token, {$expiresAt})
                ")
                ->bind(':user_id', $userId)
                ->bind(':token', $token);
            } else {
                // Use parameter binding for timestamp string
                $this->db->query("
                    INSERT INTO password_resets (user_id, token, expires_at) 
                    VALUES (:user_id, :token, :expires_at)
                ")
                ->bind(':user_id', $userId)
                ->bind(':token', $token)
                ->bind(':expires_at', $expiresAt);
            }
            
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error creating password reset token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Find valid password reset token
     * 
     * @param string $token Reset token
     * @return array|false Reset record or false if not found or invalid
     */
    public function findValidPasswordResetToken($token) {
        try {
            // Trim and validate token
            $token = trim($token);
            
            if (empty($token)) {
                return false;
            }

            // Check if token exists, is not used (used_at IS NULL), and not expired
            // Use database NOW() comparison to avoid timezone issues (same pattern as verification codes)
            $this->db->query("
                SELECT * FROM password_resets
                WHERE token = :token
                  AND used_at IS NULL
                  AND expires_at > NOW()
                ORDER BY created_at DESC
                LIMIT 1
            ");
            $this->db->bind(':token', $token);
            
            $result = $this->db->single();
            
            return $result ? $result : false;
        } catch (Exception $e) {
            error_log("Error finding password reset token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark password reset token as used
     * 
     * @param int $id Reset record ID
     * @return bool Success status
     */
    public function markPasswordResetTokenUsed($id) {
        try {
            $this->db->query("UPDATE password_resets SET used_at = NOW() WHERE id = :id");
            $this->db->bind(':id', $id);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error marking password reset token as used: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user password
     * 
     * @param int $userId User ID
     * @param string $hashedPassword Hashed password
     * @return bool Success status
     */
    public function updateUserPassword($userId, $hashedPassword) {
        try {
            $this->db->query("UPDATE users SET password = :password WHERE id = :id");
            $this->db->bind(':password', $hashedPassword);
            $this->db->bind(':id', $userId);
            return $this->db->execute();
        } catch (Exception $e) {
            error_log("Error updating user password: " . $e->getMessage());
            return false;
        }
    }
}
