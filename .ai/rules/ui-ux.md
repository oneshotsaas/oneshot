# UI/UX Design Rules

Applies to all admin and app views. Follow these rules on every task that touches a view file.
Stack: **DaisyUI v5** + **Tailwind CSS** — no custom CSS classes, no inline styles.

---

## Page Layout — One Fixed Pattern

Every admin/app page follows this structure, no exceptions:

```
[ Breadcrumbs / Page Title ]  ←→  [ Filters + Action Buttons ]   ← topbar (layout-provided)
─────────────────────────────────────────────────────────────────
[ Page Content — table, form, cards, etc. ]
```

- **Breadcrumbs = page title.** Never add `<h1>` inside the view. Set the title via `$this->appendBC('Title', url)` in the controller.
- **Filters and action buttons** go in `page_actions_view` (a partial shared via `$this->share()`). Never put them inside `<main>`.
- The topbar is provided by the layout; the partial is injected into it.

---

## Topbar Slot — Compact, Right-Aligned

The topbar right side must stay compact. Pack everything on one line:

```
[ filter dropdown ] [ type toggle ] [ search input ]   [ New Item btn ]
```

Rules:
- Use `<form method="get" class="flex items-center gap-2">` — no `flex-wrap`, no margin classes.
- Filter controls: fixed widths (`w-32`, `w-36`, `w-40`) so they don't stretch.
- Use `select-sm`, `input-sm`, `btn-sm` — never default (md) size in the topbar.
- Toggles and icon-buttons (`btn-square btn-sm btn-ghost`) are preferred over wide labeled buttons when the action is obvious.
- Action buttons: `btn btn-primary btn-sm` for the main action; `btn btn-ghost btn-sm` for secondary.
- If there are both filters and buttons, put them in a single partial separated by a visual gap or divider.

---

## Tables

See `general.md → Views — Tables` for the exact HTML structure.

Additional rules:
- **Edit link is always on the item name/title** — `<a href="<?= route_to('...edit', ...) ?>" class="font-medium hover:opacity-70">`. No separate "Edit" text link in the row.
- **Frontend view link** — if an item has a public-facing URL (post, page, product), add an external-link icon button in the actions column with `tooltip-left` "View on site". Use `target="_blank"`.
- Show only **key columns** — avoid wide tables. Secondary data (created date, IDs) should be last and visually dimmed (`text-sm opacity-60`).
- Status badges: `<span class="badge badge-sm badge-success">Active</span>` — never plain text for status.
- Action icons: pencil = edit, eye = view, trash = delete. Always `tooltip-left`.

---

## Forms and Settings Fields

See `general.md → Views — Forms` for the exact HTML pattern.

Additional rules:
- **Hints are mandatory** for any field that requires technical knowledge, has a non-obvious format, or controls behaviour (API keys, regex patterns, cron expressions, feature flags).
- Hint text must be **meaningful and concise** — explain *what the value does* and *accepted format*. Example: `"Server-side Stripe secret (sk_live_...). Never expose this in client-side code."` — not `"Enter your key here"`.
- Boolean toggles (`<input type="checkbox" class="toggle toggle-sm">`) for on/off settings — not a select with Yes/No.
- Group related fields visually using `<div class="divider text-xs opacity-50">Section Name</div>` between groups.
- For settings pages with multiple sections (General / Auth / Billing …), use a sidebar tab list — not a full-page tab row.

---

## Mobile Responsiveness

Every view must work on mobile (≥ 320 px). Rules:

- **Tables on mobile** — wrap the table container with `overflow-x-auto`. The `<table>` element itself must have `min-w-max` so the browser creates a horizontal scrollbar instead of squeezing columns:
  ```html
  <div class="rounded-lg border border-base-300 overflow-x-auto overflow-hidden">
      <table class="table table-sm w-full min-w-max">
  ```
- **Topbar filters on mobile** — if there are more than 2 filter controls, collapse them behind a `btn btn-ghost btn-sm` filter toggle (show/hide the filter row below the topbar). Use `hidden sm:flex` / `flex sm:hidden` pattern.
- **Form grid on mobile** — the `grid-cols-[12rem_1fr]` form layout switches to single-column on small screens. **All** field rows must use the responsive prefix — no exceptions, including textarea rows:
  ```php
  <div class="grid grid-cols-1 sm:grid-cols-[12rem_1fr] items-start gap-x-6 gap-y-1">
  ```
  On mobile (`grid-cols-1`) the label is above the field; textarea and other multiline inputs get full width naturally.
- **Action buttons on mobile** — full-width (`w-full sm:w-auto`) when stacked vertically.
- **Cards/panels on mobile** — `p-4` padding (not `p-6`) on small screens; use `sm:p-6` for desktop.
- Test every new view at 375 px width before considering the task complete.

---

## Navigation

- Sidebar nav items use DaisyUI `menu` component — never custom lists.
- Active state: `menu-active` on the current `<li>` item.
- Group labels (e.g. "Content", "Billing") are `<li class="menu-title">` — never `<h3>` or plain text.
- Never add navigation items in sidebar directly — register them via the module's nav contribution (event or config).

---

## Icon-Only Buttons

Every button that has no visible text label **must** have a tooltip:

```php
<div class="tooltip tooltip-left" data-tip="<?= __('module.action', 'Action') ?>">
    <button class="btn btn-ghost btn-sm btn-square">
        <!-- svg icon -->
    </button>
</div>
```

Rules:
- `tooltip-left` for actions at the right edge of the screen (table actions, topbar icons).
- `tooltip-bottom` for topbar icons that are not near the right edge.
- The `data-tip` value must always go through `__()` — never a hardcoded string.
- This applies to: table action icons, topbar icon buttons, sidebar icon buttons, floating action buttons — any clickable element without visible text.

---

## Modals and Confirmations

- Destructive actions (delete) always require confirmation — either a browser `confirm()` or a DaisyUI modal.
- For simple deletes: `onsubmit="return confirm('...')"` on the form is acceptable.
- For bulk or irreversible actions: use a `<dialog>` modal with a typed confirmation if needed.
- Modal markup: DaisyUI `modal` component — never custom-positioned `<div>` popups.

---

## Empty States

Every list/table page must have an empty-state message when there are no records:

```php
<tr>
    <td colspan="N" class="py-12 text-center text-sm opacity-40">
        <?= __('module.no_items', 'No items yet') ?>
    </td>
</tr>
```

Never show a blank table body.

---

## Loading and Feedback

- Form submit buttons: disable after click and show a loading spinner (`loading loading-spinner loading-xs`) to prevent double-submit.
- Flash messages: use the layout's flash partial — never create a custom alert div in a view.
- Inline validation errors: `<p class="text-xs text-error mt-1">error message</p>` below the field.

---

## Consistency Checklist

Before finishing any view, verify:
- [ ] No `<h1>` in the view — breadcrumb is the title
- [ ] Filters and buttons are in `page_actions_view`, not inside `<main>`
- [ ] Table has edit link on the name column
- [ ] Items with a public URL have an external-link button
- [ ] All technical settings fields have a meaningful hint
- [ ] Table wrapper has `overflow-x-auto`, `<table>` has `min-w-max`
- [ ] Form grid uses `grid-cols-1 sm:grid-cols-[12rem_1fr]` on **every** row (including textarea rows)
- [ ] Empty state row present
- [ ] All text wrapped in `__()`
