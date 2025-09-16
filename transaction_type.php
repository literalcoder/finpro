<?php
function getTransactionTypes() {
    return [
        'income' => 'Income',
        'expense' => 'Expense',
        'collection' => 'Collection',
        'disbursement' => 'Disbursement'
    ];
}

function getTransactionTypeLabel($type) {
    $types = getTransactionTypes();
    return isset($types[$type]) ? $types[$type] : ucfirst($type);
}

function renderTransactionTypeSelect($name, $selectedValue = '', $required = true) {
    $types = getTransactionTypes();
    $html = '<select name="' . htmlspecialchars($name) . '" class="form-select"' . ($required ? ' required' : '') . '>';
    $html .= '<option value="">Select transaction type</option>';
    
    foreach ($types as $value => $label) {
        $selected = $selectedValue === $value ? ' selected' : '';
        $html .= sprintf(
            '<option value="%s"%s>%s</option>',
            htmlspecialchars($value),
            $selected,
            htmlspecialchars($label)
        );
    }
    
    $html .= '</select>';
    return $html;
}
?>