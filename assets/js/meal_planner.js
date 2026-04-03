const recipeModal = document.getElementById("recipeModal");
const recipeSearchInput = document.getElementById("recipeSearchInput");
const recipeOptions = document.querySelectorAll(".recipe-option");
const openPickerButtons = document.querySelectorAll(".open-picker-btn");
const clearButtons = document.querySelectorAll(".clear-cell-btn");
const closeModalBtn = document.getElementById("closeModalBtn");
const deletePlanBtn = document.getElementById("deletePlanBtn");
const deletePlanForm = document.getElementById("deletePlanForm");

let activeMealCell = null;

function openModalForCell(cell) {
  activeMealCell = cell;
  recipeModal.classList.add("show");
  recipeSearchInput.value = "";
  filterRecipeOptions();
  recipeSearchInput.focus();
}

function closeModal() {
  recipeModal.classList.remove("show");
  activeMealCell = null;
}

function filterRecipeOptions() {
  const term = recipeSearchInput.value.trim().toLowerCase();
  recipeOptions.forEach((option) => {
    const text = option.dataset.search || "";
    option.style.display = text.includes(term) ? "" : "none";
  });
}

openPickerButtons.forEach((button) => {
  button.addEventListener("click", function () {
    const cell = this.closest(".meal-cell");
    openModalForCell(cell);
  });
});

clearButtons.forEach((button) => {
  button.addEventListener("click", function () {
    const cell = this.closest(".meal-cell");
    cell.querySelector(".recipe-id-input").value = "";
    cell.querySelector(".recipe-title-input").value = "";

    cell.querySelector(".recipe-display").outerHTML =
      '<div class="meal-placeholder recipe-display">No recipe selected</div>';
  });
});

document.querySelectorAll(".select-recipe-btn").forEach((button) => {
  button.addEventListener("click", function () {
    if (!activeMealCell) return;

    const option = this.closest(".recipe-option");
    const recipeId = option.dataset.id;
    const recipeTitle = option.dataset.title;

    activeMealCell.querySelector(".recipe-id-input").value = recipeId;
    activeMealCell.querySelector(".recipe-title-input").value = recipeTitle;

    activeMealCell.querySelector(".recipe-display").outerHTML =
      `<div class="selected-recipe recipe-display">
                    <strong>${recipeTitle}</strong>
                </div>`;

    closeModal();
  });
});

if (recipeSearchInput)
  recipeSearchInput.addEventListener("input", filterRecipeOptions);
if (closeModalBtn) closeModalBtn.addEventListener("click", closeModal);

if (recipeModal) {
  recipeModal.addEventListener("click", function (e) {
    if (e.target === recipeModal) {
      closeModal();
    }
  });
}

document.addEventListener("keydown", function (e) {
  if (
    e.key === "Escape" &&
    recipeModal &&
    recipeModal.classList.contains("show")
  ) {
    closeModal();
  }
});

if (deletePlanBtn && deletePlanForm) {
  deletePlanBtn.addEventListener("click", function () {
    const confirmed = confirm(
      "Are you sure you want to delete this meal plan? This cannot be undone.",
    );
    if (confirmed) {
      deletePlanForm.submit();
    }
  });
}

document.addEventListener("DOMContentLoaded", function () {
  const inner = document.getElementById("planCarouselInner");
  const prev = document.getElementById("planCarouselPrev");
  const next = document.getElementById("planCarouselNext");
  if (!inner) return;

  const slides = inner.querySelectorAll(".plan-carousel-slide");
  if (slides.length <= 1) return;

  const slideHeight = 120;
  let current = 0;

  // Start carousel at the active plan's slide
  for (let i = 0; i < slides.length; i++) {
    if (slides[i].querySelector(".plan-item.active")) {
      current = i;
      break;
    }
  }

  function update() {
    inner.style.transform = "translateY(-" + current * slideHeight + "px)";
  }

  update();

  if (prev) {
    prev.addEventListener("click", function () {
      current = (current - 1 + slides.length) % slides.length;
      update();
    });
  }

  if (next) {
    next.addEventListener("click", function () {
      current = (current + 1) % slides.length;
      update();
    });
  }

  var track = document.getElementById("planCarouselTrack");
  if (track) {
    track.addEventListener(
      "wheel",
      function (e) {
        e.preventDefault();
        if (e.deltaY > 0) {
          current = (current + 1) % slides.length;
        } else {
          current = (current - 1 + slides.length) % slides.length;
        }
        update();
      },
      { passive: false },
    );
  }
});

// ─── Autocomplete recipe search ─────────────────────────────────
document.addEventListener("DOMContentLoaded", function () {
  const recipes = typeof allRecipes !== "undefined" ? allRecipes : [];
  let activeIndex = -1;

  document.addEventListener("input", function (e) {
    if (!e.target.classList.contains("recipe-search-input")) return;
    const input = e.target;
    const dropdown = input
      .closest(".autocomplete-wrapper")
      .querySelector(".autocomplete-dropdown");
    const term = input.value.trim().toLowerCase();
    activeIndex = -1;

    if (term.length === 0) {
      dropdown.style.display = "none";
      dropdown.innerHTML = "";
      return;
    }

    const matches = recipes.filter(function (r) {
      return r.title.toLowerCase().includes(term);
    });

    if (matches.length === 0) {
      dropdown.innerHTML = '<div class="ac-no-results">No recipes found</div>';
      dropdown.style.display = "block";
      return;
    }

    dropdown.innerHTML = matches
      .slice(0, 20)
      .map(function (r) {
        return (
          '<div class="ac-item" data-id="' +
          r.id +
          '" data-title="' +
          r.title.replace(/"/g, "&quot;") +
          '">' +
          "<div>" +
          r.title.replace(/</g, "&lt;") +
          "</div>" +
          (r.tags
            ? '<div class="ac-tags">' + r.tags.replace(/</g, "&lt;") + "</div>"
            : "") +
          "</div>"
        );
      })
      .join("");
    dropdown.style.display = "block";
  });

  // Keyboard navigation
  document.addEventListener("keydown", function (e) {
    if (!e.target.classList.contains("recipe-search-input")) return;
    const dropdown = e.target
      .closest(".autocomplete-wrapper")
      .querySelector(".autocomplete-dropdown");
    const items = dropdown.querySelectorAll(".ac-item");
    if (items.length === 0) return;

    if (e.key === "ArrowDown") {
      e.preventDefault();
      activeIndex = Math.min(activeIndex + 1, items.length - 1);
      highlightItem(items);
    } else if (e.key === "ArrowUp") {
      e.preventDefault();
      activeIndex = Math.max(activeIndex - 1, 0);
      highlightItem(items);
    } else if (e.key === "Enter") {
      e.preventDefault();
      if (activeIndex >= 0 && items[activeIndex]) {
        selectRecipe(items[activeIndex]);
      }
    } else if (e.key === "Escape") {
      dropdown.style.display = "none";
      activeIndex = -1;
    }
  });

  function highlightItem(items) {
    items.forEach(function (item, i) {
      item.classList.toggle("ac-active", i === activeIndex);
    });
    if (items[activeIndex]) {
      items[activeIndex].scrollIntoView({ block: "nearest" });
    }
  }

  // Click on dropdown item
  document.addEventListener("click", function (e) {
    const item = e.target.closest(".ac-item");
    if (item) {
      selectRecipe(item);
      return;
    }
    // Close all dropdowns when clicking outside
    document.querySelectorAll(".autocomplete-dropdown").forEach(function (dd) {
      if (
        !dd.contains(e.target) &&
        !e.target.classList.contains("recipe-search-input")
      ) {
        dd.style.display = "none";
      }
    });
  });

  function selectRecipe(item) {
    const recipeId = item.dataset.id;
    const recipeTitle = item.dataset.title;
    const cell = item.closest(".meal-cell");

    cell.querySelector(".recipe-id-input").value = recipeId;
    cell.querySelector(".recipe-title-input").value = recipeTitle;

    cell.querySelector(".recipe-display").outerHTML =
      '<div class="selected-recipe recipe-display"><strong>' +
      recipeTitle.replace(/</g, "&lt;") +
      "</strong></div>";

    const input = cell.querySelector(".recipe-search-input");
    input.value = "";
    const dropdown = input
      .closest(".autocomplete-wrapper")
      .querySelector(".autocomplete-dropdown");
    dropdown.style.display = "none";
    dropdown.innerHTML = "";
    activeIndex = -1;
  }
});
