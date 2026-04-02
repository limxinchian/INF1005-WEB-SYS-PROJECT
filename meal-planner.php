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
    <link rel="stylesheet" href="assets/css/meal_planner.css">
    <script>
        // do not remove, this is to make sure the dropdown works!!!
        const allRecipes = <?= json_encode(array_map(fn($r) => [
            'id' => (int)$r['recipe_id'],
            'title' => $r['title'],
            'tags' => $r['tags'] ?? ''
        ], $recipes), JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    </script>
    <script src="assets/js/meal_planner.js" defer></script>
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