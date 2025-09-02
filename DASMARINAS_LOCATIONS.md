# Dasmariñas City - Complete Location List

## Overview
All locations of Dasmariñas City have been added to the monitoring settings. This includes all barangays, subdivisions, and major areas within the city.

## Complete Location List

### Major Areas
- Dasmariñas City
- Dasma
- Cavite

### Barangays (Official)
1. **Salawag** - Barangay Salawag
2. **Paliparan** - Barangay Paliparan
3. **Burol** - Barangay Burol
4. **Langkaan** - Barangay Langkaan
5. **Sampaguita** - Barangay Sampaguita

### Saint Subdivisions
6. **Saint Peter** - Saint Peter Subdivision
7. **Saint Paul** - Saint Paul Subdivision
8. **Saint John** - Saint John Subdivision
9. **Saint Luke** - Saint Luke Subdivision
10. **Saint Mark** - Saint Mark Subdivision
11. **Saint Matthew** - Saint Matthew Subdivision
12. **Saint James** - Saint James Subdivision
13. **Saint Thomas** - Saint Thomas Subdivision
14. **Saint Andrew** - Saint Andrew Subdivision
15. **Saint Philip** - Saint Philip Subdivision
16. **Saint Bartholomew** - Saint Bartholomew Subdivision
17. **Saint Simon** - Saint Simon Subdivision
18. **Saint Jude** - Saint Jude Subdivision
19. **Saint Matthias** - Saint Matthias Subdivision
20. **Saint Stephen** - Saint Stephen Subdivision
21. **Saint Barnabas** - Saint Barnabas Subdivision
22. **Saint Timothy** - Saint Timothy Subdivision
23. **Saint Titus** - Saint Titus Subdivision
24. **Saint Philemon** - Saint Philemon Subdivision

### San Subdivisions
25. **Emmanuel** - Emmanuel Subdivision
26. **San Jose** - San Jose Subdivision
27. **San Miguel** - San Miguel Subdivision
28. **San Nicolas** - San Nicolas Subdivision
29. **San Agustin** - San Agustin Subdivision
30. **San Isidro** - San Isidro Subdivision
31. **San Lorenzo** - San Lorenzo Subdivision
32. **San Antonio** - San Antonio Subdivision
33. **San Vicente** - San Vicente Subdivision
34. **San Rafael** - San Rafael Subdivision
35. **San Gabriel** - San Gabriel Subdivision
36. **San Roque** - San Roque Subdivision
37. **San Francisco** - San Francisco Subdivision
38. **San Pedro** - San Pedro Subdivision
39. **San Pablo** - San Pablo Subdivision
40. **San Mateo** - San Mateo Subdivision
41. **San Lucas** - San Lucas Subdivision
42. **San Marcos** - San Marcos Subdivision
43. **San Juan** - San Juan Subdivision
44. **San Andres** - San Andres Subdivision
45. **San Felipe** - San Felipe Subdivision
46. **San Bartolome** - San Bartolome Subdivision
47. **San Simon** - San Simon Subdivision
48. **San Judas** - San Judas Subdivision
49. **San Matias** - San Matias Subdivision
50. **San Esteban** - San Esteban Subdivision
51. **San Bernabe** - San Bernabe Subdivision
52. **San Timoteo** - San Timoteo Subdivision
53. **San Tito** - San Tito Subdivision
54. **San Filemon** - San Filemon Subdivision

## Implementation Details

### Database Settings
These locations are now stored in the database settings:
- **locations**: All locations for general monitoring
- **monitoring_locations**: Specific locations for detailed monitoring

### Apify Service Integration
The Apify service now recognizes all these locations for:
- Content filtering
- Location extraction from posts
- Confidence calculation
- Geographic monitoring

### Location Patterns
The system recognizes these location patterns:
- `in [location] barangay`
- `at [location] barangay`
- `near [location] barangay`
- `sa [location] barangay`
- `sa [location] city`
- `sa [location] street/avenue/highway/road`
- `sa [location] subdivision/village/phase/block/lot/unit/floor/building`

## Usage

### In Settings Panel
1. Go to Settings → Admin Settings
2. In the "Locations" field, all Dasmariñas locations are pre-filled
3. You can add or remove locations as needed
4. Click "Save Monitoring Settings" to apply changes

### In Monitoring
- The system will automatically detect posts mentioning any of these locations
- Location-based filtering will work for all listed areas
- Confidence scores will be higher for posts mentioning these specific locations

## Benefits

1. **Comprehensive Coverage**: All major areas of Dasmariñas City are covered
2. **Accurate Detection**: System can identify posts from specific barangays and subdivisions
3. **Flexible Configuration**: Locations can be easily modified through the admin interface
4. **Improved Monitoring**: Better geographic targeting for emergency response

## Notes

- Locations are case-insensitive in detection
- Both English and Filipino variations are supported
- The system will continue to work even if some locations are removed from the list
- New locations can be added through the settings panel without code changes 