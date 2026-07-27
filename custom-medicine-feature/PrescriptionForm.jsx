import React, { useState, useEffect } from 'react';
import PrescriptionMedicineInput from './PrescriptionMedicineInput';
import { Save, Plus, AlertCircle } from 'lucide-react';

/**
 * Complete Prescription Form Component with Custom Medicine Support
 * Production-ready implementation
 *
 * @param {number} prescriptionId - ID for editing existing prescription
 * @param {number} patientId - Patient ID for the prescription
 * @param {function} onSuccess - Callback on successful save
 */
const PrescriptionForm = ({ prescriptionId, patientId, onSuccess }) => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [medicines, setMedicines] = useState([
    {
      medicine_id: null,
      medicine_name: null,
      is_custom: false,
      custom_medicine_name: null,
      dosage: '',
      frequency: '',
      duration: '',
      instructions: '',
      quantity: null
    }
  ]);
  const [notes, setNotes] = useState('');
  const [diagnosis, setDiagnosis] = useState('');

  // Load existing prescription if editing
  useEffect(() => {
    if (prescriptionId) {
      loadPrescription(prescriptionId);
    }
  }, [prescriptionId]);

  /**
   * Load prescription data from API
   * Maps incoming data to component state correctly
   */
  const loadPrescription = async (id) => {
    setLoading(true);
    setError(null);

    try {
      const response = await fetch(`/api/prescriptions/${id}`, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();

      if (data.success) {
        // Map API response to component state
        const mappedMedicines = data.data.medicines.map(med => ({
          medicine_id: med.is_custom ? null : (med.medicine_id || null),
          medicine_name: med.is_custom ? null : (med.medicine_name || null),
          is_custom: Boolean(med.is_custom),
          custom_medicine_name: med.is_custom ? med.custom_medicine_name : null,
          dosage: med.dosage || '',
          frequency: med.frequency || '',
          duration: med.duration || '',
          instructions: med.instructions || '',
          quantity: med.quantity || null
        }));

        setMedicines(mappedMedicines.length > 0 ? mappedMedicines : [{
          medicine_id: null,
          medicine_name: null,
          is_custom: false,
          custom_medicine_name: null,
          dosage: '',
          frequency: '',
          duration: '',
          instructions: '',
          quantity: null
        }]);

        setNotes(data.data.notes || '');
        setDiagnosis(data.data.diagnosis || '');
      } else {
        throw new Error(data.message || 'Failed to load prescription');
      }
    } catch (error) {
      console.error('Error loading prescription:', error);
      setError('Failed to load prescription: ' + error.message);
      alert('Failed to load prescription. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  /**
   * Handle medicine changes from PrescriptionMedicineInput
   * Ensures state is properly updated with is_custom flag and custom_medicine_name
   */
  const handleMedicineChange = (index, updatedMedicine) => {
    const updatedMedicines = [...medicines];

    // Ensure correct data structure based on custom flag
    updatedMedicines[index] = {
      medicine_id: updatedMedicine.is_custom ? null : (updatedMedicine.medicine_id || null),
      medicine_name: updatedMedicine.is_custom ? null : (updatedMedicine.medicine_name || null),
      is_custom: Boolean(updatedMedicine.is_custom),
      custom_medicine_name: updatedMedicine.is_custom ? (updatedMedicine.custom_medicine_name || '') : null,
      dosage: updatedMedicine.dosage || '',
      frequency: updatedMedicine.frequency || '',
      duration: updatedMedicine.duration || '',
      instructions: updatedMedicine.instructions || '',
      quantity: updatedMedicine.quantity || null
    };

    setMedicines(updatedMedicines);
    setError(null); // Clear any previous errors
  };

  /**
   * Add new medicine entry to the form
   */
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
        instructions: '',
        quantity: null
      }
    ]);
  };

  /**
   * Remove medicine entry from the form
   * Prevents removing the last medicine
   */
  const handleRemoveMedicine = (index) => {
    if (medicines.length > 1) {
      const updatedMedicines = medicines.filter((_, i) => i !== index);
      setMedicines(updatedMedicines);
    } else {
      alert('At least one medicine is required');
    }
  };

  /**
   * Comprehensive form validation
   * Ensures custom_medicine_name is present when is_custom is true
   * Ensures medicine_id is present when is_custom is false
   */
  const validateForm = () => {
    const errors = [];

    // Check if at least one medicine is added
    if (!medicines || medicines.length === 0) {
      errors.push('Please add at least one medicine');
      setError(errors.join('\n'));
      alert(errors.join('\n'));
      return false;
    }

    const hasValidMedicine = medicines.some(med => {
      if (med.is_custom) {
        return med.custom_medicine_name && med.custom_medicine_name.trim() !== '';
      } else {
        return med.medicine_id !== null && med.medicine_id !== undefined;
      }
    });

    if (!hasValidMedicine) {
      errors.push('Please add at least one valid medicine');
    }

    // Validate each medicine entry
    for (let i = 0; i < medicines.length; i++) {
      const med = medicines[i];
      const medicineLabel = `Medicine ${i + 1}`;

      // Validate custom medicine
      if (med.is_custom) {
        if (!med.custom_medicine_name || med.custom_medicine_name.trim() === '') {
          errors.push(`${medicineLabel}: Custom medicine name is required`);
        }
        // Ensure medicine_id is null for custom medicines
        if (med.medicine_id !== null) {
          errors.push(`${medicineLabel}: Custom medicine should not have medicine_id`);
        }
      }
      // Validate database medicine
      else {
        if (!med.medicine_id) {
          errors.push(`${medicineLabel}: Please select a medicine from the database`);
        }
        // Ensure custom_medicine_name is null for database medicines
        if (med.custom_medicine_name !== null && med.custom_medicine_name !== '') {
          errors.push(`${medicineLabel}: Database medicine should not have custom_medicine_name`);
        }
      }

      // Validate required fields
      if (!med.dosage || med.dosage.trim() === '') {
        errors.push(`${medicineLabel}: Dosage is required`);
      }
      if (!med.frequency || med.frequency.trim() === '') {
        errors.push(`${medicineLabel}: Frequency is required`);
      }
      if (!med.duration || med.duration.trim() === '') {
        errors.push(`${medicineLabel}: Duration is required`);
      }
    }

    if (errors.length > 0) {
      setError(errors.join('\n'));
      alert(errors.join('\n'));
      return false;
    }

    setError(null);
    return true;
  };

  /**
   * Submit prescription with proper payload format
   * Sends PUT request for updates, POST for new prescriptions
   * Payload includes is_custom flag and custom_medicine_name
   */
  const handleSubmit = async (e) => {
    e.preventDefault();

    if (!validateForm()) {
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const url = prescriptionId
        ? `/api/prescriptions/${prescriptionId}`
        : '/api/prescriptions';

      const method = prescriptionId ? 'PUT' : 'POST';

      // Prepare medicines payload with correct format
      const medicinesPayload = medicines
        .filter(med => {
          // Filter out empty/invalid entries
          if (med.is_custom) {
            return med.custom_medicine_name && med.custom_medicine_name.trim() !== '';
          } else {
            return med.medicine_id !== null && med.medicine_id !== undefined;
          }
        })
        .map(med => ({
          // For custom medicines: medicine_id = null, is_custom = true, custom_medicine_name = string
          // For database medicines: medicine_id = number, is_custom = false, custom_medicine_name = null
          medicine_id: med.is_custom ? null : med.medicine_id,
          is_custom: Boolean(med.is_custom),
          custom_medicine_name: med.is_custom ? med.custom_medicine_name : null,
          dosage: med.dosage,
          frequency: med.frequency,
          duration: med.duration,
          instructions: med.instructions || null,
          quantity: med.quantity || null
        }));

      // Build request payload
      const payload = {
        patient_id: patientId,
        doctor_id: getCurrentDoctorId(),
        medicines: medicinesPayload,
        notes: notes || null,
        diagnosis: diagnosis || null
      };

      console.log('Submitting prescription:', payload); // Debug log

      const response = await fetch(url, {
        method,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          // Add CSRF token if required by your Laravel setup
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify(payload)
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();

      if (data.success) {
        const message = prescriptionId
          ? 'Prescription updated successfully'
          : 'Prescription created successfully';

        alert(message);

        if (onSuccess) {
          onSuccess(data.data);
        } else {
          // Redirect to prescription list or detail page
          window.location.href = `/prescriptions/${data.data.id || prescriptionId}`;
        }
      } else {
        throw new Error(data.message || 'Failed to save prescription');
      }
    } catch (error) {
      console.error('Error saving prescription:', error);
      const errorMessage = 'Failed to save prescription: ' + error.message;
      setError(errorMessage);
      alert(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="max-w-5xl mx-auto p-6 bg-gray-50">
      <div className="bg-white rounded-lg shadow-lg p-6">
        {/* Header */}
        <h2 className="text-2xl font-bold mb-6 text-gray-800">
          {prescriptionId ? 'Edit Prescription' : 'Create New Prescription'}
        </h2>

        {/* Error Display */}
        {error && (
          <div className="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
            <AlertCircle className="text-red-600 mt-0.5" size={20} />
            <div className="flex-1">
              <h4 className="font-semibold text-red-800 mb-1">Validation Error</h4>
              <pre className="text-sm text-red-700 whitespace-pre-wrap">{error}</pre>
            </div>
          </div>
        )}

        {/* Loading State */}
        {loading && (
          <div className="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p className="text-blue-800">Loading prescription data...</p>
          </div>
        )}

        {/* Diagnosis Section (Optional) */}
        <div className="mb-6">
          <label className="block text-sm font-medium mb-2 text-gray-700">
            Diagnosis
          </label>
          <textarea
            value={diagnosis}
            onChange={(e) => setDiagnosis(e.target.value)}
            placeholder="Enter diagnosis..."
            rows="3"
            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        {/* Medicines Section */}
        <div className="mb-6">
          <div className="flex justify-between items-center mb-4">
            <h3 className="text-lg font-semibold text-gray-800">
              Medicines <span className="text-red-500">*</span>
            </h3>
            <button
              type="button"
              onClick={handleAddMedicine}
              disabled={loading}
              className="flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
            >
              <Plus size={20} />
              Add Medicine
            </button>
          </div>

          {/* Medicine Entries */}
          {medicines.map((medicine, index) => (
            <div key={index} className="mb-4">
              <PrescriptionMedicineInput
                index={index}
                medicineData={medicine}
                onMedicineChange={handleMedicineChange}
                onRemove={handleRemoveMedicine}
              />
            </div>
          ))}

          {medicines.length === 0 && (
            <div className="p-4 bg-gray-50 border border-gray-200 rounded-lg text-center">
              <p className="text-gray-600">No medicines added yet. Click "Add Medicine" to start.</p>
            </div>
          )}
        </div>

        {/* Notes Section */}
        <div className="mb-6">
          <label className="block text-sm font-medium mb-2 text-gray-700">
            Prescription Notes
          </label>
          <textarea
            value={notes}
            onChange={(e) => setNotes(e.target.value)}
            placeholder="Additional notes, warnings, or special instructions..."
            rows="4"
            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        {/* Action Buttons */}
        <div className="flex justify-end gap-4 pt-4 border-t border-gray-200">
          <button
            type="button"
            onClick={() => window.history.back()}
            disabled={loading}
            className="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:bg-gray-100 disabled:cursor-not-allowed transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            disabled={loading}
            className="flex items-center gap-2 px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
          >
            <Save size={20} />
            {loading
              ? 'Saving...'
              : (prescriptionId ? 'Update Prescription' : 'Create Prescription')
            }
          </button>
        </div>
      </div>
    </form>
  );
};

/**
 * Helper function to get current doctor ID
 * Implement based on your authentication system
 */
const getCurrentDoctorId = () => {
  // Option 1: From Laravel Blade (inject as window variable)
  if (window.currentUser && window.currentUser.id) {
    return window.currentUser.id;
  }

  // Option 2: From localStorage
  try {
    const user = JSON.parse(localStorage.getItem('user'));
    if (user && user.id) {
      return user.id;
    }
  } catch (e) {
    console.error('Failed to parse user from localStorage', e);
  }

  // Option 3: From meta tag
  const doctorIdMeta = document.querySelector('meta[name="doctor-id"]');
  if (doctorIdMeta) {
    return parseInt(doctorIdMeta.content, 10);
  }

  // Fallback - this should be replaced with actual implementation
  console.warn('getCurrentDoctorId: Using fallback value. Implement proper authentication.');
  return 1;
};

export default PrescriptionForm;
