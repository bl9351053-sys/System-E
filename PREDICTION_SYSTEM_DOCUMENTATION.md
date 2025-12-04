# Disaster Prediction System Documentation

## Overview
The Evacuation Management System now features an **automated disaster prediction system** that generates predictions based on real-time data from official Philippine government agencies: **PAGASA**, **PhiVolcs**, and **NDRRMC**.

## Data Sources

### 1. PAGASA (Philippine Atmospheric, Geophysical and Astronomical Services Administration)
- **Weather Data**: Temperature, humidity, wind speed, pressure
- **Rainfall Monitoring**: Real-time rainfall measurements
- **Tropical Cyclone Tracking**: Active typhoons and their projected paths
- **Flood Warnings**: Official flood advisories for affected areas
- **Weather Advisories**: General weather bulletins and warnings

### 2. PhiVolcs (Philippine Institute of Volcanology and Seismology)
- **Earthquake Monitoring**: Recent seismic activity (magnitude, location, depth)
- **Fault Line Data**: Active fault lines and their risk levels
- **Volcano Status**: Alert levels and volcanic activity monitoring
- **Tsunami Advisories**: Tsunami threat assessments
- **Geological Assessments**: Terrain and landslide susceptibility data

### 3. NDRRMC (National Disaster Risk Reduction and Management Council)
- **Situation Reports**: Current disaster response status
- **Evacuation Statistics**: Number of evacuation centers and evacuees
- **Emergency Hotlines**: Contact information for disaster response
- **Preparedness Guidelines**: Official disaster preparedness information

## Prediction Types

### 1. Earthquake Predictions
**Data Sources**: PhiVolcs fault line data + recent seismic activity

**Prediction Logic**:
- Monitors active fault lines within 100km of Metro Manila
- Tracks recent earthquakes (magnitude ≥ 5.0) for aftershock predictions
- Risk levels based on fault line characteristics and historical movement
- Recovery time: 14-28 days depending on risk level

**Factors Considered**:
- Fault line length and type
- Last recorded movement
- Distance from populated areas
- Recent seismic activity patterns

### 2. Flood Predictions
**Data Sources**: PAGASA rainfall data + flood warnings

**Prediction Logic**:
- Analyzes 24-hour rainfall accumulation
- Triggers prediction when:
  - Total rainfall > 50mm in 24 hours, OR
  - 2+ periods of heavy rainfall (>15mm/3hrs)
- Risk level scales with rainfall intensity
- Recovery time: 3-5 days depending on severity

**Factors Considered**:
- Total rainfall amount
- Intensity and duration of rain
- Urban drainage capacity
- Historical flooding patterns
- Active PAGASA flood warnings

### 3. Typhoon Predictions
**Data Sources**: PAGASA tropical cyclone bulletins

**Prediction Logic**:
- Tracks active tropical cyclones in Philippine Area of Responsibility (PAR)
- Creates predictions for typhoons approaching landfall
- Risk level based on wind speed and category
- Recovery time: ~10 days

**Factors Considered**:
- Typhoon category and wind speed
- Projected track and landfall location
- Expected time of arrival
- Historical impact data

### 4. Landslide Predictions
**Data Sources**: PAGASA rainfall + PhiVolcs geological data

**Prediction Logic**:
- Triggered when rainfall > 60mm in 24 hours
- Focuses on mountainous areas with steep terrain
- Risk level increases with rainfall amount
- Recovery time: 14 days

**Factors Considered**:
- Rainfall accumulation
- Terrain slope and soil saturation
- Historical landslide-prone areas
- PhiVolcs geological assessments

### 5. Volcanic Activity Predictions
**Data Sources**: PhiVolcs volcano monitoring

**Prediction Logic**:
- Monitors volcanoes at Alert Level 2 or higher
- Creates predictions for areas within volcanic activity zones
- Risk level correlates with alert level
- Recovery time: 14-30 days

**Factors Considered**:
- Current alert level (0-5 scale)
- Volcanic status (normal/abnormal)
- Recent eruption history
- Seismic activity and volcanic tremors

## How Predictions Are Generated

### Automatic Sync Process
1. User clicks "Sync from PAGASA/PhiVolcs" button
2. System fetches latest data from all three agencies
3. Data is analyzed using prediction algorithms
4. New predictions are created if thresholds are met
5. Existing predictions are not duplicated (checked by location/time)

### Prediction Attributes
Each prediction includes:
- **Disaster Type**: earthquake, flood, typhoon, landslide
- **Location**: Specific area name with coordinates
- **Risk Level**: 1-10 scale (10 = highest risk)
- **Recovery Days**: Estimated time for area to recover
- **Prediction Factors**: Detailed explanation citing data sources
- **Timestamp**: When prediction was generated

### Risk Level Scale
- **9-10**: Critical - Immediate evacuation recommended
- **7-8**: High - Prepare for evacuation
- **5-6**: Moderate - Monitor situation closely
- **3-4**: Low - Be aware and prepared
- **1-2**: Minimal - Normal precautions

## API Integration

### Service Classes
- **`PagasaApiService.php`**: Handles PAGASA data fetching
- **`PhivolcsApiService.php`**: Handles PhiVolcs data fetching
- **`NdrmcApiService.php`**: Handles NDRRMC data fetching

### Controller Methods
- **`RealTimeDataController::syncData()`**: Main sync endpoint
- **`RealTimeDataController::createPredictionsFromData()`**: Orchestrates prediction creation
- **`createEarthquakePredictions()`**: Earthquake-specific logic
- **`createFloodPredictions()`**: Flood-specific logic
- **`createTyphoonPredictions()`**: Typhoon-specific logic
- **`createLandslidePredictions()`**: Landslide-specific logic
- **`createVolcanoPredictions()`**: Volcano-specific logic

## Data Caching

To ensure system performance and avoid overwhelming government APIs:
- Weather data: Cached for 5 minutes
- Earthquake data: Cached for 5 minutes
- Rainfall data: Cached for 5 minutes
- Flood warnings: Cached for 10 minutes
- Volcano status: Cached for 1 hour
- Fault line data: Cached for 1 hour

## User Interface

### Predictions Index Page
- Displays all active predictions in a table
- Shows risk level with visual indicators
- Includes "Sync from PAGASA/PhiVolcs" button
- Information banner explaining data sources
- Risk analysis chart

### Prediction Details Page
- Full prediction information
- Risk level visualization
- Recovery time estimate
- Detailed prediction factors with data source citations
- Location map
- Note explaining automatic generation from official sources

## Benefits

1. **Real-Time Accuracy**: Predictions based on actual government data
2. **Automated Updates**: No manual entry required
3. **Multi-Source Integration**: Combines data from three official agencies
4. **Transparent Sources**: Each prediction cites its data sources
5. **Risk Assessment**: Quantified risk levels for decision-making
6. **Recovery Planning**: Estimated recovery times for resource allocation

## Future Enhancements

1. **Machine Learning**: Train models on historical disaster data
2. **Advanced Analytics**: Combine multiple data sources for better accuracy
3. **Real-Time Alerts**: Push notifications for high-risk predictions
4. **API Webhooks**: Automatic updates when agencies publish new data
5. **Historical Validation**: Compare predictions with actual outcomes
6. **Community Reporting**: Integrate citizen reports for validation

## Testing & Validation

To test the prediction system:
1. Navigate to "Predictions" page
2. Click "Sync from PAGASA/PhiVolcs" button
3. System will fetch latest data and generate predictions
4. Check prediction factors to verify data sources are cited
5. View individual predictions for detailed information

## Notes

- Predictions are generated automatically when sync is triggered
- Duplicate predictions are prevented by checking location and time
- All predictions include citations to their data sources
- The system respects API rate limits through caching
- Manual prediction creation is still available for testing purposes

## Support

For issues or questions about the prediction system:
- Check logs in `storage/logs/laravel.log`
- Verify API keys in `.env` file (OPENWEATHER_API_KEY)
- Ensure internet connectivity for API access
- Contact system administrator for API access issues
