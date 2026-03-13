const searchForm = document.getElementById("job-search-form");
const filterForm = document.querySelector("[data-search-filter-form]");
const menuToggle = document.getElementById("menu-toggle");
const mobileMenu = document.getElementById("mobile-menu");
const normalizeSearchInputValue = (value) => value.trim();
const savedJobsStorageKey = "vopen.savedJobs";
const sanitizeSavedJobString = (value) => (typeof value === "string" ? value.trim() : "");
const normalizeSavedJobUrl = (value) => {
  const normalizedUrl = sanitizeSavedJobString(value);

  if (normalizedUrl === "") {
    return "";
  }

  try {
    const resolvedUrl = new URL(normalizedUrl, window.location.origin);

    if (!["http:", "https:"].includes(resolvedUrl.protocol)) {
      return "";
    }

    return normalizedUrl;
  } catch (error) {
    return "";
  }
};

const uniqueSavedJobValues = (values) =>
  Array.from(
    new Set(values.map((value) => sanitizeSavedJobString(value)).filter((value) => value !== ""))
  );

const buildSavedJobKey = (job) => {
  const numericId = Number.parseInt(String(job?.id ?? ""), 10);

  if (Number.isFinite(numericId) && numericId > 0) {
    return `job-${numericId}`;
  }

  const jobUrl = normalizeSavedJobUrl(job?.url);

  if (jobUrl !== "") {
    return jobUrl;
  }

  const jobTitle = sanitizeSavedJobString(job?.title);

  return jobTitle !== "" ? jobTitle.toLocaleLowerCase() : "";
};

const normalizeSavedJob = (job) => {
  if (!job || typeof job !== "object") {
    return null;
  }

  const key = buildSavedJobKey(job);
  const title = sanitizeSavedJobString(job.title);

  if (key === "" || title === "") {
    return null;
  }

  const numericId = Number.parseInt(String(job.id ?? ""), 10);
  const savedAt = Number.parseInt(String(job.savedAt ?? ""), 10);

  return {
    key,
    id: Number.isFinite(numericId) && numericId > 0 ? numericId : null,
    title,
    location: sanitizeSavedJobString(job.location),
    categories: uniqueSavedJobValues(Array.isArray(job.categories) ? job.categories : []),
    postedLabel: sanitizeSavedJobString(job.postedLabel),
    summary: sanitizeSavedJobString(job.summary),
    url: normalizeSavedJobUrl(job.url),
    savedAt: Number.isFinite(savedAt) && savedAt > 0 ? savedAt : Date.now(),
  };
};

const readSavedJobs = () => {
  try {
    const storedValue = window.localStorage.getItem(savedJobsStorageKey);

    if (!storedValue) {
      return [];
    }

    const parsedValue = JSON.parse(storedValue);

    if (!Array.isArray(parsedValue)) {
      return [];
    }

    const jobs = parsedValue.map((item) => normalizeSavedJob(item)).filter(Boolean);
    const seenKeys = new Set();

    return jobs.filter((job) => {
      if (seenKeys.has(job.key)) {
        return false;
      }

      seenKeys.add(job.key);
      return true;
    });
  } catch (error) {
    return [];
  }
};

const writeSavedJobs = (jobs) => {
  try {
    window.localStorage.setItem(savedJobsStorageKey, JSON.stringify(jobs));
  } catch (error) {
    // Ignore storage errors and keep the page usable.
  }
};

const findSavedJobIndex = (savedJobs, key) => savedJobs.findIndex((job) => job.key === key);

const toggleSavedJob = (job) => {
  const normalizedJob = normalizeSavedJob(job);

  if (!normalizedJob) {
    return {
      savedJobs: readSavedJobs(),
      isSaved: false,
    };
  }

  const savedJobs = readSavedJobs();
  const existingIndex = findSavedJobIndex(savedJobs, normalizedJob.key);

  if (existingIndex >= 0) {
    savedJobs.splice(existingIndex, 1);
    writeSavedJobs(savedJobs);

    return {
      savedJobs,
      isSaved: false,
    };
  }

  savedJobs.unshift({
    ...normalizedJob,
    savedAt: Date.now(),
  });
  writeSavedJobs(savedJobs);

  return {
    savedJobs,
    isSaved: true,
  };
};

const removeSavedJobByKey = (key) => {
  if (sanitizeSavedJobString(key) === "") {
    return readSavedJobs();
  }

  const nextJobs = readSavedJobs().filter((job) => job.key !== key);
  writeSavedJobs(nextJobs);

  return nextJobs;
};

const clearSavedJobs = () => {
  writeSavedJobs([]);
  return [];
};

const buildAbsoluteSavedJobUrl = (jobUrl) => {
  const normalizedUrl = sanitizeSavedJobString(jobUrl);

  if (normalizedUrl === "") {
    return window.location.href;
  }

  try {
    return new URL(normalizedUrl, window.location.origin).toString();
  } catch (error) {
    return normalizedUrl;
  }
};

const buildApplyHref = (job) => {
  const normalizedJob = normalizeSavedJob(job);

  if (!normalizedJob) {
    return "mailto:";
  }

  const subject = `Application for ${normalizedJob.title} | VOpen Market`;
  const bodyLines = [
    "Hello,",
    "",
    `I would like to apply for the ${normalizedJob.title} role at VOpen Market.`,
  ];

  if (normalizedJob.location !== "") {
    bodyLines.push(`Location: ${normalizedJob.location}`);
  }

  bodyLines.push(`Job link: ${buildAbsoluteSavedJobUrl(normalizedJob.url)}`);
  bodyLines.push("");
  bodyLines.push("Thank you.");

  return `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(bodyLines.join("\n"))}`;
};

const formatSavedDate = (timestamp) => {
  if (!Number.isFinite(timestamp) || timestamp <= 0) {
    return "Saved recently";
  }

  try {
    return `Saved ${new Intl.DateTimeFormat(undefined, {
      month: "short",
      day: "numeric",
      year: "numeric",
    }).format(new Date(timestamp))}`;
  } catch (error) {
    return "Saved recently";
  }
};

const parseSavedJobData = (element) => {
  const serializedJob = sanitizeSavedJobString(element?.dataset?.job);

  if (serializedJob === "") {
    return null;
  }

  try {
    return normalizeSavedJob(JSON.parse(serializedJob));
  } catch (error) {
    return null;
  }
};

const updateSavedJobsUi = () => {
  const savedJobs = readSavedJobs();
  const savedJobsCount = savedJobs.length;

  document.querySelectorAll("[data-saved-jobs-count]").forEach((countNode) => {
    countNode.textContent = String(savedJobsCount);
  });

  document.querySelectorAll("[data-saved-jobs-link]").forEach((link) => {
    link.classList.toggle("has-saved-jobs", savedJobsCount > 0);
    link.setAttribute("aria-label", `Saved Jobs (${savedJobsCount})`);
  });

  document.querySelectorAll("[data-jobcart-total]").forEach((countNode) => {
    countNode.textContent = String(savedJobsCount);
  });

  document.querySelectorAll("[data-clear-saved-jobs]").forEach((button) => {
    button.hidden = savedJobsCount === 0;
  });
};

const syncSaveJobButtons = () => {
  const savedJobs = readSavedJobs();

  document.querySelectorAll("[data-save-job]").forEach((button) => {
    const job = parseSavedJobData(button);

    if (!job) {
      return;
    }

    const isSaved = findSavedJobIndex(savedJobs, job.key) >= 0;
    const buttonLabel = button.dataset.saveJobLabel || "Save Job";
    const savedLabel = button.dataset.savedLabel || "Saved";
    const labelTarget = button.querySelector("[data-save-job-text]");

    button.classList.toggle("is-saved", isSaved);
    button.setAttribute("aria-pressed", String(isSaved));
    button.dataset.jobKey = job.key;

    if (labelTarget) {
      labelTarget.textContent = isSaved ? savedLabel : buttonLabel;
    }
  });

  document.querySelectorAll("[data-apply-job]").forEach((link) => {
    if (!(link instanceof HTMLAnchorElement)) {
      return;
    }

    const job = parseSavedJobData(link);

    if (!job) {
      return;
    }

    link.href = buildApplyHref(job);
  });
};

const createJobcartCard = (job) => {
  const card = document.createElement("article");
  const main = document.createElement("div");
  const title = document.createElement("h3");
  const meta = document.createElement("div");
  const summary = document.createElement("p");
  const footer = document.createElement("div");
  const savedNote = document.createElement("span");
  const actions = document.createElement("div");
  const applyLink = document.createElement("a");
  const removeButton = document.createElement("button");
  const normalizedUrl = sanitizeSavedJobString(job.url);

  card.className = "jobcart-card";
  main.className = "jobcart-card-main";
  title.className = "jet-heading jobcart-card-title";
  meta.className = "jobcart-card-meta";
  summary.className = "jobcart-card-copy";
  footer.className = "jobcart-card-footer";
  savedNote.className = "jobcart-saved-note";
  actions.className = "jobcart-card-actions";
  applyLink.className = "jobcart-apply-link";
  applyLink.href = buildApplyHref(job);
  applyLink.textContent = "Apply";
  removeButton.type = "button";
  removeButton.className = "jobcart-remove-button";
  removeButton.dataset.removeSavedJob = job.key;
  removeButton.textContent = "Remove";
  savedNote.textContent = formatSavedDate(job.savedAt);

  if (normalizedUrl !== "") {
    const titleLink = document.createElement("a");

    titleLink.href = normalizedUrl;
    titleLink.className = "jobcart-card-link";
    titleLink.textContent = job.title;
    title.appendChild(titleLink);
  } else {
    title.textContent = job.title;
  }

  if (job.location !== "") {
    const locationPill = document.createElement("span");

    locationPill.className = "jobcart-pill is-location";
    locationPill.textContent = job.location;
    meta.appendChild(locationPill);
  }

  job.categories.forEach((category) => {
    const categoryPill = document.createElement("span");

    categoryPill.className = "jobcart-pill";
    categoryPill.textContent = category;
    meta.appendChild(categoryPill);
  });

  if (job.postedLabel !== "") {
    const postedPill = document.createElement("span");

    postedPill.className = "jobcart-pill";
    postedPill.textContent = job.postedLabel;
    meta.appendChild(postedPill);
  }

  summary.textContent = job.summary || "Return to the job detail page to review the full description and responsibilities.";

  if (normalizedUrl !== "") {
    const viewLink = document.createElement("a");

    viewLink.href = normalizedUrl;
    viewLink.className = "jobcart-view-link";
    viewLink.textContent = "View job";
    actions.appendChild(viewLink);
  }

  actions.prepend(applyLink);
  actions.appendChild(removeButton);
  main.append(title, meta, summary);
  footer.append(savedNote, actions);
  card.append(main, footer);

  return card;
};

const renderJobCart = () => {
  const cartList = document.querySelector("[data-jobcart-list]");
  const emptyState = document.querySelector("[data-jobcart-empty]");

  if (!(cartList instanceof HTMLElement) || !(emptyState instanceof HTMLElement)) {
    return;
  }

  const savedJobs = readSavedJobs().sort((leftJob, rightJob) => rightJob.savedAt - leftJob.savedAt);

  cartList.replaceChildren();

  if (savedJobs.length === 0) {
    emptyState.hidden = false;
    cartList.hidden = true;
    return;
  }

  emptyState.hidden = true;
  cartList.hidden = false;

  const fragment = document.createDocumentFragment();

  savedJobs.forEach((job) => {
    fragment.appendChild(createJobcartCard(job));
  });

  cartList.appendChild(fragment);
};

const refreshSavedJobsExperience = () => {
  updateSavedJobsUi();
  syncSaveJobButtons();
  renderJobCart();
};

document.addEventListener("click", (event) => {
  if (!(event.target instanceof Element)) {
    return;
  }

  const saveJobButton = event.target.closest("[data-save-job]");

  if (saveJobButton instanceof HTMLButtonElement) {
    const job = parseSavedJobData(saveJobButton);

    if (!job) {
      return;
    }

    event.preventDefault();
    toggleSavedJob(job);
    refreshSavedJobsExperience();
    return;
  }

  const removeButton = event.target.closest("[data-remove-saved-job]");

  if (removeButton instanceof HTMLButtonElement) {
    const jobKey = sanitizeSavedJobString(removeButton.dataset.removeSavedJob);

    if (jobKey === "") {
      return;
    }

    removeSavedJobByKey(jobKey);
    refreshSavedJobsExperience();
    return;
  }

  const clearButton = event.target.closest("[data-clear-saved-jobs]");

  if (clearButton instanceof HTMLButtonElement) {
    clearSavedJobs();
    refreshSavedJobsExperience();
  }
});

window.addEventListener("storage", (event) => {
  if (event.key !== null && event.key !== savedJobsStorageKey) {
    return;
  }

  refreshSavedJobsExperience();
});

refreshSavedJobsExperience();

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
