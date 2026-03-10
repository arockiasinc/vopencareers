const searchForm = document.getElementById("job-search-form");
const searchInput = document.getElementById("job-search");
const menuToggle = document.getElementById("menu-toggle");
const mobileMenu = document.getElementById("mobile-menu");

if (searchForm && searchInput) {
  searchForm.addEventListener("submit", (event) => {
    event.preventDefault();
    const action = searchForm.getAttribute("action") || "https://careers.justeattakeaway.com/global/en/search-results";
    const url = new URL(action, window.location.href);
    const query = searchInput.value.trim();

    if (query) {
      url.searchParams.set("keywords", query);
    } else {
      url.searchParams.delete("keywords");
    }

    window.location.href = url.toString();
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
