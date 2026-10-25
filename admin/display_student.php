<?php
defined('APP_BOOTSTRAPPED') || exit;

$rows = db_all('SELECT id, name, email, mobile, programme, semester, created_at FROM students ORDER BY name');
?>
<h1 class="page-header">Manage students</h1>
<?php if (!$rows): ?>
    <p>No students have registered yet.</p>
<?php else: ?>
<div class="table-responsive">
<table class="table table-bordered table-striped table-hover">
    <tr class="success">
        <th>#</th><th>Name</th><th>Email</th><th>Mobile</th><th>Programme</th><th>Semester</th><th>Registered</th><th>Delete</th>
    </tr>
    <?php foreach ($rows as $i => $row): ?>
    <tr>
        <td><?= $i + 1 ?></td>
        <td><?= e($row['name']) ?></td>
        <td><?= e($row['email']) ?></td>
        <td><?= e($row['mobile']) ?></td>
        <td><?= e($row['programme']) ?></td>
        <td><?= e(strtoupper($row['semester'])) ?></td>
        <td><?= e($row['created_at']) ?></td>
        <td class="text-center">
            <form method="post" action="delete_student.php" onsubmit="return confirm('Delete this student and their feedback?');" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                <button class="btn btn-link" style="padding:0" title="Delete"><span class="glyphicon glyphicon-remove" style="color:red"></span></button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</div>
<?php endif; ?>
