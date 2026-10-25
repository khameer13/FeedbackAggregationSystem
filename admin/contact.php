<?php
defined('APP_BOOTSTRAPPED') || exit;

$rows = db_all('SELECT id, name, email, mobile, message, created_at FROM contact ORDER BY created_at DESC');
?>
<h1 class="page-header">Contact messages</h1>
<?php if (!$rows): ?>
    <h3 class="text-danger">No messages yet.</h3>
<?php else: ?>
<div class="table-responsive">
<table class="table table-bordered">
    <tr class="success"><th>#</th><th>Name</th><th>Email</th><th>Mobile</th><th>Message</th><th>Date</th><th>Delete</th></tr>
    <?php foreach ($rows as $i => $row): ?>
    <tr>
        <td><?= $i + 1 ?></td>
        <td><?= e($row['name']) ?></td>
        <td><?= e($row['email']) ?></td>
        <td><?= e($row['mobile']) ?></td>
        <td><?= nl2br(e($row['message'])) ?></td>
        <td><?= e($row['created_at']) ?></td>
        <td>
            <form method="post" action="delete_contact.php" onsubmit="return confirm('Delete this message?');" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                <button class="btn btn-link" style="padding:0">Delete</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</div>
<?php endif; ?>
