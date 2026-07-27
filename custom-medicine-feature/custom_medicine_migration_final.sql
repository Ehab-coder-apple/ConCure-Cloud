-- =====================================================
-- Custom Medicine Feature - Database Migration
-- =====================================================
-- This migration adds support for custom medicines that
-- are not in the master medicines table.
-- =====================================================

-- Step 1: Drop existing foreign key constraint
ALTER TABLE prescription_medicines 
DROP FOREIGN KEY prescription_medicines_medicine_id_foreign;

-- Step 2: Add new columns for custom medicine support
ALTER TABLE prescription_medicines 
ADD COLUMN is_custom BOOLEAN DEFAULT FALSE 
COMMENT 'Flag to indicate if this is a custom medicine entry';

ALTER TABLE prescription_medicines 
ADD COLUMN custom_medicine_name VARCHAR(255) DEFAULT NULL 
COMMENT 'Name of custom medicine (only used when is_custom is TRUE)';

-- Step 3: Make medicine_id nullable to support custom medicines
ALTER TABLE prescription_medicines 
MODIFY COLUMN medicine_id INT NULL 
COMMENT 'Reference to medicines table (NULL for custom medicines)';

-- Step 4: Re-create the foreign key constraint (now allowing NULL)
ALTER TABLE prescription_medicines
ADD CONSTRAINT prescription_medicines_medicine_id_foreign 
FOREIGN KEY (medicine_id) 
REFERENCES medicines(id) 
ON DELETE CASCADE;

-- Step 5: Add indexes for better query performance
CREATE INDEX idx_prescription_medicines_custom 
ON prescription_medicines(is_custom);

-- Step 6: Update existing records to comply with new schema
UPDATE prescription_medicines 
SET is_custom = FALSE 
WHERE is_custom IS NULL;

-- =====================================================
-- Migration Complete!
-- =====================================================
-- New columns added:
--   - is_custom (BOOLEAN) - Indicates if medicine is custom
--   - custom_medicine_name (VARCHAR) - Stores custom medicine name
--
-- Changes:
--   - medicine_id is now nullable
--   - Foreign key constraint updated to allow NULL
--   - Indexes added for performance
-- =====================================================
