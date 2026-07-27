import React, { useState, useEffect } from 'react';
import { Search, X } from 'lucide-react';

/**
 * Medicine Input Component with Custom Medicine Support
 * Allows selecting from database or adding custom medicines
 */
const PrescriptionMedicineInput = ({ 
  medicineData, 
  onMedicineChange, 
  onRemove,
  index 
}) => {
  const [searchQuery, setSearchQuery] = useState('');
  const [searchResults, setSearchResults] = useState([]);
  const [showDropdown, setShowDropdown] = useState(false);
  const [isCustomMode, setIsCustomMode] = useState(false);
  const [selectedMedicine, setSelectedMedicine] = useState(null);

  // Initialize with existing medicine data
  useEffect(() => {
    if (medicineData?.is_custom) {
      setIsCustomMode(true);
      setSearchQuery(medicineData.custom_medicine_name || '');
    } else if (medicineData?.medicine_id) {
      setSelectedMedicine(medicineData);
      setSearchQuery(medicineData.medicine_name || '');
    }
  }, [medicineData]);

  // Search medicines from API
  const handleSearch = async (query) => {
    setSearchQuery(query);
    
    if (query.length < 2) {
      setSearchResults([]);
      setShowDropdown(false);
      return;
    }

    try {
      const response = await fetch(`/api/medicines/search?q=${encodeURIComponent(query)}`);
      const data = await response.json();
      setSearchResults(data.results || []);
      setShowDropdown(true);
    } catch (error) {
      console.error('Medicine search failed:', error);
      setSearchResults([]);
    }
  };

  // Select medicine from database
  const handleSelectMedicine = (medicine) => {
    setSelectedMedicine(medicine);
    setSearchQuery(medicine.name);
    setShowDropdown(false);
    setIsCustomMode(false);
    
    onMedicineChange(index, {
      medicine_id: medicine.id,
      medicine_name: medicine.name,
      is_custom: false,
      custom_medicine_name: null,
      dosage: medicineData?.dosage || '',
      frequency: medicineData?.frequency || '',
      duration: medicineData?.duration || '',
      instructions: medicineData?.instructions || ''
    });
  };

  // Switch to custom medicine mode
  const handleAddCustomMedicine = (customName = '') => {
    setIsCustomMode(true);
    setShowDropdown(false);
    setSelectedMedicine(null);
    setSearchQuery(customName || searchQuery);
    
    onMedicineChange(index, {
      medicine_id: null,
      medicine_name: null,
      is_custom: true,
      custom_medicine_name: customName || searchQuery,
      dosage: medicineData?.dosage || '',
      frequency: medicineData?.frequency || '',
      duration: medicineData?.duration || '',
      instructions: medicineData?.instructions || ''
    });
  };

  // Handle custom medicine name change
  const handleCustomNameChange = (value) => {
    setSearchQuery(value);
    onMedicineChange(index, {
      ...medicineData,
      custom_medicine_name: value
    });
  };

  // Handle other field changes
  const handleFieldChange = (field, value) => {
    onMedicineChange(index, {
      ...medicineData,
      [field]: value
    });
  };

  // Clear selection and switch back to search mode
  const handleClearSelection = () => {
    setSearchQuery('');
    setSelectedMedicine(null);
    setIsCustomMode(false);
    setShowDropdown(false);
    onMedicineChange(index, {
      medicine_id: null,
      medicine_name: null,
      is_custom: false,
      custom_medicine_name: null,
      dosage: '',
      frequency: '',
      duration: '',
      instructions: ''
    });
  };

  return (
    <div className="medicine-input-container border rounded-lg p-4 mb-4 bg-white">
      <div className="flex justify-between items-center mb-3">
        <h3 className="text-lg font-semibold">Medicine {index + 1}</h3>
        {index > 0 && (
          <button 
            onClick={() => onRemove(index)}
            className="text-red-500 hover:text-red-700"
          >
            <X size={20} />
          </button>
        )}
      </div>

      {/* Medicine Name Input */}
      <div className="mb-4 relative">
        <label className="block text-sm font-medium mb-2">
          Medicine Name
          {isCustomMode && (
            <span className="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
              Custom Entry
            </span>
          )}
        </label>
        
        <div className="relative">
          <input
            type="text"
            value={searchQuery}
            onChange={(e) => {
              if (isCustomMode) {
                handleCustomNameChange(e.target.value);
              } else {
                handleSearch(e.target.value);
              }
            }}
            onFocus={() => !isCustomMode && searchQuery.length >= 2 && setShowDropdown(true)}
            placeholder={isCustomMode ? "Enter custom medicine name..." : "Type to search medicine..."}
            className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          
          {searchQuery && (
            <button
              onClick={handleClearSelection}
              className="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
              <X size={18} />
            </button>
          )}
        </div>

        {/* Dropdown for search results */}
        {showDropdown && !isCustomMode && (
          <div className="absolute z-10 w-full mt-1 bg-white border rounded-lg shadow-lg max-h-64 overflow-y-auto">
            {searchResults.length > 0 ? (
              <>
                {searchResults.map((medicine) => (
                  <div
                    key={medicine.id}
                    onClick={() => handleSelectMedicine(medicine)}
                    className="px-4 py-3 hover:bg-gray-100 cursor-pointer border-b"
                  >
                    <div className="font-medium">{medicine.name}</div>
                    {medicine.strength && (
                      <div className="text-sm text-gray-600">{medicine.strength}</div>
                    )}
                  </div>
                ))}
                <div
                  onClick={() => handleAddCustomMedicine()}
                  className="px-4 py-3 hover:bg-blue-50 cursor-pointer text-blue-600 font-medium border-t"
                >
                  + Add Custom Medicine
                </div>
              </>
            ) : (
              <div className="p-4">
                <div className="text-gray-500 mb-3">
                  No medicines found for "{searchQuery}"
                </div>
                <button
                  onClick={() => handleAddCustomMedicine(searchQuery)}
                  className="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                >
                  Add "{searchQuery}" as custom medicine
                </button>
              </div>
            )}
          </div>
        )}
      </div>

      {/* Dosage, Frequency, Duration Fields */}
      <div className="grid grid-cols-3 gap-4 mb-4">
        <div>
          <label className="block text-sm font-medium mb-2">Dosage</label>
          <input
            type="text"
            value={medicineData?.dosage || ''}
            onChange={(e) => handleFieldChange('dosage', e.target.value)}
            placeholder="e.g., 1 tablet"
            className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div>
          <label className="block text-sm font-medium mb-2">Frequency</label>
          <input
            type="text"
            value={medicineData?.frequency || ''}
            onChange={(e) => handleFieldChange('frequency', e.target.value)}
            placeholder="e.g., Twice daily"
            className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        <div>
          <label className="block text-sm font-medium mb-2">Duration</label>
          <input
            type="text"
            value={medicineData?.duration || ''}
            onChange={(e) => handleFieldChange('duration', e.target.value)}
            placeholder="e.g., 7 days"
            className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-blue-500"
          />
        </div>
      </div>

      {/* Instructions */}
      <div>
        <label className="block text-sm font-medium mb-2">Instructions</label>
        <textarea
          value={medicineData?.instructions || ''}
          onChange={(e) => handleFieldChange('instructions', e.target.value)}
          placeholder="Special instructions..."
          rows="3"
          className="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>
    </div>
  );
};

export default PrescriptionMedicineInput;
