document.addEventListener("DOMContentLoaded", () => {
  const loader = document.getElementById("loader");

  window.addEventListener("load", () => {
    if (loader) {
      loader.classList.add("hidden");
    }
  });

  const loadComponent = (id, file) => {
    const placeholder = document.getElementById(id);
    if (!placeholder) return Promise.reject(new Error(`No placeholder #${id}`));

    return fetch(file)
      .then(res => {
        if (!res.ok) throw new Error(`HTTP error: ${res.status}`);
        return res.text();
      })
      .then(data => {
        placeholder.innerHTML = data;
        return placeholder;
      })
      .catch(err => {
        console.error(`Nie udało się załadować ${file}:`, err);
        throw err;
      });
  };

  loadComponent("header", "../components/header.html")
    .then(headerEl => {
      console.log("Header loaded");
      initNavigation(headerEl);
    })
    .catch(() => {});

  loadComponent("footer", "../components/footer.html").catch(() => {});
  
  function initNavigation(headerEl) {
    const burger = headerEl.querySelector(".burger");
    const nav = headerEl.querySelector(".nav-links");
    const dropdowns = headerEl.querySelectorAll(".dropdown");

    if (burger && nav) {
      burger.addEventListener("click", () => {
        nav.classList.toggle("show");
        burger.classList.toggle("active");
      });
    }

    dropdowns.forEach(dropdown => {
      const link = dropdown.querySelector("a");
      const menu = dropdown.querySelector(".dropdown-menu");

      link.addEventListener("click", e => {
        if (window.innerWidth <= 768) {
          e.preventDefault();

          dropdowns.forEach(d => {
            if (d !== dropdown) {
              d.classList.remove("open");
              const otherLink = d.querySelector("a");
              if (otherLink) {
                otherLink.innerHTML = otherLink.innerHTML.replace("▴", "▾");
              }
              const otherMenu = d.querySelector(".dropdown-menu");
              if (otherMenu) otherMenu.style.maxHeight = "0px";
            }
          });

          dropdown.classList.toggle("open");

          if (dropdown.classList.contains("open")) {
            link.innerHTML = link.innerHTML.replace("▾", "▴");
          } else {
            link.innerHTML = link.innerHTML.replace("▴", "▾");
          }

          if (menu) {
            if (dropdown.classList.contains("open")) {
              menu.style.maxHeight = menu.scrollHeight + "px";
            } else {
              menu.style.maxHeight = "0px";
            }
          }
        }
      });
    });

    window.addEventListener("resize", () => {
      if (window.innerWidth > 768) {
        dropdowns.forEach(dropdown => {
          dropdown.classList.remove("open");
          const link = dropdown.querySelector("a");
          if (link) link.innerHTML = link.innerHTML.replace("▴", "▾");
          const menu = dropdown.querySelector(".dropdown-menu");
          if (menu) menu.style.maxHeight = "";
        });
        if (nav) nav.classList.remove("show");
        if (burger) burger.classList.remove("active");
      }
    });
  }
});
