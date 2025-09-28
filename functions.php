<?php
// functions.php - Helper functions for the BAF Parade System

// Get total officers count and statistics
function getOfficersCount($pdo) {
    $current_date = date('Y-m-d');
    
    // Total officers
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM officers");
    $total = $stmt->fetch()['total'];
    
    // Female officers
    $stmt = $pdo->query("SELECT COUNT(*) as female FROM officers WHERE gender = 'Female'");
    $female = $stmt->fetch()['female'];
    
    // Officers on parade today (either marked present or not marked at all)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as on_parade 
        FROM officers o
        LEFT JOIN parade_states ps ON o.id = ps.officer_id AND ps.parade_date = ?
        WHERE ps.status IS NULL OR ps.status = 'Present'
    ");
    $stmt->execute([$current_date]);
    $on_parade_today = $stmt->fetch()['on_parade'];
    
    // Absent today
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as absent 
        FROM officers o
        JOIN parade_states ps ON o.id = ps.officer_id
        WHERE ps.parade_date = ? AND ps.status != 'Present'
    ");
    $stmt->execute([$current_date]);
    $absent_today = $stmt->fetch()['absent'];
    
    return [
        'total' => $total,
        'female' => $female,
        'on_parade_today' => $on_parade_today,
        'absent_today' => $absent_today
    ];
}

// Get department statistics
function getDepartmentStats($pdo, $department, $date) {
    // Total in department
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM officers WHERE department = ?");
    $stmt->execute([$department]);
    $total = $stmt->fetch()['total'];
    
    // Present in department
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as present 
        FROM officers o
        LEFT JOIN parade_states ps ON o.id = ps.officer_id AND ps.parade_date = ?
        WHERE o.department = ? AND (ps.status IS NULL OR ps.status = 'Present')
    ");
    $stmt->execute([$date, $department]);
    $present = $stmt->fetch()['present'];
    
    // Absent in department
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as absent 
        FROM officers o
        JOIN parade_states ps ON o.id = ps.officer_id
        WHERE o.department = ? AND ps.parade_date = ? AND ps.status != 'Present'
    ");
    $stmt->execute([$department, $date]);
    $absent = $stmt->fetch()['absent'];
    
    return [
        'total' => $total,
        'present' => $present,
        'absent' => $absent
    ];
}

// Get absent officers for a specific date
function getAbsentOfficers($pdo, $date) {
    $stmt = $pdo->prepare("
        SELECT o.name, o.department, o.level, o.mess_location, ps.status, ps.remarks
        FROM officers o
        JOIN parade_states ps ON o.id = ps.officer_id
        WHERE ps.parade_date = ? AND ps.status != 'Present'
        ORDER BY o.department, o.level, o.name
    ");
    $stmt->execute([$date]);
    return $stmt->fetchAll();
}

// Get parade data for report generation
function getParadeData($pdo, $date) {
    $data = [
        'departments' => ['CSE', 'EECE', 'ME', 'AE'],
        'levels' => ['I', 'II', 'III', 'IV'],
        'mess_locations' => ['MIST Dhaka Mess', 'MIST Mirpur Mess', 'BAF Base AKR'],
        'parade_data' => []
    ];
    
    foreach($data['departments'] as $dept) {
        $data['parade_data'][$dept] = [];
        foreach($data['levels'] as $level) {
            $data['parade_data'][$dept][$level] = [];
            foreach($data['mess_locations'] as $mess) {
                // Get total strength
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as total 
                    FROM officers 
                    WHERE department = ? AND level = ? AND mess_location = ?
                ");
                $stmt->execute([$dept, $level, $mess]);
                $total = $stmt->fetch()['total'];
                
                // Get present count
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as present 
                    FROM officers o
                    LEFT JOIN parade_states ps ON o.id = ps.officer_id AND ps.parade_date = ?
                    WHERE o.department = ? AND o.level = ? AND o.mess_location = ?
                    AND (ps.status IS NULL OR ps.status = 'Present')
                ");
                $stmt->execute([$date, $dept, $level, $mess]);
                $present = $stmt->fetch()['present'];
                
                // Get absent count
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as absent 
                    FROM officers o
                    JOIN parade_states ps ON o.id = ps.officer_id
                    WHERE o.department = ? AND o.level = ? AND o.mess_location = ?
                    AND ps.parade_date = ? AND ps.status != 'Present'
                ");
                $stmt->execute([$dept, $level, $mess, $date]);
                $absent = $stmt->fetch()['absent'];
                
                $data['parade_data'][$dept][$level][$mess] = [
                    'total' => $total,
                    'present' => $present,
                    'absent' => $absent
                ];
            }
        }
    }
    
    return $data;
}

// Get detailed absent officers with reasons
function getDetailedAbsentOfficers($pdo, $date) {
    $stmt = $pdo->prepare("
        SELECT o.name, o.rank, o.mess_location as mess_location, o.department, o.level, ps.status, ps.remarks,
               CASE 
                   WHEN ps.status = 'Leave' THEN 'lve'
                   WHEN ps.status = 'CMH' THEN 'CMH'
                   WHEN ps.status = 'Sick Leave' THEN 'sick'
                   WHEN ps.status = 'SIQ' THEN 'SIQ'
                   WHEN ps.status = 'Isolation' THEN 'isolation'
                   ELSE ps.status
               END as short_status
        FROM officers o
        JOIN parade_states ps ON o.id = ps.officer_id
        WHERE ps.parade_date = ? AND ps.status != 'Present'
        ORDER BY ps.status, o.department, o.level, o.name
    ");
    $stmt->execute([$date]);
    return $stmt->fetchAll();
}

// Get summary statistics for report
function getReportSummary($pdo, $date) {
    // Total strength
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM officers");
    $total_strength = $stmt->fetch()['total'];
    
    // Total on parade
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as on_parade 
        FROM officers o
        LEFT JOIN parade_states ps ON o.id = ps.officer_id AND ps.parade_date = ?
        WHERE ps.status IS NULL OR ps.status = 'Present'
    ");
    $stmt->execute([$date]);
    $total_on_parade = $stmt->fetch()['on_parade'];
    
    // Total absent
    $total_absent = $total_strength - $total_on_parade;
    
    // Female officers by location
    $stmt = $pdo->query("
        SELECT mess_location, COUNT(*) as count 
        FROM officers 
        WHERE gender = 'Female' 
        GROUP BY mess_location
    ");
    $female_by_location = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Total female
    $total_female = array_sum($female_by_location);
    
    // Absent breakdown by reason
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as count
        FROM parade_states
        WHERE parade_date = ? AND status != 'Present'
        GROUP BY status
    ");
    $stmt->execute([$date]);
    $absent_breakdown = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Level wise totals
    $stmt = $pdo->query("
        SELECT level, COUNT(*) as count
        FROM officers
        GROUP BY level
        ORDER BY level
    ");
    $level_totals = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    return [
        'total_strength' => $total_strength,
        'total_on_parade' => $total_on_parade,
        'total_absent' => $total_absent,
        'female_by_location' => $female_by_location,
        'total_female' => $total_female,
        'absent_breakdown' => $absent_breakdown,
        'level_totals' => $level_totals
    ];
}

// Format level display (convert I, II, III, IV to Level-1, Level-2, etc for summary)
function formatLevelForSummary($level) {
    $level_map = ['I' => '1', 'II' => '2', 'III' => '3', 'IV' => '4'];
    return 'Level-' . $level_map[$level];
}

// Save generated report to database
function saveReport($pdo, $date, $summary) {
    $stmt = $pdo->prepare("
        INSERT INTO reports (report_date, total_strength, total_on_parade, total_absent, total_female, generated_at)
        VALUES (?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
        total_strength = VALUES(total_strength),
        total_on_parade = VALUES(total_on_parade),
        total_absent = VALUES(total_absent),
        total_female = VALUES(total_female),
        generated_at = NOW()
    ");
    
    return $stmt->execute([
        $date,
        $summary['total_strength'],
        $summary['total_on_parade'],
        $summary['total_absent'],
        $summary['total_female']
    ]);
}

// Update officer attendance
function updateAttendance($pdo, $officer_id, $date, $status, $remarks = '') {
    $stmt = $pdo->prepare("
        INSERT INTO parade_states (officer_id, parade_date, status, remarks)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        status = VALUES(status),
        remarks = VALUES(remarks)
    ");
    
    return $stmt->execute([$officer_id, $date, $status, $remarks]);
}

// Get all officers with their current attendance status
function getAllOfficersWithStatus($pdo, $date) {
    $stmt = $pdo->prepare("
        SELECT o.id, o.name, o.rank, o.department, o.level, o.mess_location, o.gender,
               COALESCE(ps.status, 'Present') as status, ps.remarks
        FROM officers o
        LEFT JOIN parade_states ps ON o.id = ps.officer_id AND ps.parade_date = ?
        ORDER BY o.department, o.level, o.name
    ");
    $stmt->execute([$date]);
    return $stmt->fetchAll();
}