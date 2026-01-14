-- =====================================================
-- Update email_verifications table to add 'used' field
-- =====================================================
-- This adds a 'used' boolean field for tracking if code was used
-- The table already has 'verified_at' but 'used' provides simpler checking

-- Check if column exists, if not add it
SET @dbname = DATABASE();
SET @tablename = 'email_verifications';
SET @columnname = 'used';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1', -- Column exists, do nothing
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' TINYINT(1) DEFAULT 0 COMMENT ''Whether the code has been used''')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update existing verified records to mark as used
UPDATE email_verifications SET used = 1 WHERE verified_at IS NOT NULL;


