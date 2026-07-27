/**
 * Prescription API Routes
 * Defines routes for prescription management with custom medicine support
 */

const express = require('express');
const router = express.Router();
const { 
  savePrescription, 
  getPrescription, 
  searchMedicines 
} = require('../controllers/prescriptionController');

// Middleware for authentication (implement based on your auth system)
const { authenticateUser } = require('../middleware/auth');

/**
 * Medicine Search Routes
 */

// Search medicines from database
// GET /api/medicines/search?q=search_term
router.get('/medicines/search', authenticateUser, searchMedicines);

/**
 * Prescription Routes
 */

// Create new prescription
// POST /api/prescriptions
router.post('/prescriptions', authenticateUser, savePrescription);

// Get prescription by ID
// GET /api/prescriptions/:id
router.get('/prescriptions/:id', authenticateUser, getPrescription);

// Update existing prescription
// PUT /api/prescriptions/:id
router.put('/prescriptions/:id', authenticateUser, savePrescription);

// Get all prescriptions for a patient
// GET /api/patients/:patientId/prescriptions
router.get('/patients/:patientId/prescriptions', authenticateUser, async (req, res) => {
  try {
    const { patientId } = req.params;
    const db = require('../config/database');
    const connection = await db.getConnection();
    
    const [prescriptions] = await connection.query(
      `SELECT p.id, p.patient_id, p.doctor_id, p.notes, p.created_at,
              d.name as doctor_name,
              COUNT(pm.id) as medicine_count
       FROM prescriptions p
       LEFT JOIN doctors d ON p.doctor_id = d.id
       LEFT JOIN prescription_medicines pm ON p.id = pm.prescription_id
       WHERE p.patient_id = ?
       GROUP BY p.id
       ORDER BY p.created_at DESC`,
      [patientId]
    );
    
    connection.release();
    
    res.json({
      success: true,
      data: prescriptions
    });
  } catch (error) {
    console.error('Error fetching patient prescriptions:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch prescriptions'
    });
  }
});

// Delete prescription (soft delete recommended)
// DELETE /api/prescriptions/:id
router.delete('/prescriptions/:id', authenticateUser, async (req, res) => {
  try {
    const { id } = req.params;
    const db = require('../config/database');
    const connection = await db.getConnection();
    
    await connection.beginTransaction();
    
    // Delete prescription medicines first
    await connection.query(
      'DELETE FROM prescription_medicines WHERE prescription_id = ?',
      [id]
    );
    
    // Delete prescription
    await connection.query(
      'DELETE FROM prescriptions WHERE id = ?',
      [id]
    );
    
    await connection.commit();
    connection.release();
    
    res.json({
      success: true,
      message: 'Prescription deleted successfully'
    });
  } catch (error) {
    console.error('Error deleting prescription:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to delete prescription'
    });
  }
});

module.exports = router;
