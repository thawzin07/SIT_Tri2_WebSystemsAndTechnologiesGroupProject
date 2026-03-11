<section class="container py-5">
  <h1 class="section-title">Contact Messages</h1>
  <div class="admin-toolbar">
    <label for="messages-filter" class="form-label mb-1">Search Messages</label>
    <input id="messages-filter" type="search" class="form-control" placeholder="Search by name, email, subject, or content" data-table-filter="#messages-table">
  </div>
  <?php if (empty($messages)): ?>
    <div class="empty-state">No messages in inbox.</div>
  <?php else: ?>
  <div class="table-responsive"><table id="messages-table" class="table table-sm table-striped align-middle"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Subject</th><th>Message</th><th>Date</th><th>Action</th></tr></thead><tbody>
  <?php foreach ($messages as $m): ?>
    <tr><td><?= (int)$m['id'] ?></td><td><?= e($m['name']) ?></td><td><?= e($m['email']) ?></td><td><?= e($m['subject']) ?></td><td><?= e($m['message']) ?></td><td><?= e($m['created_at']) ?></td><td><form action="/admin/messages/delete" method="post"><?= csrf_input() ?><input type="hidden" name="id" value="<?= (int)$m['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>
  <?php endforeach; ?>
  </tbody></table></div>
  <?php endif; ?>
</section>
