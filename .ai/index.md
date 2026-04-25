# .ai/index.md — Read This First

This file is the entry point for all AI work in this project.
**Always read this file before starting any task.**

---

## Always Apply

These rules are mandatory for every task, regardless of context:

→ Read **`.ai/rules/general.md`** — naming, code style, database, security, logging, routing conventions.
→ Read **`.ai/rules/ui-ux.md`** — layout, topbar, tables, forms, mobile responsiveness, DaisyUI patterns. **Required for any task that touches a view file.**

---

## Read Based on Task

### Working inside a module
→ Read **`oneshot/{ModuleName}/{modulename}.md`** or **`modules/{ModuleName}/{modulename}.md`**
— contains: purpose, contexts, controllers, models, services, events, dependencies.

### Creating a new module
→ Read **`.ai/skills/make-module.md`** — step-by-step scaffold guide.

### Working on API endpoints
→ Read **`.ai/rules/api.md`** — response format, route conventions, auth, status codes.

### Working on modules structure, overrides, routes
→ Read **`.ai/rules/modules.md`** — structure rules, override mechanism, filter placement.

### Adding a payment/notify/mail/storage integration
→ Read **`.ai/skills/add-provider.md`** — how to implement a contract and wire it up.

### Reviewing code or a pull request
→ Read **`.ai/agents/reviewer.md`** — checklist: architecture, code quality, module completeness.

### Creating or modifying database migrations
→ Read **`.ai/agents/migrator.md`** — migration conventions, naming, schema rules.

### Using Playwright CLI for visual testing / screenshots
→ Read **`.ai/rules/playwright.md`** — working directory, screenshot paths, session management.

---

## File Map

```
.ai/
├── index.md                ← YOU ARE HERE — read first, always
├── rules/
│   ├── general.md          ← mandatory: naming, DB, security, logging, routing
│   ├── ui-ux.md            ← mandatory for views: layout, topbar, tables, forms, mobile (DaisyUI+Tailwind)
│   ├── modules.md          ← module structure, override mechanism, filter placement
│   ├── api.md              ← API response format, auth, status codes
│   └── playwright.md       ← playwright-cli: working dir, screenshots, session management
├── skills/
│   ├── make-module.md      ← how to scaffold a new module end-to-end
│   └── add-provider.md     ← how to implement Payment/Notify/Mail/Storage contract
└── agents/
    ├── reviewer.md         ← code review checklist
    └── migrator.md         ← migration creation and schema conventions
```

---

## Standard Workflow

```
1. Read .ai/index.md              (this file)
2. Read .ai/rules/general.md      (always)
3. Read ONESHOT.md                (if unfamiliar with architecture)
4. Read {module}/modulename.md    (for the module you're working in)
5. Read task-specific rule/skill  (see "Read Based on Task" above)
6. Write code
```
