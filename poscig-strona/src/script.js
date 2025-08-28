document.addEventListener("DOMContentLoaded", () => {
  const loadComponent = (id, file) => {
    fetch(file)
      .then((res) => {
        if (!res.ok) throw new Error(`HTTP error: ${res.status}`);
        return res.text();
      })
      .then((data) => {
        const el = document.getElementById(id);
        if (el) el.innerHTML = data;
      })
      .catch((err) =>
        console.error(`Nie udało się załadować ${file}:`, err)
      );
  };

  loadComponent("header", "../components/header.html");
  loadComponent("footer", "../components/footer.html");
});
