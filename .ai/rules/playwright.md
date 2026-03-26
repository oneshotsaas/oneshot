# Playwright CLI Rules

## Skill

Always invoke the `playwright-cli` skill before starting any browser automation task:

```
/playwright-cli
```

---

## Working Directory

**Always** run `playwright-cli` from `writable/`:

```bash
cd writable
playwright-cli open https://...
```

This ensures `.playwright-cli/` snapshots and all artifacts are created inside `writable/`, not in the project root.

---

## Screenshots

Save to `writable/screenshots/`:

```bash
playwright-cli screenshot --filename=screenshots/name.png
```

---

## Typical Audit Flow

```bash
cd writable

playwright-cli open http://site.local/page1
playwright-cli screenshot --filename=screenshots/page1.png

playwright-cli goto http://site.local/page2
playwright-cli screenshot --filename=screenshots/page2.png

playwright-cli close
```

---

## Snapshots

A snapshot is created automatically after each navigation command. For an explicit snapshot:

```bash
playwright-cli snapshot
```

All `.yml` snapshots are stored in `writable/.playwright-cli/`.

---

## Closing the Browser

Always close at the end of the session:

```bash
playwright-cli close
```

For stuck sessions:

```bash
playwright-cli close-all
```
