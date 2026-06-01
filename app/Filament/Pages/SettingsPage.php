<?php

namespace App\Filament\Pages;

use App\Settings\SettingStore;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SettingsPage extends Page
{
    protected string $view = 'filament.pages.settings-page';

    public static function getNavigationIcon(): string  { return 'heroicon-o-cog-6-tooth'; }
    public static function getNavigationLabel(): string { return 'Configuració'; }
    public static function getNavigationGroup(): string { return 'Sistema'; }
    public static function getNavigationSort(): int     { return 100; }

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('admin') ?? false;
    }

    public function getTitle(): string { return 'Configuració del lloc'; }

    // ─── Propietats del formulari ─────────────────────────────────────────

    // Tab: Dades del Campus
    public string $campus_name          = '';
    public string $campus_tagline       = '';
    public string $campus_logo_url      = '';
    public string $campus_favicon_url   = '';
    public string $campus_contact_email = '';
    public string $campus_contact_phone = '';
    public string $campus_address       = '';

    // Tab: Aparença
    public string $hero_title      = '';
    public string $hero_subtitle   = '';
    public string $hero_color      = '#4f46e5';
    public string $hero_text_color = '#ffffff';

    // Tab: Correu electrònic
    public string $mail_from_name    = '';
    public string $mail_from_address = '';
    public string $mail_footer_text  = '';

    // Tab: Mòduls
    public bool $campus_enabled             = true;
    public bool $documents_enabled          = true;
    public bool $lms_enabled                = false;
    public bool $courses_learning_enabled   = false;
    public bool $cataleg_enabled            = true;
    public bool $cataleg_periodes_enabled   = true;
    public bool $cataleg_categories_enabled = true;
    public bool $cataleg_espais_enabled     = true;
    public bool $cataleg_franges_enabled    = true;
    public bool $tresoreria_enabled              = true;
    public bool $tresoreria_inscripcions_enabled = true;
    public bool $tresoreria_pagaments_enabled    = true;
    public bool $tresoreria_liquidacions_enabled = true;
    public bool $tresoreria_alumnes_enabled      = true;
    public bool $tresoreria_ips_enabled          = true;
    public bool $tresoreria_administracio_enabled = true;

    // Tab: Associats
    public bool   $associats_enabled        = false;
    public bool   $associats_socis_enabled  = true;
    public bool   $associats_quotes_enabled = true;
    public bool   $associats_sepa_enabled   = true;
    public string $associats_org_name       = 'AC Granollers';
    public string $associats_member_prefix  = '';

    // Creditor SEPA de l'entitat
    public string $sepa_creditor_id   = '';
    public string $sepa_org_name      = '';
    public string $sepa_iban          = '';
    public string $sepa_bic           = '';

    // Tab: Pagament manual
    public bool   $payment_transfer_enabled = false;
    public bool   $payment_bizum_enabled    = false;
    public bool   $payment_cash_enabled     = false;
    public bool   $payment_paypal_enabled   = false;
    public string $payment_iban             = '';
    public string $payment_bank_holder      = '';
    public string $payment_bizum_number     = '';
    public string $payment_paypal_email     = '';
    public string $payment_concept_template = '{NOM} - {CURS}';
    public int    $payment_expiry_value     = 5;
    public string $payment_expiry_unit      = 'days';

    // Tab: Cua d'inscripcions
    public bool   $queue_enabled               = false;
    public string $queue_start_at              = '';
    public int    $queue_batch_size            = 10;
    public int    $queue_slot_minutes          = 15;
    public int    $queue_access_window_minutes = 30;

    // Tab: Avançat
    public string $timezone = 'Europe/Madrid';
    public string $locale   = 'ca';

    // ─── Tab activa ───────────────────────────────────────────────────────
    public string $activeTab = 'campus';

    // ─── Mount ───────────────────────────────────────────────────────────

    public function mount(): void
    {
        /** @var SettingStore $store */
        $store = app(SettingStore::class);

        $this->campus_name          = (string) $store->get('campus_name', '');
        $this->campus_tagline       = (string) $store->get('campus_tagline', '');
        $this->campus_logo_url      = (string) $store->get('campus_logo_url', '');
        $this->campus_favicon_url   = (string) $store->get('campus_favicon_url', '');
        $this->campus_contact_email = (string) $store->get('campus_contact_email', '');
        $this->campus_contact_phone = (string) $store->get('campus_contact_phone', '');
        $this->campus_address       = (string) $store->get('campus_address', '');

        $this->hero_title      = (string) $store->get('hero_title', '');
        $this->hero_subtitle   = (string) $store->get('hero_subtitle', '');
        $this->hero_color      = (string) $store->get('hero_color', '#4f46e5');
        $this->hero_text_color = (string) $store->get('hero_text_color', '#ffffff');

        $this->mail_from_name    = (string) $store->get('mail_from_name', '');
        $this->mail_from_address = (string) $store->get('mail_from_address', '');
        $this->mail_footer_text  = (string) $store->get('mail_footer_text', '');

        $this->campus_enabled             = (bool) $store->get('campus_enabled', true);
        $this->documents_enabled          = (bool) $store->get('documents_enabled', true);
        $this->lms_enabled                = (bool) $store->get('lms_enabled', false);
        $this->courses_learning_enabled   = (bool) $store->get('courses_learning_enabled', false);
        $this->cataleg_enabled            = (bool) $store->get('cataleg_enabled', true);
        $this->cataleg_periodes_enabled   = (bool) $store->get('cataleg_periodes_enabled', true);
        $this->cataleg_categories_enabled = (bool) $store->get('cataleg_categories_enabled', true);
        $this->cataleg_espais_enabled     = (bool) $store->get('cataleg_espais_enabled', true);
        $this->cataleg_franges_enabled    = (bool) $store->get('cataleg_franges_enabled', true);
        $this->tresoreria_enabled              = (bool) $store->get('tresoreria_enabled', true);
        $this->tresoreria_inscripcions_enabled = (bool) $store->get('tresoreria_inscripcions_enabled', true);
        $this->tresoreria_pagaments_enabled    = (bool) $store->get('tresoreria_pagaments_enabled', true);
        $this->tresoreria_liquidacions_enabled = (bool) $store->get('tresoreria_liquidacions_enabled', true);
        $this->tresoreria_alumnes_enabled      = (bool) $store->get('tresoreria_alumnes_enabled', true);
        $this->tresoreria_ips_enabled          = (bool) $store->get('tresoreria_ips_enabled', true);
        $this->tresoreria_administracio_enabled = (bool) $store->get('tresoreria_administracio_enabled', true);

        $this->associats_enabled        = (bool) $store->get('associats_enabled', false);
        $this->associats_socis_enabled  = (bool) $store->get('associats_socis_enabled', true);
        $this->associats_quotes_enabled = (bool) $store->get('associats_quotes_enabled', true);
        $this->associats_sepa_enabled   = (bool) $store->get('associats_sepa_enabled', true);
        $this->associats_org_name       = (string) $store->get('associats_org_name', 'AC Granollers');
        $this->associats_member_prefix  = (string) $store->get('associats_member_prefix', '');

        $this->sepa_creditor_id = (string) $store->get('sepa_creditor_id', '');
        $this->sepa_org_name    = (string) $store->get('sepa_org_name', '');
        $this->sepa_iban        = (string) $store->get('sepa_iban', '');
        $this->sepa_bic         = (string) $store->get('sepa_bic', '');

        $this->payment_transfer_enabled = (bool)   $store->get('payment_transfer_enabled', false);
        $this->payment_bizum_enabled    = (bool)   $store->get('payment_bizum_enabled', false);
        $this->payment_cash_enabled     = (bool)   $store->get('payment_cash_enabled', false);
        $this->payment_paypal_enabled   = (bool)   $store->get('payment_paypal_enabled', false);
        $this->payment_iban             = (string) $store->get('payment_iban', '');
        $this->payment_bank_holder      = (string) $store->get('payment_bank_holder', '');
        $this->payment_bizum_number     = (string) $store->get('payment_bizum_number', '');
        $this->payment_paypal_email     = (string) $store->get('payment_paypal_email', '');
        $this->payment_concept_template = (string) $store->get('payment_concept_template', '{NOM} - {CURS}');
        $this->payment_expiry_value     = (int)    $store->get('payment_expiry_value', 5);
        $this->payment_expiry_unit      = (string) $store->get('payment_expiry_unit', 'days');

        $this->queue_enabled               = (bool)   $store->get('queue_enabled', false);
        $this->queue_start_at              = (string) $store->get('queue_start_at', '');
        $this->queue_batch_size            = (int)    $store->get('queue_batch_size', 10);
        $this->queue_slot_minutes          = (int)    $store->get('queue_slot_minutes', 15);
        $this->queue_access_window_minutes = (int)    $store->get('queue_access_window_minutes', 30);

        $this->timezone = (string) $store->get('timezone', 'Europe/Madrid');
        $this->locale   = (string) $store->get('locale', 'ca');
    }

    // ─── Desar ───────────────────────────────────────────────────────────

    public function save(): void
    {
        /** @var SettingStore $store */
        $store = app(SettingStore::class);

        $store->setMany([
            'campus_name'          => $this->campus_name,
            'campus_tagline'       => $this->campus_tagline,
            'campus_logo_url'      => $this->campus_logo_url ?: null,
            'campus_favicon_url'   => $this->campus_favicon_url ?: null,
            'campus_contact_email' => $this->campus_contact_email ?: null,
            'campus_contact_phone' => $this->campus_contact_phone ?: null,
            'campus_address'       => $this->campus_address ?: null,

            'hero_title'      => $this->hero_title,
            'hero_subtitle'   => $this->hero_subtitle,
            'hero_color'      => $this->hero_color,
            'hero_text_color' => $this->hero_text_color,

            'mail_from_name'    => $this->mail_from_name,
            'mail_from_address' => $this->mail_from_address ?: null,
            'mail_footer_text'  => $this->mail_footer_text ?: null,

            'campus_enabled'             => $this->campus_enabled,
            'documents_enabled'          => $this->documents_enabled,
            'lms_enabled'                => $this->lms_enabled,
            'courses_learning_enabled'   => $this->courses_learning_enabled,
            'cataleg_enabled'            => $this->cataleg_enabled,
            'cataleg_periodes_enabled'   => $this->cataleg_periodes_enabled,
            'cataleg_categories_enabled' => $this->cataleg_categories_enabled,
            'cataleg_espais_enabled'     => $this->cataleg_espais_enabled,
            'cataleg_franges_enabled'    => $this->cataleg_franges_enabled,
            'tresoreria_enabled'               => $this->tresoreria_enabled,
            'tresoreria_inscripcions_enabled'  => $this->tresoreria_inscripcions_enabled,
            'tresoreria_pagaments_enabled'     => $this->tresoreria_pagaments_enabled,
            'tresoreria_liquidacions_enabled'  => $this->tresoreria_liquidacions_enabled,
            'tresoreria_alumnes_enabled'       => $this->tresoreria_alumnes_enabled,
            'tresoreria_ips_enabled'           => $this->tresoreria_ips_enabled,
            'tresoreria_administracio_enabled' => $this->tresoreria_administracio_enabled,

            'associats_enabled'        => $this->associats_enabled,
            'associats_socis_enabled'  => $this->associats_socis_enabled,
            'associats_quotes_enabled' => $this->associats_quotes_enabled,
            'associats_sepa_enabled'   => $this->associats_sepa_enabled,
            'associats_org_name'       => $this->associats_org_name ?: 'AC Granollers',
            'associats_member_prefix'  => $this->associats_member_prefix,

            'sepa_creditor_id' => $this->sepa_creditor_id,
            'sepa_org_name'    => $this->sepa_org_name,
            'sepa_iban'        => $this->sepa_iban,
            'sepa_bic'         => $this->sepa_bic,

            'payment_transfer_enabled' => $this->payment_transfer_enabled,
            'payment_bizum_enabled'    => $this->payment_bizum_enabled,
            'payment_cash_enabled'     => $this->payment_cash_enabled,
            'payment_paypal_enabled'   => $this->payment_paypal_enabled,
            'payment_iban'             => $this->payment_iban ?: null,
            'payment_bank_holder'      => $this->payment_bank_holder ?: null,
            'payment_bizum_number'     => $this->payment_bizum_number ?: null,
            'payment_paypal_email'     => $this->payment_paypal_email ?: null,
            'payment_concept_template' => $this->payment_concept_template ?: '{NOM} - {CURS}',
            'payment_expiry_value'     => max(0, $this->payment_expiry_value),
            'payment_expiry_unit'      => in_array($this->payment_expiry_unit, ['hours', 'days'])
                                            ? $this->payment_expiry_unit
                                            : 'days',

            'queue_enabled'               => $this->queue_enabled,
            'queue_start_at'              => $this->queue_start_at ?: null,
            'queue_batch_size'            => max(1, $this->queue_batch_size),
            'queue_slot_minutes'          => max(1, $this->queue_slot_minutes),
            'queue_access_window_minutes' => max(5, $this->queue_access_window_minutes),

            'timezone' => $this->timezone,
            'locale'   => $this->locale,
        ]);

        // Forçar neteja de caché per garantir que el sidebar s'actualitzi
        \Illuminate\Support\Facades\Cache::forget('site_settings.all');

        Notification::make()
            ->title('Configuració desada correctament')
            ->success()
            ->send();

        $this->js('window.location.reload()');
    }

    public function getTimezoneOptions(): array
    {
        $zones = [];
        foreach (timezone_identifiers_list() as $tz) {
            $zones[$tz] = $tz;
        }
        return $zones;
    }

    public function getLocaleOptions(): array
    {
        return [
            'ca' => 'Català',
            'es' => 'Castellà',
            'en' => 'Anglès',
        ];
    }
}
