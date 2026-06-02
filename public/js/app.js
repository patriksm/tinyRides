window.addEventListener("DOMContentLoaded", function () {
  // =================================================
  // PROFILE TABS (users-menu)
  // =================================================
  function initLanguageDropdown() {
    const langToggle = document.getElementById("langToggle");
    const langDropdown = document.getElementById("langDropdown");

    if (!langToggle || !langDropdown) return;

    langToggle.addEventListener("click", function (e) {
      e.stopPropagation();
      langDropdown.classList.toggle("hidden");
    });

    document.addEventListener("click", function (e) {
      if (!langDropdown.contains(e.target) && !langToggle.contains(e.target)) {
        langDropdown.classList.add("hidden");
      }
    });
  }
  initLanguageDropdown();

  (function () {
    const usersMenu = document.querySelector(".users-menu");
    if (!usersMenu) return;

    const tabs = usersMenu.querySelectorAll("[data-tab]");
    const panels = usersMenu.querySelectorAll("[data-panel]");
    if (!tabs.length || !panels.length) return;

    function setActive(tabName) {
      tabs.forEach((t) => {
        const isActive = t.dataset.tab === tabName;
        t.classList.toggle("bg-[var(--main-bg)]", isActive);
      });

      panels.forEach((p) => {
        const isActive = p.dataset.panel === tabName;
        p.classList.toggle("hidden", !isActive);
      });
    }

    const initial =
      Array.from(tabs).find(
        (t) => t.classList.contains("bg-[var(--main-bg)]") && t.dataset.tab,
      )?.dataset.tab || tabs[0].dataset.tab;

    setActive(initial);

    usersMenu.addEventListener("click", (e) => {
      const tabEl = e.target.closest("[data-tab]");
      if (!tabEl || !usersMenu.contains(tabEl)) return;

      const activeTab = tabEl.dataset.tab;
      if (!activeTab) return;

      setActive(activeTab);
    });
  })();

  // =================================================
  // PROFILE LISTINGS + RENTALS: ellipsis menu + delete modal + status change
  // =================================================
  (function () {
    const root =
      document.querySelector(".users-menu") ||
      document.querySelector(".profile-section") ||
      document;

    const modal = document.getElementById("delete-modal");
    const cancelBtn = document.getElementById("delete-cancel");
    const deleteForm = document.getElementById("delete-form");

    let openedMenu = null;

    function normalizeStatus(s, fallback = "active") {
      const v = String(s ?? fallback)
        .trim()
        .toLowerCase();
      if (v === "rent") return "rented";
      return v || fallback;
    }

    function getI18n() {
      return (
        window.PROFILE_I18N || {
          statusActive: "Active",
          statusInactive: "Inactive",
          statusRented: "Rented",
          setInactive: "Set as inactive",
          setRented: "Set as rented",
        }
      );
    }

    function getStatusLabel(status) {
      const i18n = getI18n();
      const map = {
        active: i18n.statusActive,
        inactive: i18n.statusInactive,
        rented: i18n.statusRented,
      };
      return map[status] || status;
    }

    function closeOpenedMenu() {
      if (!openedMenu) return;

      const post = openedMenu.closest(".posts");
      post?.classList.remove("menu-open");

      const sub = openedMenu.querySelector("[data-status-submenu]");
      if (sub) sub.classList.add("hidden");

      openedMenu.classList.add("hidden");

      const btn = openedMenu
        .closest(".relative")
        ?.querySelector(".post-menu-btn");
      if (btn) btn.setAttribute("aria-expanded", "false");

      openedMenu = null;
    }

    function renderStatusSubmenu(menu) {
      const post = menu.closest(".posts");
      const sub = menu.querySelector("[data-status-submenu]");
      if (!post || !sub) return;

      const current = normalizeStatus(
        post.getAttribute("data-status") || "active",
      );

      const i18n = getI18n();
      const all = ["active", "inactive", "rented"];
      const opts = all.filter((s) => s !== current);

      sub.innerHTML = opts
        .map((s) => {
          let text = getStatusLabel(s);

          if (s === "inactive") text = i18n.setInactive;
          if (s === "rented") text = i18n.setRented;

          return `
          <button type="button"
            class="status-option w-full text-left px-4 py-2 hover:bg-slate-50 text-sm max-sm:text-[12px]"
            data-next-status="${s}">
            ${text}
          </button>
        `;
        })
        .join("");
    }

    function openMenu(menu) {
      if (openedMenu && openedMenu !== menu) closeOpenedMenu();

      menu.classList.remove("hidden");

      const btn = menu.closest(".relative")?.querySelector(".post-menu-btn");
      if (btn) btn.setAttribute("aria-expanded", "true");

      openedMenu = menu;

      const post = menu.closest(".posts");
      post?.classList.add("menu-open");

      renderStatusSubmenu(menu);

      const sub = menu.querySelector("[data-status-submenu]");
      if (sub) sub.classList.add("hidden");
    }

    function getMenuFromAnyChild(el) {
      const menu =
        el?.closest(".post-menu") ||
        el?.closest(".relative")?.querySelector(".post-menu");
      return menu || null;
    }

    document.addEventListener("click", (e) => {
      if (
        openedMenu &&
        !openedMenu.contains(e.target) &&
        !e.target.closest(".post-menu-btn")
      ) {
        closeOpenedMenu();
      }
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        closeOpenedMenu();
        if (modal && !modal.classList.contains("hidden")) {
          modal.classList.add("hidden");
        }
      }
    });

    root.addEventListener("click", async (e) => {
      const menuBtn = e.target.closest(".post-menu-btn");
      const itemBtn = e.target.closest(".menu-item");
      const statusOpt = e.target.closest(".status-option");
      const statusTrigger = e.target.closest("[data-status-trigger]");

      if (menuBtn) {
        const wrapper = menuBtn.closest(".relative");
        const menu = wrapper?.querySelector(".post-menu");
        if (!menu) return;

        const isOpen = !menu.classList.contains("hidden");
        if (isOpen) closeOpenedMenu();
        else openMenu(menu);
        return;
      }

      if (statusTrigger) {
        const menu = getMenuFromAnyChild(statusTrigger);
        if (!menu || menu.classList.contains("hidden")) return;

        const sub = menu.querySelector("[data-status-submenu]");
        if (!sub) return;

        renderStatusSubmenu(menu);
        sub.classList.toggle("hidden");

        e.stopPropagation();
        return;
      }

      if (statusOpt) {
        const post = statusOpt.closest(".posts");
        const carId = post?.getAttribute("data-car-id");
        if (!post || !carId) return;

        const nextStatus = normalizeStatus(
          statusOpt.getAttribute("data-next-status") || "",
        );
        if (!nextStatus) return;

        const base = window.BASE_URL || "";
        const token = window.CSRF_TOKEN || "";
        if (!base || !token) {
          alert("BASE_URL yoki CSRF_TOKEN topilmadi");
          return;
        }

        try {
          const resp = await fetch(`${base}/car/changeStatus/${carId}`, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `_token=${encodeURIComponent(token)}&status=${encodeURIComponent(nextStatus)}`,
          });

          const data = await resp.json().catch(() => null);
          if (!resp.ok || !data || !data.status) {
            alert("Status change failed");
            return;
          }

          const newStatus = normalizeStatus(data.status);

          const badge = post.querySelector(".status-badge");
          if (badge) {
            badge.textContent = getStatusLabel(newStatus);

            badge.classList.remove(
              "bg-[var(--btn-bg)]",
              "bg-gray-500",
              "bg-red-600",
              "bg-orange-500",
            );

            if (newStatus === "active") {
              badge.classList.add("bg-[var(--btn-bg)]");
            } else if (newStatus === "inactive") {
              badge.classList.add("bg-red-600");
            } else if (newStatus === "rented") {
              badge.classList.add("bg-orange-500");
            } else {
              badge.classList.add("bg-gray-500");
            }
          }

          post.classList.remove("bg-white", "bg-slate-50");
          if (newStatus === "inactive") post.classList.add("bg-slate-50");
          else post.classList.add("bg-white");

          post.classList.toggle("rented-post", newStatus === "rented");
          post.setAttribute("data-status", newStatus);

          const rentalsList = document.getElementById("rentalsList");
          const rentalsEmpty = document.getElementById("rentalsEmpty");

          const listingsList = document.getElementById("listingsList");
          const listingsFallback = document.querySelector(
            ".my-listing-section",
          );

          if (newStatus === "rented") {
            if (rentalsList) {
              rentalsEmpty?.classList.add("hidden");
              rentalsList.appendChild(post);
            }
          } else {
            (listingsList || listingsFallback)?.appendChild(post);
          }

          if (rentalsList && rentalsEmpty) {
            const has = rentalsList.querySelectorAll(".posts").length > 0;
            rentalsEmpty.classList.toggle("hidden", has);
          }

          closeOpenedMenu();
        } catch (err) {
          alert("Network error");
        }

        return;
      }

      if (itemBtn) {
        const action = itemBtn.dataset.action;
        const post = itemBtn.closest(".posts");
        if (!post) return;

        const carId = post.getAttribute("data-car-id");
        closeOpenedMenu();
        if (!carId) return;

        if (action === "edit") {
          const base = window.BASE_URL || "";
          window.location.href = `${base}/car/edit/${carId}`;
          return;
        }

        if (action === "delete") {
          if (!modal || !deleteForm) return;
          const base = window.BASE_URL || "";
          deleteForm.action = `${base}/car/delete/${carId}`;
          modal.classList.remove("hidden");
          return;
        }
      }
    });

    cancelBtn?.addEventListener("click", () => modal?.classList.add("hidden"));
    modal?.addEventListener("click", (e) => {
      if (
        e.target === modal ||
        e.target.classList.contains("delete-modal-backdrop")
      ) {
        modal.classList.add("hidden");
      }
    });
  })();

  // =================================================
  // CREATE PAGE: electric fields toggle
  // =================================================
  (function () {
    const categorySelect = document.getElementById("category_id");
    const electricBlock = document.getElementById("electric-features");
    if (!categorySelect || !electricBlock) return;

    const electricCategoryIds = new Set(["6", "1", "2"]);
    const electricInputIds = ["battery_life", "charging_time", "max_speed"];
    let prevWasElectric = false;

    function isElectric(val) {
      return electricCategoryIds.has(String(val || ""));
    }

    function setDisabled(disabled) {
      electricInputIds.forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.disabled = disabled;
      });
    }

    function clearElectricValues() {
      electricInputIds.forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.value = "";
      });
    }

    function toggleElectric(reason) {
      const nowElectric = isElectric(categorySelect.value);

      if (nowElectric) {
        electricBlock.classList.remove("hidden");
        setDisabled(false);
      } else {
        electricBlock.classList.add("hidden");
        setDisabled(true);

        if (reason === "change" && prevWasElectric) {
          clearElectricValues();
        }
      }
      prevWasElectric = nowElectric;
    }

    categorySelect.addEventListener("change", () => toggleElectric("change"));
    toggleElectric("init");
  })();

  // =================================================
  // CREATE PAGE: photos preview
  // =================================================
  (function () {
    const input = document.getElementById("photos");
    const preview = document.getElementById("photo-previews");
    if (!input || !preview) return;

    const MAX_FILES = 5;
    let selectedFiles = [];

    function clearPreview() {
      preview.innerHTML = "";
    }

    function syncInputFiles() {
      const dt = new DataTransfer();
      selectedFiles.forEach((file) => dt.items.add(file));
      input.files = dt.files;
    }

    function renderPreview() {
      clearPreview();

      selectedFiles.forEach((file, index) => {
        const wrapper = document.createElement("div");
        wrapper.className =
          "relative w-24 h-24 sm:w-28 sm:h-28 border rounded-xl overflow-hidden bg-white shadow-sm";

        const img = document.createElement("img");
        img.className = "w-full h-full object-cover";
        img.alt = "photo preview";

        const url = URL.createObjectURL(file);
        img.src = url;
        img.onload = () => URL.revokeObjectURL(url);

        const removeBtn = document.createElement("button");
        removeBtn.type = "button";
        removeBtn.innerHTML = "&times;";
        removeBtn.className =
          "absolute top-1 right-1 w-6 h-6 rounded-full bg-black/70 text-white text-xs flex items-center justify-center";

        removeBtn.addEventListener("click", () => {
          selectedFiles.splice(index, 1);
          syncInputFiles();
          renderPreview();
        });

        wrapper.appendChild(img);
        wrapper.appendChild(removeBtn);
        preview.appendChild(wrapper);
      });
    }

    input.addEventListener("change", function () {
      const newFiles = input.files ? Array.from(input.files) : [];
      if (!newFiles.length) return;

      for (const file of newFiles) {
        const exists = selectedFiles.some(
          (f) =>
            f.name === file.name &&
            f.size === file.size &&
            f.lastModified === file.lastModified,
        );

        if (!exists) {
          selectedFiles.push(file);
        }
      }

      if (selectedFiles.length > MAX_FILES) {
        alert("Max 5 ta rasm yuklash mumkin.");
        selectedFiles = selectedFiles.slice(0, MAX_FILES);
      }

      syncInputFiles();
      renderPreview();
    });
  })();

  // =================================================
  // FILTERS accordion
  // =================================================
  (function () {
    const filtersHeader = document.querySelectorAll(".filter-header");
    if (!filtersHeader.length) return;

    filtersHeader.forEach((header) => {
      header.addEventListener("click", (e) => {
        let filterSection = e.currentTarget.closest(".filter-section");
        if (!filterSection) return;

        let filterContent = filterSection.querySelector(".filter-content");
        let icon = header.querySelector(".fa-angle-up");

        filterContent?.classList.toggle("hidden");
        icon?.classList.toggle("rotate-180");
      });
    });
  })();

  // =================================================
  // OTHER PAGE: category slug-based electric
  // =================================================
  (function () {
    const category = document.getElementById("category");
    const electric = document.getElementById("electric-features");
    if (!category || !electric) return;

    function isElectric(val) {
      val = (val || "").toLowerCase();
      return (
        val.includes("electric") ||
        val.includes("scooters-electric") ||
        val.includes("ride-on-cars-electric") ||
        val.includes("rc-cars-electric") ||
        val.includes("mini-tractors-electric")
      );
    }

    function toggle() {
      const show = isElectric(category.value);
      electric.classList.toggle("hidden", !show);
    }

    category.addEventListener("change", toggle);
    toggle();
  })();

  // =================================================
  // CREATE PAGE LOCATION (STRICT): City + Street must be selected from suggestions
  // =================================================
  (function () {
    const citySearch = document.getElementById("citySearch");
    const cityInput = document.getElementById("cityInput");
    const latInput = document.getElementById("latInput");
    const lonInput = document.getElementById("lonInput");
    const cityDD = document.getElementById("cityDropdown");
    const cityList = document.getElementById("cityList");

    const streetSearch = document.getElementById("streetSearch");
    const streetInput = document.getElementById("streetInput");
    const streetDD = document.getElementById("streetDropdown");
    const streetList = document.getElementById("streetList");

    if (
      !citySearch ||
      !cityInput ||
      !latInput ||
      !lonInput ||
      !cityDD ||
      !cityList
    ) {
      return;
    }

    const CITY_URL =
      window.__citySuggestUrl || "/public/api/locations/suggest.php?type=city";
    const STREET_URL =
      window.__streetSuggestUrl ||
      "/public/api/locations/suggest.php?type=street";

    let tCity = null,
      lastCityQ = "";
    let tStreet = null,
      lastStreetQ = "";

    function open(el) {
      if (el) el.classList.remove("hidden");
    }
    function close(el) {
      if (el) el.classList.add("hidden");
    }

    function escapeHtml(s) {
      return String(s ?? "").replace(
        /[&<>"']/g,
        (m) =>
          ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#39;",
          })[m],
      );
    }

    async function fetchJson(url) {
      try {
        const res = await fetch(url);
        if (!res.ok) return [];
        return await res.json();
      } catch (err) {
        console.error("Fetch error:", err);
        return [];
      }
    }

    function renderCity(rows) {
      cityList.innerHTML = "";
      if (!rows.length) {
        cityList.innerHTML =
          '<div class="px-4 py-3 text-sm text-slate-500">No results</div>';
        open(cityDD);
        return;
      }

      rows.forEach((r) => {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "w-full text-left px-4 py-3 hover:bg-slate-50 text-sm";
        btn.innerHTML = `
          <div class="font-semibold text-slate-900">${escapeHtml(r.city)}</div>
          <div class="text-xs text-slate-500">${escapeHtml(r.label)}</div>
        `;
        btn.addEventListener("click", () => {
          citySearch.value = r.city || "";
          cityInput.value = r.city || "";
          latInput.value = r.lat || "";
          lonInput.value = r.lon || "";

          if (streetSearch) {
            streetSearch.disabled = false;
            streetSearch.value = "";
            streetInput.value = "";
          }
          close(cityDD);
        });
        cityList.appendChild(btn);
      });

      open(cityDD);
    }

    citySearch.addEventListener("input", () => {
      const q = citySearch.value.trim();

      cityInput.value = "";
      latInput.value = "";
      lonInput.value = "";

      if (streetSearch) {
        streetSearch.disabled = true;
        streetSearch.value = "";
        streetInput.value = "";
      }

      if (q.length < 2) {
        close(cityDD);
        return;
      }

      clearTimeout(tCity);
      tCity = setTimeout(async () => {
        if (q === lastCityQ) return;
        lastCityQ = q;

        const sep = CITY_URL.includes("?") ? "&" : "?";
        const data = await fetchJson(
          `${CITY_URL}${sep}q=${encodeURIComponent(q)}`,
        );
        renderCity(Array.isArray(data) ? data : []);
      }, 300);
    });

    citySearch.addEventListener("focus", () => {
      if (cityList.innerHTML.trim() !== "") open(cityDD);
    });

    if (streetSearch && streetInput && streetDD && streetList) {
      function renderStreet(rows) {
        streetList.innerHTML = "";
        if (!rows.length) {
          streetList.innerHTML =
            '<div class="px-4 py-3 text-sm text-slate-500">No results</div>';
          open(streetDD);
          return;
        }

        rows.forEach((r) => {
          const btn = document.createElement("button");
          btn.type = "button";
          btn.className =
            "w-full text-left px-4 py-3 hover:bg-slate-50 text-sm";
          btn.innerHTML = `
            <div class="font-semibold text-slate-900">${escapeHtml(r.street)}</div>
            <div class="text-xs text-slate-500">${escapeHtml(r.label)}</div>
          `;
          btn.addEventListener("click", () => {
            streetSearch.value = r.street || "";
            streetInput.value = r.street || "";

            if (r.lat && r.lon) {
              latInput.value = r.lat;
              lonInput.value = r.lon;
            }

            close(streetDD);
          });
          streetList.appendChild(btn);
        });

        open(streetDD);
      }

      streetSearch.addEventListener("input", () => {
        const q = streetSearch.value.trim();
        const city = cityInput.value.trim();

        streetInput.value = "";

        if (streetSearch.disabled || !city) {
          close(streetDD);
          return;
        }
        if (q.length < 2) {
          close(streetDD);
          return;
        }

        clearTimeout(tStreet);
        tStreet = setTimeout(async () => {
          if (q === lastStreetQ) return;
          lastStreetQ = q;

          const sep = STREET_URL.includes("?") ? "&" : "?";
          const url = `${STREET_URL}${sep}q=${encodeURIComponent(
            q,
          )}&city=${encodeURIComponent(city)}`;
          const data = await fetchJson(url);
          renderStreet(Array.isArray(data) ? data : []);
        }, 300);
      });

      streetSearch.addEventListener("focus", () => {
        if (streetList.innerHTML.trim() !== "") open(streetDD);
      });
    }

    document.addEventListener("click", (e) => {
      if (!cityDD.contains(e.target) && e.target !== citySearch) close(cityDD);
      if (
        streetSearch &&
        !streetDD.contains(e.target) &&
        e.target !== streetSearch
      )
        close(streetDD);
    });

    const form = citySearch.closest("form");
    if (form) {
      form.addEventListener("submit", (e) => {
        const okCity =
          cityInput.value.trim() !== "" &&
          latInput.value.trim() !== "" &&
          lonInput.value.trim() !== "";
        const okStreet = streetInput ? streetInput.value.trim() !== "" : true;

        if (!okCity || !okStreet) {
          e.preventDefault();
          alert(
            "City and Street must be selected from the list (typing only is not allowed).",
          );
        }
      });
    }

    if (cityInput.value.trim() !== "" && streetSearch) {
      streetSearch.disabled = false;
      if (streetInput.value.trim() !== "") {
        streetSearch.value = streetInput.value;
      }
    } else if (streetSearch) {
      streetSearch.disabled = true;
      streetSearch.placeholder = "Select city first";
    }
  })();

  // =================================================
  // LEAFLET MAP (DETAIL PAGE) - INLINE BLOCK (NO MODAL)
  // =================================================
  (function () {
    const btn = document.getElementById("seeLocationBtn");
    const section = document.getElementById("mapSection");
    const closeBtn = document.getElementById("mapCloseBtn");
    const mapEl = document.getElementById("leafletMap");

    if (!btn || !section || !closeBtn || !mapEl) return;

    const loc = window.__carLocation || {};
    let map = null;
    let marker = null;

    function showSection() {
      section.classList.remove("hidden");

      section.scrollIntoView({ behavior: "smooth", block: "start" });

      if (!loc.lat || !loc.lon) {
        mapEl.innerHTML =
          '<div style="padding:16px;color:#64748b">Location not available.</div>';
        return;
      }

      if (typeof L === "undefined") {
        console.error(
          "Leaflet not loaded (L is undefined). Check leaflet.js include.",
        );
        mapEl.innerHTML =
          '<div style="padding:16px;color:#ef4444">Leaflet not loaded.</div>';
        return;
      }

      if (!map) {
        map = L.map(mapEl).setView([loc.lat, loc.lon], 13);

        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
          maxZoom: 19,
          attribution: "&copy; OpenStreetMap",
        }).addTo(map);

        marker = L.marker([loc.lat, loc.lon]).addTo(map);
        if (loc.label) marker.bindPopup(loc.label);
        if (loc.label) marker.openPopup();
      } else {
        setTimeout(() => {
          map.invalidateSize();
          map.setView([loc.lat, loc.lon], 13);
        }, 120);
      }
    }

    function hideSection() {
      section.classList.add("hidden");
    }

    btn.addEventListener("click", showSection);
    closeBtn.addEventListener("click", hideSection);

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && !section.classList.contains("hidden")) {
        hideSection();
      }
    });
  })();

  (function () {
    const mainImage = document.getElementById("mainDetailImage");
    const thumbs = document.querySelectorAll(".detail-thumb");

    const modal = document.getElementById("galleryModal");
    const modalImg = document.getElementById("galleryModalImage");
    const closeBtn = document.getElementById("galleryModalClose");
    const prevBtn = document.getElementById("galleryModalPrev");
    const nextBtn = document.getElementById("galleryModalNext");

    if (!mainImage || !thumbs.length) return;
    if (!modal || !modalImg || !closeBtn || !prevBtn || !nextBtn) return;

    const images = Array.from(thumbs).map((t) => t.dataset.src);
    let current = 0;

    function show(i) {
      if (i < 0) i = images.length - 1;
      if (i >= images.length) i = 0;

      current = i;
      mainImage.src = images[i];
      modalImg.src = images[i];

      thumbs.forEach((t, idx) => {
        t.classList.toggle("ring-2", idx === i);
        t.classList.toggle("ring-green-600", idx === i);
      });
    }

    thumbs.forEach((btn, i) => {
      btn.addEventListener("click", () => show(i));
    });

    mainImage.addEventListener("click", () => {
      modal.classList.remove("hidden");
      modal.classList.add("flex");
      modalImg.src = images[current];
    });

    closeBtn.onclick = () => {
      modal.classList.add("hidden");
      modal.classList.remove("flex");
    };

    prevBtn.onclick = (e) => {
      e.stopPropagation();
      show(current - 1);
    };

    nextBtn.onclick = (e) => {
      e.stopPropagation();
      show(current + 1);
    };

    modal.onclick = (e) => {
      if (e.target === modal) closeBtn.onclick();
    };

    document.addEventListener("keydown", (e) => {
      if (modal.classList.contains("hidden")) return;

      if (e.key === "Escape") closeBtn.onclick();
      if (e.key === "ArrowLeft") show(current - 1);
      if (e.key === "ArrowRight") show(current + 1);
    });

    show(0);
  })();

  // =================================================
  // public/js/browse-filters.js
  // =================================================
  (function () {
    const form = document.getElementById("filtersForm");
    if (!form) return;

    let timer = null;

    form.addEventListener("input", (e) => {
      const n = e.target && e.target.name ? e.target.name : "";
      if (n === "search" || n === "min_price" || n === "max_price") {
        clearTimeout(timer);
        timer = setTimeout(() => {
          form.submit();
        }, 400);
      }
    });

    form.addEventListener("change", (e) => {
      const n = e.target && e.target.name ? e.target.name : "";
      if (n && n !== "search" && n !== "min_price" && n !== "max_price") {
        form.submit();
      }
    });
  })();

  // =================================================
  // public/js/browse-filters.js
  // =================================================
  (function () {
    const drawer = document.getElementById("filtersDrawer");
    const openBtn = document.getElementById("openFiltersBtn");
    const closeBtn = document.getElementById("closeFiltersBtn");
    const overlay = document.getElementById("filtersOverlay");
    const panel = document.getElementById("filtersPanel");

    if (!drawer || !openBtn || !closeBtn || !overlay || !panel) {
      return;
    }

    const DURATION = 300;

    function openDrawer() {
      drawer.classList.remove("pointer-events-none");
      drawer.classList.remove("opacity-0");

      overlay.classList.remove("opacity-0");
      overlay.classList.add("opacity-100");

      panel.classList.remove("-translate-x-full");
      panel.classList.add("translate-x-0");

      document.documentElement.classList.add("overflow-hidden");
    }

    function closeDrawer() {
      overlay.classList.remove("opacity-100");
      overlay.classList.add("opacity-0");

      panel.classList.remove("translate-x-0");
      panel.classList.add("-translate-x-full");

      document.documentElement.classList.remove("overflow-hidden");

      window.setTimeout(() => {
        drawer.classList.add("pointer-events-none");
        drawer.classList.add("opacity-0");
      }, DURATION);
    }

    openBtn.addEventListener("click", openDrawer);
    closeBtn.addEventListener("click", closeDrawer);
    overlay.addEventListener("click", closeDrawer);

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") closeDrawer();
    });

    const CITY_URL =
      window.__citySuggestUrl || "/public/api/locations/suggest.php?type=city";

    function escapeHtml(s) {
      return String(s ?? "").replace(
        /[&<>"']/g,
        (m) =>
          ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#39;",
          })[m],
      );
    }

    async function fetchJson(url) {
      const res = await fetch(url);
      if (!res.ok) return [];
      return await res.json();
    }

    function initCityAutocomplete(opts) {
      const form = document.getElementById(opts.formId);
      const citySearch = document.getElementById(opts.searchId);
      const hidden = document.getElementById(opts.hiddenId);
      const dd = document.getElementById(opts.dropdownId);
      const list = document.getElementById(opts.listId);

      if (!form || !citySearch || !hidden || !dd || !list) return;

      let timer = null;
      let lastQ = "";

      function openDD() {
        dd.classList.remove("hidden");
      }
      function closeDD() {
        dd.classList.add("hidden");
      }

      function render(rows) {
        list.innerHTML = "";

        if (!rows.length) {
          list.innerHTML = `<div class="px-4 py-3 text-sm text-slate-500">No results</div>`;
          openDD();
          return;
        }

        rows.forEach((r) => {
          const btn = document.createElement("button");
          btn.type = "button";
          btn.className =
            "w-full text-left px-4 py-3 hover:bg-slate-50 text-sm";
          btn.innerHTML = `
          <div class="font-semibold text-slate-900">${escapeHtml(r.city)}</div>
          <div class="text-xs text-slate-500">${escapeHtml(r.label)}</div>
        `;

          btn.addEventListener("click", () => {
            citySearch.value = r.city || "";
            hidden.value = r.city || "";

            closeDD();
            if (opts.closeDrawerOnSelect) closeDrawer();

            form.submit();
          });

          list.appendChild(btn);
        });

        openDD();
      }

      citySearch.addEventListener("input", () => {
        const q = citySearch.value.trim();
        hidden.value = "";

        if (q.length < 2) {
          closeDD();
          return;
        }

        clearTimeout(timer);
        timer = setTimeout(async () => {
          if (q === lastQ) return;
          lastQ = q;

          const sep = CITY_URL.includes("?") ? "&" : "?";
          const data = await fetchJson(
            `${CITY_URL}${sep}q=${encodeURIComponent(q)}`,
          );
          render(Array.isArray(data) ? data : []);
        }, 250);
      });

      citySearch.addEventListener("focus", () => {
        if (list.innerHTML.trim() !== "") openDD();
      });

      citySearch.addEventListener("keydown", (e) => {
        if (e.key === "Enter" && !dd.classList.contains("hidden")) {
          e.preventDefault();
        }
      });

      document.addEventListener("click", (e) => {
        if (!dd.contains(e.target) && e.target !== citySearch) closeDD();
      });
    }

    initCityAutocomplete({
      formId: "filtersForm",
      searchId: "citySearch",
      hiddenId: "locationHidden",
      dropdownId: "cityDropdown",
      listId: "cityList",
      closeDrawerOnSelect: false,
    });

    initCityAutocomplete({
      formId: "filtersFormMobile",
      searchId: "citySearchMobile",
      hiddenId: "locationHiddenMobile",
      dropdownId: "cityDropdownMobile",
      listId: "cityListMobile",
      closeDrawerOnSelect: true,
    });
  })();

  // ===============================
  // Responsive Header Navigation
  // ===============================
  (function () {
    const btn = document.getElementById("navToggleBtn");
    const mobileNav = document.getElementById("mobileNav");
    const bars = document.getElementById("navIconBars");
    const x = document.getElementById("navIconX");

    if (!btn || !mobileNav) return;

    let isOpen = false;

    const OPEN_CLASSES = ["max-h-[520px]", "opacity-100"];
    const CLOSE_CLASSES = ["max-h-0", "opacity-0", "pointer-events-none"];

    function openMenu() {
      isOpen = true;
      btn.setAttribute("aria-expanded", "true");

      mobileNav.classList.remove(...CLOSE_CLASSES);
      mobileNav.classList.add(...OPEN_CLASSES);

      if (bars) bars.classList.add("hidden");
      if (x) x.classList.remove("hidden");

      document.documentElement.classList.add("overflow-hidden");
    }

    function closeMenu() {
      isOpen = false;
      btn.setAttribute("aria-expanded", "false");

      mobileNav.classList.add(...CLOSE_CLASSES);
      mobileNav.classList.remove(...OPEN_CLASSES);

      if (bars) bars.classList.remove("hidden");
      if (x) x.classList.add("hidden");

      document.documentElement.classList.remove("overflow-hidden");
    }

    btn.addEventListener("click", () => {
      isOpen ? closeMenu() : openMenu();
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") closeMenu();
    });

    mobileNav.addEventListener("click", (e) => {
      const link = e.target.closest("a");
      if (link) closeMenu();
    });

    window.addEventListener("resize", () => {
      if (window.innerWidth >= 768) {
        closeMenu();
      }
    });
  })();

  // ===============================
  // Mobile Header Language Dropdown
  // ===============================
  (function () {
    const mobileLangToggle = document.getElementById("mobileLangToggle");
    const mobileLangDropdown = document.getElementById("mobileLangDropdown");
    const mobileLangChevron = document.getElementById("mobileLangChevron");

    if (!mobileLangToggle || !mobileLangDropdown || !mobileLangChevron) return;

    mobileLangToggle.addEventListener("click", function (e) {
      e.stopPropagation();
      mobileLangDropdown.classList.toggle("hidden");
      mobileLangChevron.classList.toggle("rotate-180");
    });

    document.addEventListener("click", function (e) {
      if (
        !mobileLangToggle.contains(e.target) &&
        !mobileLangDropdown.contains(e.target)
      ) {
        mobileLangDropdown.classList.add("hidden");
        mobileLangChevron.classList.remove("rotate-180");
      }
    });

    const mobileNav = document.getElementById("mobileNav");
    if (mobileNav) {
      mobileNav.addEventListener("click", function (e) {
        const link = e.target.closest("a");
        if (link) {
          mobileLangDropdown.classList.add("hidden");
          mobileLangChevron.classList.remove("rotate-180");
        }
      });
    }
  })();
});
