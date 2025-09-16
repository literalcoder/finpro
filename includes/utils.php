<?php
require_once __DIR__ . '/../config/config.php';

function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function getCurrentSemester() {
    $month = (int)date('m');
    $year = date('Y');
    
    if ($month >= 8 && $month <= 12) {
        $semester = '1st Semester';
        $academic_year = $year . '-' . ($year + 1);
    } elseif ($month >= 1 && $month <= 5) {
        $semester = '2nd Semester';
        $academic_year = ($year - 1) . '-' . $year;
    } else {
        $semester = 'Summer';
        $academic_year = ($year - 1) . '-' . $year;
    }
    
    return [
        'semester' => $semester,
        'year' => $academic_year
    ];
}

function formatAmount($amount) {
    return '₱' . number_format($amount, 2);
}

function logActivity($user_id, $org_id, $action, $entity_type = null, $entity_id = null) {
    global $conn;
    
    $sql = "INSERT INTO audit_logs (user_id, org_id, action, entity_type, entity_id, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $user_id,
        $org_id,
        $action,
        $entity_type,
        $entity_id,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}