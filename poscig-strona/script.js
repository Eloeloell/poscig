document.addEventListener("DOMContentLoaded", () => {
  const loadComponent = (id, file) => {
  fetch(file)
    .then(res => res.ok ? res.text() : Promise.reject(res))
    .then(data => document.getElementById(id).innerHTML = data)
    .catch(() => console.error('Nie udalo sie zaladowac ${file}'));
};

  loadComponent("header", "components/header.html");
  loadComponent("header", "components/header.html");
});