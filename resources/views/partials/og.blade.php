    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="{{ env('SITE_NAME') }}" />
    <meta property="og:image" content="{{ ROOT_URL }}/assets/images/favicons/android-chrome-512x512.png" />
@if (isset($title))
    <meta property="og:url" content="{{ $permalink }}"/>
    <meta property="og:title" content="{{ $title }} &ndash; {{ env('SITE_NAME') }}" />
    <meta property="og:description" content="{{ $excerpt }}" />
@else
    <meta property="og:url" content="{{ ROOT_URL }}/"/>
    <meta property="og:title" content="{{ env('SITE_NAME') }} &ndash; avoid endorsing idiots" />
    <meta property="og:description" content="{{ env('SITE_NAME') }} is a tool to escape linkbaits, trolls, idiots and asshats." />
@endif
