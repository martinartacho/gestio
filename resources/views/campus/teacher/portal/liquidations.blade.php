@extends('campus.teacher.layouts.app')

@section('title', 'Liquidacions')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Liquidacions</h1>

@if ($payments->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-500 text-sm">
        No hi ha liquidacions registrades.
    </div>
@else
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Curs</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Temporada</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Sessions</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Import brut</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Retenció</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Net</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Estat</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Pagat</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($payments as $payment)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">
                        {{ $payment->course?->title ?? '—' }}
                        @if ($payment->sessions_description)
                            <p class="text-xs text-gray-400 font-normal mt-0.5">{{ $payment->sessions_description }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $payment->course?->season?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right text-gray-600">{{ $payment->sessions_count ?? '—' }}</td>
                    <td class="px-4 py-3 text-right text-gray-600">
                        @if ($payment->gross_amount !== null)
                            {{ number_format($payment->gross_amount, 2, ',', '.') }} €
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-gray-500">
                        @if ($payment->retention_pct)
                            {{ $payment->retention_pct }}%
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-900">
                        @if ($payment->net_amount !== null)
                            {{ number_format($payment->net_amount, 2, ',', '.') }} €
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @php $color = \App\Models\CampusTeacherPayment::STATUS_COLORS[$payment->status] ?? 'gray'; @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $color === 'success' ? 'bg-green-100 text-green-700' : ($color === 'info' ? 'bg-blue-100 text-blue-700' : ($color === 'danger' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500')) }}">
                            {{ \App\Models\CampusTeacherPayment::STATUSES[$payment->status] ?? $payment->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">
                        {{ $payment->paid_at?->format('d/m/Y') ?? '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 border-t border-gray-200 font-semibold text-sm">
                <tr>
                    <td colspan="5" class="px-4 py-3 text-right text-gray-700">Total net:</td>
                    <td class="px-4 py-3 text-right text-gray-900">
                        {{ number_format($payments->sum('net_amount'), 2, ',', '.') }} €
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
@endif
@endsection
