# Query Settings Integration

## Overview
Yes, the query in the ApifyService **IS changeable** based on the query input in the settings. The system is fully integrated to dynamically update the search query from the database settings.

## How It Works

### 1. **Settings Storage**
- Query is stored in the `settings` table in the database
- Key: `query`
- Value: User-defined search query (e.g., "fire emergency OR sunog OR fire alert")

### 2. **ApifyService Integration**
The ApifyService automatically loads and uses the query from settings:

```javascript
// In ApifyService constructor
this.scraperConfig = {
    query: "#bfpdasmahelp", // Default, will be updated from settings
    // ... other config
};

// Load settings from database
this.loadSettingsFromDatabase();
```

### 3. **Settings Loading Process**
```javascript
async loadSettingsFromDatabase() {
    const response = await fetch('api/settings.php');
    const data = await response.json();
    
    if (data.success && data.data) {
        const settings = data.data;
        
        // Update query from settings
        if (settings.query) {
            this.scraperConfig.query = settings.query;
            console.log('Query loaded from settings:', this.scraperConfig.query);
        }
    }
}
```

### 4. **Real-time Updates**
When settings are saved, the ApifyService is automatically refreshed:

```javascript
// In index.php - saveMonitoringSettings function
if (window.apifyService && typeof window.apifyService.refreshSettings === 'function') {
    await window.apifyService.refreshSettings();
    createNotification('Info', 'Apify monitoring service updated with new settings', 'info');
}
```

## Settings Interface

### Frontend Settings Panel
The settings panel includes:
- **Query Input**: Text area for custom search queries
- **Keywords Input**: Comma-separated keywords
- **Locations Input**: Comma-separated locations
- **Save Button**: Updates database and refreshes ApifyService

### Example Query Formats
```
fire emergency OR sunog OR fire alert OR emergency response
#bfpdasmahelp OR #sunog OR #emergency
fire incident OR nasusunog OR emergency response
```

## Integration Flow

```
1. User enters query in settings panel
2. Settings saved to database via API
3. ApifyService.refreshSettings() called
4. ApifyService.loadSettingsFromDatabase() loads new query
5. this.scraperConfig.query updated with new value
6. Next monitoring cycle uses new query
```

## Verification

### Test the Integration
1. **Open Settings Panel**: Navigate to Settings in the admin panel
2. **Update Query**: Change the query input field
3. **Save Settings**: Click "Save Monitoring Settings"
4. **Check Console**: Look for "Query loaded from settings" message
5. **Verify Monitoring**: New monitoring cycles will use the updated query

### Console Logs
When working correctly, you should see:
```
Keywords loaded from settings: [keywords]
Query loaded from settings: [new query]
Apify monitoring service updated with new settings
```

## Configuration Details

### Default Query
```javascript
query: "#bfpdasmahelp" // Default fallback
```

### Settings Structure
```javascript
{
    query: "fire emergency OR sunog OR fire alert",
    keywords: "sunog,fire,nasusunog,fire alert,emergency,disaster",
    locations: "Dasmariñas City,Cavite,Salawag,Paliparan,Burol",
    notification_keywords: "sunog,fire,emergency,disaster,alarm"
}
```

### ApifyService Configuration
```javascript
this.scraperConfig = {
    max_posts: 100,
    max_retries: 100,
    proxy: { useApifyProxy: false },
    query: settings.query, // Updated from database
    recent_posts: true,
    search_type: "posts"
};
```

## Benefits

### 1. **Dynamic Updates**
- Query can be changed without restarting the system
- Real-time updates to monitoring parameters
- No code changes required for query modifications

### 2. **User-Friendly**
- Web interface for query management
- No technical knowledge required
- Immediate feedback on changes

### 3. **Flexible Search**
- Support for complex queries with OR operators
- Multiple keyword combinations
- Location-specific searches

### 4. **Persistent Storage**
- Settings saved in database
- Survives system restarts
- Consistent across sessions

## Troubleshooting

### Common Issues

1. **Query Not Updating**
   - Check if `refreshSettings()` is called after save
   - Verify database connection
   - Check browser console for errors

2. **Settings Not Loading**
   - Verify `api/settings.php` endpoint
   - Check database permissions
   - Ensure settings table exists

3. **Monitoring Not Restarting**
   - Check if monitoring is active
   - Verify `stopMonitoring()` and `startMonitoring()` calls
   - Check for JavaScript errors

### Debug Steps

1. **Check Current Query**
   ```javascript
   console.log('Current query:', apifyService.scraperConfig.query);
   ```

2. **Test Settings Load**
   ```javascript
   await apifyService.loadSettingsFromDatabase();
   console.log('Updated query:', apifyService.scraperConfig.query);
   ```

3. **Verify Database**
   ```sql
   SELECT * FROM settings WHERE setting_key = 'query';
   ```

## Status: ✅ FULLY INTEGRATED

The query settings integration is **fully functional** and allows dynamic updates to the ApifyService search query through the web interface. The system automatically refreshes the monitoring service when settings are changed, ensuring real-time updates to the search parameters. 