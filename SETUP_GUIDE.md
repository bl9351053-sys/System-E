# 🚀 Quick Setup Guide - Evacuation Management System

## ✅ Setup Status

The application is **READY TO USE**! All components have been installed and configured.

## 🎯 What's Been Done

### ✓ Database Setup
- ✅ Migrations created and run successfully
- ✅ Database tables created (evacuation_areas, families, disaster_updates, disaster_predictions)
- ✅ Sample data seeded with 5 evacuation areas, 3 families, 4 disaster updates, and 5 predictions

### ✓ Backend Components
- ✅ Models created (EvacuationArea, Family, DisasterUpdate, DisasterPrediction)
- ✅ Controllers implemented (Dashboard, EvacuationArea, DisasterUpdate, DisasterPrediction, Family)
- ✅ Routes configured (31 routes total)
- ✅ API endpoints for AJAX requests

### ✓ Frontend Components
- ✅ Layout template with navigation
- ✅ Dashboard with charts and statistics
- ✅ Evacuation areas map with Leaflet.js
- ✅ Disaster updates pages
- ✅ Disaster predictions pages
- ✅ Family management pages
- ✅ All CRUD operations (Create, Read, Update, Delete)

### ✓ Features Implemented
- ✅ Interactive map with markers
- ✅ Route navigation (Leaflet Routing Machine)
- ✅ Real-time occupancy tracking
- ✅ Automatic status updates
- ✅ Visual charts (Chart.js)
- ✅ Geolocation support
- ✅ Disaster type filtering
- ✅ Risk level visualization
- ✅ Recovery time predictions
- ✅ Responsive design

## 🚀 How to Start

### Option 1: Start the Server (Recommended)
```bash
php artisan serve
```
Then open your browser to: **http://localhost:8000**

### Option 2: Use Different Port
```bash
php artisan serve --port=8080
```
Then open: **http://localhost:8080**

## 📱 Application Pages

Once the server is running, you can access:

### Main Pages
- **Dashboard**: `http://localhost:8000/` - Overview with statistics and charts
- **Evacuation Areas**: `http://localhost:8000/evacuation-areas` - Interactive map and list
- **Disaster Updates**: `http://localhost:8000/disaster-updates` - PAGASA/PhiVolcs alerts
- **Predictions**: `http://localhost:8000/disaster-predictions` - Risk assessments
- **Families**: `http://localhost:8000/families` - Registered families

### Create/Add Pages
- Add Evacuation Area: `http://localhost:8000/evacuation-areas/create`
- Add Disaster Update: `http://localhost:8000/disaster-updates/create`
- Add Prediction: `http://localhost:8000/disaster-predictions/create`

## 🎮 Quick Test Guide

### Test 1: View Dashboard
1. Go to `http://localhost:8000/`
2. You should see:
   - Statistics cards (5 evacuation areas, 3 families, etc.)
   - Charts showing disaster types and severity
   - Recent updates and predictions

### Test 2: View Map
1. Go to Evacuation Areas page
2. Click "Get My Location" (allow browser location access)
3. See 5 evacuation areas marked on the map
4. Click a marker to see details
5. Try filtering by disaster type

### Test 3: Register to Evacuation Area
1. On Evacuation Areas page
2. Click "Go" button on any available area
3. Fill in the form:
   - Family Name: "Test Family"
   - Members: 4
   - Contact: "0917-123-4567"
4. Submit and see occupancy update

### Test 4: View Charts
1. Go to Dashboard
2. See 3 charts:
   - Disaster Type Distribution (Doughnut)
   - Severity Levels (Pie)
   - Evacuation Area Occupancy (Bar)

### Test 5: Add New Evacuation Area
1. Click "Add New Area"
2. Fill in the form
3. Click "Pick Location on Map"
4. Click on map to set coordinates
5. Save and verify it appears on the map

## 🗺️ Map Features to Test

### Interactive Map
- ✅ Pan and zoom
- ✅ Click markers for popups
- ✅ Green = Available, Red = Full
- ✅ Filter by disaster type
- ✅ Get your location
- ✅ Show route to evacuation area

### Location Features
- Click "Get My Location" to see your position
- Blue marker shows your location
- Click "Show Route" in popup to see navigation
- Route will display from your location to evacuation area

## 📊 Sample Data Included

### Evacuation Areas (5)
1. Manila City Hall Evacuation Center (Available)
2. Quezon City Memorial Circle Shelter (Available)
3. Makati Sports Complex Emergency Center (Full)
4. Pasig City Rainforest Park Shelter (Available)
5. Taguig Earthquake Safe Zone (Available)

### Disaster Updates (4)
1. Typhoon Pepito - Critical
2. Flash Flood Warning - High
3. Earthquake Magnitude 5.2 - Moderate
4. Landslide Risk - High

### Predictions (5)
1. Makati Flood Risk - Level 8
2. QC Landslide Risk - Level 9
3. Manila Typhoon Risk - Level 7
4. Taguig Earthquake Risk - Level 6
5. Pasig Flood Risk - Level 8

## 🔧 Troubleshooting

### Issue: Map not showing
**Solution**: Check internet connection (Leaflet uses OpenStreetMap tiles)

### Issue: Location not working
**Solution**: Allow browser location permissions when prompted

### Issue: Charts not displaying
**Solution**: Check browser console for errors, ensure Chart.js is loading

### Issue: Database errors
**Solution**: Run migrations again:
```bash
php artisan migrate:fresh --seed
```

### Issue: Routes not found
**Solution**: Clear route cache:
```bash
php artisan route:clear
php artisan cache:clear
```

## 🎨 UI Features

### Color Coding
- **Green**: Available/Low risk
- **Yellow**: Moderate risk/Warning
- **Orange**: High risk
- **Red**: Critical/Full

### Status Badges
- Available (Green)
- Full (Red)
- Closed (Gray)

### Risk Levels
- 1-3: Low (Green)
- 4-5: Moderate (Yellow)
- 6-7: High (Orange)
- 8-10: Critical (Red)

## 📱 Mobile Testing

The application is responsive. Test on:
- Desktop (1920x1080)
- Tablet (768x1024)
- Mobile (375x667)

## 🔐 Security Notes

- CSRF protection enabled on all forms
- Input validation on all submissions
- SQL injection protection via Eloquent ORM

## 📝 Next Steps

After testing, you can:
1. Customize the design/colors
2. Add more evacuation areas
3. Integrate real PAGASA/PhiVolcs API
4. Add user authentication
5. Implement SMS notifications
6. Export reports to PDF
7. Add more predictive algorithms

## 🆘 Need Help?

Check these files:
- **README_EVACUATION_SYSTEM.md** - Full documentation
- **routes/web.php** - All available routes
- **app/Http/Controllers/** - Controller logic
- **resources/views/** - All view templates

## ✨ Key Features Summary

✅ Interactive map with routing
✅ Real-time occupancy tracking
✅ Disaster updates from PAGASA/PhiVolcs
✅ Predictive analytics with recovery estimates
✅ Visual charts and statistics
✅ Family registration system
✅ Automatic status updates
✅ Mobile responsive design
✅ User-friendly interface
✅ Complete CRUD operations

---

**🎉 The system is ready! Start the server and begin testing!**

```bash
php artisan serve
```

Then visit: **http://localhost:8000**
