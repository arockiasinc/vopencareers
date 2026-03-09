const searchForm = document.getElementById("job-search-form");
const searchInput = document.getElementById("job-search");
const menuToggle = document.getElementById("menu-toggle");
const mobileMenu = document.getElementById("mobile-menu");
const dropdowns = Array.from(document.querySelectorAll("[data-dropdown]"));

if (searchForm && searchInput) {
  searchForm.addEventListener("submit", (event) => {
    event.preventDefault();
    const url = new URL("https://careers.justeattakeaway.com/global/en/search-results");
    const query = searchInput.value.trim();

    if (query) {
      url.searchParams.set("keywords", query);
    }

    window.location.href = url.toString();
  });
}

if (menuToggle && mobileMenu) {
  const closeMenu = () => {
    menuToggle.setAttribute("aria-expanded", "false");
    mobileMenu.classList.add("hidden");
    document.body.classList.remove("overflow-hidden");
    mobileMenu.querySelectorAll("details[open]").forEach((detail) => {
      detail.open = false;
    });
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

if (dropdowns.length) {
  const isDesktopDropdownMode = () => window.innerWidth >= 1024;
  const dropdownCloseTimers = new WeakMap();

  const clearCloseTimer = (dropdown) => {
    const timerId = dropdownCloseTimers.get(dropdown);

    if (timerId) {
      window.clearTimeout(timerId);
      dropdownCloseTimers.delete(dropdown);
    }
  };

  const scheduleClose = (dropdown) => {
    clearCloseTimer(dropdown);
    const timerId = window.setTimeout(() => {
      closeDropdown(dropdown);
      dropdownCloseTimers.delete(dropdown);
    }, 140);
    dropdownCloseTimers.set(dropdown, timerId);
  };

  const openDropdown = (dropdown) => {
    const trigger = dropdown.querySelector("[data-dropdown-trigger]");
    const menu = dropdown.querySelector("[data-dropdown-menu]");

    if (!trigger || !menu) {
      return;
    }

    clearCloseTimer(dropdown);
    closeAllDropdowns(dropdown);
    dropdown.classList.add("is-open");
    trigger.setAttribute("aria-expanded", "true");
    menu.classList.remove("hidden");
  };

  const closeDropdown = (dropdown) => {
    const trigger = dropdown.querySelector("[data-dropdown-trigger]");
    const menu = dropdown.querySelector("[data-dropdown-menu]");

    if (!trigger || !menu) {
      return;
    }

    clearCloseTimer(dropdown);
    dropdown.classList.remove("is-open");
    trigger.setAttribute("aria-expanded", "false");
    menu.classList.add("hidden");
  };

  const closeAllDropdowns = (activeDropdown) => {
    dropdowns.forEach((dropdown) => {
      if (dropdown !== activeDropdown) {
        closeDropdown(dropdown);
      }
    });
  };

  dropdowns.forEach((dropdown) => {
    const trigger = dropdown.querySelector("[data-dropdown-trigger]");
    const menu = dropdown.querySelector("[data-dropdown-menu]");

    if (!trigger || !menu) {
      return;
    }

    trigger.addEventListener("click", () => {
      const isOpen = trigger.getAttribute("aria-expanded") === "true";

      if (isOpen) {
        closeDropdown(dropdown);
        return;
      }

      openDropdown(dropdown);
    });

    menu.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", () => {
        closeDropdown(dropdown);
      });
    });

    dropdown.addEventListener("mouseenter", () => {
      if (isDesktopDropdownMode()) {
        openDropdown(dropdown);
      }
    });

    dropdown.addEventListener("mouseleave", () => {
      if (isDesktopDropdownMode()) {
        scheduleClose(dropdown);
      }
    });

    trigger.addEventListener("focus", () => {
      openDropdown(dropdown);
    });

    dropdown.addEventListener("focusout", (event) => {
      const nextFocusedElement = event.relatedTarget;

      if (!(nextFocusedElement instanceof Element) || !dropdown.contains(nextFocusedElement)) {
        closeDropdown(dropdown);
      }
    });
  });

  document.addEventListener("click", (event) => {
    const target = event.target;

    if (!(target instanceof Element)) {
      return;
    }

    if (!target.closest("[data-dropdown]")) {
      closeAllDropdowns();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      closeAllDropdowns();
    }
  });

  window.addEventListener("resize", () => {
    closeAllDropdowns();
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
