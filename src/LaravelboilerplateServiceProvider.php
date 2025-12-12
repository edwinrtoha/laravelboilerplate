<?php

namespace Edwinrtoha\Laravelboilerplate;

use Composer\InstalledVersions;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Contracts\Role as RoleContract;

class LaravelboilerplateServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->offerPublishing();
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     */
    protected function getMigrationFileName(string $migrationFileName, int $offset = 0): string
    {
        $timestamp = date('Y_m_d_His', time() + $offset);

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make([$this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR])
            ->flatMap(fn ($path) => $filesystem->glob($path.'*_'.$migrationFileName))
            ->push($this->app->databasePath()."/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }

    protected function offerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        if (! function_exists('config_path')) {
            // function not available and 'publish' not relevant in Lumen
            return;
        }

        $this->publishes([
            __DIR__.'/database/migrations/create_endpoint_has_permissions_table.php.stub' => $this->getMigrationFileName('create_endpoint_has_permissions_table.php'),
            __DIR__.'/database/migrations/create_workflows_table.php.stub' => $this->getMigrationFileName('create_workflows_table.php', 1),
            __DIR__.'/database/migrations/create_workflow_states_table.php.stub' => $this->getMigrationFileName('create_workflow_states_table.php', 2),
            __DIR__.'/database/migrations/create_workflow_transitions_table.php.stub' => $this->getMigrationFileName('create_workflow_transitions_table.php', 3),
            __DIR__.'/database/migrations/create_workflow_histories_table.php.stub' => $this->getMigrationFileName('create_workflow_histories_table.php', 4),
            __DIR__.'/database/migrations/set_nullable_to_field_from_state_id_in_workflow_transitions_table.php.stub' => $this->getMigrationFileName('set_nullable_to_field_from_state_id_in_workflow_transitions_table.php', 5),
            __DIR__.'/database/migrations/add_notes_to_workflow_histories_table.php.stub' => $this->getMigrationFileName('add_notes_to_workflow_histories_table.php', 6),
            __DIR__.'/database/migrations/add_idx_to_workflow_transitions_table.php.stub' => $this->getMigrationFileName('add_idx_to_workflow_transitions_table.php', 7),
            __DIR__.'/database/migrations/create_workflow_transition_permissions_table.php.stub' => $this->getMigrationFileName('create_workflow_transition_permissions_table.php', 8),
        ], 'endpoint-has-permissions-migrations');
    }
}
