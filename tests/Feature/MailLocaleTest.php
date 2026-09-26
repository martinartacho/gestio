<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\Campus\EmailVerificationMail;
use App\Mail\Campus\TeacherPasswordResetMail;
use App\Models\CampusStudent;
use App\Models\CampusTeacher;
use App\Models\SiteSetting;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_uses_recipient_locale(): void
    {
        $student = CampusStudent::factory()->create(['locale' => 'es']);

        $mail = new EmailVerificationMail($student);

        $this->assertSame('es', $mail->locale);
        $mail->assertHasSubject('Tu código de verificación — '.config('app.name'));
        $mail->assertSeeInHtml('Introduce el siguiente código');
    }

    public function test_mail_falls_back_to_recipient_tenant_locale(): void
    {
        $tenant = Tenant::factory()->create();
        SiteSetting::create(['tenant_id' => $tenant->id, 'key' => 'locale', 'value' => 'es']);
        $teacher = CampusTeacher::factory()->create(['locale' => null]);
        $teacher->tenants()->sync([$tenant->id]);

        $mail = new TeacherPasswordResetMail($teacher->fresh());

        $this->assertSame('es', $mail->locale);
        $mail->assertSeeInHtml('Recuperar contraseña');
    }

    public function test_mail_stays_in_catalan_by_default(): void
    {
        $student = CampusStudent::factory()->create(['locale' => null]);

        $mail = new EmailVerificationMail($student);

        $this->assertSame('ca', $mail->locale);
        $mail->assertSeeInHtml('Introduïu el codi següent');
    }
}
