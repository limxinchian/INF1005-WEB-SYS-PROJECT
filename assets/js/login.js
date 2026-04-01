// ── reCAPTCHA State ───────────────────────────────────────
let recaptchaDone = false;

function onRecaptchaSuccess() {
  recaptchaDone = true;
  document.getElementById("recaptchaError").style.display = "none";
}

function onRecaptchaExpired() {
  recaptchaDone = false;
}

// ── Show/Hide Password ────────────────────────────────────
document
  .getElementById("togglePassword")
  .addEventListener("click", function () {
    const pwd = document.getElementById("password");
    pwd.type = pwd.type === "password" ? "text" : "password";
    this.title = pwd.type === "password" ? "Show password" : "Hide password";
  });

// ── Client-side Validation ────────────────────────────────
document.getElementById("loginForm").addEventListener("submit", function (e) {
  let valid = true;

  const email = document.getElementById("email");
  const password = document.getElementById("password");

  // Reset all errors
  setError(email, "emailError", null);
  setError(password, "passwordError", null);
  document.getElementById("recaptchaError").style.display = "none";

  // Validate email
  if (email.value.trim() === "") {
    setError(email, "emailError", "Email address is required.");
    valid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
    setError(email, "emailError", "Please enter a valid email address.");
    valid = false;
  }

  // Validate password
  if (password.value.trim() === "") {
    setError(password, "passwordError", "Password is required.");
    valid = false;
  }

  // Validate reCAPTCHA
  if (!recaptchaDone) {
    document.getElementById("recaptchaError").style.display = "block";
    valid = false;
  }

  // Stop form if any error
  if (!valid) {
    e.preventDefault();
    return;
  }

  // Show loading spinner on button
  const btn = document.getElementById("submitBtn");
  btn.disabled = true;
  btn.innerHTML =
    '<span class="spinner-border spinner-border-sm me-2"' +
    ' role="status" aria-hidden="true"></span>Signing in...';
});

// ── Show or Clear Field Error ─────────────────────────────
function setError(input, errorId, message) {
  const errorDiv = document.getElementById(errorId);
  if (message) {
    input.classList.add("is-invalid");
    errorDiv.textContent = message;
    errorDiv.style.display = "block";
  } else {
    input.classList.remove("is-invalid");
    errorDiv.textContent = "";
    errorDiv.style.display = "none";
  }
}

// ── Clear Error When User Starts Typing ──────────────────
document.getElementById("email").addEventListener("input", function () {
  setError(this, "emailError", null);
});

document.getElementById("password").addEventListener("input", function () {
  setError(this, "passwordError", null);
});
