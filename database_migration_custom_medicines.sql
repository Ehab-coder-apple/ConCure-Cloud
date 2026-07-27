-- Database Migration: Add Custom Medicine Support to Prescriptions
-- This migration adds the ability to save custom medicines directly in prescriptions

-- Step 1: Add new columns to prescription_medicines table
ALTER TABLE prescription_medicines 
ADD COLUMN is_custom BOOLEAN DEFAULT FALSE COMMENT 'Flag to indicate if this is a custom medicine entry';

ALTER TABLE prescription_medicines 
ADD COLUMN custom_medicine_name VARCHAR(255) DEFAULT NULL COMMENT 'Name of custom medicine (only used when is_custom is TRUE)';

-- Step 2: Make medicine_id nullable to allow custom entries
ALTER TABLE prescription_medicines 
MODIFY COLUMN medicine_id INT NULL COMMENT 'Reference to medicines table (NULL for custom medicines)';

-- Step 3: Add index for performance
CREATE INDEX idx_prescription_medicines_custom ON prescription_medicines(is_custom);
CREATE INDEX idx_prescription_medicines_medicine_id ON prescription_medicines(medicine_id);

-- Step 4: Add constraint to ensure data integrity
-- Either medicine_id must be set (for predefined) OR custom_medicine_name must be set (for custom)
ALTER TABLE prescription_medicines
ADD CONSTRAINT chk_medicine_entry CHECK (
  (is_custom = FALSE AND medicine_id IS NOT NULL AND custom_medicine_name IS NULL) OR
  (is_custom = TRUE AND medicine_id IS NULL AND custom_medicine_name IS NOT NULL AND custom_medicine_name != '')
);

-- Step 5: Update existing records to ensure they comply with new schema
UPDATE prescription_medicines 
SET is_custom = FALSE 
WHERE is_custom IS NULL;

-- Rollback Script (if needed):
/*
ALTER TABLE prescription_medicines DROP CONSTRAINT chk_medicine_entry;
DROP INDEX idx_prescription_medicines_custom ON prescription_medicines;
DROP INDEX idx_prescription_medicines_medicine_id ON prescription_medicines;
ALTER TABLE prescription_medicines DROP COLUMN is_custom;
ALTER TABLE prescription_medicines DROP COLUMN custom_medicine_name;
ALTER TABLE prescription_medicines MODIFY COLUMN medicine_id INT NOT NULL;
*/
