<?php

use App\Models\Practice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

test('show practice', function () {
    $practice = Practice::factory()->create();
    $user = User::factory()->create([
        'practice_id' => $practice->id,
    ]);

    actingAs($user);

    $this->get(route('practices.show', $practice))
        ->assertInertia(fn (Assert $page) => $page
            ->component('practice/Show')
            ->has('practice', fn (Assert $page) => $page
                ->has('data', fn (Assert $page) => $page
                    ->where('id', $practice->id)
                    ->where('name', $practice->name)
                    ->where('address', $practice->address)
                    ->where('postal_code', $practice->postal_code)
                    ->where('city', $practice->city)
                    ->where('country', $practice->country)
                    ->where('phone', $practice->phone)
                    ->where('email', $practice->email)
                )
            )
        );
});

test('update practice', function () {
    $practice = Practice::factory()->create();
    $user = User::factory()->create([
        'practice_id' => $practice->id,
    ]);

    actingAs($user);

    $this->put(route('practices.update', $practice->id), [
        'name' => 'Updated Practice',
    ])->assertRedirect(route('practices.show', $practice));

    $this->assertDatabaseHas('practices', [
        'id' => $practice->id,
        'name' => 'Updated Practice',
        'address' => $practice->address,
    ]);
});
