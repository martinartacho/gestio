<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Mail\Campus\QueueConfirmationMail;
use App\Models\CampusQueueEntry;
use App\Settings\SettingStore;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class QueueController extends Controller
{
    public function join(): View|RedirectResponse
    {
        $settings = app(SettingStore::class);

        if (! $settings->get('queue_enabled', false)) {
            return redirect()->route('campus.catalog.index');
        }

        return view('campus.queue.join', compact('settings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $settings = app(SettingStore::class);

        if (! $settings->get('queue_enabled', false)) {
            return redirect()->route('campus.catalog.index');
        }

        $request->validate(['email' => ['required', 'email', 'max:150']]);

        $email = strtolower(trim($request->input('email')));

        // Si ja és a la cua, redirigir a l'estat
        $existing = CampusQueueEntry::where('email', $email)->first();
        if ($existing) {
            return redirect()->route('campus.queue.status', ['email' => $email])
                ->with('info', 'Ja esteu a la cua. Aquí teniu el vostre estat.');
        }

        // Número seqüencial + càlcul del torn
        $nextNumber   = (CampusQueueEntry::max('queue_number') ?? 0) + 1;
        $batchSize    = max(1, (int) $settings->get('queue_batch_size', 10));
        $slotMinutes  = max(1, (int) $settings->get('queue_slot_minutes', 15));
        $queueStart   = Carbon::parse($settings->get('queue_start_at'));
        $slotIndex    = CampusQueueEntry::slotIndexFor($nextNumber, $batchSize);
        $slotStartsAt = $queueStart->copy()->addMinutes($slotIndex * $slotMinutes);

        $entry = CampusQueueEntry::create([
            'email'          => $email,
            'queue_number'   => $nextNumber,
            'slot_starts_at' => $slotStartsAt,
            'status'         => CampusQueueEntry::STATUS_WAITING,
        ]);

        Mail::to($email)->send(new QueueConfirmationMail($entry));

        return redirect()->route('campus.queue.status', ['email' => $email])
            ->with('success', 'Torn #' . $nextNumber . ' reservat! Confirmació enviada a ' . $email . '.');
    }

    public function status(Request $request): View|RedirectResponse
    {
        $email = $request->query('email', '');
        $entry = CampusQueueEntry::where('email', $email)->first();

        if (! $entry) {
            return redirect()->route('campus.queue.join')
                ->with('error', 'No s\'ha trobat cap entrada a la cua per a aquest correu.');
        }

        return view('campus.queue.status', compact('entry'));
    }

    public function enterCode(Request $request): RedirectResponse
    {
        // Normalitzar: treure espais (l'email mostra "652 KSD" però el camp pot tenir espai copiat)
        $raw = strtoupper(str_replace(' ', '', trim($request->input('code', ''))));
        $request->merge(['code' => $raw]);

        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $code  = $raw;
        $entry = CampusQueueEntry::where('access_code', $code)
            ->where('status', CampusQueueEntry::STATUS_NOTIFIED)
            ->first();

        if (! $entry || ! $entry->canAccess()) {
            return back()->withErrors(['code' => 'Codi incorrecte, caducat o ja utilitzat.']);
        }

        $entry->update(['status' => CampusQueueEntry::STATUS_ACCESSED, 'accessed_at' => now()]);

        $request->session()->put('queue_access_token', $code);
        $request->session()->put('queue_access_expires', $entry->access_expires_at->toISOString());

        return redirect()->route('campus.catalog.index')
            ->with('success', '✓ Accés concedit fins a les ' . $entry->access_expires_at->format('H:i') . ' h.');
    }
}
