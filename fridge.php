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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MealMate - My Fridge</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: white;
            color: black;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px 40px;
        }

        .page-header {
            margin-bottom: 25px;
        }

        .page-header h1 {
            font-size: 32px;
            margin-bottom: 8px;
            color: #1f3b2d;
        }

        .page-header p {
            color: grey;
            font-size: 15px;
            line-height: 1.6;
        }

        .card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        }

        .top-nav {
            background: white;
            border-bottom: 1px solid grey;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .brand {
            font-size: 24px;
            font-weight: 700;
            color: darkblue;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .nav-links a {
            text-decoration: none;
            color: black;
            font-weight: 600;
            font-size: 15px;
            padding: 8px 12px;
            border-radius: 8px;
            transition: 0.2s ease;
        }

        .nav-links a:hover {
            background: whitesmoke;
            color: black;
        }

        .nav-links a.active {
            background: black;
            color: white;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .profile-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: grey;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            overflow: hidden;
        }

        .signout-btn {
            text-decoration: none;
            background: white;
            color: black;
            font-weight: 600;
            padding: 9px 14px;
            border-radius: 8px;
            transition: 0.2s ease;
        }

        .signout-btn:hover {
            background: lightgray;
        }

        .success-alert {
            background: whitesmoke;
            color: black;
            border: 1px solid #ddd;
            padding: 14px 18px;
            border-radius: 14px;
            margin-bottom: 22px;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .stat-card h3 {
            font-size: 15px;
            color: grey;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
            color: black;
        }

        .toolbar-card {
            margin-bottom: 24px;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 260px;
            padding: 12px 14px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 15px;
            color: black;
            background: white;
        }

        .search-input:focus {
            outline: none;
            border-color: black;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            border: none;
            border-radius: 10px;
            padding: 10px 16px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            transition: 0.2s ease;
        }

        .btn-primary {
            background: black;
            color: white;
        }

        .btn-primary:hover {
            background: #222;
        }

        .btn-secondary {
            background: whitesmoke;
            color: black;
        }

        .btn-secondary:hover {
            background: #eeeeee;
        }

        .ingredient-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
        }

        .ingredient-card {
            background: white;
            border-radius: 14px;
            padding: 18px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
            transition: 0.2s ease;
        }

        .ingredient-card.selected {
            border: 2px solid black;
            background: whitesmoke;
        }

        .ingredient-label {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            cursor: pointer;
        }

        .ingredient-label input {
            margin-top: 4px;
            width: 18px;
            height: 18px;
            accent-color: black;
        }

        .ingredient-name {
            font-weight: 700;
            color: #1f3b2d;
            margin-bottom: 6px;
            font-size: 16px;
        }

        .ingredient-id {
            color: grey;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .ingredient-meta {
            color: black;
            font-size: 13px;
            line-height: 1.5;
        }

        .footer-actions {
            margin-top: 24px;
        }

        .selection-count {
            color: grey;
            font-weight: 600;
            font-size: 14px;
        }

        .empty-state {
            text-align: center;
            padding: 30px;
            border-radius: 14px;
            background: lightgray;
            border: 1px dashed lightgray;
            color: black;
        }

        @media (max-width: 900px) {
            .nav-inner {
                flex-direction: column;
                align-items: flex-start;
            }

            .nav-left, .nav-right {
                width: 100%;
                justify-content: space-between;
            }

            .nav-left {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .nav-links {
                gap: 10px;
            }
        }
    </style>
</head>
<body>

<nav class="top-nav">
    <div class="nav-inner">
        <div class="nav-left">
            <a href="dashboard.php" class="brand">MealMate</a>

            <div class="nav-links">
                <a href="recipes.php" class="<?= $currentPage === 'recipes.php' ? 'active' : '' ?>">Recipes</a>
                <a href="fridge.php" class="<?= $currentPage === 'fridge.php' ? 'active' : '' ?>">Fridge</a>
                <a href="meal-planner.php" class="<?= $currentPage === 'meal-planner.php' ? 'active' : '' ?>">Planner</a>
                <a href="favourites.php" class="<?= $currentPage === 'favourites.php' ? 'active' : '' ?>">Favourites</a>
            </div>
        </div>

        <div class="nav-right">
            <a href="profile.php" class="profile-circle" title="My Profile">
                <?= htmlspecialchars($initials) ?>
            </a>
            <a href="logout.php" class="signout-btn">Sign Out</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="page-header">
        <h1>My Fridge</h1>
        <p>Select the ingredients you currently have at home. This shows the full ingredient list from your database.</p>
    </div>

    <?php if ($success): ?>
        <div class="success-alert">Your fridge ingredients were updated successfully.</div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="card stat-card">
            <h3>Total Ingredients in Schema</h3>
            <div class="value"><?= $totalIngredients ?></div>
        </div>

        <div class="card stat-card">
            <h3>Ingredients Selected</h3>
            <div class="value" id="selectedCount"><?= $fridgeCount ?></div>
        </div>
    </div>

    <form action="actions/fridge-update.php" method="POST">
        <div class="card toolbar-card">
            <div class="toolbar">
                <input
                    type="text"
                    id="ingredientSearch"
                    class="search-input"
                    placeholder="Search ingredients or allergens..."
                >

                <div class="action-buttons">
                    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                    <button type="button" class="btn btn-secondary" id="clearAllBtn">Clear All</button>
                    <button type="submit" class="btn btn-primary">Save Fridge</button>
                </div>
            </div>
        </div>

        <?php if (!empty($allIngredients)): ?>
            <div class="ingredient-grid">
                <?php foreach ($allIngredients as $ingredient): ?>
                    <?php
                        $id = (int)$ingredient['ingredient_id'];
                        $checked = in_array($id, $fridgeIds, true);
                        $allergenText = trim((string)($ingredient['allergens'] ?? ''));
                    ?>
                    <div
                        class="ingredient-card searchable-item <?= $checked ? 'selected' : '' ?>"
                        data-search="<?= htmlspecialchars(strtolower($ingredient['ingredient_name'] . ' ' . $allergenText)) ?>"
                    >
                        <label class="ingredient-label">
                            <input
                                type="checkbox"
                                name="ingredients[]"
                                value="<?= $id ?>"
                                <?= $checked ? 'checked' : '' ?>
                            >
                            <div>
                                <div class="ingredient-name"><?= htmlspecialchars($ingredient['ingredient_name']) ?></div>
                                <div class="ingredient-id">Ingredient ID: <?= $id ?></div>
                                <div class="ingredient-meta">
                                    <?= $allergenText !== '' ? 'Allergens: ' . htmlspecialchars($allergenText) : 'No allergen tags recorded' ?>
                                </div>
                            </div>
                        </label>
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

<script>
    const searchInput = document.getElementById('ingredientSearch');
    const ingredientCards = document.querySelectorAll('.searchable-item');
    const checkboxes = document.querySelectorAll('input[type="checkbox"][name="ingredients[]"]');
    const selectedCountTop = document.getElementById('selectedCount');
    const selectedCountBottom = document.getElementById('bottomSelectedCount');
    const clearAllBtn = document.getElementById('clearAllBtn');

    function updateSelectedCount() {
        const checked = document.querySelectorAll('input[type="checkbox"][name="ingredients[]"]:checked').length;
        selectedCountTop.textContent = checked;
        selectedCountBottom.textContent = checked;

        ingredientCards.forEach(card => {
            const checkbox = card.querySelector('input[type="checkbox"]');
            card.classList.toggle('selected', checkbox.checked);
        });
    }

    function filterIngredients() {
        const term = searchInput.value.trim().toLowerCase();

        ingredientCards.forEach(card => {
            const searchText = card.dataset.search || '';
            card.style.display = searchText.includes(term) ? '' : 'none';
        });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateSelectedCount));
    searchInput.addEventListener('input', filterIngredients);

    clearAllBtn.addEventListener('click', () => {
        checkboxes.forEach(cb => cb.checked = false);
        updateSelectedCount();
    });

    updateSelectedCount();
</script>
</body>
</html>