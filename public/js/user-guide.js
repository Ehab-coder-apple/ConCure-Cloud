// ConCure User Guide JavaScript
console.log('User Guide JavaScript loading from external file...');

// Language switching function
function setGuideLanguage(lang) {
    console.log('Switching to language:', lang);
    
    // Hide all content sections
    var allSections = document.querySelectorAll('.guide-content');
    for (var i = 0; i < allSections.length; i++) {
        allSections[i].style.display = 'none';
        allSections[i].classList.add('d-none');
        allSections[i].classList.remove('d-block');
    }

    // Show selected language section
    var targetSection = document.querySelector('[data-lang="' + lang + '"]');
    console.log('Looking for section with data-lang="' + lang + '"');
    console.log('Found section:', targetSection);

    if (targetSection) {
        targetSection.style.display = 'block';
        targetSection.classList.remove('d-none');
        targetSection.classList.add('d-block');
        console.log('Showing content for:', lang);
        console.log('Section classes after update:', targetSection.className);
        console.log('Section display style:', targetSection.style.display);
    } else {
        console.log('Content not found for:', lang);
        // Show English as fallback
        var englishSection = document.querySelector('[data-lang="en"]');
        if (englishSection) {
            englishSection.style.display = 'block';
            englishSection.classList.remove('d-none');
            englishSection.classList.add('d-block');
        }
    }
    
    // Update language indicator
    var languageNames = {
        'en': 'English',
        'ar': 'العربية (Arabic)',
        'ku-bahdeni': 'کوردی بادینی (Kurdish Bahdeni)',
        'ku-sorani': 'کوردی سۆرانی (Kurdish Sorani)'
    };
    
    var currentLangElement = document.getElementById('currentGuideLanguage');
    if (currentLangElement) {
        currentLangElement.textContent = languageNames[lang] || 'English';
    }
    
    var dropdownText = document.getElementById('currentLanguageText');
    if (dropdownText) {
        dropdownText.textContent = languageNames[lang] || 'English';
    }
    
    // Save preference
    localStorage.setItem('userGuideLanguage', lang);
}

// PDF export function
function exportUserGuide() {
    console.log('Exporting PDF...');
    
    var currentLang = localStorage.getItem('userGuideLanguage') || 'en';
    var content = document.querySelector('[data-lang="' + currentLang + '"]');
    
    if (!content) {
        alert('No content available for export');
        return;
    }
    
    // Create print window
    var printWindow = window.open('', '_blank');
    if (!printWindow) {
        alert('Please allow popups to export PDF');
        return;
    }
    
    var languageNames = {
        'en': 'English',
        'ar': 'العربية',
        'ku-bahdeni': 'کوردی بادینی',
        'ku-sorani': 'کوردی سۆرانی'
    };
    
    var isRTL = currentLang === 'ar' || currentLang.includes('ku-');
    var today = new Date().toLocaleDateString();
    
    var htmlContent = '<!DOCTYPE html>' +
        '<html' + (isRTL ? ' dir="rtl"' : '') + '>' +
        '<head>' +
            '<meta charset="UTF-8">' +
            '<title>ConCure User Guide - ' + languageNames[currentLang] + '</title>' +
            '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">' +
            '<style>' +
                'body { font-family: Arial, sans-serif; font-size: 12px; }' +
                '.card { margin-bottom: 20px; break-inside: avoid; }' +
                '.card-header { background-color: #f8f9fa !important; color: #000 !important; }' +
                '@media print { .no-print { display: none !important; } }' +
            '</style>' +
        '</head>' +
        '<body>' +
            '<div class="container p-4">' +
                '<div class="text-center mb-4">' +
                    '<h2>ConCure User Guide</h2>' +
                    '<h4>' + languageNames[currentLang] + '</h4>' +
                    '<p>Generated on ' + today + '</p>' +
                '</div>' +
                content.innerHTML +
            '</div>' +
            '<script>window.onload = function() { setTimeout(function() { window.print(); }, 500); };</script>' +
        '</body>' +
        '</html>';
    
    printWindow.document.write(htmlContent);
    printWindow.document.close();
}

// Make functions globally available
window.setGuideLanguage = setGuideLanguage;
window.exportUserGuide = exportUserGuide;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('User guide initializing...');
    
    // Load saved language or default to English
    var savedLang = localStorage.getItem('userGuideLanguage') || 'en';
    setGuideLanguage(savedLang);
    
    console.log('User guide initialized with language:', savedLang);
});

console.log('User Guide JavaScript loaded successfully from external file');
console.log('Functions available:', typeof setGuideLanguage, typeof exportUserGuide);
