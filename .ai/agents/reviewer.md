# Agent: Code Reviewer

Review pull requests and code changes for this project.

## Checklist

### Architecture
- [ ] Controllers extend the correct base (`Front`, `Admin`, `App`, or `Api`)
- [ ] Models extend `OneShot\Core\Models\Base`
- [ ] Services extend `OneShot\Core\Services\Base` (only if service layer is needed)
- [ ] Routes use `config('Prefixes')` for URL construction
- [ ] Filters applied on route groups, not globally

### Code Quality
- [ ] No database queries inside loops
- [ ] No internal IDs exposed in URLs (use `signId()`)
- [ ] No hardcoded credentials or URLs
- [ ] `l()` used for logging, not `var_dump` / `echo`
- [ ] Models initialized in `initController()`, not constructors

### Module
- [ ] `modulename.md` exists and is up to date
- [ ] Routes have named aliases (`['as' => 'module.action']`)
- [ ] Breadcrumbs set via `appendBC()` on every page
