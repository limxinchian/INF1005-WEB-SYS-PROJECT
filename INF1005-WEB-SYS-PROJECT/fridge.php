<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        require_once 'config/session.php';
        require_once 'config/db.php';

        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        $userId = (int) $_SESSION['user_id'];
        $success = isset($_GET['saved']) && $_GET['saved'] === '1';

        $allIngredients = $pdo->query(
            "SELECT
                i.ingredient_id,
                i.ingredient_name,
                GROUP_CONCAT(DISTINCT a.allergen_name ORDER BY a.allergen_name SEPARATOR ', ') AS allergens
            FROM ingredients i
            LEFT JOIN ingredient_allergens ia ON ia.ingredient_id = i.ingredient_id
            LEFT JOIN allergens a ON a.allergen_id = ia.allergen_id
            GROUP BY i.ingredient_id, i.ingredient_name
            ORDER BY i.ingredient_id ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT ingredient_id FROM user_fridge WHERE user_id = ?");
        $stmt->execute([$userId]);
        $fridgeIds = array_map('intval', array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'ingredient_id'));

        $totalIngredients = count($allIngredients);
        $fridgeCount = count($fridgeIds);
        $initials = strtoupper(substr($_SESSION['username'] ?? 'U', 0, 2));
        $currentPage = basename($_SERVER['PHP_SELF']);

        $title= "MealMate - My Fridge";
        require_once 'includes/header.php';
    ?>
    <link rel="stylesheet" href="assets/css/fridge.css">
     <script src="assets/js/fridge.js" defer></script>
</head>
<body>
    <?php require_once 'includes/nav.php'; ?>
    
    <main>
        <div class="container">
            <div class="mt-3">
                <h1>My Fridge</h1>
                <p>Select the ingredients you currently have at home. This shows the full ingredient list from your database.</p>
            </div>
            <form action="actions/fridge-update.php" method="POST">
                
        <div class="card d-flex flex-lg-row flex-column gap-3 p-4 mb-4">
            
            <input
                type="text"
                id="ingredientSearch"
                class="flex-grow-1"
                placeholder="Search ingredients or allergens..."
                aria-label="Search ingredients or allergens"
            >

            <div class="action-buttons ms-auto">
                <button type="button" class="btn btn-secondary" id="clearAllBtn">Clear All</button>
                <button type="submit" class="btn btn-primary">Save Fridge</button>
            </div>
        </div>

        <?php if (!empty($allIngredients)): ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
                    <?php foreach ($allIngredients as $ingredient): ?>
                        <?php
                            $id = (int)$ingredient['ingredient_id'];
                            $checked = in_array($id, $fridgeIds, true);
                            $allergenText = trim((string)($ingredient['allergens'] ?? ''));
                        ?>
                        <div class="col">
                        <div
                            class="card p-3 h-100 searchable-item <?= $checked ? 'selected' : '' ?>"
                            data-search="<?= htmlspecialchars(strtolower($ingredient['ingredient_name'] . ' ' . $allergenText)) ?>">
                        <label class="ingredient-label d-flex align-items-start">
                                <input
                                    type="checkbox"
                                    name="ingredients[]"
                                    value="<?= $id ?>"
                                    class="mt-1 me-2"
                                    <?= $checked ? 'checked' : '' ?>
                                >
                                <span class="d-block">
                                    <span class="h4 m-0 d-block"><?= htmlspecialchars(ucwords(strtolower($ingredient['ingredient_name']))) ?></span>
                                    <span class="m-0 fs-small d-block text-muted mt-1">Ingredient ID: <?= $id ?></span>
                                    <span class="m-0 fs-small d-block text-muted">
                                        <?= $allergenText !== '' ? 'Allergens: ' . htmlspecialchars($allergenText) : 'No allergen tags recorded' ?>
                                    </span>
                                </span>
                            </label>
                        </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    No ingredients were found in the database.
                </div>
            <?php endif; ?>

            <div class="footer-actions">
                <div class="selection-count">
                    Selected ingredients: <span id="bottomSelectedCount"><?= $fridgeCount ?></span> / <?= $totalIngredients ?>
                </div>
            </div>
        </form>
        </div>
        
    </main>
    
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>