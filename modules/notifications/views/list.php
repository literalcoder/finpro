<?php defined('BASE_PATH') or exit('No direct script access allowed'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-bell"></i> Notifications</h2>
    <?php if (!empty($notifications)): ?>
    <button type="button" class="btn btn-outline-secondary" onclick="markAllRead()">
        <i class="bi bi-check2-all"></i> Mark All as Read
    </button>
    <?php endif; ?>

<div class="card shadow">
    <div class="card-body">
        <?php if (empty($notifications)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-bell-slash" style="font-size: 3rem;"></i>
                <p class="mt-3">No notifications to display</p>
            </div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($notifications as $notification): ?>
                    <div class="list-group-item list-group-item-action <?php echo $notification['is_read'] ? '' : 'bg-light'; ?>" id="notification-<?php echo $notification['id']; ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-1">
                                <i class="bi bi-<?php 
                                    echo match($notification['type']) {
                                        'success' => 'check-circle-fill text-success',
                                        'warning' => 'exclamation-triangle-fill text-warning',
                                        'danger' => 'x-circle-fill text-danger',
                                        default => 'info-circle-fill text-info'
                                    };
                                ?>"></i>
                                <?php echo escape($notification['title']); ?>
                            </h6>
                            <small class="text-muted">
                                <?php echo date('M j, g:i A', strtotime($notification['created_at'])); ?>
                            </small>
                        </div>
                        <p class="mb-1"><?php echo escape($notification['message']); ?></p>
                        <?php if ($notification['org_name']): ?>
                            <small class="text-muted">Organization: <?php echo escape($notification['org_name']); ?></small>
                        <?php endif; ?>
                        
                        <?php if (!$notification['is_read']): ?>
                            <div class="mt-2">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="mark_read" value="<?php echo $notification['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        Mark as Read
                                    </button>
                                </form>
                                
                                <?php if ($notification['related_type'] && $notification['related_id']): ?>
                                    <a href="<?php 
                                        echo match($notification['related_type']) {
                                            'proposal' => '../proposals/view.php',
                                            'terminal_report' => '../terminal/view.php',
                                            default => '#'
                                        };
                                    ?>?id=<?php echo $notification['related_id']; ?>" 
                                       class="btn btn-sm btn-primary ms-2">
                                        View Details
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function markRead(id) {
    updateNotification('mark_read', id);
}

function markAllRead() {
    if (confirm('Mark all notifications as read?')) {
        updateNotification('mark_all_read');
    }
}

function deleteNotification(id) {
    if (confirm('Delete this notification?')) {
        updateNotification('delete', id);
    }
}

function updateNotification(action, id = null) {
    const data = new FormData();
    data.append('action', action);
    if (id) data.append('id', id);
    
    fetch('handle.php', {
        method: 'POST',
        body: data
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (action === 'delete') {
                document.getElementById('notification-' + id).remove();
            } else if (action === 'mark_read') {
                document.getElementById('notification-' + id).classList.remove('bg-light');
            } else if (action === 'mark_all_read') {
                document.querySelectorAll('.list-group-item').forEach(item => {
                    item.classList.remove('bg-light');
                });
            }
            // Update global notification counter
            const counter = document.getElementById('notification-counter');
            if (counter) {
                if (data.unread_count > 0) {
                    counter.textContent = data.unread_count;
                    counter.style.display = 'inline';
                } else {
                    counter.style.display = 'none';
                }
            }
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>