// ─── Delete plan ─────────────────────────────────────────────────
(function () {
  var deletePlanBtn = document.getElementById("deletePlanBtn");
  var deletePlanForm = document.getElementById("deletePlanForm");
  if (deletePlanBtn && deletePlanForm) {
    deletePlanBtn.addEventListener("click", function () {
      if (
        confirm(
          "Are you sure you want to delete this meal plan? This cannot be undone.",
        )
      ) {
        deletePlanForm.submit();
      }
    });
  }
})();

// ─── Clear cell buttons ─────────────────────────────────────────
(function () {
  document.querySelectorAll(".clear-cell-btn").forEach(function (button) {
    button.addEventListener("click", function () {
      var cell = this.closest(".meal-cell");
      cell.querySelector(".recipe-id-input").value = "";
      cell.querySelector(".recipe-title-input").value = "";
      cell.querySelector(".recipe-display").outerHTML =
        '<div class="meal-placeholder recipe-display">No recipe selected</div>';
    });
  });
})();

// ─── Plan carousel ──────────────────────────────────────────────
(function () {
  var inner = document.getElementById("planCarouselInner");
  var prev = document.getElementById("planCarouselPrev");
  var next = document.getElementById("planCarouselNext");
  if (!inner) return;

  var slides = inner.querySelectorAll(".plan-carousel-slide");
  if (slides.length <= 1) return;

  var slideHeight = 120;
  var current = 0;

  for (var i = 0; i < slides.length; i++) {
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
})();

// ─── Autocomplete recipe search ─────────────────────────────────
(function () {
  var recipes = typeof allRecipes !== "undefined" ? allRecipes : [];
  var activeIndex = -1;

  document.addEventListener("input", function (e) {
    if (!e.target.classList.contains("recipe-search-input")) return;
    var input = e.target;
    var wrapper = input.closest(".autocomplete-wrapper");
    if (!wrapper) return;
    var dropdown = wrapper.querySelector(".autocomplete-dropdown");
    if (!dropdown) return;
    var term = input.value.trim().toLowerCase();
    activeIndex = -1;

    if (term.length === 0) {
      dropdown.style.cssText = "display: none !important;";
      dropdown.innerHTML = "";
      return;
    }

    var matches = recipes.filter(function (r) {
      return r.title.toLowerCase().indexOf(term) !== -1;
    });

    if (matches.length === 0) {
      dropdown.innerHTML = '<div class="ac-no-results">No recipes found</div>';
      dropdown.style.cssText = "display: block !important;";
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
    dropdown.style.cssText = "display: block !important;";
  });

  document.addEventListener("keydown", function (e) {
    if (!e.target.classList.contains("recipe-search-input")) return;
    var wrapper = e.target.closest(".autocomplete-wrapper");
    if (!wrapper) return;
    var dropdown = wrapper.querySelector(".autocomplete-dropdown");
    if (!dropdown) return;
    var items = dropdown.querySelectorAll(".ac-item");
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
      dropdown.style.cssText = "display: none !important;";
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

  document.addEventListener("click", function (e) {
    var item = e.target.closest(".ac-item");
    if (item) {
      selectRecipe(item);
      return;
    }
    document.querySelectorAll(".autocomplete-dropdown").forEach(function (dd) {
      if (
        !dd.contains(e.target) &&
        !e.target.classList.contains("recipe-search-input")
      ) {
        dd.style.cssText = "display: none !important;";
      }
    });
  });

  function selectRecipe(item) {
    var recipeId = item.dataset.id;
    var recipeTitle = item.dataset.title;
    var cell = item.closest(".meal-cell");

    cell.querySelector(".recipe-id-input").value = recipeId;
    cell.querySelector(".recipe-title-input").value = recipeTitle;
    cell.querySelector(".recipe-display").outerHTML =
      '<div class="selected-recipe recipe-display"><strong>' +
      recipeTitle.replace(/</g, "&lt;") +
      "</strong></div>";

    var input = cell.querySelector(".recipe-search-input");
    input.value = "";
    var wrapper = input.closest(".autocomplete-wrapper");
    var dropdown = wrapper.querySelector(".autocomplete-dropdown");
    dropdown.style.cssText = "display: none !important;";
    dropdown.innerHTML = "";
    activeIndex = -1;
  }
})();
