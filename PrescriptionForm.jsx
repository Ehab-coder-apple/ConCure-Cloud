import React, { useState, useEffect } from 'react';
import PrescriptionMedicineInput from './PrescriptionMedicineInput';
import { Save, Plus } from 'lucide-react';

/**
 * Complete Prescription Form Component
 * Integrates custom medicine functionality
 */
const PrescriptionForm = ({ prescriptionId, patientId, onSuccess }) => {
  const [loading, setLoading] = useState(false);
  const [medicines, setMedicines] = useState([
    {
      medicine_id: null,
      medicine_name: null,
      is_custom: false,
      custom_medicine_name: null,
      dosage: '',
      frequency: '',
      duration: '',
      instructions: ''
    }
  ]);
  const [notes, setNotes] = useState('');

  // Load existing prescription if editing
  useEffect(() => {
    if (prescriptionId) {
      loadPrescription(prescriptionId);
    }
  }, [prescriptionId]);

  const loadPrescription = async (id) => {
    try {
      const response = await fetch(`/api/prescriptions/${id}`);
      const data = await response.json();
      
      if (data.success) {
        setMedicines(data.data.medicines);
        setNotes(data.data.notes || '');
      }
    } catch (error) {
      console.error('Error loading prescription:', error);
      alert('Failed to load prescription');
    }
  };

  // Handle medicine changes
  const handleMedicineChange = (index, updatedMedicine) => {
    const updatedMedicines = [...medicines];
    updatedMedicines[index] = updatedMedicine;
    setMedicines(updatedMedicines);
  };

  // Add new medicine entry
  const handleAddMedicine = () => {
    setMedicines([
      ...medicines,
      {
        medicine_id: null,
        medicine_name: null,
        is_custom: false,
        custom_medicine_name: null,
        dosage: '',
        frequency: '',
        duration: '',
        instructions: ''
      }
    ]);
  };

  // Remove medicine entry
  const handleRemoveMedicine = (index) => {
    if (medicines.length > 1) {
      const updatedMedicines = medicines.filter((_, i) => i !== index);
      setMedicines(updatedMedicines);
    }
  };

  // Validate form
  const validateForm = () => {
    // Check if at least one medicine is added
    const hasValidMedicine = medicines.some(med => {
      if (med.is_custom) {
        return med.custom_medicine_name && med.custom_medicine_name.trim() !== '';
      } else {
        return med.medicine_id !== null;
      }
    });

    if (!hasValidMedicine) {
      alert('Please add at least one medicine');
      return false;
    }

    // Check if all medicines have required fields
    for (let i = 0; i < medicines.length; i++) {
      const med = medicines[i];
      
      // Check medicine name/selection
      if (med.is_custom && (!med.custom_medicine_name || med.custom_medicine_name.trim() === '')) {
        alert(`Please enter medicine name for Medicine ${i + 1}`);
        return false;
      }
      
      if (!med.is_custom && !med.medicine_id) {
        alert(`Please select medicine for Medicine ${i + 1}`);
        return false;
      }

      // Check required fields
      if (!med.dosage || !med.frequency || !med.duration) {
        alert(`Please fill in all required fields for Medicine ${i + 1}`);
        return false;
      }
    }

    return true;
  };

  // Submit prescription
  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }

    setLoading(true);

    try {
      const url = prescriptionId 
        ? `/api/prescriptions/${prescriptionId}` 
        : '/api/prescriptions';
      
      const method = prescriptionId ? 'PUT' : 'POST';

      const response = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          patient_id: patientId,
          doctor_id: getCurrentDoctorId(), // Implement this based on your auth system
          medicines: medicines.filter(med => {
            // Filter out empty entries
            return med.is_custom ? med.custom_medicine_name : med.medicine_id;
          }),
          notes
        })
      });

      const data = await response.json();

      if (data.success) {
        alert(prescriptionId ? 'Prescription updated successfully' : 'Prescription created successfully');
        if (onSuccess) {
          onSuccess(data.data);
        }
      } else {
        alert('Error: ' + (data.message || 'Failed to save prescription'));
      }
    } catch (error) {
      console.error('Error saving prescription:', error);
      alert('Failed to save prescription');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="max-w-5xl mx-auto p-6 bg-gray-50">
      <div className="bg-white rounded-lg shadow-lg p-6">
        <h2 className="text-2xl font-bold mb-6">
          {prescriptionId ? 'Edit Prescription' : 'Create Prescription'}
        </h2>

        {/* Medicines Section */}
        <div className="mb-6">
          <div className="flex justify-between items-center mb-4">
            <h3 className="text-lg font-semibold">Medicines</h3>
            <button
              type="button"
              onClick={handleAddMedicine}
              className="flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
            >
              <Plus size={20} />
              Add Medicine
            </button>
          </div>

          {medicines.map((medicine, index) => (
            <PrescriptionMedicineInput
              key={index}
              index={index}
              medicineData={medicine}
              onMedicineChange={handleMedicineChange}
              onRemove={handleRemoveMedicine}
            />
          ))}
        </div>

        {/* Notes Section */}
        <div className="mb-6">
          <label className="block text-sm font-medium mb-2">Prescription Notes</label>
          <textarea
            value={notes}
            onChange={(e) => setNotes(e.target.value)}
            placeholder="Additional notes or instructions..."
            rows="4"
            className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        {/* Action Buttons */}
        <div className="flex justify-end gap-4">
          <button
            type="button"
            onClick={() => window.history.back()}
            className="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            disabled={loading}
            className="flex items-center gap-2 px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:bg-gray-400"
          >
            <Save size={20} />
            {loading ? 'Saving...' : (prescriptionId ? 'Update Prescription' : 'Create Prescription')}
          </button>
        </div>
      </div>
    </form>
  );
};

// Helper function - implement based on your auth system
const getCurrentDoctorId = () => {
  // This should retrieve the current logged-in doctor's ID
  // Example: return JSON.parse(localStorage.getItem('user')).doctor_id;
  return 1; // Placeholder
};

export default PrescriptionForm;
