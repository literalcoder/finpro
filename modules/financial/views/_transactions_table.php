<?php defined('BASE_PATH') or exit('No direct script access allowed'); ?>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Recorded By</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_transactions as $transaction): ?>
            <tr>
                <td><?php echo date('M j, Y', strtotime($transaction['transaction_date'])); ?></td>
                <td>
                    <span class="badge bg-<?php echo in_array($transaction['type'], ['income', 'collection']) ? 'success' : 'danger'; ?>">
                        <?php echo ucfirst($transaction['type']); ?>
                    </span>
                </td>
                <td class="<?php echo in_array($transaction['type'], ['income', 'collection']) ? 'text-success' : 'text-danger'; ?>">
                    <?php echo formatAmount($transaction['amount']); ?>
                </td>
                <td><?php echo escape($transaction['description']); ?></td>
                <td><?php echo escape($transaction['created_by_name']); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($recent_transactions)): ?>
            <tr>
                <td colspan="5" class="text-center text-muted">No transactions found</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>