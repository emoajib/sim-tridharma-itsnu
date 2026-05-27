<?php

namespace Tests\Feature\Http;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

abstract class BaseCrudTestCase extends TestCase
{
    use RefreshDatabase, SeedOnce;

    abstract protected function routePrefix(): string;

    abstract protected function modelClass(): string;

    abstract protected function defaultStoreData(): array;

    abstract protected function defaultUpdateData(): array;

    abstract protected function createRecord();

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    protected function noPermissionUser(): User
    {
        return User::factory()->create(['is_active' => true]);
    }

    protected function assertPersisted(array $data): void
    {
        $model = new ($this->modelClass());
        $table = $model->getTable();
        $casts = $model->getCasts();

        $dbData = Arr::except($data, ['file', '_token', 'password', 'password_confirmation']);

        foreach ($dbData as $key => $value) {
            if (isset($casts[$key]) && in_array($casts[$key], ['array', 'json'], true)) {
                $dbData[$key] = json_encode($value);
            }
            if (isset($casts[$key]) && in_array($casts[$key], ['date', 'datetime'], true)) {
                $dbData[$key] = Carbon::parse($value)->toDateTimeString();
            }
        }

        $this->assertDatabaseHas($table, $dbData);
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route($this->routePrefix()));

        $response->assertStatus(302);
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $response = $this->actingAs($this->noPermissionUser())->get(route($this->routePrefix()));

        $response->assertStatus(403);
    }

    public function test_authorized_user_can_access_index(): void
    {
        $response = $this->actingAs($this->admin())->get(route($this->routePrefix()));

        $response->assertStatus(200);
    }

    public function test_store_creates_record_and_redirects(): void
    {
        $data = $this->defaultStoreData();

        $response = $this->actingAs($this->admin())
            ->from(route($this->routePrefix()))
            ->post(route($this->routePrefix().'.store'), $data);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertPersisted($data);
    }

    public function test_update_updates_record_and_redirects(): void
    {
        $record = $this->createRecord();
        $data = $this->defaultUpdateData();

        $response = $this->actingAs($this->admin())
            ->from(route($this->routePrefix()))
            ->put(route($this->routePrefix().'.update', $record), $data);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $record->fresh();
        $this->assertPersisted($data);
    }

    public function test_destroy_soft_deletes_record(): void
    {
        $record = $this->createRecord();

        $response = $this->actingAs($this->admin())
            ->delete(route($this->routePrefix().'.destroy', $record));

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        if (in_array(SoftDeletes::class, class_uses_recursive($record))) {
            $this->assertSoftDeleted($record);
        } else {
            $this->assertModelMissing($record);
        }
    }
}
