<section class="container page-shell">
  <?php partial('back_button', ['label' => 'Back', 'fallback' => '/admin/dashboard']); ?>
  <h1 class="section-title">Manage Locations</h1>
  <p class="section-subtitle">Maintain branch addresses, contact numbers, operating hours, and map coordinates.</p>

  <form action="/admin/locations/create" method="post" enctype="multipart/form-data" class="card p-3 mb-4">
    <?= csrf_input() ?>
    <h2 class="h5">Create Location</h2>
    
    <div class="row g-2 mb-2">
      <div class="col-md-3"><input class="form-control" name="name" placeholder="Name" required></div>
      <div class="col-md-3"><input class="form-control" name="address" placeholder="Address" required></div>
      <div class="col-md-2"><input class="form-control" name="phone" placeholder="Phone" required></div>
      <div class="col-md-2"><input class="form-control" name="opening_hours" placeholder="Opening hours" required></div>
      <div class="col-md-2">
        <select name="status" class="form-select">
          <option value="active">active</option>
          <option value="inactive">inactive</option>
        </select>
      </div>
    </div>

    <div class="row g-2">
      <div class="col-md-3"><input class="form-control" name="latitude" placeholder="Latitude (e.g. 1.2902)" required></div>
      <div class="col-md-3"><input class="form-control" name="longitude" placeholder="Longitude (e.g. 103.8519)" required></div>
      <div class="col-md-2"><input class="form-control" name="map_place_id" placeholder="Google Place ID"></div>
      <div class="col-md-2"><input class="form-control" type="file" name="image" accept="image/*"></div>
      <div class="col-md-2"><button class="btn btn-brand w-100" type="submit">Add Location</button></div>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-sm table-striped align-middle" style="min-width: 1200px;">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Address</th>
          <th>Phone</th>
          <th>Hours</th>
          <th>Lat</th>
          <th>Lng</th>
          <th>Place ID</th>
          <th>Image</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($locations as $l): ?>
          <?php
            $locationImage = media_url((string) ($l['image_path'] ?? ''), 'location');
            $hasPhoto = $locationImage !== '';
          ?>
          <tr>
            <form action="/admin/locations/update" method="post" enctype="multipart/form-data">
              <?= csrf_input() ?>
              <td><?= (int)$l['id'] ?><input type="hidden" name="id" value="<?= (int)$l['id'] ?>"></td>
              <td><input class="form-control form-control-sm" name="name" value="<?= e($l['name']) ?>" required></td>
              <td><input class="form-control form-control-sm" name="address" value="<?= e($l['address']) ?>" required></td>
              <td><input class="form-control form-control-sm" name="phone" value="<?= e($l['phone']) ?>" required></td>
              <td><input class="form-control form-control-sm" name="opening_hours" value="<?= e($l['opening_hours']) ?>" required></td>
              <td><input class="form-control form-control-sm" name="latitude" value="<?= e($l['latitude']) ?>" required></td>
              <td><input class="form-control form-control-sm" name="longitude" value="<?= e($l['longitude']) ?>" required></td>
              <td><input class="form-control form-control-sm" name="map_place_id" value="<?= e($l['map_place_id']) ?>"></td>
              <td>
                <input class="form-control form-control-sm mb-1" name="image_path" value="<?= e((string) ($l['image_path'] ?? '')) ?>" placeholder="/assets/images/locations/...">
                <input class="form-control form-control-sm mb-1" type="file" name="image" accept="image/*">
                <?php if ($hasPhoto): ?>
                  <img src="<?= e($locationImage) ?>" alt="Location image" style="width: 84px; height: 48px; object-fit: cover; border-radius: 4px;">
                <?php endif; ?>
              </td>
              
              <td>
                <select name="status" class="form-select form-select-sm">
                  <option value="active" <?= $l['status']==='active'?'selected':'' ?>>active</option>
                  <option value="inactive" <?= $l['status']==='inactive'?'selected':'' ?>>inactive</option>
                </select>
              </td>
              <td class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-primary">Save</button>
            </form>
            
            <form action="/admin/locations/delete" method="post">
              <?= csrf_input() ?>
              <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
              <button class="btn btn-sm btn-outline-danger location-delete-btn" type="submit">Delete</button>
            </form>
              </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
