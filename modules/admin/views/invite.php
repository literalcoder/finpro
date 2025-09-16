<?php defined('BASE_PATH') or exit('No direct script access allowed'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-plus"></i> Invite Members</h2>
    <a href="members.php" class="btn btn-secondary">Back to Members</a>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-body">
        <form method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label class="form-label">Email Addresses</label>
                <textarea name="emails" class="form-control" rows="3" required 
                          placeholder="Enter email addresses separated by commas"></textarea>
                <div class="form-text">You can enter multiple email addresses separated by commas</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="">Select role</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['code']; ?>">
                            <?php echo escape($role['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Invitation Message (Optional)</label>
                <textarea name="message" class="form-control" rows="3" 
                          placeholder="Enter a personal message to include in the invitation"></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Send Invitations</button>
                <a href="members.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>