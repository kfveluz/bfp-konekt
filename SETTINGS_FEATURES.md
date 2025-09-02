# BFP Konekt - Settings Features

## Overview
The BFP Konekt system now includes comprehensive settings management for monitoring keywords, locations, and system configuration. All settings are stored in the database and can be managed through the admin interface.

## New Features Added

### 1. Database Storage
- All settings are now stored in the `settings` table
- Settings persist across sessions and server restarts
- Admin-only access for settings management

### 2. Monitoring Settings Panel
The new Monitoring Settings panel includes:

#### Keywords Management
- **Keywords Input**: Comma-separated keywords for detecting fire-related incidents
- **Default**: `sunog,fire,nasusunog,fire alert,emergency,disaster`
- **Purpose**: Used to identify relevant posts and messages

#### Locations Management
- **Locations Input**: Comma-separated locations to monitor
- **Default**: `Dasmariñas City,Cavite,Manila,Quezon City,Makati`
- **Purpose**: Geographic areas to focus monitoring efforts

#### Search Query
- **Query Input**: Advanced search query for social media monitoring
- **Default**: `fire emergency OR sunog OR fire alert OR emergency response`
- **Purpose**: Complex search patterns for better detection

#### Notification Keywords
- **Notification Keywords Input**: Keywords that trigger immediate notifications
- **Default**: `sunog,fire,emergency,disaster,alarm`
- **Purpose**: High-priority keywords for urgent alerts

#### Monitoring Locations
- **Monitoring Locations Input**: Specific locations for detailed monitoring
- **Default**: `Dasmariñas City,Cavite`
- **Purpose**: Primary areas for intensive monitoring

### 3. System Settings Integration
- Update interval settings now save to database
- Slider interval settings now save to database
- Notification sound settings remain in localStorage for user preference

### 4. API Endpoints

#### GET /api/settings.php
- Retrieves all settings from database
- Requires admin authentication
- Returns JSON with all setting key-value pairs

#### POST /api/settings.php
- Updates a single setting
- Requires admin authentication
- Body: `{"setting_key": "key", "setting_value": "value"}`

#### PUT /api/settings.php
- Updates multiple settings at once
- Requires admin authentication
- Body: `{"settings": [{"key": "key1", "value": "value1"}, ...]}`

### 5. Database Schema
```sql
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Usage Instructions

### For Administrators

1. **Access Settings**:
   - Log in as admin user
   - Navigate to Settings in the sidebar
   - Click on "Admin Settings"

2. **Configure Monitoring Settings**:
   - Enter keywords separated by commas
   - Enter locations separated by commas
   - Configure advanced search queries
   - Set notification keywords
   - Specify monitoring locations

3. **Save Settings**:
   - Click "Save Monitoring Settings" to save changes
   - Use "Reset to Default" to restore default values

4. **System Settings**:
   - Update interval changes are saved automatically
   - Slider interval changes are saved automatically

### For Developers

#### Testing the API
```bash
# Test settings API
curl -X GET http://your-domain/api/settings.php

# Update a setting
curl -X POST http://your-domain/api/settings.php \
  -H "Content-Type: application/json" \
  -d '{"setting_key": "keywords", "setting_value": "sunog,fire,emergency"}'

# Update multiple settings
curl -X PUT http://your-domain/api/settings.php \
  -H "Content-Type: application/json" \
  -d '{"settings": [{"key": "keywords", "value": "sunog,fire"}, {"key": "locations", "value": "Dasmariñas City"}]}'
```

#### Testing Files
- `test-settings.html`: Standalone test page for settings functionality
- `api/test_settings_api.php`: API test script for database connectivity

## Security Features

1. **Admin Authentication**: All settings operations require admin privileges
2. **Input Validation**: Setting keys are validated against allowed values
3. **SQL Injection Protection**: All database queries use prepared statements
4. **XSS Protection**: Output is properly escaped

## Default Settings

When the system is first installed, these default settings are created:

```php
$defaultSettings = [
    ['update_interval', '1'],
    ['notification_sound', 'enabled'],
    ['keywords', 'sunog,fire,nasusunog,fire alert,emergency,disaster'],
    ['slider_interval', '3500'],
    ['locations', 'Dasmariñas City,Cavite,Manila,Quezon City,Makati'],
    ['query', 'fire emergency OR sunog OR fire alert OR emergency response'],
    ['notification_keywords', 'sunog,fire,emergency,disaster,alarm'],
    ['monitoring_locations', 'Dasmariñas City,Cavite']
];
```

## Integration with Existing Features

### Social Media Monitoring
The keywords and query settings are used by the Apify service to:
- Filter relevant posts from social media
- Identify fire-related incidents
- Trigger notifications for urgent cases

### Notification System
The notification keywords are used to:
- Determine which incidents trigger immediate alerts
- Filter high-priority events
- Manage notification frequency

### Geographic Filtering
The locations settings are used to:
- Focus monitoring on specific areas
- Filter incidents by location
- Provide location-based analytics

## Troubleshooting

### Common Issues

1. **Settings not saving**:
   - Check admin authentication
   - Verify database connection
   - Check browser console for errors

2. **Settings not loading**:
   - Verify settings table exists
   - Check API endpoint accessibility
   - Review server error logs

3. **Permission denied**:
   - Ensure user is logged in as admin
   - Check session validity
   - Verify user type in database

### Testing Steps

1. Run `api/test_settings_api.php` to verify database connectivity
2. Use `test-settings.html` to test the full settings interface
3. Check browser developer tools for any JavaScript errors
4. Verify settings are saved in the database

## Future Enhancements

1. **Settings Import/Export**: Allow backup and restore of settings
2. **Settings History**: Track changes to settings over time
3. **Role-based Settings**: Different settings for different user roles
4. **Settings Validation**: More sophisticated input validation
5. **Settings Templates**: Pre-configured settings for different scenarios 