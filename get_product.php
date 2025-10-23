<?php
header('Content-Type: application/json; charset=utf-8');
include "db.php";

if (!isset($_POST['barcode'])) {
    echo json_encode(['ok' => false, 'error' => 'No barcode provided']);
    exit;
}

$barcode = trim($_POST['barcode']);

if ($barcode === '') {
    echo json_encode(['ok' => false, 'error' => 'Empty barcode']);
    exit;
}

$stmt = $conn->prepare("SELECT id, name, barcode, price FROM item WHERE barcode = ? LIMIT 1");
$stmt->bind_param("s", $barcode);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    echo json_encode(['ok' => true, 'product' => $row]);
} else {
    echo json_encode(['ok' => false, 'error' => 'Product not found']);
}
$stmt->close();
$conn->close();
