    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="{{ env('SITE_NAME') }}" />
    <meta property="og:image" content="{{ ROOT_URL }}/assets/images/og.png" />
@if (isset($permalink))
    <meta property="og:url" content="{{ $permalink }}"/>
@else
    <meta property="og:url" content="{{ ROOT_URL }}/"/>
@endif
@if (isset($title))
    <meta property="og:title" content="{{ $title }} &ndash; {{ env('SITE_NAME') }}" />
@else
    <meta property="og:title" content="{{ env('SITE_NAME') }} &ndash; avoid endorsing idiots" />
@endif
@if (isset($excerpt))
    <meta property="og:description" content="{{ $excerpt }}" />
@else
    <meta property="og:description" content="{{ env('SITE_NAME') }} is a tool to escape linkbaits, trolls, idiots and asshats." />
@endif
