document.addEventListener("DOMContentLoaded", function () {
  // --- SEARCHABLE INGREDIENT DROPDOWN ---
  function initIngredientPicker(picker) {
    const searchInput = picker.querySelector(".ingredient-search");
    const hiddenInput = picker.querySelector(".ingredient-id-input");
    const dropdown = picker.querySelector(".ingredient-dropdown");

    function renderDropdown(term) {
      const filtered = allIngredients.filter((ing) =>
        ing.name.toLowerCase().includes(term.toLowerCase()),
      );
      dropdown.innerHTML = "";

      if (filtered.length === 0) {
        dropdown.innerHTML =
          '<div class="list-group-item text-muted">No results</div>';
      } else {
        filtered.forEach((ing) => {
          const item = document.createElement("button");
          item.type = "button";
          item.className = "list-group-item list-group-item-action";
          item.textContent = ing.name;
          item.dataset.id = ing.id;
          item.addEventListener("click", function () {
            hiddenInput.value = ing.id;
            searchInput.value = ing.name;
            dropdown.style.display = "none";
          });
          dropdown.appendChild(item);
        });
      }
      dropdown.style.display = "block";
    }

    searchInput.addEventListener("focus", function () {
      renderDropdown(this.value);
    });

    searchInput.addEventListener("input", function () {
      hiddenInput.value = "";
      renderDropdown(this.value);
    });

    document.addEventListener("click", function (e) {
      if (!picker.contains(e.target)) {
        dropdown.style.display = "none";
      }
    });
  }

  // Initialize the first picker
  document.querySelectorAll(".ingredient-picker").forEach(initIngredientPicker);

  // --- INGREDIENTS LOGIC ---
  const ingredientsContainer = document.getElementById("ingredients-container");
  const addIngredientBtn = document.getElementById("add-ingredient-btn");

  addIngredientBtn.addEventListener("click", function () {
    const newRow = document.createElement("div");
    newRow.className =
      "mb-3 ingredient-row d-flex flex-row justify-content-between align-items-center gap-2";

    newRow.innerHTML = `
                    <div class="ingredient-picker position-relative flex-grow-1">
                        <input type="hidden" name="ingredient_ids[]" class="ingredient-id-input" required>
                        <input type="text" class="form-control ingredient-search" placeholder="Type to search ingredient..." autocomplete="off">
                        <div class="ingredient-dropdown list-group position-absolute w-100 shadow" style="z-index:1050; max-height:200px; overflow-y:auto; display:none;"></div>
                    </div>
                    <input type="number" step="0.1" min="0" class="form-control" name="ingredient_amounts[]" placeholder="Amount (e.g. 100)" required style="width: 130px;">
                    <select class="form-select" name="ingredient_units[]" required style="width: 100px;">
                        <option value="">Unit</option>
                        <option value="g">g</option>
                        <option value="kg">kg</option>
                        <option value="ml">ml</option>
                        <option value="L">L</option>
                        <option value="tbsp">tbsp</option>
                        <option value="tsp">tsp</option>
                        <option value="cup">cup</option>
                        <option value="pcs">pcs</option>
                        <option value="whole">whole</option>
                        <option value="pinch">pinch</option>
                    </select>
                    <button type="button" class="btn btn-danger remove-btn">X</button>
            `;

    ingredientsContainer.appendChild(newRow);
    initIngredientPicker(newRow.querySelector(".ingredient-picker"));
    updateRemoveButtons(ingredientsContainer, ".ingredient-row");
  });

  // --- STEPS LOGIC ---
  const stepsContainer = document.getElementById("steps-container");
  const addStepBtn = document.getElementById("add-step-btn");

  addStepBtn.addEventListener("click", function () {
    const firstRow = document.querySelector(".step-row");
    const newRow = firstRow.cloneNode(true);
    newRow.querySelector("textarea").value = "";
    stepsContainer.appendChild(newRow);
    updateStepNumbers();
    updateRemoveButtons(stepsContainer, ".step-row");
  });

  // --- GLOBAL REMOVE LOGIC ---
  document.body.addEventListener("click", function (e) {
    if (e.target.classList.contains("remove-btn") && !e.target.disabled) {
      const row = e.target.closest(".dynamic-row");
      const container = row.parentElement;
      const rowClass = row.classList.contains("ingredient-row")
        ? ".ingredient-row"
        : ".step-row";

      row.remove();

      if (rowClass === ".step-row") updateStepNumbers();
      updateRemoveButtons(container, rowClass);
    }
  });

  function updateStepNumbers() {
    const counters = stepsContainer.querySelectorAll(".step-counter");
    counters.forEach((counter, index) => {
      counter.textContent = index + 1;
    });
  }

  function updateRemoveButtons(container, rowSelector) {
    const rows = container.querySelectorAll(rowSelector);
    const removeBtns = container.querySelectorAll(".remove-btn");

    if (rows.length === 1) {
      removeBtns[0].disabled = true;
    } else {
      removeBtns.forEach((btn) => (btn.disabled = false));
    }
  }
});
