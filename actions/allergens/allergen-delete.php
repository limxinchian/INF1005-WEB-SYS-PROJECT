<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/admin-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../admin/allergens/allergens.php');
    exit();
}

$allergenId = $_POST['allergen_id'] ?? null;

if (!$allergenId) {
    die('Allergen ID is required.');
}

try {
    $stmt = $pdo->prepare("
        DELETE FROM allergens
        WHERE allergen_id = ?
    ");
    $stmt->execute([$allergenId]);

    header('Location: ../../admin/allergens/allergens.php?message=' . urlencode('Allergen deleted successfully.'));
    exit();
} catch (Throwable $e) {
    die('Failed to delete allergen: ' . $e->getMessage());
}