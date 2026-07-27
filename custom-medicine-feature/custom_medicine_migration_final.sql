-- =====================================================
-- Custom Medicine Feature - Database Migration
-- =====================================================
-- This migration adds support for custom medicines that
-- are not in the master medicines table.
-- Safe to run multiple times (checks for existing changes)
-- =====================================================

-- Step 1: Drop foreign key if it exists
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'prescription_medicines'
    AND CONSTRAINT_NAME = 'prescription_medicines_medicine_id_foreign'
    AND CONSTRAINT_TYPE = 'FOREIGN KEY');

SET @drop_fk = IF(@fk_exists > 0,
    'ALTER TABLE prescription_medicines DROP FOREIGN KEY prescription_medicines_medicine_id_foreign',
    'SELECT "Foreign key does not exist, skipping..."');

PREPARE stmt FROM @drop_fk;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 2: Add is_custom column if it doesn't exist
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'prescription_medicines'
    AND COLUMN_NAME = 'is_custom');

SET @add_is_custom = IF(@col_exists = 0,
    'ALTER TABLE prescription_medicines ADD COLUMN is_custom BOOLEAN DEFAULT FALSE COMMENT "Flag to indicate if this is a custom medicine entry"',
    'SELECT "is_custom column already exists, skipping..."');

PREPARE stmt FROM @add_is_custom;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 3: Add custom_medicine_name column if it doesn't exist
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'prescription_medicines'
    AND COLUMN_NAME = 'custom_medicine_name');

SET @add_custom_name = IF(@col_exists = 0,
    'ALTER TABLE prescription_medicines ADD COLUMN custom_medicine_name VARCHAR(255) DEFAULT NULL COMMENT "Name of custom medicine (only used when is_custom is TRUE)"',
    'SELECT "custom_medicine_name column already exists, skipping..."');

PREPARE stmt FROM @add_custom_name;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 4: Make medicine_id nullable
ALTER TABLE prescription_medicines
MODIFY COLUMN medicine_id INT NULL
COMMENT 'Reference to medicines table (NULL for custom medicines)';

-- Step 5: Re-create the foreign key constraint (now allowing NULL)
ALTER TABLE prescription_medicines
ADD CONSTRAINT prescription_medicines_medicine_id_foreign
FOREIGN KEY (medicine_id)
REFERENCES medicines(id)
ON DELETE CASCADE;

-- Step 6: Add index if it doesn't exist
SET @index_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'prescription_medicines'
    AND INDEX_NAME = 'idx_prescription_medicines_custom');

SET @add_index = IF(@index_exists = 0,
    'CREATE INDEX idx_prescription_medicines_custom ON prescription_medicines(is_custom)',
    'SELECT "Index already exists, skipping..."');

PREPARE stmt FROM @add_index;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Step 7: Update existing records to comply with new schema
UPDATE prescription_medicines
SET is_custom = FALSE
WHERE is_custom IS NULL;

-- =====================================================
-- Migration Complete!
-- =====================================================
