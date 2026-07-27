/**
 * Prescription Validation Logic
 * Validates prescription data including custom medicines
 */

/**
 * Validate prescription request
 */
const validatePrescription = (data) => {
  const errors = [];
  
  // Validate patient_id
  if (!data.patient_id) {
    errors.push({ field: 'patient_id', message: 'Patient ID is required' });
  }
  
  // Validate doctor_id
  if (!data.doctor_id) {
    errors.push({ field: 'doctor_id', message: 'Doctor ID is required' });
  }
  
  // Validate medicines
  if (!data.medicines || !Array.isArray(data.medicines) || data.medicines.length === 0) {
    errors.push({ field: 'medicines', message: 'At least one medicine is required' });
  } else {
    // Validate each medicine
    data.medicines.forEach((medicine, index) => {
      const medicineErrors = validateMedicine(medicine, index);
      errors.push(...medicineErrors);
    });
  }
  
  return {
    isValid: errors.length === 0,
    errors
  };
};

/**
 * Validate individual medicine entry
 */
const validateMedicine = (medicine, index) => {
  const errors = [];
  const prefix = `medicines[${index}]`;
  
  // Check if custom or predefined
  if (medicine.is_custom) {
    // Custom medicine validation
    if (!medicine.custom_medicine_name || medicine.custom_medicine_name.trim() === '') {
      errors.push({
        field: `${prefix}.custom_medicine_name`,
        message: 'Custom medicine name is required'
      });
    }
    
    // Validate length
    if (medicine.custom_medicine_name && medicine.custom_medicine_name.length > 255) {
      errors.push({
        field: `${prefix}.custom_medicine_name`,
        message: 'Medicine name must not exceed 255 characters'
      });
    }
  } else {
    // Predefined medicine validation
    if (!medicine.medicine_id) {
      errors.push({
        field: `${prefix}.medicine_id`,
        message: 'Medicine ID is required for predefined medicines'
      });
    }
  }
  
  // Validate dosage
  if (!medicine.dosage || medicine.dosage.trim() === '') {
    errors.push({
      field: `${prefix}.dosage`,
      message: 'Dosage is required'
    });
  }
  
  // Validate frequency
  if (!medicine.frequency || medicine.frequency.trim() === '') {
    errors.push({
      field: `${prefix}.frequency`,
      message: 'Frequency is required'
    });
  }
  
  // Validate duration
  if (!medicine.duration || medicine.duration.trim() === '') {
    errors.push({
      field: `${prefix}.duration`,
      message: 'Duration is required'
    });
  }
  
  // Instructions are optional, no validation needed
  
  return errors;
};

/**
 * Sanitize medicine input
 */
const sanitizeMedicineInput = (medicine) => {
  const sanitized = {
    is_custom: Boolean(medicine.is_custom),
    dosage: medicine.dosage ? medicine.dosage.trim() : '',
    frequency: medicine.frequency ? medicine.frequency.trim() : '',
    duration: medicine.duration ? medicine.duration.trim() : '',
    instructions: medicine.instructions ? medicine.instructions.trim() : ''
  };
  
  if (medicine.is_custom) {
    sanitized.medicine_id = null;
    sanitized.custom_medicine_name = medicine.custom_medicine_name ? 
      medicine.custom_medicine_name.trim() : '';
  } else {
    sanitized.medicine_id = parseInt(medicine.medicine_id, 10);
    sanitized.custom_medicine_name = null;
  }
  
  return sanitized;
};

module.exports = {
  validatePrescription,
  validateMedicine,
  sanitizeMedicineInput
};
