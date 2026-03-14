(() => {
  const enhanceNavState = () => {
    const currentPath = window.location.pathname || "/";
    const links = document.querySelectorAll("[data-nav-link]");

    links.forEach((link) => {
      const href = link.getAttribute("href");
      if (!href) {
        return;
      }

      const active = href === "/"
        ? currentPath === "/"
        : currentPath === href || currentPath.startsWith(`${href}/`);

      link.classList.toggle("active", active);
      if (active) {
        link.setAttribute("aria-current", "page");

        const parentDropdown = link.closest(".dropdown");
        if (parentDropdown) {
          const toggle = parentDropdown.querySelector(".dropdown-toggle");
          if (toggle) {
            toggle.classList.add("active");
          }
        }
      } else {
        link.removeAttribute("aria-current");
      }
    });
  };

  const enhanceValidation = () => {
    const forms = document.querySelectorAll("form[novalidate]");
    forms.forEach((form) => {
      form.addEventListener("submit", (event) => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add("was-validated");
      });
    });
  };

  const enhanceResponsiveTables = () => {
    const wrappers = document.querySelectorAll(".table-responsive");
    wrappers.forEach((wrapper) => {
      wrapper.classList.add("mobile-stack");
      const table = wrapper.querySelector("table");
      if (!table) {
        return;
      }

      const headers = Array.from(table.querySelectorAll("thead th")).map((th) => th.textContent.trim());
      table.querySelectorAll("tbody tr").forEach((row) => {
        row.querySelectorAll("td").forEach((cell, idx) => {
          if (!cell.dataset.label && headers[idx]) {
            cell.dataset.label = headers[idx];
          }
        });
      });
    });
  };

  const enhanceAdminFilters = () => {
    document.querySelectorAll("[data-table-filter]").forEach((input) => {
      const targetSelector = input.getAttribute("data-table-filter");
      if (!targetSelector) {
        return;
      }

      const table = document.querySelector(targetSelector);
      if (!table) {
        return;
      }

      const rows = Array.from(table.querySelectorAll("tbody tr"));
      input.addEventListener("input", () => {
        const keyword = input.value.trim().toLowerCase();
        rows.forEach((row) => {
          const text = row.textContent.toLowerCase();
          row.style.display = keyword === "" || text.includes(keyword) ? "" : "none";
        });
      });
    });
  };

  const animateCards = () => {
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (reduceMotion) {
      return;
    }

    document.querySelectorAll(".card, .table-responsive, .admin-toolbar, .cta-band").forEach((node, index) => {
      node.classList.add("pp-fade-in");
      node.style.animationDelay = `${Math.min(index * 35, 220)}ms`;
    });
  };

  enhanceNavState();
  enhanceValidation();
  enhanceResponsiveTables();
  enhanceAdminFilters();
  animateCards();
})();
