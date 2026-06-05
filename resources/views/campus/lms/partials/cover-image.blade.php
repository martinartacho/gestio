@php $imgs = collect($lesson->images ?? [])->where('position', $position); @endphp
@if($imgs->isNotEmpty())
    @foreach($imgs as $img)
    <figure style="margin:1.5rem 0;">
        <img src="{{ Storage::url($img['path']) }}"
             alt="{{ $img['caption'] ?? $lesson->title }}"
             style="width:100%;max-height:360px;object-fit:cover;border-radius:0.75rem;border:1px solid #e5e7eb;">
        @if(!empty($img['caption']))
            <figcaption style="text-align:center;font-size:0.8125rem;color:#9ca3af;margin-top:0.5rem;font-style:italic;">
                {{ $img['caption'] }}
            </figcaption>
        @endif
    </figure>
    @endforeach
@endif
