// Toggle show/hide password
function togglePwd(fieldId) {
  const field = document.getElementById(fieldId);
  field.type = field.type === "password" ? "text" : "password";
}

// Password strength bar for new password
document.getElementById("new_password").addEventListener("input", function () {
  const val = this.value;
  const bar = document.getElementById("pwdStrengthBar");
  const text = document.getElementById("pwdStrengthText");
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

// Password match checker
document
  .getElementById("confirm_new_password")
  .addEventListener("input", function () {
    const newPwd = document.getElementById("new_password").value;
    const confirm = this.value;
    const text = document.getElementById("pwdMatchText");

    if (confirm === "") {
      text.textContent = "";
    } else if (newPwd === confirm) {
      text.textContent = "Passwords match";
      text.className = "text-success small";
    } else {
      text.textContent = "Passwords do not match";
      text.className = "text-danger small";
    }
  });
