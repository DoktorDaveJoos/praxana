<?php

use App\Models\Patient;
use App\Models\Practice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

test('index patients', function () {
    $user = User::factory()->create();
    $practice = $user->practice;

    $patients = Patient::factory(3)->create(['practice_hash' => $practice->getHash()]);

    actingAs($practice->users()->first());

    $this->get(route('practices.patients.index', $practice))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('patients/Index')
            ->has('patients', fn ($patientsData) => $patientsData
                ->has('data', 3)
                ->where('data.0.id', $patients[0]->id)
                ->where('data.1.id', $patients[1]->id)
                ->where('data.2.id', $patients[2]->id)
            )
        );
});

test('show patient', function () {
    $user = User::factory()->create();
    $practice = $user->practice;

    $patient = Patient::factory()->create(['practice_hash' => $practice->getHash()]);

    actingAs($user);

    $this->get(route('practices.patients.show', [$practice, $patient]))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('patients/Show')
            ->has('patient', fn ($patientData) => $patientData
                ->has('data', fn ($patientData) => $patientData
                    ->where('id', $patient->id)
                    ->where('first_name', $patient->first_name)
                    ->where('last_name', $patient->last_name)
                    ->where('name', sprintf('%s, %s', $patient->last_name, $patient->first_name))
                    ->where('email', $patient->email)
                    ->where('phone', $patient->phone)
                    ->where('address', $patient->address)
                    ->where('postal_code', $patient->postal_code)
                    ->where('city', $patient->city)
                    ->where('country', $patient->country)
                    ->where('birth_date', $patient->birth_date->format('d.m.Y'))
                    ->where('gender', $patient->gender)
                    ->where('occupation', $patient->occupation)
                    ->where('insurance_type', $patient->insurance_type)
                    ->where('insurance_name', $patient->insurance_name)
                    ->where('insurance_number', $patient->insurance_number)
                    ->where('emergency_contact', $patient->emergency_contact)
                )
            )
        );
});

test('show patient that does not belong to the practice', function () {
    $user = User::factory()->create();
    $practice = $user->practice;

    $otherPractice = Practice::factory()->create();

    $patient = Patient::factory()->create([
        'practice_hash' => $otherPractice->getHash(),
    ]);

    actingAs($user);

    $this->get(route('practices.patients.show', [$practice, $patient]))
        ->assertForbidden();
});

test('store patient', function () {
    $user = User::factory()->create();
    $practice = $user->practice;

    actingAs($user);

    $this->post(route('practices.patients.store', $practice), [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'birth_date' => '1990-01-01',
    ])->assertRedirect();

    $this->assertDatabaseHas('patients', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'birth_date' => '1990-01-01 00:00:00',
    ]);
});

test('store patient with invalid data', function () {
    $user = User::factory()->create();
    $practice = $user->practice;

    actingAs($user);

    $this->post(route('practices.patients.store', $practice), [
        'first_name' => '',
        'last_name' => '',
        'birth_date' => 'invalid-date',
    ])->assertSessionHasErrors(['first_name', 'last_name', 'birth_date']);
});

test('store patient with valid data that does not belong to the practice', function () {
    $user = User::factory()->create();
    $practice = $user->practice;

    $otherPractice = Practice::factory()->create();

    actingAs($user);

    $this->post(route('practices.patients.store', $otherPractice), [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'birth_date' => '1990-01-01',
    ])->assertForbidden();
});

test('update patient', function () {
    $user = User::factory()->create();
    $practice = $user->practice;

    $patient = Patient::factory()->create(['practice_hash' => $practice->getHash()]);

    actingAs($user);

    $this->put(route('practices.patients.update', [$practice, $patient]), [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'birth_date' => '1990-01-01',
    ])->assertRedirect();

    $this->assertDatabaseHas('patients', [
        'id' => $patient->id,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'birth_date' => '1990-01-01 00:00:00',
    ]);
});

test('update patient with invalid data', function () {
    $user = User::factory()->create();
    $practice = $user->practice;

    $patient = Patient::factory()->create(['practice_hash' => $practice->getHash()]);

    actingAs($user);

    $this->put(route('practices.patients.update', [$practice, $patient]), [
        'first_name' => '',
        'last_name' => '',
        'birth_date' => 'invalid-date',
    ])->assertSessionHasErrors(['first_name', 'last_name', 'birth_date']);
});

test('delete patient', function () {
    $user = User::factory()->create();
    $practice = $user->practice;

    $patient = Patient::factory()->create(['practice_hash' => $practice->getHash()]);

    actingAs($user);

    $this->delete(route('practices.patients.destroy', [$practice, $patient]))
        ->assertRedirect();

    $this->assertSoftDeleted('patients', [
        'id' => $patient->id,
    ]);
});

test('index patients does not show deleted', function () {
    $user = User::factory()->create();
    $practice = $user->practice;

    $patient = Patient::factory()->create(['practice_hash' => $practice->getHash()]);

    actingAs($user);

    $this->delete(route('practices.patients.destroy', [$practice, $patient]));

    $this->get(route('practices.patients.index', $practice))
        ->assertInertia(fn ($page) => $page
            ->component('patients/Index')
            ->has('patients', fn ($patientsData) => $patientsData
                ->has('data', 0)
            )
        );

    $this->assertDatabaseHas('patients', [
        'id' => $patient->id,
    ]);
});
