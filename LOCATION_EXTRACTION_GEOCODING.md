# Location Extraction & Geocoding System

## Overview
The BFP Konekt system now includes advanced location extraction and geocoding capabilities that automatically detect locations from social media posts and convert them to map coordinates for pinning on the interactive map.

## How It Works

### 1. **Location Detection Flow**
```
Social Media Post → NLP Processing → Location Extraction → Geocoding → Map Pin
```

### 2. **NLP Location Patterns**
The system recognizes these location patterns in text:
- `sa [location] barangay/brgy/bgy`
- `at [location] barangay`
- `in [location] barangay`
- `near [location] barangay`
- `location: [location]`
- `sa [location] city/street/avenue/highway/road`
- `sa [location] subdivision/village/phase/block/lot/unit/floor/building`

### 3. **Supported Locations**
All 54 Dasmariñas City locations are supported:

#### Major Areas
- Dasmariñas City, Dasma, Cavite

#### Official Barangays (5)
- Salawag, Paliparan, Burol, Langkaan, Sampaguita

#### Saint Subdivisions (19)
- Saint Peter, Saint Paul, Saint John, Saint Luke, Saint Mark, Saint Matthew, Saint James, Saint Thomas, Saint Andrew, Saint Philip, Saint Bartholomew, Saint Simon, Saint Jude, Saint Matthias, Saint Stephen, Saint Barnabas, Saint Timothy, Saint Titus, Saint Philemon

#### San Subdivisions (30)
- Emmanuel, San Jose, San Miguel, San Nicolas, San Agustin, San Isidro, San Lorenzo, San Antonio, San Vicente, San Rafael, San Gabriel, San Roque, San Francisco, San Pedro, San Pablo, San Mateo, San Lucas, San Marcos, San Juan, San Andres, San Felipe, San Bartolome, San Simon, San Judas, San Matias, San Esteban, San Bernabe, San Timoteo, San Tito, San Filemon

#### Major Landmarks
- SM City Dasmariñas, Robinsons Place Dasmariñas, Walter Mart Dasmariñas
- De La Salle University Dasmariñas, DLSUMC
- Dasmariñas City Hall, BFP Dasmariñas City Main Station, BFP Salawag Sub-Station

## Implementation Details

### 1. **ApifyService Location Extraction**
```javascript
// Enhanced extractLocation method
extractLocation(text) {
    // Returns: { name: "Location Name", coordinates: { lat: 14.3294, lng: 120.9367 } }
}
```

### 2. **Geocoding API**
```php
// api/geocode.php
// Input: ?q=Salawag
// Output: { "success": true, "coordinates": { "lat": 14.3345, "lng": 120.9423 } }
```

### 3. **Coordinate Database**
All locations have predefined coordinates for instant mapping:
```javascript
const locationCoordinates = {
    'Salawag': { lat: 14.3345, lng: 120.9423 },
    'Saint Peter': { lat: 14.3285, lng: 120.9370 },
    'San Jose': { lat: 14.3301, lng: 120.9356 },
    // ... all 54+ locations
};
```

## Usage Examples

### 1. **Text Processing**
```javascript
const apifyService = new ApifyService();

// Example 1: Barangay mention
const text1 = "May sunog sa Salawag barangay, emergency response needed!";
const location1 = apifyService.extractLocation(text1);
// Result: { name: "Salawag", coordinates: { lat: 14.3345, lng: 120.9423 } }

// Example 2: Subdivision mention
const text2 = "Emergency sa Saint Peter subdivision";
const location2 = apifyService.extractLocation(text2);
// Result: { name: "Saint Peter", coordinates: { lat: 14.3285, lng: 120.9370 } }

// Example 3: Landmark mention
const text3 = "Fire alert sa SM City Dasmariñas";
const location3 = apifyService.extractLocation(text3);
// Result: { name: "SM City Dasmariñas", coordinates: { lat: 14.3289, lng: 120.9372 } }
```

### 2. **Geocoding API Usage**
```javascript
// Direct API call
const response = await fetch('api/geocode.php?q=Salawag');
const data = await response.json();
// Result: { success: true, coordinates: { lat: 14.3345, lng: 120.9423 } }
```

### 3. **Map Integration**
```javascript
// When processing incidents
const incident = {
    id: 'incident_123',
    message: 'May sunog sa Salawag barangay',
    location: extractedLocation.name,
    coordinates: extractedLocation.coordinates
};

// Add to map
if (incident.coordinates) {
    const marker = L.marker([incident.coordinates.lat, incident.coordinates.lng])
        .addTo(map)
        .bindPopup(`<b>${incident.location}</b><br>${incident.message}`);
}
```

## Features

### 1. **Intelligent Pattern Matching**
- Recognizes multiple location patterns
- Handles both English and Filipino text
- Case-insensitive matching
- Partial location matching

### 2. **Fallback System**
- If exact location not found, uses partial matching
- If no match found, uses Dasmariñas City center coordinates
- Graceful degradation ensures system always works

### 3. **Real-time Processing**
- Location extraction happens in real-time as posts are processed
- Coordinates are immediately available for map pinning
- No external API delays

### 4. **Comprehensive Coverage**
- All major areas of Dasmariñas City covered
- Includes barangays, subdivisions, landmarks
- Extensible for new locations

## Testing

### Test Files
1. **`test-location-geocoding.html`**: Comprehensive test interface
2. **`test-apify-settings.html`**: Apify service integration test

### Test Scenarios
1. **Manual Testing**: Enter custom text to test location extraction
2. **API Testing**: Test geocoding API directly
3. **Predefined Cases**: Test with common location patterns
4. **Integration Testing**: Test full Apify service integration

### Example Test Cases
```javascript
const testCases = [
    'May sunog sa Salawag barangay',
    'Emergency sa Saint Peter subdivision',
    'Fire alert sa San Jose',
    'May nasusunog sa Paliparan',
    'Emergency response needed sa Burol',
    'Fire alert sa SM City Dasmariñas'
];
```

## Benefits

### 1. **Automatic Map Pinning**
- No manual coordinate entry needed
- Locations automatically appear on map
- Real-time incident visualization

### 2. **Accurate Location Detection**
- NLP-based pattern recognition
- Comprehensive location database
- High accuracy for Dasmariñas City

### 3. **Improved Emergency Response**
- Precise location information
- Faster response coordination
- Better resource allocation

### 4. **User-Friendly**
- Works with natural language
- Handles various text formats
- No special formatting required

## Integration Points

### Frontend
- ApifyService location extraction
- Map marker creation
- Real-time incident display

### Backend
- Geocoding API (`api/geocode.php`)
- Location coordinate database
- Incident processing pipeline

### Database
- Settings integration for location keywords
- Incident storage with coordinates
- Location-based filtering

## Future Enhancements

1. **Advanced NLP**: Machine learning for better location recognition
2. **Dynamic Geocoding**: Integration with external geocoding services
3. **Location Validation**: Verify coordinates against actual addresses
4. **Multi-language Support**: Support for more languages
5. **Location History**: Track location mentions over time
6. **Heat Maps**: Visualize incident density by location

## Troubleshooting

### Common Issues

1. **Location Not Detected**
   - Check if location is in the supported list
   - Verify text format matches patterns
   - Test with geocoding API directly

2. **Wrong Coordinates**
   - Verify location name spelling
   - Check coordinate database
   - Test with known locations

3. **No Map Pin**
   - Ensure coordinates are valid
   - Check map initialization
   - Verify marker creation code

### Debug Steps

1. **Test Location Extraction**
   ```javascript
   const location = apifyService.extractLocation(text);
   console.log('Extracted location:', location);
   ```

2. **Test Geocoding API**
   ```bash
   curl "api/geocode.php?q=Salawag"
   ```

3. **Check Coordinates**
   ```javascript
   console.log('Coordinates:', location.coordinates);
   ```

This system provides comprehensive location detection and mapping capabilities for the BFP Konekt emergency response platform. 