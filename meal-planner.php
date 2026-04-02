<!DOCTYPE html>
<html lang="en">
<head>
    <?php
        /**
         * meal-planner.php
         * ---------------------------------------------------------------
         * Weekly meal planner grid.
         * - Rows    = days of the week (Mon–Sun)
         * - Columns = meal slots (Breakfast, Lunch, Dinner)
         *
         * Features:
         *  • Create a new named plan with date range
         *  • Load any existing plan owned by the user
         *  • Each cell has a searchable recipe picker (modal)
         *  • Shows recipe title in cell once chosen
         *  • One-click clear per cell
         *  • Save submits to actions/meal-plan-save.php
         *  • Delete existing meal plan
         * ---------------------------------------------------------------
         */

        require_once 'config/session.php';
        require_once 'config/db.php';

        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        $userId   = (int) $_SESSION['user_id'];
        $username = $_SESSION['username'] ?? 'User';
        $initials = strtoupper(substr($username, 0, 2));

        // Flash messages
        $savedMsg = match($_GET['status'] ?? '') {
            'saved'   => ['type' => 'success', 'text' => 'Meal plan saved successfully!'],
            'created' => ['type' => 'success', 'text' => 'New meal plan created!'],
            'deleted' => ['type' => 'success', 'text' => 'Meal plan deleted successfully!'],
            'error'   => ['type' => 'error',   'text' => 'Something went wrong. Please try again.'],
            default   => null,
        };

        // Load this user's existing plans
        $plansStmt = $pdo->prepare(
            "SELECT plan_id, plan_name, start_date, end_date,
                    (SELECT COUNT(*) FROM meal_plan_entries WHERE plan_id = mp.plan_id) AS entry_count
            FROM meal_plans mp
            WHERE user_id = ?
            ORDER BY created_at DESC"
        );
        $plansStmt->execute([$userId]);
        $allPlans = $plansStmt->fetchAll(PDO::FETCH_ASSOC);

        // Determine active plan
        $activePlanId = (int) ($_GET['plan'] ?? ($allPlans[0]['plan_id'] ?? 0));
        $activePlan   = null;
        foreach ($allPlans as $p) {
            if ((int)$p['plan_id'] === $activePlanId) {
                $activePlan = $p;
                break;
            }
        }

        // Load entries for the active plan
        $planEntries = [];
        if ($activePlan) {
            $entriesStmt = $pdo->prepare(
                "SELECT mpe.day_of_week, mpe.meal_slot, mpe.recipe_id,
                        r.title, r.prep_time_min, r.cook_time_min
                FROM meal_plan_entries mpe
                JOIN recipes r ON r.recipe_id = mpe.recipe_id
                WHERE mpe.plan_id = ?"
            );
            $entriesStmt->execute([$activePlanId]);

            foreach ($entriesStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $planEntries[$row['day_of_week']][$row['meal_slot']] = $row;
            }
        }

        // All approved recipes for the picker
        $recipes = $pdo->query(
            "SELECT r.recipe_id, r.title,
                    r.prep_time_min, r.cook_time_min, r.servings,
                    GROUP_CONCAT(DISTINCT dt.tag_name ORDER BY dt.tag_name SEPARATOR ', ') AS tags
            FROM recipes r
            LEFT JOIN recipe_dietary_tags rdt ON rdt.recipe_id = r.recipe_id
            LEFT JOIN dietary_tags dt         ON dt.tag_id     = rdt.tag_id
            WHERE r.status = 'approved'
            GROUP BY r.recipe_id
            ORDER BY r.title ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

        $days  = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        $slots = ['Breakfast','Lunch','Dinner'];
        $currentPage = basename($_SERVER['PHP_SELF']);

        $title = "MealMate - Meal Planner";
        include_once 'includes/header.php';
    ?>
    <script>
        const allRecipes = <?= json_encode(array_map(fn($r) => [
            'id' => (int)$r['recipe_id'],
            'title' => $r['title'],
            'tags' => $r['tags'] ?? ''
        ], $recipes), JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    </script>
    <script src="assets/js/meal_planner.js" defer></script>
    <style>
        .autocomplete-wrapper {
            position: relative;
            flex-grow: 1;
        }
        .autocomplete-wrapper input {
            width: 100%;
        }
        .autocomplete-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 4px 14px rgba(0,0,0,0.1);
            margin-top: 4px;
        }
        .autocomplete-dropdown .ac-item {
            padding: 8px 12px;
            cursor: pointer;
            font-size: 14px;
        }
        .autocomplete-dropdown .ac-item:hover,
        .autocomplete-dropdown .ac-item.ac-active {
            background: #f0f0f0;
        }
        .autocomplete-dropdown .ac-item .ac-tags {
            font-size: 12px;
            color: grey;
        }
        .autocomplete-dropdown .ac-no-results {
            padding: 8px 12px;
            font-size: 14px;
            color: grey;
        }
    </style>
    <!-- <link rel="stylesheet" href="assets/css/meal-planner.css">
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

        .flash {
            padding: 14px 18px;
            border-radius: 14px;
            margin-bottom: 22px;
            font-weight: 600;
            border: 1px solid #ddd;
            background: whitesmoke;
            color: black;
        }

        .flash.error {
            background: #fff0f0;
            border-color: #f0caca;
            color: #8b1e1e;
        }

        .card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
            margin-bottom: 24px;
        }

        .card h2 {
            font-size: 20px;
            margin-bottom: 16px;
            color: #1f3b2d;
        }

        .top-section {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .field-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .field-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .field-group.full {
            grid-column: 1 / -1;
        }

        .field-group label {
            font-size: 14px;
            font-weight: 600;
            color: black;
        }

        .field-group input {
            padding: 11px 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            background: white;
            color: black;
        }

        .field-group input:focus {
            outline: none;
            border-color: black;
        }

        .small-note {
            font-size: 13px;
            color: grey;
            margin-top: 10px;
        }

        .plan-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .plan-item {
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 14px;
            background: white;
            transition: 0.2s ease;
        }

        .plan-item.active {
            border: 2px solid black;
            background: whitesmoke;
        }

        .plan-item a {
            display: block;
        }

        .plan-item h3 {
            font-size: 16px;
            margin-bottom: 6px;
            color: #1f3b2d;
        }

        .plan-item p {
            font-size: 14px;
            color: grey;
            margin-bottom: 4px;
        }

        .stats-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card h3 {
            font-size: 15px;
            color: grey;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 30px;
            font-weight: bold;
            color: black;
        }

        .planner-form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .planner-actions-left,
        .planner-actions-right {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
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

        .btn-danger {
            background: #b91c1c;
            color: white;
        }

        .btn-danger:hover {
            background: #991b1b;
        }

        .planner-wrapper {
            overflow-x: auto;
        }

        .planner-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
            min-width: 760px;
        }

        .planner-table th {
            text-align: left;
            font-size: 14px;
            color: grey;
            padding: 0 10px 8px;
        }

        .planner-table td {
            vertical-align: top;
            padding: 0 10px;
        }

        .day-cell {
            min-width: 130px;
            font-weight: 700;
            color: #1f3b2d;
            padding-top: 12px;
        }

        .meal-cell {
            background: white;
            border-radius: 14px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
            padding: 14px;
            min-height: 125px;
            position: relative;
        }

        .meal-slot-title {
            font-size: 13px;
            color: grey;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .meal-placeholder {
            font-size: 14px;
            color: grey;
            margin-bottom: 12px;
        }

        .selected-recipe {
            margin-bottom: 12px;
        }

        .selected-recipe strong {
            display: block;
            color: #1f3b2d;
            margin-bottom: 6px;
            font-size: 15px;
        }

        .cell-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: auto;
        }

        .cell-btn {
            font-size: 13px;
            padding: 8px 10px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 700;
        }

        .pick-btn {
            background: black;
            color: white;
        }

        .clear-btn {
            background: whitesmoke;
            color: black;
        }

        .hidden-input {
            display: none;
        }

        .empty-state {
            text-align: center;
            padding: 30px;
            border-radius: 14px;
            background: lightgray;
            border: 1px dashed lightgray;
            color: black;
        }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.35);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 2000;
        }

        .modal-backdrop.show {
            display: flex;
        }

        .modal {
            background: white;
            width: 100%;
            max-width: 760px;
            border-radius: 16px;
            box-shadow: 0 18px 50px rgba(0,0,0,0.2);
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .modal-header {
            padding: 18px 20px;
            border-bottom: 1px solid #eee;
        }

        .modal-header h3 {
            color: #1f3b2d;
            font-size: 20px;
            margin-bottom: 6px;
        }

        .modal-header p {
            color: grey;
            font-size: 14px;
        }

        .modal-search {
            padding: 16px 20px;
            border-bottom: 1px solid #eee;
        }

        .modal-search input {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
        }

        .modal-body {
            padding: 18px 20px;
            overflow-y: auto;
        }

        .recipe-picker-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .picker-item {
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 14px;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            background: white;
        }

        .picker-info h4 {
            color: #1f3b2d;
            font-size: 16px;
            margin-bottom: 6px;
        }

        .picker-info p {
            color: grey;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .picker-tags {
            font-size: 12px;
            color: black;
        }

        .modal-footer {
            padding: 16px 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
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

            .top-section,
            .stats-row,
            .field-grid {
                grid-template-columns: 1fr;
            }

            .planner-form-actions {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style> -->
</head>
<body>
    <?php require_once 'includes/nav.php'; ?>

<div class="container">
    <div class="d-flex flex-column flex-lg-row gap-4">
        <div class="card p-3 mt-3 flex-grow-1 d-flex flex-column">
            <h2>Create New Plan</h2>
            <form action="actions/meal-plan-save.php" method="POST" class="d-flex flex-column flex-grow-1 mt-5">
                <input type="hidden" name="mode" value="create_plan">

                <div class="d-flex flex-row gap-4 flex-wrap w-100">
                    <div class="d-flex flex-column gap-2 flex-grow-1">
                        <label for="plan_name">Plan Name</label>
                        <input type="text" id="plan_name" name="plan_name" class="form-control" placeholder="e.g. Week 3 Healthy Plan" required>
                    </div>

                    <div class="d-flex flex-column gap-2 flex-grow-1">
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" required>
                    </div>

                    <div class="d-flex flex-column gap-2 flex-grow-1">
                        <label for="end_date">End Date</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" required>
                    </div>
                </div>

                <div class="small-note mt-2">Create a named plan first, then assign recipes into the planner grid below.</div>

                <div class="mt-auto text-end">
                    <button type="submit" class="btn btn-primary">Create Plan</button>
                </div>
            </form>
        </div>

        <div class="card flex-grow-1 p-3 mt-3">
            <h2>My Existing Plans</h2>
            <?php if (!empty($allPlans)): ?>
                <p>Current Number of Plans: <?= count($allPlans) ?></p>
                <div class="d-flex flex-column align-items-center gap-2">
                    <?php if (count($allPlans) > 1): ?>
                        <button class="btn btn-sm btn-outline-none w-100" type="button" id="planCarouselPrev">
                            &#9650;
                        </button>
                    <?php endif; ?>

                    <div class="w-100 overflow-hidden" id="planCarouselTrack" style="height: 120px;">
                        <div class="d-flex flex-column transition-transform" id="planCarouselInner" style="transition: transform 0.3s ease;">
                            <?php foreach (array_reverse($allPlans) as $plan): ?>
                                <div class="plan-carousel-slide" style="min-height: 120px;">
                                    <div class="plan-item <?= (int)$plan['plan_id'] === $activePlanId ? 'active' : '' ?>">
                                        <a href="meal-planner.php?plan=<?= (int)$plan['plan_id'] ?>">
                                            <h3><?= htmlspecialchars($plan['plan_name']) ?></h3>
                                            <p><?= htmlspecialchars($plan['start_date']) ?> to <?= htmlspecialchars($plan['end_date']) ?></p>
                                            <p><?= (int)$plan['entry_count'] ?> planned meal<?= (int)$plan['entry_count'] === 1 ? '' : 's' ?></p>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php if (count($allPlans) > 1): ?>
                        <button class="btn btn-sm btn-outline-none w-100" type="button" id="planCarouselNext">
                            &#9660;
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    You have not created any meal plans yet.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($activePlan): ?>
        <div class="card mt-3 p-3">
            <form action="actions/meal-plan-save.php" method="POST" id="plannerForm">
                <input type="hidden" name="mode" value="save_entries">
                <input type="hidden" name="plan_id" value="<?= (int)$activePlanId ?>">

                <div class="">
                    <div class="d-flex flex-row gap-3 justify-content-between">
                        <div class="mb-3">
                            <h2 class="m-0">Editing:</h2>
                            <span class="fs-medium"><?= htmlspecialchars($activePlan['plan_name']) ?></span>
                        </div>

                        <div class="mb-3">
                            <button type="button" class="btn btn-danger" id="deletePlanBtn">Delete Plan</button>
                            <button type="submit" class="btn btn-primary">Save Meal Plan</button>
                        </div>
                    </div>
                    <div class="">
                        <table class="w-100">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <?php foreach ($slots as $slot): ?>
                                        <th><?= htmlspecialchars($slot) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($days as $day): ?>
                                    <tr>
                                        <td class="day-cell">
                                            <?= htmlspecialchars($day) ?>
                                        </td>

                                        <?php foreach ($slots as $slot): ?>
                                            <?php $entry = $planEntries[$day][$slot] ?? null; ?>
                                            <td>
                                                <div class="meal-cell" data-day="<?= htmlspecialchars($day) ?>" data-slot="<?= htmlspecialchars($slot) ?>">
                                                        
                                                    <input
                                                        type="hidden"
                                                        class="hidden-input recipe-id-input"
                                                        name="entries[<?= htmlspecialchars($day) ?>][<?= htmlspecialchars($slot) ?>][recipe_id]"
                                                        value="<?= $entry ? (int)$entry['recipe_id'] : '' ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        class="hidden-input recipe-title-input"
                                                        value="<?= $entry ? htmlspecialchars($entry['title']) : '' ?>"
                                                    >

                                                    <?php if ($entry): ?>
                                                        <div class="selected-recipe recipe-display">
                                                            <strong><?= htmlspecialchars($entry['title']) ?></strong>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="meal-placeholder recipe-display">No recipe selected</div>
                                                    <?php endif; ?>

                                                    <div class="cell-actions d-flex gap-1">
                                                        <div class="autocomplete-wrapper flex-grow-1">
                                                            <input type="text" class="form-control recipe-search-input" placeholder="Search recipes..." autocomplete="off">
                                                            <div class="autocomplete-dropdown"></div>
                                                        </div>
                                                        <button type="button" class="btn px-2 me-2 py-0 btn-primary clear-cell-btn">Clear</button>
                                                    </div>
                                                </div>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>

            <form action="actions/meal-plan-save.php" method="POST" id="deletePlanForm">
                <input type="hidden" name="mode" value="delete_plan">
                <input type="hidden" name="plan_id" value="<?= (int)$activePlanId ?>">
            </form>
        </div>
    <?php else: ?>
        <div class="empty-state">
            Create a meal plan first to start filling your weekly planner.
        </div>
    <?php endif; ?>
    
    </div>
</div>
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>