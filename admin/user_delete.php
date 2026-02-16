<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
$pdo = db();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
$stmt->execute([$id]);

header("Location: users.php");
exit;
