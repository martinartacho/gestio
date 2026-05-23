<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // ── Dades del Campus ────────────────────────────────────────────
            'campus_name'           => 'Campus',
            'campus_tagline'        => 'Aprèn. Creix. Transforma.',
            'campus_logo_url'       => null,
            'campus_favicon_url'    => null,
            'campus_contact_email'  => null,
            'campus_contact_phone'  => null,
            'campus_address'        => null,

            // ── Aparença ────────────────────────────────────────────────────
            'hero_title'            => 'Benvingut/da al Campus',
            'hero_subtitle'         => 'Descobreix la nostra oferta formativa.',
            'hero_color'            => '#4f46e5',    // indigo-600
            'hero_text_color'       => '#ffffff',

            // ── Correu electrònic ────────────────────────────────────────────
            'mail_from_name'        => 'Campus',
            'mail_from_address'     => null,
            'mail_footer_text'      => null,

            // ── Mòduls / Feature flags ───────────────────────────────────────
            'documents_enabled'     => true,
            'lms_enabled'           => false,
            'courses_learning_enabled' => false,

            // ── Pagament manual ──────────────────────────────────────────────
            'payment_transfer_enabled' => false,
            'payment_bizum_enabled'    => false,
            'payment_cash_enabled'     => false,
            'payment_paypal_enabled'   => false,
            'payment_iban'             => null,
            'payment_bank_holder'      => null,
            'payment_bizum_number'     => null,
            'payment_paypal_email'     => null,
            'payment_concept_template' => '{NOM} - {CURS}',

            // ── Avançat ──────────────────────────────────────────────────────
            'timezone'              => 'Europe/Madrid',
            'locale'                => 'ca',
        ];

        foreach ($defaults as $key => $value) {
            SiteSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
