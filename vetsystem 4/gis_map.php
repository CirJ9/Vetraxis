<?php
require_once 'config/db.php'; require_once 'config/auth.php'; require_login();
$page_title='GIS Monitoring Map';
$sql="SELECT f.id,f.farm_name,f.animal_type,f.animal_detail,f.animal_count,f.barangay,f.latitude,f.longitude,o.owner_name,o.phone,
 (SELECT m.deaths FROM monitoring m WHERE m.farm_id=f.id ORDER BY m.observation_date DESC,m.id DESC LIMIT 1) AS latest_deaths,
 (SELECT m.observation_date FROM monitoring m WHERE m.farm_id=f.id ORDER BY m.observation_date DESC,m.id DESC LIMIT 1) AS latest_date
 FROM farms f JOIN owners o ON o.id=f.owner_id WHERE f.latitude IS NOT NULL AND f.longitude IS NOT NULL";
$rows=$pdo->query($sql)->fetchAll();$mapData=[];
foreach($rows as $r){$deaths=$r['latest_deaths']===null?0:(int)$r['latest_deaths'];$level=$deaths>=8?'critical':($deaths>=4?'warning':'good');$mapData[]=['id'=>(int)$r['id'],'owner'=>$r['owner_name'],'farm'=>$r['farm_name'],'type'=>$r['animal_type'],'detail'=>$r['animal_detail'],'count'=>(int)$r['animal_count'],'barangay'=>$r['barangay'],'lat'=>(float)$r['latitude'],'lng'=>(float)$r['longitude'],'deaths'=>$deaths,'level'=>$level,'date'=>$r['latest_date']];}
include 'partials/header.php';
?>
<section class="panel"><div class="panel-head"><h3>Naic Poultry & Livestock GIS</h3><span class="muted"><?=count($mapData)?> pinned farms</span></div>
 <div class="search-row"><input id="ownerSearch" type="search" placeholder="Search owner, farm, or barangay"><button class="btn primary" id="searchBtn" type="button">Search</button><button class="btn yellow" id="resetBtn" type="button">Show all</button></div>
 <div class="map-toolbar"><label for="alertRadius"><b>Neighbor alert radius</b></label><input id="alertRadius" type="range" min="500" max="10000" step="500" value="2000"><output id="radiusValue">2.0 km</output><span class="muted">Adjustable for local review</span></div>
 <div class="risk-legend"><span><i class="dot" style="background:#23965a"></i>Good: 0–3 deaths</span><span><i class="dot" style="background:#e5b323"></i>Warning: 4–7 deaths</span><span><i class="dot" style="background:#d94343"></i>Critical farm: 8+ deaths</span><span><i class="dot" style="background:#f2c744;border:1px solid #9a7600"></i>Nearby barangay alert</span></div>
 <div id="alertSummary" class="alert-summary" hidden></div><p class="map-help">When any farm reaches Critical status (8+ deaths in its latest monitoring record), farms in a different barangay within the selected radius are highlighted yellow. The critical farm stays red; nearby farms turn yellow. This is a proximity-based screening alert, not an official barangay-boundary or disease-transmission determination.</p>
 <div id="gisMap" class="map"></div><p id="resultCount" class="map-help" aria-live="polite"></p>
</section>
<script>window.VETSYSTEM_FARM_POINTS = <?=json_encode($mapData,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP|JSON_INVALID_UTF8_SUBSTITUTE)?>;</script>
<script src="assets/js/gis-map.js"></script>
<?php include 'partials/footer.php'; ?>
