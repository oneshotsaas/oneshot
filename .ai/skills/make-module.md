# Skill: Create a New Module

When asked to create a new module:

1. Run the generator:
   ```bash
   php spark make:module ModuleName
   ```

2. Generated structure in `modules/ModuleName/`:
   - `Config/Routes.php` — routes reading `config('Prefixes')`
   - `Controllers/ModuleName.php` — base controller
   - `modulename.md` — module documentation

3. Add database migration if needed:
   ```bash
   php spark make:migration ModuleName CreateModuleNameTable
   ```

4. Edit `modulename.md` to document:
   - Purpose (1-2 sentences)
   - Contexts and URL patterns
   - Controllers, Models, Services
   - Events triggered and consumed
   - Dependencies on other modules

5. Verify routes appear:
   ```bash
   php spark routes
   ```

6. After completing any task inside a module, update `modulename.md` to reflect the current state:
   - Remove outdated contexts, controllers, routes
   - Add new ones that were created
   - Keep it accurate — this file is the AI's reference for future work in this module
