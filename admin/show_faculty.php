<?php
defined('APP_BOOTSTRAPPED') || exit;

$rows = db_all('SELECT id, name, designation, programme, semester, email, mobile, is_active FROM faculty ORDER BY name');
?>
<h1 class="page-header">Manage faculty</h1>
<?php if (!$rows): ?>
    <p>No faculty yet. <a href="dashboard.php?info=add_faculty">Add one</a>.</p>
<?php else: ?>
<div class="table-responsive">
<table class="table table-bordered table-striped table-hover">
    <tr>
        <th>#</th><th>Name</th><th>Designation</th><th>Programme</th><th>Semester</th>
        <th>Email</th><th>Mobile</th><th>Status</th><th>Edit</th><th>Delete</th>
    </tr>
    <?php foreach ($rows as $i => $row): ?>
    <tr>
        <td><?= $i + 1 ?></td>
        <td><?= e($row['name']) ?></td>
        <td><?= e($row['designation']) ?></td>
        <td><?= e($row['programme']) ?></td>
        <td><?= e(strtoupper($row['semester'])) ?></td>
        <td><?= e($row['email']) ?></td>
        <td><?= e($row['mobile']) ?></td>
        <td>
            <form method="post" action="update_status.php" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                <?php if ($row['is_active']): ?>
                    <button class="btn btn-xs btn-success" title="Click to deactivate">Active</button>
                <?php else: ?>
                    <button class="btn btn-xs btn-default" title="Click to activate">Inactive</button>
                <?php endif; ?>
            </form>
        </td>
        <td class="text-center"><a href="dashboard.php?info=edit_faculty&amp;id=<?= (int) $row['id'] ?>"><span class="glyphicon glyphicon-pencil" style="color:green"></span></a></td>
        <td class="text-center">
            <form method="post" action="delete_faculty.php" onsubmit="return confirm('Delete this faculty member and their feedback?');" style="display:inline">
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
