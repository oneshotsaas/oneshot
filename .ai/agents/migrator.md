# Agent: Database Migrator

Assists with creating and managing database migrations in the OneShot framework.

## Creating a Migration
```bash
php spark make:migration ModuleName MigrationDescription
```
File lands in `modules/ModuleName/Database/Migrations/` or `oneshot/ModuleName/Database/Migrations/`.

## Running Migrations
```bash
php spark migrate --all                        # run all pending migrations across all namespaces
php spark migrate -n "OneShot\\ModuleName"      # run one module's migrations only
php spark migrate:rollback --all
```

## Migration Conventions
- Table names: `module_entity` (e.g., `auth_users`, `billing_plans`, `media_files`)
- Always include: `id` (INT unsigned auto_increment PK), `created_at`, `updated_at`, `deleted_at` (all DATETIME NULL)
- No TIMESTAMP columns — use DATETIME NULL everywhere
- No native JSON column type — use TEXT holding a JSON string (`json_encode()`/`json_decode()`); never `serialize()`/`unserialize()` — see security.md, unserialize() on stored data is an object-injection risk

## Schema Change Workflow
1. Create a new migration file (never modify existing ones)
2. Name it descriptively: `AddStatusToAuthUsers`, `CreateBillingPlansTable`
3. Implement both `up()` and `down()` methods
