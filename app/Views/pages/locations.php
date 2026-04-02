<section class="container page-shell">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  
  <h1 class="section-title">Gym Locations</h1>
  <p class="section-subtitle">Find a branch near you and check operating hours.</p>
  
  <div id="gym-map" style="height: 400px; width: 100%; border-radius: 8px; margin-top: 20px; margin-bottom: 20px; z-index: 1;"></div>
  
  <div class="row g-3">
    <?php foreach ($locations as $location): ?>
      <?php $locationImageUrl = media_url((string) ($location['image_path'] ?? ''), 'location'); ?>
      <div class="col-md-6">
        <article class="card h-100">
          <div class="location-card-image">
              <?php if ($locationImageUrl !== ''): ?>
                  <img src="<?= e($locationImageUrl) ?>" class="card-img-top w-100" alt="<?= e($location['name']) ?>" style="height: 200px; object-fit: cover;">
              <?php else: ?>
                  <div class="location-card-placeholder d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                      <span class="text-muted">No Image Available</span>
                  </div>
              <?php endif; ?>
          </div>
          <div class="card-body p-4">
            <h2 class="h5"><?= e($location['name']) ?></h2>
            <p class="mb-1"><?= e($location['address']) ?></p>
            <p class="mb-1">Phone: <?= e($location['phone']) ?></p>
            <p class="mb-3">Hours: <?= e($location['opening_hours']) ?></p>
            
            <?php
                $mapsUrl = "https://www.google.com/maps/dir/?api=1&destination=" . $location['latitude'] . "," . $location['longitude'];
                if (!empty($location['map_place_id'])) {
                  $mapsUrl .= "&destination_place_id=" . urlencode($location['map_place_id']);
                 }
            ?>
            <a href="<?= e($mapsUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary w-100">Get Directions</a>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>

<script>
var bounds = L.latLngBounds([1.144, 103.535], [1.494, 104.502]);
var map = L.map('gym-map', {
    maxBounds: bounds,
    maxBoundsViscosity: 1.0
}).setView([1.3521, 103.8198], 11);
L.tileLayer('https://www.onemap.gov.sg/maps/tiles/Default/{z}/{x}/{y}.png', {
    detectRetina: true,
    maxZoom: 19,
    minZoom: 11,
    attribution: '<img src="https://www.onemap.gov.sg/web-assets/images/logo/om_logo.png" style="height:20px;width:20px;"/> <a href="https://www.onemap.gov.sg/" target="_blank" rel="noopener noreferrer">OneMap</a> © contributors | <a href="https://www.sla.gov.sg/" target="_blank" rel="noopener noreferrer">Singapore Land Authority</a>'
}).addTo(map);

var gymData = <?= json_encode($locations, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
var escapeHtml = function(value) {
    return String(value || '').replace(/[&<>"']/g, function(ch) {
        return ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        })[ch];
    });
};

gymData.forEach(function(gym) {
    if (gym.latitude && gym.longitude) {
        var marker = L.marker([gym.latitude, gym.longitude]).addTo(map);
        var popupMapsUrl = '';
        if (gym.map_place_id) {
            popupMapsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(gym.name) + '&destination_place_id=' + encodeURIComponent(gym.map_place_id);
        } else {
            popupMapsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' + gym.latitude + ',' + gym.longitude;
        }

        marker.bindPopup(
            '<div style="text-align: center;">'
            + '<b>' + escapeHtml(gym.name) + '</b><br>'
            + '<span style="font-size: 0.9em; color: #555;">' + escapeHtml(gym.address) + '</span><br><br>'
            + '<a href="' + popupMapsUrl + '" target="_blank" rel="noopener noreferrer" style="background: #0d6efd; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; display: inline-block;">Get Directions</a>'
            + '</div>'
        );
    }
});

map.locate({setView: true, maxZoom: 13});

map.on('locationfound', function(e) {
    var userIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    L.marker(e.latlng, {icon: userIcon}).addTo(map)
        .bindPopup('<b>You are here!</b>').openPopup();
});
</script>
</section>
