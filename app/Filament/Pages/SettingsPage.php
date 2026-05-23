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
    public bool $documents_enabled          = true;
    public bool $lms_enabled                = false;
    public bool $courses_learning_enabled   = false;

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

        $this->documents_enabled        = (bool) $store->get('documents_enabled', true);
        $this->lms_enabled              = (bool) $store->get('lms_enabled', false);
        $this->courses_learning_enabled = (bool) $store->get('courses_learning_enabled', false);

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

            'documents_enabled'        => $this->documents_enabled,
            'lms_enabled'              => $this->lms_enabled,
            'courses_learning_enabled' => $this->courses_learning_enabled,

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

            'timezone' => $this->timezone,
            'locale'   => $this->locale,
        ]);

        Notification::make()
            ->title('Configuració desada correctament')
            ->success()
            ->send();
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
