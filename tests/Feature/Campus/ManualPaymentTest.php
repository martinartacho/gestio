<?php

namespace Tests\Feature\Campus;

use App\Models\CampusCourse;
use App\Models\CampusEnrollment;
use App\Models\CampusSeason;
use App\Models\CampusStudent;
use App\Settings\SettingStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ManualPaymentTest extends TestCase
{
    use RefreshDatabase;

    private CampusStudent $student;
    private CampusCourse  $course;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->app->forgetInstance(SettingStore::class);

        $this->student = CampusStudent::factory()->create();

        // Temporada activa amb dates presents/futures per evitar que isPast() bloquegi
        $season = CampusSeason::factory()->create([
            'status'     => 'active',
            'start_date' => now()->subMonth()->format('Y-m-d'),
            'end_date'   => now()->addMonths(6)->format('Y-m-d'),
        ]);

        $this->course = CampusCourse::factory()->create([
            'season_id'       => $season->id,
            'status'          => 'active',
            'is_public'       => true,
            'price'           => 120.00,
            'open_enrollment' => false,
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function enablePaymentMethod(string $method): void
    {
        app(SettingStore::class)->set("payment_{$method}_enabled", true);
        $this->app->forgetInstance(SettingStore::class);
        Cache::flush();
    }

    private function postCheckout(array $data = []): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->student, 'student')
            ->post(route('campus.checkout.create', $this->course->slug), array_merge(
                ['payment_method' => 'transfer'],
                $data
            ));
    }

    // ─── Tests ───────────────────────────────────────────────────────────────

    /** @test */
    public function test_enrolled_student_sees_payment_method_selector(): void
    {
        $this->enablePaymentMethod('transfer');
        $this->enablePaymentMethod('bizum');

        $this->actingAs($this->student, 'student')
            ->get(route('campus.catalog.show', $this->course->slug))
            ->assertSuccessful()
            ->assertSee('payment_method')
            ->assertSee('Transferència bancària')
            ->assertSee('Bizum');
    }

    /** @test */
    public function test_manual_enrollment_transfer_creates_pending(): void
    {
        $this->enablePaymentMethod('transfer');

        $this->postCheckout(['payment_method' => 'transfer'])
            ->assertSuccessful();

        $this->assertDatabaseHas('campus_enrollments', [
            'student_id'     => $this->student->id,
            'course_id'      => $this->course->id,
            'status'         => 'pending',
            'payment_method' => 'transfer',
        ]);
    }

    /** @test */
    public function test_manual_enrollment_shows_payment_instructions(): void
    {
        $this->enablePaymentMethod('transfer');
        app(SettingStore::class)->setMany([
            'payment_iban'        => 'ES76 0049 3000 1014 1021 5229',
            'payment_bank_holder' => 'Associació Campus Test',
        ]);
        $this->app->forgetInstance(SettingStore::class);

        $this->postCheckout(['payment_method' => 'transfer'])
            ->assertSuccessful()
            ->assertSee('Transferència bancària')
            ->assertSee('ES76 0049 3000 1014 1021 5229')
            ->assertSee('Associació Campus Test');
    }

    /** @test */
    public function test_bizum_enrollment_shows_phone_number(): void
    {
        $this->enablePaymentMethod('bizum');
        app(SettingStore::class)->set('payment_bizum_number', '612 345 678');
        $this->app->forgetInstance(SettingStore::class);

        $this->postCheckout(['payment_method' => 'bizum'])
            ->assertSuccessful()
            ->assertSee('Bizum')
            ->assertSee('612 345 678');
    }

    /** @test */
    public function test_free_course_auto_enrolls_as_paid(): void
    {
        $freeCourse = CampusCourse::factory()->create([
            'season_id' => $this->course->season_id,
            'status'    => 'active',
            'is_public' => true,
            'price'     => 0,
        ]);

        $this->actingAs($this->student, 'student')
            ->post(route('campus.checkout.create', $freeCourse->slug), [
                'payment_method' => 'free',
            ])
            ->assertRedirect(route('campus.portal.courses'));

        $this->assertDatabaseHas('campus_enrollments', [
            'student_id'     => $this->student->id,
            'course_id'      => $freeCourse->id,
            'status'         => 'paid',
            'payment_method' => 'free',
            'amount'         => 0,
        ]);
    }

    /** @test */
    public function test_disabled_method_rejected_by_controller(): void
    {
        // transfer NO habilitat (per defecte false)
        $this->postCheckout(['payment_method' => 'transfer'])
            ->assertRedirect(route('campus.catalog.show', $this->course->slug));

        $this->assertDatabaseMissing('campus_enrollments', [
            'student_id' => $this->student->id,
            'course_id'  => $this->course->id,
        ]);
    }

    /** @test */
    public function test_disabled_method_not_shown_in_form(): void
    {
        // Cap mètode habilitat → no apareixen radio buttons de mètodes manuals
        $this->actingAs($this->student, 'student')
            ->get(route('campus.catalog.show', $this->course->slug))
            ->assertSuccessful()
            ->assertDontSee('Transferència bancària')
            ->assertDontSee('Bizum');
    }

    /** @test */
    public function test_double_enrollment_is_prevented(): void
    {
        $this->enablePaymentMethod('transfer');

        // Primera inscripció → crea enrollment
        $this->postCheckout(['payment_method' => 'transfer'])
            ->assertSuccessful();

        $this->assertDatabaseCount('campus_enrollments', 1);

        // Segona inscripció → redirecció al show del curs
        $this->actingAs($this->student, 'student')
            ->post(route('campus.checkout.create', $this->course->slug), [
                'payment_method' => 'transfer',
            ])
            ->assertRedirect(route('campus.catalog.show', $this->course->slug));

        // Continua havent-hi un únic registre
        $this->assertDatabaseCount('campus_enrollments', 1);
    }
}
