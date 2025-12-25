document.addEventListener("click", (e) => {
  const btn = e.target.closest('[data-action="toggle-sidebar"]');
  if (!btn) return;

  const shell = document.getElementById("shell-sidebar");
  if (!shell) return;

  const open = shell.getAttribute("data-sidebar-open") === "true";
  shell.setAttribute("data-sidebar-open", open ? "false" : "true");

  const burger = document.querySelector(".dash-burger");
  if (burger) {
    burger.setAttribute("aria-expanded", open ? "false" : "true");
  }
});

document.addEventListener("keydown", (e) => {
  if (e.key !== "Escape") return;

  const shell = document.getElementById("shell-sidebar");
  if (!shell) return;

  shell.setAttribute("data-sidebar-open", "false");

  const burger = document.querySelector(".dash-burger");
  if (burger) {
    burger.setAttribute("aria-expanded", "false");
  }
});