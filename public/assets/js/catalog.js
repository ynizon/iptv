// Search results: tabs, sort and filters, detail panel and navigation with the ← → keys
window.Catalog = (function () {
    // Kept between two searches
    const state = { tab: null, sort: "year", good: false, low: false, selected: {} };
    let root = null;
    let labels = {};

    function init(element) {
        root = element;
        labels = JSON.parse(root.dataset.labels || "{}");
        // The "recent" list comes sorted by last watched: keep that order, by default and as a choice
        // Same for the "recently added" categories, sorted last added first
        if (root.dataset.recent === "1") {
            state.sort = "recent";
        } else if (root.dataset.added === "1") {
            state.sort = "added";
        } else if (state.sort === "recent" || state.sort === "added") {
            state.sort = "year";
        }
        const tabs = Array.from(root.querySelectorAll(".cat-tab"));
        if (!tabs.some((tab) => tab.dataset.tab === state.tab)) {
            state.tab = tabs.length ? tabs[0].dataset.tab : null;
        }
        tabs.forEach((tab) => tab.addEventListener("click", () => {
            state.tab = tab.dataset.tab;
            render();
        }));
        root.querySelectorAll("[data-sort]").forEach((button) => button.addEventListener("click", () => {
            state.sort = button.dataset.sort;
            render();
        }));
        root.querySelectorAll("[data-filter]").forEach((button) => button.addEventListener("click", () => {
            state[button.dataset.filter] = !state[button.dataset.filter];
            render();
        }));
        root.querySelectorAll(".cat-card").forEach((card) => card.addEventListener("click", () => {
            select(card);
            showDetailOnSmallScreen(card);
        }));
        render();
    }

    function render() {
        root.querySelectorAll(".cat-tab").forEach((tab) => {
            const on = tab.dataset.tab === state.tab;
            tab.classList.toggle("active", on);
            tab.setAttribute("aria-selected", on);
        });
        root.querySelectorAll("[data-sort]").forEach((button) => {
            const on = button.dataset.sort === state.sort;
            button.classList.toggle("active", on);
            button.setAttribute("aria-pressed", on);
        });
        root.querySelectorAll("[data-filter]").forEach((button) => {
            const on = !!state[button.dataset.filter];
            button.classList.toggle("active", on);
            button.setAttribute("aria-pressed", on);
        });
        // Channels have no rating
        root.querySelectorAll("[data-rated-tools]").forEach((element) => {
            element.hidden = state.tab === "channels" || state.tab === null;
        });
        root.querySelectorAll(".cat-pane").forEach((pane) => {
            pane.hidden = pane.dataset.pane !== state.tab;
            if (pane.dataset.pane !== "channels") {
                arrange(pane);
            }
        });
    }

    function arrange(pane) {
        const grid = pane.querySelector(".cat-grid");
        const cards = Array.from(grid.querySelectorAll(".cat-card"));
        const num = (card, key) => parseFloat(card.dataset[key]) || 0;
        const byScore = (a, b) => num(b, "score") - num(a, "score");
        const byOrder = (a, b) => num(a, "order") - num(b, "order");
        const sorts = {
            recent: byOrder,
            added: byOrder,
            score: byScore,
            year: (a, b) => num(b, "year") - num(a, "year") || byScore(a, b),
            az: (a, b) => a.dataset.title.localeCompare(b.dataset.title, "fr"),
        };
        cards.sort(sorts[state.sort]).forEach((card) => grid.appendChild(card));
        cards.forEach((card) => {
            card.hidden = (state.good && card.dataset.good !== "1") || (state.low && card.dataset.low === "1");
        });

        const visible = cards.filter((card) => !card.hidden);
        let text = (visible.length > 1 ? labels.many : labels.one || "").replace(":count", visible.length);
        if (state.sort === "score" && visible.length > 1) {
            text += labels.bestFirst || "";
        } else if (state.sort === "year" && visible.length > 1) {
            text += labels.newestFirst || "";
        } else if (state.sort === "added" && visible.length > 1) {
            text += labels.addedFirst || "";
        }
        pane.querySelector(".cat-count").textContent = visible.length ? text : labels.empty || "";
        pane.querySelector(".cat-detail").hidden = !visible.length;

        const selected = visible.find((card) => card.dataset.id === state.selected[pane.dataset.pane]);
        if (visible.length) {
            select(selected || visible[0]);
        }
    }

    function select(card) {
        const pane = card.closest(".cat-pane");
        pane.querySelectorAll(".cat-card.active").forEach((other) => other.classList.remove("active"));
        card.classList.add("active");
        state.selected[pane.dataset.pane] = card.dataset.id;
        let loaded = false;
        pane.querySelectorAll(".cat-detail-item").forEach((detail) => {
            const on = detail.dataset.detail === card.dataset.id;
            // A failed load is tried again
            if (on && detail.dataset.failed) {
                detail.remove();
                return;
            }
            detail.hidden = !on;
            loaded = loaded || on;
        });
        if (!loaded) {
            load(card.dataset.id, pane);
        }
        pane.querySelector(".cat-detail").scrollTop = 0;
    }

    // The detail panels are loaded when their card is selected: with all of them, a page took megabytes
    function load(id, pane) {
        const placeholder = document.createElement("div");
        placeholder.className = "cat-detail-item cat-detail-loading";
        placeholder.dataset.detail = id;
        placeholder.innerHTML = '<span class="loader"></span>';
        pane.querySelector(".cat-detail").appendChild(placeholder);
        const url = id.charAt(0) === "s" ? "/detail_serie/" + id.substring(1) : "/detail/" + id;
        $.get(url).done((html) => {
            const box = document.createElement("div");
            box.innerHTML = html;
            const item = box.querySelector(".cat-detail-item");
            item.hidden = state.selected[pane.dataset.pane] !== id;
            placeholder.replaceWith(item);
        }).fail(() => {
            placeholder.dataset.failed = "1";
            placeholder.textContent = labels.loadError || "";
        });
    }

    // Below the grid on small screens: bring the detail into view
    function showDetailOnSmallScreen(card) {
        if (window.matchMedia("(max-width: 991.98px)").matches) {
            card.closest(".cat-pane").querySelector(".cat-detail").scrollIntoView({ behavior: "smooth", block: "start" });
        }
    }

    // Index of the card to go to: ← → previous / next, ↑ ↓ same column in the row above / below
    function target(cards, current, key) {
        if (current < 0) {
            return 0;
        }
        if (key === "ArrowLeft" || key === "ArrowRight") {
            return key === "ArrowRight" ? Math.min(current + 1, cards.length - 1) : Math.max(current - 1, 0);
        }
        const columns = cards.filter((card) => card.offsetTop === cards[0].offsetTop).length || 1;
        return key === "ArrowDown" ? Math.min(current + columns, cards.length - 1) : Math.max(current - columns, 0);
    }

    document.addEventListener("keydown", (event) => {
        if (["ArrowLeft", "ArrowRight", "ArrowUp", "ArrowDown"].indexOf(event.key) < 0) {
            return;
        }
        if (!root || !document.body.contains(root) || event.altKey || event.ctrlKey || event.metaKey) {
            return;
        }
        // Keep the arrows for text fields and the "watching" dialog
        if (event.target.closest("input, textarea, select, [contenteditable]") || document.querySelector(".modal.show")) {
            return;
        }
        const pane = root.querySelector('.cat-pane[data-pane="' + state.tab + '"]');
        if (!pane || pane.dataset.pane === "channels") {
            return;
        }
        const cards = Array.from(pane.querySelectorAll(".cat-card")).filter((card) => !card.hidden);
        if (!cards.length) {
            return;
        }
        const current = cards.findIndex((card) => card.classList.contains("active"));
        const next = target(cards, current, event.key);
        event.preventDefault();
        select(cards[next]);
        cards[next].focus({ preventScroll: true });
        cards[next].scrollIntoView({ behavior: "smooth", block: "nearest" });
    });

    // Copy the favorite / seen state of the detail buttons to the card badges
    function sync(id) {
        if (!root) {
            return;
        }
        id = String(id);
        const card = root.querySelector('.cat-card[data-id="' + id + '"]');
        const detail = root.querySelector('.cat-detail-item[data-detail="' + id + '"]');
        if (!card || !detail) {
            return;
        }
        const favorite = detail.querySelector('[data-action="favorite"]');
        const seen = detail.querySelector('[data-action="seen"]');
        const favoriteFlag = card.querySelector(".cat-flag-fav");
        const seenFlag = card.querySelector(".cat-flag-seen");
        if (favorite && favoriteFlag) {
            favoriteFlag.hidden = !favorite.classList.contains("active");
        }
        if (seen && seenFlag) {
            seenFlag.hidden = !seen.classList.contains("active");
        }
    }

    function removeItem(id) {
        const card = root.querySelector('.cat-card[data-id="' + id + '"]');
        const detail = root.querySelector('.cat-detail-item[data-detail="' + id + '"]');
        if (card) {
            const pane = card.closest(".cat-pane");
            card.remove();
            if (detail) {
                detail.remove();
            }
            arrange(pane);
        }
    }

    return {
        init: init,
        sync: sync,
        remove: (id) => $.get("/remove/" + id).done(() => removeItem(String(id))),
        removeSerie: (name, id) => $.post("/remove_serie", { name: name }).done(() => removeItem(id)),
    };
})();

// A channel logo that cannot be loaded: show the channel name instead
function catalogLogoMissing(img) {
    const tile = document.createElement("span");
    tile.className = "cat-logo cat-logo-text";
    tile.setAttribute("aria-hidden", "true");
    tile.textContent = img.dataset.name || "";
    img.replaceWith(tile);
}

function catalogSync(id) { Catalog.sync(id); }
function catalogRemove(id) { Catalog.remove(id); }
function catalogRemoveSerie(name, id) { Catalog.removeSerie(name, id); }
