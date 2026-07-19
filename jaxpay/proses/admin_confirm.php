<?php
session_start();
header('Content-Type: application/json');
require_once '../koneksi.php';

if (!isset($_SESSION['admin_id'])) { echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'message'=>'Invalid']); exit; }

$topup_id  = (int)($_POST['topup_id'] ?? 0);
$action    = sanitize($_POST['action'] ?? '');
$note      = sanitize($_POST['note'] ?? '');
$admin_id  = (int)$_SESSION['admin_id'];

if (!in_array($action, ['approve','reject'])) {
    echo json_encode(['success'=>false,'message'=>'Aksi tidak valid']); exit;
}

$topup = $koneksi->query("SELECT * FROM topup WHERE id=$topup_id AND status='pending'")->fetch_assoc();
if (!$topup) { echo json_encode(['success'=>false,'message'=>'Top up tidak ditemukan atau sudah diproses']); exit; }

$koneksi->begin_transaction();
try {
    $now = date('Y-m-d H:i:s');

    if ($action === 'approve') {
        $user_id = $topup['user_id'];
        $jumlah  = $topup['jumlah'];

        $user = $koneksi->query("SELECT saldo FROM users WHERE id=$user_id FOR UPDATE")->fetch_assoc();
        $saldo_before = $user['saldo'];
        $saldo_after  = $saldo_before + $jumlah;

        $koneksi->query("UPDATE users SET saldo=$saldo_after WHERE id=$user_id");
        $koneksi->query("UPDATE topup SET status='approved', admin_id=$admin_id, admin_note='$note', approved_at='$now' WHERE id=$topup_id");

        $kode = generateKode('TRX');
        $ket  = 'Top Up via ' . $topup['metode_bayar'] . ' (' . $topup['kode_topup'] . ')';
        $stmt = $koneksi->prepare("INSERT INTO transaksi (kode_transaksi,user_id,tipe,jumlah,saldo_sebelum,saldo_sesudah,keterangan,status) VALUES (?,?,'topup',?,?,?,?,'success')");
        $stmt->bind_param('siddds', $kode, $user_id, $jumlah, $saldo_before, $saldo_after, $ket);
        $stmt->execute();

        // Notif user
        $jfmt = 'Rp ' . number_format($jumlah,0,',','.');
        $msg  = "Top Up $jfmt via {$topup['metode_bayar']} telah disetujui. Saldo bertambah.";
        $stmt2 = $koneksi->prepare("INSERT INTO notifications (user_id,judul,pesan,tipe) VALUES (?,'Top Up Disetujui ✅',?,'topup')");
        $stmt2->bind_param('is', $user_id, $msg);
        $stmt2->execute();

        $detail = "Approve top up {$topup['kode_topup']} - $jfmt";

    } else {
        $koneksi->query("UPDATE topup SET status='rejected', admin_id=$admin_id, admin_note='$note', approved_at='$now' WHERE id=$topup_id");

        $jfmt = 'Rp ' . number_format($topup['jumlah'],0,',','.');
        $msg  = "Top Up $jfmt via {$topup['metode_bayar']} ditolak." . ($note ? " Alasan: $note" : "");
        $stmt2 = $koneksi->prepare("INSERT INTO notifications (user_id,judul,pesan,tipe) VALUES (?,'Top Up Ditolak ❌',?,'info')");
        $stmt2->bind_param('is', $topup['user_id'], $msg);
        $stmt2->execute();

        $detail = "Reject top up {$topup['kode_topup']}";
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $koneksi->query("INSERT INTO activity_logs (user_type,user_id,aksi,detail,ip_address) VALUES ('admin',$admin_id,'".ucfirst($action)." Top Up','$detail','$ip')");

    $koneksi->commit();
    echo json_encode(['success'=>true,'action'=>$action]);

} catch (Exception $e) {
    $koneksi->rollback();
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
?>
