# 🛡️ Evacuation Area Management System

A comprehensive full-stack Laravel application for managing evacuation areas during natural disasters (Typhoon, Earthquake, Flood, Landslide).

## 🌟 Features

### 1. **Interactive Map System**
- 📍 Locate nearest evacuation areas based on your current location
- 🗺️ Real-time route navigation to evacuation centers
- 🎯 Filter evacuation areas by disaster type
- 📊 Visual indicators for area capacity and availability

### 2. **Disaster Updates (PAGASA/PhiVolcs)**
- 🚨 Real-time disaster alerts and warnings
- ⚠️ Severity levels (Low, Moderate, High, Critical)
- 📡 Official updates from PAGASA and PhiVolcs
- 🌍 Location-based disaster information

### 3. **Family Management**
- 👨‍👩‍👧‍👦 Register families to evacuation areas
- 📝 Track number of family members
- 🏥 Record special needs (medical, elderly, infants)
- ✅ Check-in/Check-out system
- 📞 Contact information management

### 4. **Evacuation Area Status**
- 🏢 Real-time occupancy tracking
- 🚦 Automatic status updates (Available/Full/Closed)
- 📊 Capacity visualization with progress bars
- 🔄 Auto-update when "Go" button is clicked
- 📋 Facility and contact information

### 5. **Disaster Predictions**
- 🔮 Predictive analytics for disasters (Floods, Landslides, Earthquakes, Typhoons)
- 📈 Risk level assessment (1-10 scale)
- ⏱️ Recovery time estimates (days until area recovers)
- 📊 Prediction factors and analysis
- 🎯 Location-based risk mapping

### 6. **Visual Charts & Dashboard**
- 📊 Disaster type distribution charts
- 📈 Severity level analytics
- 📉 Evacuation area occupancy graphs
- 📋 Comprehensive statistics
- 🎨 Interactive data visualization using Chart.js

### 7. **User Features**
- ➕ Add new evacuation areas
- 📝 Submit disaster updates
- 🔮 Create disaster predictions
- 🗺️ Interactive map with routing
- 📱 Responsive design for mobile devices

### 8. **Dashboard & Reporting**
- 📊 Summary of all evacuation areas
- 👥 Total families and people evacuated
- 🚨 Recent disaster updates
- 🔮 Active predictions
- 📈 Visual analytics and trends
- 📋 Comprehensive reporting system

## 🛠️ Technology Stack

- **Backend**: Laravel 11 (PHP)
- **Frontend**: Blade Templates (HTML, CSS, JavaScript)
- **Database**: SQLite (easily switchable to MySQL/PostgreSQL)
- **Maps**: Leaflet.js with OpenStreetMap
- **Routing**: Leaflet Routing Machine
- **Charts**: Chart.js
- **Styling**: Custom CSS with modern gradient design

## 📦 Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- SQLite (or MySQL/PostgreSQL)

### Installation Steps

1. **Install Dependencies**
```bash
composer install
```

2. **Environment Setup**
```bash
# Copy .env.example if needed
# Database is already configured for SQLite
```

3. **Run Migrations** (Already done)
```bash
php artisan migrate
```

4. **Seed Sample Data** (Already done)
```bash
php artisan db:seed
```

5. **Start Development Server**
```bash
php artisan serve
```

6. **Access the Application**
```
http://localhost:8000
```

## 📱 Usage Guide

### For Citizens/Evacuees

1. **Find Nearest Evacuation Area**
   - Go to "Evacuation Areas" page
   - Click "Get My Location" button
   - View nearest evacuation centers on the map
   - Filter by disaster type if needed

2. **Navigate to Evacuation Area**
   - Click on a marker on the map
   - Click "Show Route" to see navigation
   - Or click "Go" button to register your family

3. **Register Your Family**
   - Click "Go" button on an evacuation area
   - Fill in family details:
     - Family name
     - Number of members
     - Contact number
     - Special needs (optional)
   - Submit to check-in

4. **View Disaster Updates**
   - Check "Disaster Updates" page for latest alerts
   - View severity levels and affected areas
   - Read official PAGASA/PhiVolcs updates

5. **Check Predictions**
   - View "Predictions" page for risk assessments
   - See recovery time estimates
   - Plan accordingly based on risk levels

### For Administrators/Officials

1. **Add Evacuation Area**
   - Click "Add Evacuation Area"
   - Fill in details (name, address, capacity, etc.)
   - Use map to pick exact coordinates
   - Specify disaster type and facilities

2. **Post Disaster Updates**
   - Click "Add Update" on Disaster Updates page
   - Select disaster type and severity
   - Provide detailed description
   - Add location coordinates if applicable

3. **Create Predictions**
   - Go to Predictions page
   - Click "Add Prediction"
   - Set risk level (1-10)
   - Estimate recovery days
   - Provide prediction factors

4. **Manage Families**
   - View all families in "Families" page
   - Check-out families when they leave
   - Monitor occupancy levels

5. **Monitor Dashboard**
   - View comprehensive statistics
   - Analyze trends with charts
   - Generate reports
   - Track real-time occupancy

## 🗺️ Map Features

### Interactive Elements
- **Green Markers**: Available evacuation areas
- **Red Markers**: Full evacuation areas
- **Blue Marker**: Your current location
- **Click on Map**: Set coordinates when adding areas
- **Routing**: Automatic route calculation from your location

### Map Controls
- Zoom in/out
- Pan around
- Click markers for details
- Filter by disaster type
- Show/hide routes

## 📊 Dashboard Analytics

### Key Metrics
- Total evacuation areas
- Families evacuated
- Total people in evacuation
- Available vs. full areas
- Recent disaster updates
- Active predictions

### Visual Charts
- Disaster type distribution (Doughnut chart)
- Severity levels (Pie chart)
- Evacuation area occupancy (Bar chart)
- Risk analysis by location

## 🔮 Prediction System

### Risk Levels
- **1-3**: Low risk (Green)
- **4-5**: Moderate risk (Yellow)
- **6-7**: High risk (Orange)
- **8-10**: Critical risk (Red)

### Recovery Estimates
- Predicts days until area recovers
- Considers factors like:
  - Flood water drainage
  - Debris clearing
  - Infrastructure repair
  - Weather conditions

## 🚨 Disaster Types Supported

1. **Typhoon** 🌀
   - Wind speed tracking
   - Storm surge warnings
   - Evacuation timing

2. **Earthquake** 🌍
   - Magnitude monitoring
   - Aftershock predictions
   - Structural safety

3. **Flood** 🌊
   - Water level tracking
   - Drainage estimates
   - Recovery timeline

4. **Landslide** ⛰️
   - Soil saturation
   - Slope stability
   - Debris clearing time

## 📱 Mobile Responsive

The system is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones
- All modern browsers

## 🔒 Data Management

### Database Tables
- `evacuation_areas` - Evacuation center information
- `families` - Family registration records
- `disaster_updates` - Official disaster alerts
- `disaster_predictions` - Risk assessments and predictions

### Automatic Updates
- Occupancy auto-updates on check-in/check-out
- Status changes (available/full) based on capacity
- Real-time data synchronization

## 🎨 Design Features

- Modern gradient background
- Card-based layout
- Smooth animations
- Color-coded severity levels
- Progress bars for capacity
- Badge system for status
- Responsive grid layouts

## 📞 Contact Information

Each evacuation area includes:
- Contact phone number
- Address
- GPS coordinates
- Available facilities
- Capacity information

## 🚀 Future Enhancements

Potential additions:
- SMS/Email notifications
- Real-time PAGASA API integration
- Mobile app version
- Multi-language support
- Advanced analytics
- Export reports to PDF
- QR code check-in system

## 📝 Sample Data Included

The system comes pre-loaded with:
- 5 evacuation areas in Metro Manila
- 3 sample families
- 4 disaster updates
- 5 disaster predictions

## 🆘 Emergency Contacts

Always keep these numbers handy:
- **NDRRMC**: 911
- **PAGASA**: (02) 8284-0800
- **PhiVolcs**: (02) 8426-1468
- **Red Cross**: 143

## 📄 License

This is an educational project for disaster management and preparedness.

## 👨‍💻 Support

For issues or questions, please check the documentation or contact your system administrator.

---

**Stay Safe! Be Prepared! 🛡️**
