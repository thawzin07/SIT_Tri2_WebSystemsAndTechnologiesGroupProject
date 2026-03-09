<section class="container py-5">
  <h1 class="section-title">Contact Messages</h1>
  <div class="table-responsive"><table class="table table-sm table-striped align-middle"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Date</th><th>Action</th></tr></thead><tbody>
  <?php foreach ($messages as $m): ?>
    <tr><td><?= (int)$m['id'] ?></td><td><?= e($m['name']) ?></td><td><?= e($m['email']) ?></td><td><?= e($m['subject']) ?></td><td><?= e($m['message']) ?></td><td><?= e($m['created_at']) ?></td><td><form action="/admin/messages/delete" method="post"><?= csrf_input() ?><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>
  <?php endforeach; ?>
  </tbody></table></div>
</section>
