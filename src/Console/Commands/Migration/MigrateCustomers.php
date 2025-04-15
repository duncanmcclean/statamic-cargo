<?php

namespace DuncanMcClean\Cargo\Console\Commands\Migration;

use DuncanMcClean\Cargo\Contracts\Cart\Cart as CartContract;
use DuncanMcClean\Cargo\Facades\Discount;
use DuncanMcClean\Cargo\Facades\Cart;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Statamic\Console\RunsInPlease;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Statamic;
use stdClass;
use function Laravel\Prompts\progress;

class MigrateCustomers extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:cargo:migrate:customers';

    protected $description = 'Migrates customers from Simple Commerce to Cargo.';

    public function handle(): void
    {
        $repository = Str::afterLast(config('simple-commerce.content.customers.repository'), "\\");

        if ($repository === 'EntryCustomerRepository') {
            $this->migrateCustomerBlueprint();
            $this->migrateCustomersFromUsers();

            return;
        }

        if ($repository === 'EloquentCustomerRepository') {
            $this->migrateCustomerBlueprint();
            $this->migrateCustomersFromEloquent();

            return;
        }

        if ($repository === 'UserCustomerRepository') {
            $this->removeOrdersArrayFromUsers();

            return;
        }

        $this->components->error("You're using a custom customer repository. You need to migrate your customers manually.");
    }

    private function migrateCustomerBlueprint(): self
    {
        $blueprint = match(Str::afterLast(config('simple-commerce.content.customers.repository'), "\\")) {
            'EntryCustomerRepository' => Collection::find(config('simple-commerce.content.customers.collection'))?->entryBlueprint(),
            'EloquentCustomerRepository' => Blueprint::find('runway::customers'),
        };

        $fieldHandles = $blueprint->fields()->all()->map->handle();

        $additionalFieldHandles = $fieldHandles->except([
            'name', 'first_name', 'last_name', 'email', 'orders', 'roles', 'groups', 'super',
        ])->all();

        $userBlueprint = Blueprint::find('user');
        $additionalFields = $blueprint->fields()->items()->whereIn('handle', $additionalFieldHandles)->values();

        if ($additionalFields->isEmpty()) {
            return $this;
        }

        $userBlueprint
            ->setContents(array_filter([
                'title' => $userBlueprint->title(),
                'tabs' => [
                    ...$userBlueprint->contents()['tabs'] ?? [],
                    'additional' => [
                        'sections' => [[
                            'fields' => $additionalFields->all(),
                        ]],
                    ],
                ],
            ]))
            ->save();

        $this->components->info("Copied custom fields from the [{$blueprint->handle()}] blueprint to the [user] blueprint.");

        return $this;
    }

    private function migrateCustomersFromUsers(): self
    {
        $entries = Entry::query()
            ->where('collection', config('simple-commerce.content.customers.collection'))
            ->lazy();

        progress(
            label: 'Migrating customers',
            steps: $entries,
            callback: function (EntryContract $entry) {
                $data = $entry->data()->merge(['id' => $entry->id()]);

                $this->createUserFromData($data)->save();
            },
            hint: 'This may take some time.'
        );

        $this->components->info('Customers migrated successfully.');

        return $this;
    }

    private function migrateCustomersFromEloquent(): self
    {
        $rows = DB::table('customers')->orderBy('id')->lazy();

        progress(
            label: 'Migrating customers',
            steps: $rows,
            callback: function (stdClass $row) {
                $data = collect($row)
                    ->reject(fn ($value, string $key) => in_array($key, ['data']))
                    ->merge(Json::decode($row->data));

                $this->createUserFromData($data)->save();
            },
            hint: 'This may take some time.'
        );

        $this->components->info('Customers migrated successfully.');

        return $this;
    }

    private function removeOrdersArrayFromUsers(): self
    {
        User::query()
            ->whereNotNull('orders')
            ->chunk(100, function ($users) {
                $users->each(function ($user) {
                    $user->remove('orders')->save();
                });
            });

        $this->components->info("Removed the [orders] array from user data. It is now a computed field.");

        return $this;
    }

    private function createUserFromData(\Illuminate\Support\Collection $data): UserContract
    {
        $blueprint = Blueprint::find('user');

        if (
            ($fullName = $data->get('name'))
            && ! $blueprint->hasField('name')
            && $blueprint->hasField('first_name')
            && $blueprint->hasField('last_name')
        ) {
            $data->put('first_name', Str::before($fullName, ' '));
            $data->put('last_name', Str::contains($fullName, ' ') ? Str::after($fullName, ' ') : null);
            $data->forget('name');
        }

        return (User::find($data->get('id')) ?? User::make())
            ->id($data->get('id'))
            ->email($data->get('email'))
            ->data($data->except([
                'id',
                'email',
                'orders',
                'site',
                'title',
                'blueprint',
                'created_at',
                'updated_at',
            ]));
    }
}