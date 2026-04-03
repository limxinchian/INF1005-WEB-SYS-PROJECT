document.querySelectorAll(".remove-favourite-btn").forEach((button) => {
  button.addEventListener("click", async function () {
    const recipeId = this.dataset.recipeId;
    const card = this.closest(".fav-card");

    if (!confirm("Remove this recipe from your favourites?")) {
      return;
    }

    this.disabled = true;
    this.textContent = "Removing...";

    try {
      const response = await fetch("actions/favourite-toggle.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: "recipe_id=" + encodeURIComponent(recipeId),
      });

      if (!response.ok) {
        this.disabled = false;
        this.textContent = "Remove";
        return;
      }

      if (card) {
        card.classList.add("removing");
        setTimeout(() => {
          card.remove();
          window.location.reload();
        }, 300);
      } else {
        window.location.reload();
      }
    } catch (error) {
      this.disabled = false;
      this.textContent = "Remove";
    }
  });
});
