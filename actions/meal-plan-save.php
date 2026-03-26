<?php
require_once '../config/session.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Location: ../meal-planner.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$mode   = trim($_POST['mode'] ?? '');

// MODE 0 — Delete an existing meal plan
if ($mode === 'delete_plan') {
    $planId = (int)($_POST['plan_id'] ?? 0);

    if ($planId < 1) {
        header('Location: ../meal-planner.php?status=error');
        exit;
    }

    // Verify plan belongs to this user
    $ownerStmt = $pdo->prepare(
        "SELECT plan_id
         FROM meal_plans
         WHERE plan_id = ? AND user_id = ?"
    );
    $ownerStmt->execute([$planId, $userId]);

    if (!$ownerStmt->fetch()) {
        header('Location: ../meal-planner.php?status=error');
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Delete child rows first
        $deleteEntriesStmt = $pdo->prepare(
            "DELETE FROM meal_plan_entries WHERE plan_id = ?"
        );
        $deleteEntriesStmt->execute([$planId]);

        // Delete the plan
        $deletePlanStmt = $pdo->prepare(
            "DELETE FROM meal_plans WHERE plan_id = ? AND user_id = ?"
        );
        $deletePlanStmt->execute([$planId, $userId]);

        $pdo->commit();

        header('Location: ../meal-planner.php?status=deleted');
        exit;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        header('Location: ../meal-planner.php?status=error');
        exit;
    }
}

// MODE 1 — Create a new meal plan
if ($mode === 'create_plan') {
    $planName  = trim($_POST['plan_name'] ?? 'My Meal Plan');
    $startDate = trim($_POST['start_date'] ?? '');
    $endDate   = trim($_POST['end_date'] ?? '');

    if ($planName === '') {
        $planName = 'My Meal Plan';
    }
    $planName = mb_substr($planName, 0, 120);

    $startTs = strtotime($startDate);
    $endTs   = strtotime($endDate);

    if (!$startTs || !$endTs || $endTs < $startTs) {
        header('Location: ../meal-planner.php?status=error');
        exit;
    }

    $startDate = date('Y-m-d', $startTs);
    $endDate   = date('Y-m-d', $endTs);

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO meal_plans (user_id, plan_name, start_date, end_date)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $planName, $startDate, $endDate]);

        $newPlanId = (int)$pdo->lastInsertId();

        header("Location: ../meal-planner.php?plan={$newPlanId}&status=created");
        exit;
    } catch (PDOException $e) {
        header('Location: ../meal-planner.php?status=error');
        exit;
    }
}

// MODE 2 — Save entries for an existing plan
if ($mode !== 'save_entries') {
    header('Location: ../meal-planner.php?status=error');
    exit;
}

$planId = (int)($_POST['plan_id'] ?? 0);

if ($planId < 1) {
    header('Location: ../meal-planner.php?status=error');
    exit;
}

// Verify plan ownership
$ownerStmt = $pdo->prepare(
    "SELECT plan_id
     FROM meal_plans
     WHERE plan_id = ? AND user_id = ?"
);
$ownerStmt->execute([$planId, $userId]);

if (!$ownerStmt->fetch()) {
    header('Location: ../meal-planner.php?status=error');
    exit;
}

// Allowed values
$allowedDays  = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
$allowedSlots = ['Breakfast','Lunch','Dinner'];

// Parse submitted entries
$submitted = $_POST['entries'] ?? [];
$toInsert  = [];

if (is_array($submitted)) {
    foreach ($submitted as $day => $slots) {
        if (!in_array($day, $allowedDays, true) || !is_array($slots)) {
            continue;
        }

        foreach ($slots as $slot => $data) {
            if (!in_array($slot, $allowedSlots, true) || !is_array($data)) {
                continue;
            }

            $recipeId = (int)($data['recipe_id'] ?? 0);
            if ($recipeId < 1) {
                continue;
            }

            $toInsert[] = [$day, $slot, $recipeId];
        }
    }
}

// Validate recipe IDs exist and are approved
if (!empty($toInsert)) {
    $ids = array_values(array_unique(array_column($toInsert, 2)));

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $recipeStmt = $pdo->prepare(
        "SELECT recipe_id
         FROM recipes
         WHERE recipe_id IN ($placeholders)
           AND status = 'approved'"
    );
    $recipeStmt->execute($ids);

    $validRecipeIds = array_map(
        'intval',
        array_column($recipeStmt->fetchAll(PDO::FETCH_ASSOC), 'recipe_id')
    );

    $toInsert = array_values(array_filter(
        $toInsert,
        fn($row) => in_array($row[2], $validRecipeIds, true)
    ));
}

try {
    $pdo->beginTransaction();

    $deleteStmt = $pdo->prepare("DELETE FROM meal_plan_entries WHERE plan_id = ?");
    $deleteStmt->execute([$planId]);

    if (!empty($toInsert)) {
        $placeholders = implode(',', array_fill(0, count($toInsert), '(?, ?, ?, ?)'));
        $values = [];

        foreach ($toInsert as [$day, $slot, $recipeId]) {
            $values[] = $planId;
            $values[] = $recipeId;
            $values[] = $day;
            $values[] = $slot;
        }

        $insertStmt = $pdo->prepare(
            "INSERT INTO meal_plan_entries (plan_id, recipe_id, day_of_week, meal_slot)
             VALUES $placeholders"
        );
        $insertStmt->execute($values);
    }

    $pdo->commit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    header("Location: ../meal-planner.php?plan={$planId}&status=error");
    exit;
}

header("Location: ../meal-planner.php?plan={$planId}&status=saved");
exit;