-- Add 'used' column to email_verifications table if it doesn't exist
-- Run this in phpMyAdmin if you get "Column not found: used" error

ALTER TABLE email_verifications 
ADD COLUMN IF NOT EXISTS used TINYINT(1) DEFAULT 0 AFTER expires_at;

-- If the above doesn't work (MySQL version < 8.0), use this instead:
-- ALTER TABLE email_verifications ADD COLUMN used TINYINT(1) DEFAULT 0;

-- Verify the column was added
-- SELECT COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_SCHEMA = DATABASE() 
--   AND TABLE_NAME = 'email_verifications' 
--   AND COLUMN_NAME = 'used';


