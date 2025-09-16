<?php defined('BASE_PATH') or exit('No direct script access allowed'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people"></i> Organization Members</h2>
    <a href="invite.php" class="btn btn-primary">
        <i class="bi bi-person-plus"></i> Invite Member
    </a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                    <tr>
                        <td><?php echo escape($member['name']); ?></td>
                        <td><?php echo escape($member['email']); ?></td>
                        <td><?php echo escape($member['role_name']); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo match($member['status']) {
                                    'active' => 'success',
                                    'pending' => 'warning',
                                    'inactive' => 'danger',
                                    default => 'secondary'
                                }; 
                            ?>">
                                <?php echo ucfirst($member['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($member['created_at'])); ?></td>
                        <td>
                            <?php if ($member['status'] === 'pending'): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo $member['id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                            </form>
                            <?php endif; ?>
                            
                            <?php if ($member['status'] === 'active' && $member['role'] !== 'president'): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="user_id" value="<?php echo $member['id']; ?>">
                                <input type="hidden" name="action" value="deactivate">
                                <button type="submit" class="btn btn-sm btn-warning">Deactivate</button>
                            </form>
                            <?php endif; ?>
                            
                            <?php if ($member['role'] !== 'president'): ?>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this member?');">
                                <input type="hidden" name="user_id" value="<?php echo $member['id']; ?>">
                                <input type="hidden" name="action" value="remove">
                                <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($members)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No members found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>