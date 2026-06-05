@php
    $imgs = collect($lesson->images ?? [])->where('position', $position);
@endphp
@foreach($imgs as $img)
@php
    $type    = $img['type']    ?? 'upload';
    $caption = $img['caption'] ?? '';

    if ($type === 'upload') {
        $src = Storage::url($img['path']);
        $renderAs = 'image';
    } else {
        $url = $img['url'] ?? '';
        // Detectar tipus per renderitzar
        if (preg_match('/youtube\.com\/watch\?v=([\w-]+)|youtu\.be\/([\w-]+)/', $url, $m)) {
            $ytId = $m[1] ?: $m[2];
            $renderAs = 'youtube';
        } elseif (preg_match('/vimeo\.com\/(\d+)/', $url, $m)) {
            $vimeoId = $m[1];
            $renderAs = 'vimeo';
        } elseif (preg_match('/\.(jpg|jpeg|png|gif|webp|svg)(\?.*)?$/i', $url)) {
            $src = $url;
            $renderAs = 'image';
        } elseif (preg_match('/\.pdf(\?.*)?$/i', $url)) {
            $renderAs = 'pdf';
        } else {
            $renderAs = 'link';
        }
    }
@endphp

<figure style="margin:1.5rem 0;">
    @if($renderAs === 'image')
        <img src="{{ $src }}"
             alt="{{ $caption ?: $lesson->title }}"
             style="width:100%;max-height:360px;object-fit:cover;border-radius:0.75rem;border:1px solid #e5e7eb;">

    @elseif($renderAs === 'youtube')
        <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:0.75rem;">
            <iframe src="https://www.youtube.com/embed/{{ $ytId }}"
                    style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;border-radius:0.75rem;"
                    allowfullscreen loading="lazy"></iframe>
        </div>

    @elseif($renderAs === 'vimeo')
        <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:0.75rem;">
            <iframe src="https://player.vimeo.com/video/{{ $vimeoId }}"
                    style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;border-radius:0.75rem;"
                    allowfullscreen loading="lazy"></iframe>
        </div>

    @elseif($renderAs === 'pdf')
        <a href="{{ $url }}" target="_blank" rel="noopener"
           style="display:flex;align-items:center;gap:0.75rem;padding:1rem 1.25rem;background:#fef2f2;border:1px solid #fecaca;border-radius:0.75rem;text-decoration:none;color:#991b1b;">
            <span style="font-size:1.5rem;">📄</span>
            <span style="font-size:0.9375rem;font-weight:500;">{{ $caption ?: 'Obrir document PDF' }}</span>
        </a>

    @else
        <a href="{{ $url }}" target="_blank" rel="noopener"
           style="display:flex;align-items:center;gap:0.75rem;padding:1rem 1.25rem;background:#f0f9ff;border:1px solid #bae6fd;border-radius:0.75rem;text-decoration:none;color:#0c4a6e;">
            <span style="font-size:1.5rem;">🔗</span>
            <span style="font-size:0.9375rem;font-weight:500;">{{ $caption ?: $url }}</span>
        </a>
    @endif

    @if($caption && in_array($renderAs, ['image', 'youtube', 'vimeo']))
        <figcaption style="text-align:center;font-size:0.8125rem;color:#9ca3af;margin-top:0.5rem;font-style:italic;">
            {{ $caption }}
        </figcaption>
    @endif
</figure>
@endforeach
