document.querySelectorAll(".favourite-btn").forEach((button) => {
  button.addEventListener("click", async function () {
    const recipeId = this.dataset.recipeId;
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
        this.textContent = "Add to Favourites";
        return;
      }
      window.location.reload();
    } catch (error) {
      this.disabled = false;
      this.textContent = "Add to Favourites";
    }
  });
});
