<?php
session_start();
require_once '../koneksi.php';
if (!isset($_SESSION['user_id'])) exit;
$user_id = (int)$_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);
if ($id > 0) $koneksi->query("UPDATE notifications SET is_read=1 WHERE id=$id AND user_id=$user_id");
echo 'ok';
?>
