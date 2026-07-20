<?php
session_start();
header('Content-Type: application/json');
require_once '../koneksi.php';
if (!isset($_SESSION['admin_id'])) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}

$chart_labels = [];
$chart_data = [];
$chart_topup = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('d M', strtotime($date));
    $tx = $koneksi->query("SELECT SUM(jumlah) as s FROM transaksi WHERE DATE(created_at)='$date' AND tipe IN ('pembayaran','qr_payment')")->fetch_assoc()['s'] ?? 0;
    $tp = $koneksi->query("SELECT SUM(jumlah) as s FROM topup WHERE DATE(created_at)='$date' AND status='approved'")->fetch_assoc()['s'] ?? 0;
    $chart_data[] = (float)$tx;
    $chart_topup[] = (float)$tp;
}

$roles = ['student', 'teacher', 'parent', 'merchant'];
$role_data = [];
foreach ($roles as $role) {
    $role_data[] = (int)$koneksi->query("SELECT COUNT(*) as c FROM users WHERE role='$role'")->fetch_assoc()['c'];
}

echo json_encode([
    'success' => true,
    'labels' => $chart_labels,
    'chart_data' => $chart_data,
    'chart_topup' => $chart_topup,
    'role_data' => $role_data,
]);
