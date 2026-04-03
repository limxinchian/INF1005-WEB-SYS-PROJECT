document
  .getElementById("togglePassword")
  .addEventListener("click", function () {
    const pwd = document.getElementById("password");
    pwd.type = pwd.type === "password" ? "text" : "password";
  });

document.getElementById("toggleConfirm").addEventListener("click", function () {
  const pwd = document.getElementById("confirm_password");
  pwd.type = pwd.type === "password" ? "text" : "password";
});

document.getElementById("password").addEventListener("input", function () {
  const val = this.value;
  const bar = document.getElementById("strengthBar");
  const text = document.getElementById("strengthText");
  let strength = 0;

  if (val.length >= 8) strength++;
  if (/[A-Z]/.test(val)) strength++;
  if (/[0-9]/.test(val)) strength++;
  if (/[^A-Za-z0-9]/.test(val)) strength++;

  const levels = [
    { width: "0%", cls: "", label: "" },
    { width: "25%", cls: "bg-danger", label: "Weak" },
    { width: "50%", cls: "bg-warning", label: "Fair" },
    { width: "75%", cls: "bg-info", label: "Good" },
    { width: "100%", cls: "bg-success", label: "Strong" },
  ];

  bar.style.width = levels[strength].width;
  bar.className = "progress-bar " + levels[strength].cls;
  text.textContent = levels[strength].label;
});

document
  .getElementById("confirm_password")
  .addEventListener("input", function () {
    const pwd = document.getElementById("password").value;
    const confirm = this.value;
    const text = document.getElementById("matchText");

    if (confirm === "") {
      text.textContent = "";
    } else if (pwd === confirm) {
      text.textContent = "Passwords match";
      text.className = "text-success small";
    } else {
      text.textContent = "Passwords do not match";
      text.className = "text-danger small";
    }
  });

document.querySelector("form").addEventListener("submit", function (e) {
  const pwd = document.getElementById("password").value;
  const confirm = document.getElementById("confirm_password").value;
  const terms = document.getElementById("terms").checked;

  if (pwd !== confirm) {
    e.preventDefault();
    alert("Passwords do not match.");
    return;
  }
  if (pwd.length < 8) {
    e.preventDefault();
    alert("Password must be at least 8 characters.");
    return;
  }
  if (!terms) {
    e.preventDefault();
    alert("You must agree to the Terms and Conditions.");
    return;
  }
});
