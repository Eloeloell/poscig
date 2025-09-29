document.addEventListener("DOMContentLoaded", () => {
  const loadComponent = (id, file) => {
    const placeholder = document.getElementById(id);
    if (!placeholder) return Promise.reject(new Error(`No placeholder #${id}`));

    return fetch(file)
      .then((res) => {
        if (!res.ok) throw new Error(`HTTP error: ${res.status}`);
        return res.text();
      })
      .then((data) => {
        placeholder.innerHTML = data;
        return placeholder; // resolve with inserted element
      })
      .catch((err) => {
        console.error(`Nie udało się załadować ${file}:`, err);
        throw err;
      });
  };

  // load header, then init burger
  loadComponent("header", "../components/header.html")
    .then((headerEl) => {
      console.log("Header loaded");
      const burger = headerEl.querySelector(".burger");
      const nav = headerEl.querySelector(".nav-links") || document.querySelector(".nav-links");
      if (burger && nav) {
        burger.addEventListener("click", () => {
          nav.classList.toggle("show");
          burger.classList.toggle("active");
        });
      } else {
        console.warn("Burger or nav not found inside header.");
      }
    })
    .catch(() => { /* error logged earlier */ });

  // footer can load independently
  loadComponent("footer", "../components/footer.html").catch(() => {});
});
