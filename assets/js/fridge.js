const searchInput = document.getElementById("ingredientSearch");
const ingredientCards = document.querySelectorAll(".searchable-item");
const checkboxes = document.querySelectorAll(
  'input[type="checkbox"][name="ingredients[]"]',
);
const selectedCountTop = document.getElementById("selectedCount");
const selectedCountBottom = document.getElementById("bottomSelectedCount");
const clearAllBtn = document.getElementById("clearAllBtn");

function updateSelectedCount() {
  const checked = document.querySelectorAll(
    'input[type="checkbox"][name="ingredients[]"]:checked',
  ).length;

  if (selectedCountTop) selectedCountTop.textContent = checked;
  if (selectedCountBottom) selectedCountBottom.textContent = checked;

  ingredientCards.forEach((card) => {
    const checkbox = card.querySelector('input[type="checkbox"]');
    card.classList.toggle("selected", checkbox.checked);
  });
}

function filterIngredients() {
  const term = searchInput.value.trim().toLowerCase();

  ingredientCards.forEach((card) => {
    const searchText = card.dataset.search || "";
    const col = card.closest(".col");
    const target = col || card;
    target.style.display = searchText.includes(term) ? "" : "none";
  });
}

checkboxes.forEach((cb) => cb.addEventListener("change", updateSelectedCount));
searchInput.addEventListener("input", filterIngredients);

clearAllBtn.addEventListener("click", () => {
  checkboxes.forEach((cb) => (cb.checked = false));
  updateSelectedCount();
});

updateSelectedCount();
