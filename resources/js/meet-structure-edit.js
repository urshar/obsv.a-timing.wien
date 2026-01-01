/**
 * Meet structure editor JS (PhpStorm-friendly)
 */

/**
 * @typedef {Object} MeetStructureEditBootstrap
 * @property {number} sessionCount
 */

/**
 * @type {{ __MEET_STRUCTURE_EDIT__?: MeetStructureEditBootstrap }}
 */
const _g = globalThis;

/** @type {MeetStructureEditBootstrap|undefined} */
const BOOT = _g.__MEET_STRUCTURE_EDIT__;

(function () {
    /** @param {string} id @returns {HTMLElement|null} */
    function byId(id) {
        return document.getElementById(id);
    }

    /** @param {ParentNode} root @param {string} selector @returns {Element|null}
     * @param selector
     */
    function qs(root, selector) {
        return root.querySelector(selector);
    }

    /** @param {ParentNode} root @param {string} selector @returns {Element[]}
     * @param selector
     */
    function qsa(root, selector) {
        return Array.from(root.querySelectorAll(selector));
    }

    /**
     * Replace placeholders in HTML string.
     * @param {string} html
     * @param {Record<string,string|number>} map
     * @returns {string}
     */
    function replaceTokens(html, map) {
        let out = html;
        for (const k in map) {
            if (!Object.prototype.hasOwnProperty.call(map, k)) continue;
            out = out.split(k).join(String(map[k]));
        }
        return out;
    }

    /** @type {HTMLFormElement|null} */
    const form = /** @type {HTMLFormElement|null} */ (byId('meet-structure-form'));
    if (!form) return;

    const sessionsContainer = qs(form, '#sessions-container');
    if (!sessionsContainer) return;

    const btnAddSession = qs(form, '#btn-add-session');
    if (!btnAddSession) return;

    // Used to inject AgeGroup options into new event rows
    const firstAgSelect = qs(
        form,
        'select[name^="sessions"][name$="[meet_age_group_id]"]'
    );

    /** @type {string} */
    const ageGroupOptionsHtml = firstAgSelect
        ? firstAgSelect.innerHTML
        : '<option value="">—</option>';

    /** @type {number} */
    let sessionIndex = (BOOT && Number.isFinite(BOOT.sessionCount))
        ? BOOT.sessionCount
        : 0;

    /**
     * Compute next event index within a session by counting existing rows.
     * @param {HTMLElement} sessionEl
     * @returns {number}
     */
    function nextEventIndex(sessionEl) {
        const tbody = qs(sessionEl, '[data-events-container]');
        if (!tbody) return 0;
        return qsa(tbody, '[data-event]').length;
    }

    /**
     * Add a new event row into a session.
     * @param {HTMLElement} sessionEl
     * @param {number} si
     */
    function addEventRow(sessionEl, si) {
        const tbody = qs(sessionEl, '[data-events-container]');
        if (!tbody) return;

        const ei = nextEventIndex(sessionEl);

        /** @type {HTMLTemplateElement|null} */
        const tpl = /** @type {HTMLTemplateElement|null} */ (byId('tpl-event'));
        if (!tpl) {
            throw new Error('Template not found: tpl-event');
        }

        const html = replaceTokens(tpl.innerHTML, {
            '__SI__': si,
            '__EI__': ei,
        });

        const tmp = document.createElement('tbody');
        tmp.innerHTML = html.trim();

        /** @type {HTMLElement|null} */
        const tr = /** @type {HTMLElement|null} */ (tmp.firstElementChild);
        if (!tr) return;

        // Inject age group options into the new select
        const agSel = qs(tr, 'select[name$="[meet_age_group_id]"]');
        if (agSel) {
            agSel.innerHTML = ageGroupOptionsHtml;
        }

        // Bind remove button
        const btnRemoveEvent = qs(tr, '.btn-remove-event');
        if (btnRemoveEvent) {
            btnRemoveEvent.addEventListener('click', () => {
                tr.remove();
            });
        }

        tbody.appendChild(tr);
    }

    /**
     * Wire up buttons within a session element.
     * @param {HTMLElement} sessionEl
     * @param {number} si
     */
    function bindSessionHandlers(sessionEl, si) {
        const btnRemoveSession = qs(sessionEl, '.btn-remove-session');
        if (btnRemoveSession) {
            btnRemoveSession.addEventListener('click', () => {
                sessionEl.remove();
            });
        }

        const btnAddEvent = qs(sessionEl, '.btn-add-event');
        if (btnAddEvent) {
            btnAddEvent.addEventListener('click', () => {
                addEventRow(sessionEl, si);
            });
        }

        qsa(sessionEl, '.btn-remove-event').forEach((btn) => {
            btn.addEventListener('click', () => {
                const tr = /** @type {HTMLElement|null} */ (btn.closest('tr'));
                if (tr) tr.remove();
            });
        });
    }

    /**
     * Add a new session block.
     */
    function addSession() {
        /** @type {HTMLTemplateElement|null} */
        const tpl = /** @type {HTMLTemplateElement|null} */ (byId('tpl-session'));
        if (!tpl) {
            throw new Error('Template not found: tpl-session');
        }

        const html = replaceTokens(tpl.innerHTML, {'__SI__': sessionIndex});

        const tmp = document.createElement('div');
        tmp.innerHTML = html.trim();

        /** @type {HTMLElement|null} */
        const sessionEl = /** @type {HTMLElement|null} */ (tmp.firstElementChild);
        if (!sessionEl) return;

        sessionsContainer.appendChild(sessionEl);

        bindSessionHandlers(sessionEl, sessionIndex);
        sessionIndex++;
    }

    // Bind existing sessions (index entspricht Blade-Index)
    qsa(sessionsContainer, '[data-session]').forEach((node, idx) => {
        const sessionEl = /** @type {HTMLElement} */ (node);
        bindSessionHandlers(sessionEl, idx);
    });

    btnAddSession.addEventListener('click', () => addSession());
})();
