import React, { useState, useEffect } from 'react';
import { Search, X } from 'lucide-react';
import './custom-medicine-styles.css'; // Import CSS file

/**
 * Medicine Input Component with Custom Medicine Support (CSS Version)
 * Uses custom-medicine-styles.css instead of Tailwind
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

  useEffect(() => {
    if (medicineData?.is_custom) {
      setIsCustomMode(true);
      setSearchQuery(medicineData.custom_medicine_name || '');
    } else if (medicineData?.medicine_id) {
      setSelectedMedicine(medicineData);
      setSearchQuery(medicineData.medicine_name || '');
    }
  }, [medicineData]);

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

  const handleCustomNameChange = (value) => {
    setSearchQuery(value);
    onMedicineChange(index, {
      ...medicineData,
      custom_medicine_name: value
    });
  };

  const handleFieldChange = (field, value) => {
    onMedicineChange(index, {
      ...medicineData,
      [field]: value
    });
  };

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
    <div className="medicine-input-container">
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '12px' }}>
        <h3>Medicine {index + 1}</h3>
        {index > 0 && (
          <button 
            onClick={() => onRemove(index)}
            className="medicine-remove-btn"
            type="button"
          >
            <X size={20} />
          </button>
        )}
      </div>

      {/* Medicine Name Input */}
      <div className="medicine-search-container">
        <label className="medicine-label">
          Medicine Name
          {isCustomMode && (
            <span className="custom-entry-badge">
              Custom Entry
            </span>
          )}
        </label>
        
        <div style={{ position: 'relative' }}>
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
            className="medicine-input"
          />
          
          {searchQuery && (
            <button
              onClick={handleClearSelection}
              className="medicine-clear-btn"
              type="button"
            >
              <X size={18} />
            </button>
          )}
        </div>

        {/* Dropdown for search results */}
        {showDropdown && !isCustomMode && (
          <div className="medicine-dropdown">
            {searchResults.length > 0 ? (
              <>
                {searchResults.map((medicine) => (
                  <div
                    key={medicine.id}
                    onClick={() => handleSelectMedicine(medicine)}
                    className="medicine-dropdown-item"
                  >
                    <div className="medicine-dropdown-name">{medicine.name}</div>
                    {medicine.strength && (
                      <div className="medicine-dropdown-details">{medicine.strength}</div>
                    )}
                  </div>
                ))}
                <button
                  onClick={() => handleAddCustomMedicine()}
                  className="add-custom-dropdown-btn"
                  type="button"
                >
                  + Add Custom Medicine
                </button>
              </>
            ) : (
              <div className="medicine-no-results">
                <div className="medicine-no-results-text">
                  No medicines found for "{searchQuery}"
                </div>
                <button
                  onClick={() => handleAddCustomMedicine(searchQuery)}
                  className="add-custom-primary-btn"
                  type="button"
                >
                  Add "{searchQuery}" as custom medicine
                </button>
              </div>
            )}
          </div>
        )}
      </div>

      {/* Dosage, Frequency, Duration Fields */}
      <div className="medicine-fields-grid">
        <div>
          <label className="medicine-label">Dosage</label>
          <input
            type="text"
            value={medicineData?.dosage || ''}
            onChange={(e) => handleFieldChange('dosage', e.target.value)}
            placeholder="e.g., 1 tablet"
            className="medicine-input"
          />
        </div>

        <div>
          <label className="medicine-label">Frequency</label>
          <input
            type="text"
            value={medicineData?.frequency || ''}
            onChange={(e) => handleFieldChange('frequency', e.target.value)}
            placeholder="e.g., Twice daily"
            className="medicine-input"
          />
        </div>

        <div>
          <label className="medicine-label">Duration</label>
          <input
            type="text"
            value={medicineData?.duration || ''}
            onChange={(e) => handleFieldChange('duration', e.target.value)}
            placeholder="e.g., 7 days"
            className="medicine-input"
          />
        </div>
      </div>

      {/* Instructions */}
      <div>
        <label className="medicine-label">Instructions</label>
        <textarea
          value={medicineData?.instructions || ''}
          onChange={(e) => handleFieldChange('instructions', e.target.value)}
          placeholder="Special instructions..."
          rows="3"
          className="medicine-textarea"
        />
      </div>
    </div>
  );
};

export default PrescriptionMedicineInput;
