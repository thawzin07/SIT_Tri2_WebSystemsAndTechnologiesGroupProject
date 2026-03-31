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

    if (!toggle || !container || !close || !input || !send || !messages) return;
    const STORAGE_KEY = 'pulsepoint_chatbot_history_v1';
    const MAX_HISTORY = 60;

    const loadHistory = () => {
      try {
        const raw = window.localStorage.getItem(STORAGE_KEY);
        if (!raw) return [];
        const parsed = JSON.parse(raw);
        if (!Array.isArray(parsed)) return [];
        return parsed.filter((item) =>
          item &&
          (item.sender === 'user' || item.sender === 'bot') &&
          typeof item.text === 'string' &&
          item.text.trim() !== ''
        );
      } catch (e) {
        return [];
      }
    };

    const saveHistory = (history) => {
      try {
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(history.slice(-MAX_HISTORY)));
      } catch (e) {
      }
    };

    const addMessage = (text, sender, persist = true) => {
      const message = document.createElement('div');
      message.className = `message ${sender}`;
      message.textContent = text;
      messages.appendChild(message);
      messages.scrollTop = messages.scrollHeight;

      if (persist) {
        const history = loadHistory();
        history.push({ sender, text });
        saveHistory(history);
      }

      return message;
    };

    const renderHistory = () => {
      const history = loadHistory();
      messages.innerHTML = '';
      if (history.length === 0) {
        addMessage("Hi! I'm here to help with your questions. Ask me about memberships, classes, or anything else!", 'bot');
        return;
      }

      history.forEach((item) => {
        addMessage(item.text, item.sender, false);
      });
    };

    renderHistory();

    toggle.addEventListener('click', () => {
      container.classList.toggle('d-none');
    });

    close.addEventListener('click', () => {
      container.classList.add('d-none');
    });

    const sendMessage = async () => {
      const text = input.value.trim();
      if (!text) return;

      addMessage(text, 'user');
      input.value = '';

      send.disabled = true;
      input.disabled = true;

      const typingIndicator = addMessage('Typing...', 'bot', false);

      try {
        const response = await fetch('/api/chatbot', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ message: text })
        });

        let payload = {};
        try {
          payload = await response.json();
        } catch (e) {
          payload = {};
        }

        typingIndicator.remove();

        if (!response.ok) {
          const fallback = payload.error || 'I can only help with PulsePoint Fitness website topics. Please ask about memberships, classes, trainers, locations, bookings, or contact details.';
          addMessage(fallback, 'bot');
          return;
        }

        const botReply = typeof payload.reply === 'string' && payload.reply.trim() !== ''
          ? payload.reply
          : 'I can only help with PulsePoint Fitness website topics. Please ask about memberships, classes, trainers, locations, bookings, or contact details.';

        addMessage(botReply, 'bot');
      } catch (error) {
        typingIndicator.remove();
        addMessage('Chatbot is temporarily unavailable. Please try again shortly.', 'bot');
      } finally {
        send.disabled = false;
        input.disabled = false;
        input.focus();
      }
    };

    send.addEventListener('click', sendMessage);
    input.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        sendMessage();
      }
    });
  };

  enhanceNavState();
  enhanceValidation();
  enhanceResponsiveTables();
  enhanceAdminFilters();
  animateCards();
  enhanceChatbot();
})();
