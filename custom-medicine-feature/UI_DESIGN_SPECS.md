# Custom Medicine Feature - UI Design Specifications

## Overview
This document describes the UI changes for the custom medicine feature.

## Component States

### State 1: Default Search Mode
```
┌─────────────────────────────────────────────────────────┐
│ Medicine Name                                           │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ Type to search medicine...                        🔍 │ │
│ └─────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
```

### State 2: Search Results with Matches
```
┌─────────────────────────────────────────────────────────┐
│ Medicine Name                                           │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ Salbutamol                                        ✕ │ │
│ └─────────────────────────────────────────────────────┘ │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ Salbutamol - 2 mg (Tablet)                          │ │
│ │ Manufacturer: ABC Pharma                            │ │
│ ├─────────────────────────────────────────────────────┤ │
│ │ Salbutamol - 4 mg (Tablet)                          │ │
│ │ Manufacturer: XYZ Pharma                            │ │
│ ├─────────────────────────────────────────────────────┤ │
│ │ + Add Custom Medicine                          ✨   │ │
│ └─────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
```

### State 3: No Search Results Found
```
┌─────────────────────────────────────────────────────────┐
│ Medicine Name                                           │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ Custom Herbal Med                                 ✕ │ │
│ └─────────────────────────────────────────────────────┘ │
│ ┌─────────────────────────────────────────────────────┐ │
│ │                                                       │ │
│ │   No medicines found for "Custom Herbal Med"          │ │
│ │                                                       │ │
│ │   ┌───────────────────────────────────────────────┐  │ │
│ │   │ Add "Custom Herbal Med" as custom medicine    │  │ │
│ │   └───────────────────────────────────────────────┘  │ │
│ │                                                       │ │
│ └─────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
```

### State 4: Custom Medicine Mode (After Selection)
```
┌─────────────────────────────────────────────────────────┐
│ Medicine Name              [Custom Entry]               │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ Custom Herbal Medicine XYZ                        ✕ │ │
│ └─────────────────────────────────────────────────────┘ │
│                                                         │
│ ┌──────────────┐  ┌──────────────┐  ┌──────────────┐   │
│ │ Dosage       │  │ Frequency    │  │ Duration     │   │
│ │ 2 capsules   │  │ Twice daily  │  │ 30 days      │   │
│ └──────────────┘  └──────────────┘  └──────────────┘   │
└─────────────────────────────────────────────────────────┘
```

## Color Scheme

### Custom Medicine Indicator Badge
- Background: `#DBEAFE` (blue-100)
- Text: `#1E40AF` (blue-800)
- Border-radius: `4px`
- Padding: `4px 8px`
- Font-size: `12px`

### Add Custom Medicine Button (in dropdown)
- Background: `#EFF6FF` (blue-50) on hover
- Text: `#2563EB` (blue-600)
- Icon: ✨ sparkle or ➕ plus
- Border-top: `1px solid #E5E7EB`

### No Results - Add Custom Button
- Background: `#3B82F6` (blue-500)
- Text: `#FFFFFF` (white)
- Hover: `#2563EB` (blue-600)
- Border-radius: `8px`
- Padding: `12px 24px`

## Typography

### Medicine Name Input
- Font-size: `16px`
- Font-weight: `400` (normal)
- Placeholder color: `#9CA3AF` (gray-400)

### Custom Entry Badge
- Font-size: `12px`
- Font-weight: `500` (medium)
- Text-transform: `none`

### Dropdown Items
- Medicine name: `14px`, `500` (medium)
- Medicine details: `12px`, `400` (normal), `#6B7280` (gray-500)

## Spacing

### Medicine Input Container
- Padding: `16px`
- Margin-bottom: `16px`
- Border: `1px solid #E5E7EB`
- Border-radius: `8px`

### Dropdown
- Max-height: `256px` (16rem)
- Overflow-y: `auto`
- Shadow: `0 10px 15px -3px rgba(0, 0, 0, 0.1)`

### Dropdown Items
- Padding: `12px 16px`
- Border-bottom: `1px solid #F3F4F6`

## Interactions

### Search Input Focus
- Outline: `none`
- Ring: `2px solid #3B82F6` (blue-500)
- Ring-offset: `2px`

### Dropdown Item Hover
- Background: `#F3F4F6` (gray-100)
- Cursor: `pointer`
- Transition: `background-color 150ms ease-in-out`

### Custom Medicine Button Hover
- Background: `#2563EB` (blue-600)
- Transform: `scale(1.02)`
- Transition: `all 150ms ease-in-out`

## Responsive Design

### Desktop (> 1024px)
- Form width: `100%`
- Dosage/Frequency/Duration: 3 columns grid

### Tablet (768px - 1024px)
- Form width: `100%`
- Dosage/Frequency/Duration: 3 columns grid

### Mobile (< 768px)
- Form width: `100%`
- Dosage/Frequency/Duration: 1 column stack
- Dropdown: Full width
- Font-size adjustments for touch targets (minimum 44px height)

## Accessibility

### ARIA Labels
```html
<input 
  type="text"
  aria-label="Medicine name"
  aria-describedby="medicine-help-text"
  aria-autocomplete="list"
  role="combobox"
/>

<div role="listbox" aria-label="Medicine suggestions">
  <div role="option" tabindex="0">Medicine 1</div>
  <div role="option" tabindex="0">Medicine 2</div>
</div>
```

### Keyboard Navigation
- Tab: Move between fields
- Enter: Select dropdown item
- Escape: Close dropdown
- Arrow Up/Down: Navigate dropdown items

### Screen Reader Support
- Announce when switching to custom mode: "Custom medicine mode activated"
- Announce search results count: "5 medicines found"
- Announce when no results: "No medicines found. Add as custom medicine"

## Animation

### Dropdown Open/Close
- Duration: `200ms`
- Easing: `ease-in-out`
- Property: `opacity`, `transform`

### Badge Appearance
- Duration: `150ms`
- Easing: `ease-out`
- Property: `opacity`

## Icons

### Search Icon (🔍)
- Library: Lucide React
- Component: `<Search />`
- Size: `18px`
- Color: `#9CA3AF` (gray-400)

### Close Icon (✕)
- Library: Lucide React
- Component: `<X />`
- Size: `18px`
- Color: `#6B7280` (gray-500)

### Plus Icon (➕)
- Library: Lucide React
- Component: `<Plus />`
- Size: `20px`
- Color: `#2563EB` (blue-600)
