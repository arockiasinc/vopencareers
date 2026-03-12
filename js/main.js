const searchForm = document.getElementById("job-search-form");
const filterForm = document.querySelector("[data-search-filter-form]");
const menuToggle = document.getElementById("menu-toggle");
const mobileMenu = document.getElementById("mobile-menu");
const normalizeSearchInputValue = (value) => value.trim();

if (searchForm) {
  searchForm.addEventListener("submit", () => {
    const searchInput = searchForm.querySelector('input[name="keywords"]');

    if (!(searchInput instanceof HTMLInputElement)) {
      return;
    }

    searchInput.value = normalizeSearchInputValue(searchInput.value);
  });
}

document.querySelectorAll("[data-search-autocomplete]").forEach((container, containerIndex) => {
  const form = container.closest("form");
  const input = container.querySelector("[data-search-autocomplete-input]");
  const list = container.querySelector("[data-search-autocomplete-list]");
  const source = container.querySelector("[data-search-autocomplete-source]");
  const status = container.querySelector("[data-search-autocomplete-status]");

  if (!(form instanceof HTMLFormElement) || !(input instanceof HTMLInputElement) || !(list instanceof HTMLElement) || !(source instanceof HTMLScriptElement)) {
    return;
  }

  let suggestions = [];

  try {
    const parsed = JSON.parse(source.textContent || "[]");

    if (Array.isArray(parsed)) {
      suggestions = parsed.filter((item) => typeof item === "string" && normalizeSearchInputValue(item) !== "");
    }
  } catch (error) {
    suggestions = [];
  }

  if (suggestions.length === 0) {
    return;
  }

  const listId = list.id || `job-search-suggestions-${containerIndex + 1}`;
  let matches = [];
  let activeIndex = -1;

  list.id = listId;
  input.setAttribute("aria-controls", listId);

  const closeSuggestions = () => {
    matches = [];
    activeIndex = -1;
    list.replaceChildren();
    list.hidden = true;
    input.setAttribute("aria-expanded", "false");
    input.removeAttribute("aria-activedescendant");

    if (status instanceof HTMLElement) {
      status.textContent = "";
    }
  };

  const updateActiveOption = () => {
    const options = Array.from(list.querySelectorAll(".search-autocomplete-option"));

    options.forEach((option, index) => {
      const isActive = index === activeIndex;

      option.classList.toggle("is-active", isActive);
      option.setAttribute("aria-selected", String(isActive));

      if (isActive) {
        input.setAttribute("aria-activedescendant", option.id);
        option.scrollIntoView({ block: "nearest" });
      }
    });

    if (activeIndex < 0) {
      input.removeAttribute("aria-activedescendant");
    }
  };

  const submitSuggestion = (value) => {
    input.value = value;
    closeSuggestions();

    if (typeof form.requestSubmit === "function") {
      form.requestSubmit();
      return;
    }

    form.submit();
  };

  const buildMatches = (query) => {
    const normalizedQuery = query.toLocaleLowerCase();

    return suggestions
      .map((value) => {
        const normalizedValue = value.toLocaleLowerCase();
        const matchIndex = normalizedValue.indexOf(normalizedQuery);

        if (matchIndex === -1) {
          return null;
        }

        return {
          value,
          matchIndex,
          startsWithMatch: matchIndex === 0,
        };
      })
      .filter(Boolean)
      .sort((left, right) => {
        if (left.startsWithMatch !== right.startsWithMatch) {
          return left.startsWithMatch ? -1 : 1;
        }

        if (left.matchIndex !== right.matchIndex) {
          return left.matchIndex - right.matchIndex;
        }

        if (left.value.length !== right.value.length) {
          return left.value.length - right.value.length;
        }

        return left.value.localeCompare(right.value);
      })
      .slice(0, 8)
      .map((item) => item.value);
  };

  const renderSuggestions = () => {
    const query = normalizeSearchInputValue(input.value);

    if (query === "") {
      closeSuggestions();
      return;
    }

    matches = buildMatches(query);
    activeIndex = -1;
    list.replaceChildren();

    if (matches.length === 0) {
      const emptyState = document.createElement("div");
      emptyState.className = "search-autocomplete-empty";
      emptyState.textContent = "No matching jobs found.";
      list.appendChild(emptyState);
      list.hidden = false;
      input.setAttribute("aria-expanded", "true");
      input.removeAttribute("aria-activedescendant");

      if (status instanceof HTMLElement) {
        status.textContent = emptyState.textContent;
      }

      return;
    }

    const fragment = document.createDocumentFragment();

    matches.forEach((value, index) => {
      const option = document.createElement("button");

      option.type = "button";
      option.id = `${listId}-option-${index + 1}`;
      option.className = "search-autocomplete-option";
      option.setAttribute("role", "option");
      option.setAttribute("aria-selected", "false");
      option.textContent = value;
      option.addEventListener("mousedown", (event) => {
        event.preventDefault();
      });
      option.addEventListener("click", () => {
        submitSuggestion(value);
      });
      fragment.appendChild(option);
    });

    list.appendChild(fragment);
    list.hidden = false;
    input.setAttribute("aria-expanded", "true");

    if (status instanceof HTMLElement) {
      status.textContent = `${matches.length} suggestion${matches.length === 1 ? "" : "s"} available.`;
    }
  };

  input.addEventListener("input", () => {
    renderSuggestions();
  });

  input.addEventListener("focus", () => {
    if (normalizeSearchInputValue(input.value) !== "") {
      renderSuggestions();
    }
  });

  input.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      closeSuggestions();
      return;
    }

    if (list.hidden || matches.length === 0) {
      return;
    }

    if (event.key === "ArrowDown") {
      event.preventDefault();
      activeIndex = activeIndex >= matches.length - 1 ? 0 : activeIndex + 1;
      updateActiveOption();
      return;
    }

    if (event.key === "ArrowUp") {
      event.preventDefault();
      activeIndex = activeIndex <= 0 ? matches.length - 1 : activeIndex - 1;
      updateActiveOption();
      return;
    }

    if (event.key === "Enter" && activeIndex >= 0) {
      event.preventDefault();
      submitSuggestion(matches[activeIndex]);
    }
  });

  input.addEventListener("blur", () => {
    window.setTimeout(() => {
      if (!container.contains(document.activeElement)) {
        closeSuggestions();
      }
    }, 120);
  });

  document.addEventListener("pointerdown", (event) => {
    if (!(event.target instanceof Node) || !container.contains(event.target)) {
      closeSuggestions();
    }
  });
});

if (filterForm) {
  filterForm.querySelectorAll('input[type="checkbox"]').forEach((input) => {
    input.addEventListener("change", () => {
      if (typeof filterForm.requestSubmit === "function") {
        filterForm.requestSubmit();
        return;
      }

      filterForm.submit();
    });
  });
}

if (menuToggle && mobileMenu) {
  const closeMenu = () => {
    menuToggle.setAttribute("aria-expanded", "false");
    mobileMenu.classList.add("hidden");
    document.body.classList.remove("overflow-hidden");
  };

  menuToggle.addEventListener("click", () => {
    const isOpen = menuToggle.getAttribute("aria-expanded") === "true";
    menuToggle.setAttribute("aria-expanded", String(!isOpen));
    mobileMenu.classList.toggle("hidden", isOpen);
    document.body.classList.toggle("overflow-hidden", !isOpen);
  });

  mobileMenu.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", closeMenu);
  });

  window.addEventListener("resize", () => {
    if (window.innerWidth >= 1024) {
      closeMenu();
    }
  });
}

const carouselState = new Map();

const getSlideIndex = (track) => {
  const slides = Array.from(track.children);
  const center = track.scrollLeft + track.clientWidth / 2;
  let nearestIndex = 0;
  let nearestDistance = Number.POSITIVE_INFINITY;

  slides.forEach((slide, index) => {
    const slideCenter = slide.offsetLeft + slide.clientWidth / 2;
    const distance = Math.abs(center - slideCenter);

    if (distance < nearestDistance) {
      nearestDistance = distance;
      nearestIndex = index;
    }
  });

  return nearestIndex;
};

const syncCarouselStatus = (name) => {
  const state = carouselState.get(name);

  if (!state) {
    return;
  }

  state.index = getSlideIndex(state.track);
  document.querySelectorAll(`[data-carousel-status="${name}"]`).forEach((label) => {
    label.textContent = `${state.index + 1} / ${state.slides.length}`;
  });

  const isAtStart = state.index <= 0;
  const isAtEnd = state.index >= state.slides.length - 1;

  document.querySelectorAll(`[data-carousel-prev="${name}"]`).forEach((button) => {
    button.disabled = isAtStart;
    button.setAttribute("aria-disabled", String(isAtStart));
  });

  document.querySelectorAll(`[data-carousel-next="${name}"]`).forEach((button) => {
    button.disabled = isAtEnd;
    button.setAttribute("aria-disabled", String(isAtEnd));
  });
};

document.querySelectorAll("[data-carousel]").forEach((track) => {
  const name = track.dataset.carousel;
  const slides = Array.from(track.children);

  carouselState.set(name, {
    track,
    slides,
    index: 0,
  });

  track.addEventListener(
    "scroll",
    () => {
      window.requestAnimationFrame(() => syncCarouselStatus(name));
    },
    { passive: true }
  );

  syncCarouselStatus(name);
});

const moveCarousel = (name, direction) => {
  const state = carouselState.get(name);

  if (!state) {
    return;
  }

  const nextIndex = Math.max(0, Math.min(state.slides.length - 1, state.index + direction));
  const nextSlide = state.slides[nextIndex];

  if (!nextSlide) {
    return;
  }

  state.track.scrollTo({
    left: nextSlide.offsetLeft,
    behavior: "smooth",
  });
};

document.querySelectorAll("[data-carousel-prev]").forEach((button) => {
  button.addEventListener("click", () => {
    moveCarousel(button.dataset.carouselPrev, -1);
  });
});

document.querySelectorAll("[data-carousel-next]").forEach((button) => {
  button.addEventListener("click", () => {
    moveCarousel(button.dataset.carouselNext, 1);
  });
});

window.addEventListener("resize", () => {
  carouselState.forEach((_, name) => syncCarouselStatus(name));
});

document.querySelectorAll("[data-marquee-track]").forEach((track) => {
  const group = track.querySelector(".countries-marquee-group");

  if (!group) {
    return;
  }

  if (!track.querySelector("[data-marquee-clone]")) {
    const clone = group.cloneNode(true);
    clone.dataset.marqueeClone = "true";
    clone.setAttribute("aria-hidden", "true");
    track.appendChild(clone);
  }

  track.classList.add("is-ready");
});

document.querySelectorAll("[data-phrase-rotator]").forEach((rotator) => {
  const text = rotator.querySelector("[data-phrase-rotator-text]");
  const phrases = Array.from(rotator.querySelectorAll("[data-phrase-rotator-source] li"), (item) => item.textContent?.trim()).filter(Boolean);

  if (!text || phrases.length === 0) {
    return;
  }

  const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  let index = 0;

  text.textContent = phrases[index];

  window.setInterval(() => {
    index = (index + 1) % phrases.length;

    if (prefersReducedMotion) {
      text.textContent = phrases[index];
      return;
    }

    rotator.classList.add("is-switching");

    window.setTimeout(() => {
      text.textContent = phrases[index];
      rotator.classList.remove("is-switching");
    }, 220);
  }, 2600);
});

document.querySelectorAll("[data-filter-toggle]").forEach((button) => {
  const panelId = button.getAttribute("aria-controls");
  const panel = panelId ? document.getElementById(panelId) : null;
  const section = button.closest("[data-filter-section]");

  if (!panel) {
    return;
  }

  button.addEventListener("click", () => {
    const isExpanded = button.getAttribute("aria-expanded") === "true";

    button.setAttribute("aria-expanded", String(!isExpanded));
    panel.hidden = isExpanded;
    section?.classList.toggle("is-open", !isExpanded);
  });
});
