<div class="w-full max-w-lg">

    <!-- Header -->
    <div class="text-center mb-8">
        <div class="text-4xl font-black tracking-tight mb-1">OneShot</div>
        <div class="text-base-content/50 text-sm">Installation wizard · Step 1 of 3</div>
    </div>

    <ul class="steps w-full mb-8 text-xs">
        <li class="step step-primary">Database</li>
        <li class="step">Application</li>
        <li class="step">Admin</li>
        <li class="step">Done</li>
    </ul>

    <?php if ($error): ?>
    <div class="alert alert-error mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
        <span><?= esc($error) ?></span>
    </div>
    <?php endif ?>

    <?php $failed = array_filter($checks, fn($c) => ! $c['ok']); ?>
    <div class="card bg-base-200 shadow-xl mb-4">
        <div class="card-body gap-2 py-4">
            <div class="flex items-center justify-between mb-1">
                <span class="font-semibold text-sm">System requirements</span>
                <?php if (empty($failed)): ?>
                <span class="badge badge-success badge-sm">All passed</span>
                <?php else: ?>
                <span class="badge badge-error badge-sm"><?= count($failed) ?> issue<?= count($failed) > 1 ? 's' : '' ?></span>
                <?php endif ?>
            </div>
            <div class="grid grid-cols-2 gap-x-6 gap-y-1 text-xs">
                <?php foreach ($checks as $c): ?>
                <div class="flex items-center gap-1.5 <?= $c['ok'] ? 'text-success' : 'text-error font-semibold' ?>">
                    <span><?= $c['ok'] ? '✓' : '✗' ?></span>
                    <span><?= esc($c['label']) ?></span>
                </div>
                <?php endforeach ?>
            </div>
            <?php if (! empty($failed)): ?>
            <p class="text-xs text-warning mt-1">Fix the issues above before proceeding.</p>
            <?php endif ?>
        </div>
    </div>

    <div class="card bg-base-200 shadow-xl">
        <div class="card-body gap-5">

            <h2 class="card-title">Database connection</h2>

            <?php
                $isDsn      = ! empty($saved['dsn']);
                $savedDriver= $saved['driver']   ?? 'MySQLi';
                $savedPort  = $saved['port']     ?? 3306;
            ?>

            <!-- Mode toggle -->
            <div class="tabs tabs-box" id="db-tabs">
                <input type="radio" name="db_mode_tab" class="tab" aria-label="Connection fields" id="tab-fields" <?= ! $isDsn ? 'checked' : '' ?> />
                <input type="radio" name="db_mode_tab" class="tab" aria-label="Connection URL (DSN)" id="tab-dsn" <?= $isDsn ? 'checked' : '' ?> />
            </div>

            <form method="post" action="<?= route_to('install.database') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="mode" id="mode-input" value="<?= $isDsn ? 'dsn' : 'fields' ?>" />

                <!-- Fields panel -->
                <div id="panel-fields" class="flex flex-col gap-4">
                    <div class="grid grid-cols-2 gap-4">
                        <fieldset class="fieldset col-span-2 sm:col-span-1">
                            <legend class="fieldset-legend">Driver</legend>
                            <select name="driver" id="driver-select" class="select select-bordered w-full">
                                <option value="MySQLi" <?= $savedDriver === 'MySQLi' ? 'selected' : '' ?>>MySQL / MariaDB</option>
                                <option value="Postgre" <?= $savedDriver === 'Postgre' ? 'selected' : '' ?>>PostgreSQL</option>
                            </select>
                        </fieldset>
                        <fieldset class="fieldset col-span-2 sm:col-span-1">
                            <legend class="fieldset-legend">Port</legend>
                            <input type="number" name="port" id="port-input" class="input input-bordered w-full"
                                   value="<?= esc($savedPort) ?>" />
                        </fieldset>
                    </div>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Host</legend>
                        <input type="text" name="hostname" class="input input-bordered w-full"
                               value="<?= esc($saved['hostname'] ?? 'localhost') ?>" />
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Database name</legend>
                        <input type="text" name="database" class="input input-bordered w-full"
                               value="<?= esc($saved['database'] ?? '') ?>" placeholder="myapp" />
                    </fieldset>
                    <div class="grid grid-cols-2 gap-4">
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Username</legend>
                            <input type="text" name="username" class="input input-bordered w-full"
                                   value="<?= esc($saved['username'] ?? '') ?>" placeholder="root" />
                        </fieldset>
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend">Password</legend>
                            <input type="password" name="password" class="input input-bordered w-full"
                                   value="<?= esc($saved['password'] ?? '') ?>"
                                   placeholder="<?= ! empty($saved['password']) ? '● saved — leave blank to keep' : '••••••••' ?>" />
                        </fieldset>
                    </div>
                </div>

                <!-- DSN panel -->
                <div id="panel-dsn" class="hidden flex-col gap-4">
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend">Connection URL</legend>
                        <input type="text" name="dsn" class="input input-bordered w-full font-mono text-sm"
                               value="<?= esc($saved['dsn'] ?? '') ?>"
                               placeholder="postgres://user:pass@db.example.com:5432/mydb" />
                        <p class="fieldset-label mt-1 text-base-content/40">Supports mysql:// and postgres:// · URL-encode special chars in password</p>
                    </fieldset>
                </div>

                <div class="card-actions mt-2">
                    <button type="submit" class="btn btn-primary w-full">Test &amp; continue →</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const tabFields = document.getElementById('tab-fields');
    const tabDsn    = document.getElementById('tab-dsn');
    const panelF    = document.getElementById('panel-fields');
    const panelD    = document.getElementById('panel-dsn');
    const modeInput = document.getElementById('mode-input');
    const driverSel = document.getElementById('driver-select');
    const portInput = document.getElementById('port-input');

    function switchMode(mode) {
        modeInput.value = mode;
        panelF.classList.toggle('hidden', mode === 'dsn');
        panelF.classList.toggle('flex',   mode !== 'dsn');
        panelD.classList.toggle('hidden', mode !== 'dsn');
        panelD.classList.toggle('flex',   mode === 'dsn');
    }

    tabFields.addEventListener('change', () => switchMode('fields'));
    tabDsn.addEventListener('change',    () => switchMode('dsn'));

    driverSel.addEventListener('change', function () {
        portInput.value = this.value === 'Postgre' ? '5432' : '3306';
    });

    switchMode(document.getElementById('tab-dsn').checked ? 'dsn' : 'fields');
})();
</script>
