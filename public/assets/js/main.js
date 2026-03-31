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

  const enhanceChatbot = () => {
    const toggle = document.getElementById('chatbot-toggle');
    const container = document.getElementById('chatbot-container');
    const close = document.getElementById('chatbot-close');
    const input = document.getElementById('chatbot-input');
    const send = document.getElementById('chatbot-send');
    const messages = document.getElementById('chatbot-messages');

    if (!toggle || !container) return;

    const faqData = {
      'pause membership': 'This demo system supports renew and cancel flows. Pause can be an optional extension.',
      'book classes': 'Yes. Members can reserve class slots on the bookings dashboard.',
      'beginners': 'Absolutely. Programs are structured for all levels.',
      'membership': 'We offer various membership plans. Check our plans page for details.',
      'classes': 'We have a variety of classes. Visit our schedule page to see available classes.',
      'trainers': 'Our trainers are experienced professionals. Meet them on the trainers page.',
      'locations': 'We have multiple locations. Find them on the locations page.',
      'contact': 'You can contact us through the contact page.',
      'default': 'I\'m sorry, I don\'t have information on that. Please check our FAQ page or contact us directly.'
    };

    const addMessage = (text, sender) => {
      const message = document.createElement('div');
      message.className = `message ${sender}`;
      message.textContent = text;
      messages.appendChild(message);
      messages.scrollTop = messages.scrollHeight;
    };

    const getResponse = (query) => {
      const lowerQuery = query.toLowerCase();
      for (const key in faqData) {
        if (lowerQuery.includes(key)) {
          return faqData[key];
        }
      }
      return faqData['default'];
    };

    toggle.addEventListener('click', () => {
      container.classList.toggle('d-none');
    });

    close.addEventListener('click', () => {
      container.classList.add('d-none');
    });

    const sendMessage = () => {
      const text = input.value.trim();
      if (!text) return;
      addMessage(text, 'user');
      input.value = '';
      setTimeout(() => {
        const response = getResponse(text);
        addMessage(response, 'bot');
      }, 500);
    };

    send.addEventListener('click', sendMessage);
    input.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') sendMessage();
    });
  };

  enhanceNavState();
  enhanceValidation();
  enhanceResponsiveTables();
  enhanceAdminFilters();
  animateCards();
  enhanceChatbot();
})();
