-- =====================================================
-- Final Fix: Match medicine_id type and add foreign key
-- =====================================================

-- Step 1: Change medicine_id from INT to BIGINT to match medicines.id
ALTER TABLE prescription_medicines 
MODIFY COLUMN medicine_id BIGINT(20) UNSIGNED NULL 
COMMENT 'Reference to medicines table (NULL for custom medicines)';

-- Step 2: Add the foreign key constraint
ALTER TABLE prescription_medicines
ADD CONSTRAINT prescription_medicines_medicine_id_foreign 
FOREIGN KEY (medicine_id) 
REFERENCES medicines(id) 
ON DELETE CASCADE;

-- Step 3: Add index for performance (if not exists)
CREATE INDEX IF NOT EXISTS idx_prescription_medicines_custom 
ON prescription_medicines(is_custom);

-- =====================================================
-- Migration Complete!
-- =====================================================
-- Changes:
--   - medicine_id changed from INT to BIGINT UNSIGNED
--   - Foreign key constraint added
--   - Performance index added
-- =====================================================
