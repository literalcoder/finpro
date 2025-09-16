<?php defined('BASE_PATH') or exit('No direct script access allowed'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil-square"></i> Edit Proposal</h2>
    <div class="btn-group">
        <a href="view.php?id=<?php echo $proposal['id']; ?>" class="btn btn-secondary">Cancel</a>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-body">
        <form method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" required maxlength="255" 
                       value="<?php echo escape($proposal['title']); ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Type</label>
                <select name="type" class="form-select" required>
                    <option value="">Select proposal type</option>
                    <option value="budget" <?php echo $proposal['type'] === 'budget' ? 'selected' : ''; ?>>Budget Request</option>
                    <option value="event" <?php echo $proposal['type'] === 'event' ? 'selected' : ''; ?>>Event</option>
                    <option value="project" <?php echo $proposal['type'] === 'project' ? 'selected' : ''; ?>>Project</option>
                    <option value="other" <?php echo $proposal['type'] === 'other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="5" required><?php 
                    echo escape($proposal['description']); 
                ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Budget Amount</label>
                <div class="input-group">
                    <span class="input-group-text">₱</span>
                    <input type="number" name="budget_amount" class="form-control" step="0.01" min="0" required 
                           value="<?php echo $proposal['budget_amount']; ?>">
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="view.php?id=<?php echo $proposal['id']; ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>