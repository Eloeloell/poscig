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

  loadComponent("header", "/poscig-strona/src/components/header.html")
    .then(headerEl => {
      initNavigation(headerEl);
    })
    .catch(() => {});

  loadComponent("footer", "/poscig-strona/src/components/footer.html").catch(() => {});

  function initNavigation(headerEl) {
    const burger = headerEl.querySelector(".burger");
    const nav = headerEl.querySelector(".nav-links");
    const navbar = headerEl.querySelector(".navbar");
    const dropdowns = headerEl.querySelectorAll(".dropdown");
    const isMobileNav = () => window.matchMedia("(max-width: 768px)").matches;

    const closeDropdown = dropdown => {
      const link = dropdown.querySelector(":scope > a");
      dropdown.classList.remove("open");
      if (link) {
        link.setAttribute("aria-expanded", "false");
      }
    };

    const closeAllDropdowns = current => {
      dropdowns.forEach(dropdown => {
        if (!current || dropdown !== current) {
          closeDropdown(dropdown);
        }
      });
    };

    if (burger && nav) {
      burger.setAttribute("aria-expanded", "false");
      burger.addEventListener("click", () => {
        const isOpen = nav.classList.toggle("show");
        burger.classList.toggle("active", isOpen);
        burger.setAttribute("aria-expanded", String(isOpen));
      });
    }

    dropdowns.forEach(dropdown => {
      const link = dropdown.querySelector(":scope > a");
      const menu = dropdown.querySelector(":scope > .dropdown-menu");

      if (!link || !menu) return;

      link.setAttribute("aria-haspopup", "true");
      link.setAttribute("aria-expanded", "false");

      link.addEventListener("click", e => {
        if (!isMobileNav()) return;

        e.preventDefault();
        const willOpen = !dropdown.classList.contains("open");
        closeAllDropdowns(dropdown);
        dropdown.classList.toggle("open", willOpen);
        link.setAttribute("aria-expanded", String(willOpen));
      });
    });

    document.addEventListener("click", e => {
      if (!isMobileNav() || headerEl.contains(e.target)) return;

      closeAllDropdowns();
      if (nav) nav.classList.remove("show");
      if (burger) {
        burger.classList.remove("active");
        burger.setAttribute("aria-expanded", "false");
      }
    });

    const syncFloatingHeader = () => {
      if (!navbar) return;
      const threshold = Math.max(80, headerEl.offsetHeight - navbar.offsetHeight);
      document.body.classList.toggle("header-scrolled", window.scrollY > threshold);
    };

    window.addEventListener("resize", () => {
      if (isMobileNav()) return;

      closeAllDropdowns();
      if (nav) nav.classList.remove("show");
      if (burger) {
        burger.classList.remove("active");
        burger.setAttribute("aria-expanded", "false");
      }
      syncFloatingHeader();
    });

    window.addEventListener("scroll", syncFloatingHeader, { passive: true });
    syncFloatingHeader();
  }
});
