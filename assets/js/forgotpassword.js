document.getElementById("forgotForm").addEventListener("submit", function (e) {
  let valid = true;
  const email = document.getElementById("email");
  setError(email, "emailError", null);

  if (email.value.trim() === "") {
    setError(email, "emailError", "Email address is required.");
    valid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
    setError(email, "emailError", "Please enter a valid email address.");
    valid = false;
  }

  if (!valid) {
    e.preventDefault();
    return;
  }

  const btn = document.getElementById("submitBtn");
  btn.disabled = true;
  btn.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"' +
    ' role="status" aria-hidden="true"></span>Sending...';
});

function setError(input, errorId, message) {
  const div = document.getElementById(errorId);
  if (message) {
    input.classList.add("is-invalid");
    div.textContent = message;
    div.style.display = "block";
  } else {
    input.classList.remove("is-invalid");
    div.textContent = "";
    div.style.display = "none";
  }
}

document.getElementById("email").addEventListener("input", function () {
  setError(this, "emailError", null);
});
