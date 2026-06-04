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
            'campus_enabled'             => true,
            'documents_enabled'          => true,
            'lms_enabled'                => false,
            'courses_learning_enabled'   => false,
            'cataleg_enabled'            => true,
            'cataleg_periodes_enabled'   => true,
            'cataleg_categories_enabled' => true,
            'cataleg_espais_enabled'     => true,
            'cataleg_franges_enabled'    => true,
            'campus_cursos_enabled'      => true,
            'campus_professorat_enabled' => true,
            'tresoreria_enabled'              => true,
            'tresoreria_inscripcions_enabled' => true,
            'tresoreria_pagaments_enabled'    => true,
            'tresoreria_liquidacions_enabled' => true,
            'tresoreria_alumnes_enabled'      => true,
            'tresoreria_ips_enabled'          => true,
            'tresoreria_quotes_socis_enabled' => true,
            'tresoreria_sepa_socis_enabled'   => true,
            'gestio_enabled'               => true,
            'gestio_administracio_enabled' => true,
            'gestio_calendari_enabled'     => true,

            // ── Associats ────────────────────────────────────────────────────
            'associats_enabled'        => false,
            'associats_socis_enabled'  => true,
            'associats_quotes_enabled' => true,
            'associats_sepa_enabled'   => true,
            'associats_org_name'       => 'Entitat',
            'associats_member_prefix'  => '',
            'associat_quota_amount'    => 0,
            'associat_quota_concept'   => 'Quota anual soci {YEAR}',

            // ── SEPA creditor de l'entitat ────────────────────────────────────
            'sepa_creditor_id'  => '',
            'sepa_org_name'     => '',
            'sepa_iban'         => '',
            'sepa_bic'          => '',

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
            'payment_expiry_value'     => 5,
            'payment_expiry_unit'      => 'days',

            // ── Cua d'inscripcions ────────────────────────────────────────────
            'queue_enabled'               => false,
            'queue_start_at'              => null,
            'queue_batch_size'            => 10,
            'queue_slot_minutes'          => 15,
            'queue_access_window_minutes' => 30,

            // ── Avançat ──────────────────────────────────────────────────────
            'timezone'              => 'Europe/Madrid',
            'locale'                => 'ca',
        ];

        foreach ($defaults as $key => $value) {
            SiteSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
