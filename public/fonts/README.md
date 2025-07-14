# Kurdish Font Setup for PDF Generation

## Navshke Font Installation

To enable proper Kurdish text rendering in PDFs, you can install the Navshke font:

### Steps:

1. **Download Navshke Font Files:**
   - Get `Navshke-Regular.ttf`
   - Get `Navshke-Bold.ttf` (if available)

2. **Place Font Files:**
   - Copy the font files to this directory: `public/fonts/`
   - Ensure the files are named exactly:
     - `Navshke-Regular.ttf`
     - `Navshke-Bold.ttf`

3. **Font Fallbacks:**
   If Navshke is not available, the system will use these fallback fonts in order:
   - DejaVu Sans (good Unicode support)
   - Arial Unicode MS
   - Tahoma

### Alternative Kurdish Fonts:

If Navshke is not available, you can use these alternatives:
- **Noto Sans Arabic** (excellent Kurdish support)
- **Amiri** (traditional Arabic/Kurdish font)
- **Scheherazade New** (SIL font with Kurdish support)

### Testing:

After adding the font files, test by:
1. Creating a nutrition plan with Kurdish food names
2. Generating a PDF
3. Checking that Kurdish text displays with proper letter connections

### Font Features:

The Kurdish text styling includes:
- Right-to-left text direction
- Proper letter shaping and connections
- Unicode bidirectional support
- Optimized text rendering
