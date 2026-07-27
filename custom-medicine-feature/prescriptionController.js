/**
 * Prescription Controller - Backend API Handler
 * Handles prescription creation/update with custom medicine support
 */

const db = require('../config/database');
const { validatePrescription } = require('../validators/prescriptionValidation');

/**
 * Create or Update Prescription
 * POST /api/prescriptions OR PUT /api/prescriptions/:id
 */
const savePrescription = async (req, res) => {
  const connection = await db.getConnection();
  
  try {
    await connection.beginTransaction();
    
    const { patient_id, doctor_id, medicines, notes } = req.body;
    const prescriptionId = req.params.id;
    
    // Validate request
    const validation = validatePrescription(req.body);
    if (!validation.isValid) {
      return res.status(400).json({
        success: false,
        errors: validation.errors
      });
    }
    
    let prescription;
    
    if (prescriptionId) {
      // Update existing prescription
      await connection.query(
        `UPDATE prescriptions 
         SET patient_id = ?, doctor_id = ?, notes = ?, updated_at = NOW()
         WHERE id = ?`,
        [patient_id, doctor_id, notes, prescriptionId]
      );
      
      // Delete existing medicines (we'll re-insert)
      await connection.query(
        'DELETE FROM prescription_medicines WHERE prescription_id = ?',
        [prescriptionId]
      );
      
      prescription = { id: prescriptionId };
    } else {
      // Create new prescription
      const [result] = await connection.query(
        `INSERT INTO prescriptions (patient_id, doctor_id, notes, created_at, updated_at)
         VALUES (?, ?, ?, NOW(), NOW())`,
        [patient_id, doctor_id, notes]
      );
      
      prescription = { id: result.insertId };
    }
    
    // Insert medicines (both custom and predefined)
    if (medicines && medicines.length > 0) {
      for (const medicine of medicines) {
        await savePrescriptionMedicine(connection, prescription.id, medicine);
      }
    }
    
    await connection.commit();
    
    // Fetch complete prescription data
    const fullPrescription = await getPrescriptionById(prescription.id);
    
    res.json({
      success: true,
      message: prescriptionId ? 'Prescription updated successfully' : 'Prescription created successfully',
      data: fullPrescription
    });
    
  } catch (error) {
    await connection.rollback();
    console.error('Error saving prescription:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to save prescription',
      error: error.message
    });
  } finally {
    connection.release();
  }
};

/**
 * Save individual medicine to prescription
 * Handles both custom and predefined medicines
 */
const savePrescriptionMedicine = async (connection, prescriptionId, medicine) => {
  const {
    medicine_id,
    is_custom,
    custom_medicine_name,
    dosage,
    frequency,
    duration,
    instructions
  } = medicine;
  
  if (is_custom) {
    // Insert custom medicine
    await connection.query(
      `INSERT INTO prescription_medicines 
       (prescription_id, medicine_id, is_custom, custom_medicine_name, 
        dosage, frequency, duration, instructions)
       VALUES (?, NULL, TRUE, ?, ?, ?, ?, ?)`,
      [prescriptionId, custom_medicine_name, dosage, frequency, duration, instructions]
    );
  } else {
    // Insert predefined medicine
    // Verify medicine exists
    const [medicineExists] = await connection.query(
      'SELECT id FROM medicines WHERE id = ?',
      [medicine_id]
    );
    
    if (medicineExists.length === 0) {
      throw new Error(`Medicine with ID ${medicine_id} not found`);
    }
    
    await connection.query(
      `INSERT INTO prescription_medicines 
       (prescription_id, medicine_id, is_custom, custom_medicine_name,
        dosage, frequency, duration, instructions)
       VALUES (?, ?, FALSE, NULL, ?, ?, ?, ?)`,
      [prescriptionId, medicine_id, dosage, frequency, duration, instructions]
    );
  }
};

/**
 * Get prescription by ID with all medicines
 */
const getPrescriptionById = async (prescriptionId) => {
  const connection = await db.getConnection();
  
  try {
    // Get prescription basic info
    const [prescriptions] = await connection.query(
      `SELECT p.*, 
              pt.name as patient_name,
              d.name as doctor_name
       FROM prescriptions p
       LEFT JOIN patients pt ON p.patient_id = pt.id
       LEFT JOIN doctors d ON p.doctor_id = d.id
       WHERE p.id = ?`,
      [prescriptionId]
    );
    
    if (prescriptions.length === 0) {
      return null;
    }
    
    const prescription = prescriptions[0];
    
    // Get medicines
    const [medicines] = await connection.query(
      `SELECT pm.*,
              m.name as medicine_name,
              m.strength as medicine_strength
       FROM prescription_medicines pm
       LEFT JOIN medicines m ON pm.medicine_id = m.id
       WHERE pm.prescription_id = ?
       ORDER BY pm.id`,
      [prescriptionId]
    );
    
    // Format medicines for response
    prescription.medicines = medicines.map(med => ({
      id: med.id,
      medicine_id: med.medicine_id,
      is_custom: med.is_custom,
      custom_medicine_name: med.custom_medicine_name,
      medicine_name: med.is_custom ? med.custom_medicine_name : med.medicine_name,
      medicine_strength: med.medicine_strength,
      dosage: med.dosage,
      frequency: med.frequency,
      duration: med.duration,
      instructions: med.instructions
    }));

    return prescription;

  } finally {
    connection.release();
  }
};

/**
 * Search medicines from database
 * GET /api/medicines/search?q=search_term
 */
const searchMedicines = async (req, res) => {
  try {
    const { q } = req.query;

    if (!q || q.length < 2) {
      return res.json({
        success: true,
        results: []
      });
    }

    const connection = await db.getConnection();

    const [medicines] = await connection.query(
      `SELECT id, name, strength, form, manufacturer
       FROM medicines
       WHERE name LIKE ? OR manufacturer LIKE ?
       ORDER BY name
       LIMIT 20`,
      [`%${q}%`, `%${q}%`]
    );

    connection.release();

    res.json({
      success: true,
      results: medicines
    });

  } catch (error) {
    console.error('Error searching medicines:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to search medicines',
      error: error.message
    });
  }
};

/**
 * Get prescription (with auth check)
 */
const getPrescription = async (req, res) => {
  try {
    const { id } = req.params;
    const prescription = await getPrescriptionById(id);

    if (!prescription) {
      return res.status(404).json({
        success: false,
        message: 'Prescription not found'
      });
    }

    res.json({
      success: true,
      data: prescription
    });

  } catch (error) {
    console.error('Error fetching prescription:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch prescription',
      error: error.message
    });
  }
};

module.exports = {
  savePrescription,
  getPrescription,
  searchMedicines
};
