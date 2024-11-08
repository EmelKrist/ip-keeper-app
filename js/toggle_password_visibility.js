const toggle_password = document.getElementById("toggle_password");
const password_input = document.getElementById("password");
const toggle_icon = document.getElementById("toggle_icon");

// Смена типа поля ввода пароля и иконки кнопки переключения
toggle_password.addEventListener("click", function () {
  const password_type =
    password_input.getAttribute("type") === "password" ? "text" : "password";
  password_input.setAttribute("type", password_type);
  toggle_icon.classList.toggle("bi-eye");
  toggle_icon.classList.toggle("bi-eye-slash");
});
