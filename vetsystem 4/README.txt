VETSYSTEM - NAIC MUNICIPAL VETERINARY OFFICE
PHP + MySQL + XAMPP + Leaflet GIS

INSTALLATION
1. Extract the vetsystem folder into your XAMPP htdocs directory.
   Windows: C:\xampp\htdocs\
   macOS: /Applications/XAMPP/xamppfiles/htdocs/
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin: http://localhost/phpmyadmin
4. Import vetsystem/database.sql. It creates the database named vetsystem.
5. Check config/db.php and update the MySQL username/password if needed.
6. Open http://localhost/vetsystem/
7. Select Create an account. The FIRST registered account becomes admin;
   later registrations become staff.

FEATURES
- Login, registration, logout, password hashing and session protection
- Dashboard counts and registered owner listing
- Poultry/livestock farm records with barangay and map coordinates
- Animal health monitoring history
- GIS map with owner/farm/barangay search
- Green: 0-3 latest reported deaths; Yellow: 4-7; Red: 8+
- Critical poultry trigger: 8 or more deaths in the latest monitoring record
- Neighbor alert: farms in a DIFFERENT barangay within the selected radius of a
  critical poultry farm are highlighted yellow. Default radius is 2 km.

IMPORTANT ABOUT THE NEIGHBOR ALERT
The yellow alert is calculated from saved farm coordinates and barangay labels.
It is a proximity-based screening aid; it does not use official barangay boundary
polygons, confirm that barangays share a legal boundary, diagnose disease, or prove
transmission. The map radius can be changed on the GIS page (0.5 to 10 km).
Only farms with saved latitude and longitude can be highlighted. Ask the Municipal
Veterinary Office to set the operational threshold and validate local response
protocols before using this for official decisions.

EDITING / DEBUGGING
- Page body and navigation: partials/header.php and partials/footer.php
- GIS map logic and alert rules: assets/js/gis-map.js
  Change criticalDeaths, warningDeaths, and defaultAlertRadiusMeters in CONFIG.
- Farm pin picker: assets/js/farm-picker.js
- Visual design: assets/css/style.css
- Database connection: config/db.php
- Authentication/session helpers: config/auth.php

MAP REQUIREMENTS
Leaflet and OpenStreetMap tiles load from online CDNs, so internet access is needed.
Open the browser Developer Tools Console to see JavaScript errors. Check that
Leaflet loaded if the map area is blank.

DATABASE
Import database.sql. Default local XAMPP settings are user root and blank password;
change config/db.php if your setup differs.

SECURITY
This is a local prototype. Before real deployment, configure HTTPS, backups, access
controls, registration policy, audit logging, and approved government hosting.


GIS NEIGHBOR ALERT UPDATE
- Any farm with 8 or more deaths in its latest monitoring record is a critical source (red).
- Other farms in a different barangay within the selected radius are highlighted yellow.
- The critical source remains red; nearby farm markers become yellow.
- The default radius is 2 km and can be adjusted on the GIS map.
- This is a proximity screening alert only. It does not establish official barangay
  boundaries, infection, or disease transmission.
