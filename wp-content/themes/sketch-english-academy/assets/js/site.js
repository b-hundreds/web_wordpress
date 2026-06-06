(function () {
  const navLinks = document.querySelectorAll('a[href^="#"], a[href*="/#"]');

  navLinks.forEach((link) => {
    link.addEventListener("click", () => {
      link.blur();
    });
  });

  const revealItems = document.querySelectorAll(".section, .card, .course-card, .quote, .lead-form, .role-panel, .stat-card, .class-card, .lesson-item, .grade-form, .sidebar-box");
  revealItems.forEach((item, index) => {
    item.classList.add("reveal-on-scroll");
    item.style.setProperty("--reveal-delay", `${Math.min(index % 4, 3) * 70}ms`);
  });

  if ("IntersectionObserver" in window) {
    const revealObserver = new IntersectionObserver(
      (entries, observer) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          entry.target.classList.add("is-visible");
          observer.unobserve(entry.target);
        });
      },
      { threshold: 0.12, rootMargin: "0px 0px -40px 0px" }
    );

    revealItems.forEach((item) => revealObserver.observe(item));
  } else {
    revealItems.forEach((item) => item.classList.add("is-visible"));
  }

  document.querySelectorAll(".button, button, input[type='submit'], .class-card, .calendar-event").forEach((control) => {
    control.addEventListener("pointerdown", (event) => {
      control.classList.add("is-clicking");
      window.setTimeout(() => control.classList.remove("is-clicking"), 180);

      const burst = document.createElement("span");
      burst.className = "sketch-click-burst";
      burst.style.left = `${event.clientX}px`;
      burst.style.top = `${event.clientY}px`;
      document.body.appendChild(burst);
      window.setTimeout(() => burst.remove(), 460);
    });
  });

  const authRoot = document.querySelector("[data-auth-root]");
  if (authRoot) {
    const authPanels = authRoot.querySelectorAll("[data-auth-panel]");
    const showAuthPanel = (view) => {
      authPanels.forEach((panel) => {
        panel.hidden = panel.dataset.authPanel !== view;
      });

      const firstField = authRoot.querySelector(`[data-auth-panel="${view}"] input:not([type="hidden"])`);
      if (firstField) {
        firstField.focus({ preventScroll: true });
      }
    };

    showAuthPanel(authRoot.dataset.initialView || "login");

    authRoot.querySelectorAll("[data-auth-switch]").forEach((button) => {
      button.addEventListener("click", () => {
        showAuthPanel(button.dataset.authSwitch || "login");
      });
    });
  }

  const googleButton = document.querySelector("[data-google-login]");
  const googleForm = document.getElementById("sea-google-login-form");
  const googleAccessInput = document.getElementById("sea_google_access_token");
  const googleCredentialInput = document.getElementById("sea_google_credential");

  window.seaHandleGoogleCredential = (response) => {
    if (!googleForm || !googleCredentialInput || !response?.credential) return;
    googleCredentialInput.value = response.credential;
    googleForm.submit();
  };

  if (googleButton && googleForm && googleAccessInput) {
    let tokenClient = null;
    const createGoogleClient = () => {
      if (tokenClient || !window.google?.accounts?.oauth2) return tokenClient;

      tokenClient = window.google.accounts.oauth2.initTokenClient({
        client_id: googleButton.dataset.googleClientId,
        scope: "openid email profile",
        prompt: "select_account",
        callback: (response) => {
          if (!response?.access_token) return;
          googleAccessInput.value = response.access_token;
          googleForm.submit();
        },
      });

      return tokenClient;
    };

    googleButton.addEventListener("click", () => {
      const client = createGoogleClient();
      if (!client) {
        googleButton.classList.add("google-login-unavailable");
        googleButton.querySelector("span:last-child").textContent = "Đang tải Google, bấm lại sau";
        return;
      }

      client.requestAccessToken();
    });
  }

  const trialButtons = document.querySelectorAll("[data-trial-class][data-trial-date]");
  const trialClassSelect = document.getElementById("sea_trial_class");
  const trialDateSelect = document.getElementById("sea_trial_date");
  const courseSelect = document.getElementById("sea_course");
  const nameInput = document.getElementById("sea_name");
  const trialOptionsNode = document.getElementById("sea-trial-options");
  let trialOptions = { by_class: {}, by_date: {}, dates: [] };

  if (trialOptionsNode) {
    try {
      trialOptions = JSON.parse(trialOptionsNode.textContent || "{}");
    } catch (error) {
      trialOptions = { by_class: {}, by_date: {}, dates: [] };
    }
  }

  const setOptionAvailability = (select, allowedValues) => {
    if (!select) return;
    Array.from(select.options).forEach((option) => {
      if (!option.value) {
        option.hidden = false;
        option.disabled = false;
        return;
      }

      const isAllowed = !allowedValues || allowedValues.includes(option.value);
      option.hidden = !isAllowed;
      option.disabled = !isAllowed;
    });

    if (select.value && select.selectedOptions[0] && select.selectedOptions[0].disabled) {
      select.value = "";
    }
  };

  const syncTrialFilters = (source) => {
    const selectedClass = trialClassSelect ? trialClassSelect.value : "";
    const selectedDate = trialDateSelect ? trialDateSelect.value : "";

    if ((source === "class" || source === "calendar") && selectedClass) {
      setOptionAvailability(trialDateSelect, trialOptions.by_class[selectedClass] || []);
      if (selectedDate && !(trialOptions.by_class[selectedClass] || []).includes(selectedDate)) {
        trialDateSelect.value = "";
      }
    } else if (!selectedClass) {
      setOptionAvailability(trialDateSelect, trialOptions.dates || null);
    }

    if (source === "date" && selectedDate) {
      setOptionAvailability(trialClassSelect, trialOptions.by_date[selectedDate] || []);
      if (selectedClass && !(trialOptions.by_date[selectedDate] || []).includes(selectedClass)) {
        trialClassSelect.value = "";
      }
    } else if (!selectedDate || source === "class" || source === "calendar") {
      setOptionAvailability(trialClassSelect, null);
    }

    const currentClass = trialClassSelect ? trialClassSelect.value : "";
    if (courseSelect && currentClass) {
      const courseTitle = trialOptions.class_course?.[currentClass] || trialClassSelect.selectedOptions?.[0]?.dataset?.courseTitle || "";
      if (courseTitle) {
        const matchingCourse = Array.from(courseSelect.options).find((option) => option.value === courseTitle || option.textContent.trim() === courseTitle);
        if (matchingCourse) {
          courseSelect.value = matchingCourse.value;
        }
      }
    }

    trialButtons.forEach((button) => {
      const matchesClass = !trialClassSelect || !trialClassSelect.value || button.dataset.trialClass === trialClassSelect.value;
      const matchesDate = !trialDateSelect || !trialDateSelect.value || button.dataset.trialDate === trialDateSelect.value;
      button.classList.toggle("filtered-out", !(matchesClass && matchesDate));
      button.classList.toggle("selected", !!trialClassSelect?.value && !!trialDateSelect?.value && button.dataset.trialClass === trialClassSelect.value && button.dataset.trialDate === trialDateSelect.value);
    });
  };

  trialButtons.forEach((button) => {
    button.addEventListener("click", () => {
      trialButtons.forEach((item) => item.classList.remove("selected"));
      button.classList.add("selected");

      if (trialClassSelect) {
        trialClassSelect.value = button.dataset.trialClass || "";
      }

      if (trialDateSelect) {
        trialDateSelect.value = button.dataset.trialDate || "";
      }

      syncTrialFilters("calendar");

      if (nameInput) {
        nameInput.scrollIntoView({ behavior: "smooth", block: "center" });
        nameInput.focus({ preventScroll: true });
      }
    });
  });

  if (trialClassSelect) {
    trialClassSelect.addEventListener("change", () => syncTrialFilters("class"));
  }

  if (trialDateSelect) {
    trialDateSelect.addEventListener("change", () => syncTrialFilters("date"));
  }

  syncTrialFilters();
})();
