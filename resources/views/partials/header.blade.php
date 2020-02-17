<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>@if (isset($title)){{ $title }} &ndash; @endif{{ env('SITE_NAME') }}@if (!isset($title)) &ndash; avoid endorsing idiots @endif</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <link rel="stylesheet" type="text/css" media="screen" href="{{ ROOT_URL }}/assets/styles/app.css" />
    <meta name="theme-color" content="#3b6ea5">
    @include('partials.favicons')
    <meta property="og:site_name" content="{{ env('SITE_NAME') }}" />
    <meta property="og:image" content="{{ ROOT_URL }}/assets/images/favicons/android-chrome-512x512.png" />
    @if (isset($title))
<meta property="og:type" content="article" />
    <meta property="og:url" content="{{ $permalink }}"/>
    <meta property="og:title" content="{{ $title }} &ndash; {{ env('SITE_NAME') }}" />
    <meta property="og:description" content="{{ $excerpt }}" />
    @else
<meta property="og:type" content="website" />
    <meta property="og:url" content="{{ ROOT_URL }}/"/>
    <meta property="og:title" content="{{ env('SITE_NAME') }} &ndash; avoid endorsing idiots" />
    <meta property="og:description" content="{{ env('SITE_NAME') }} is a tool to escape linkbaits, trolls, idiots and asshats." />
    @endif
</head>
  <body>
    <header role="banner" class="site-header">
      <div class="container">
        <div class="row">
          <div class="col">
            <form id="url-form" action="{{ ROOT_URL }}/">
              <div class="input-group">
                <div class="input-group-prepend">
                  <div class="input-group-text"><a class="form-label" href="{{ ROOT_URL }}/">{{--<span class="logo">{{ require_image( "assets/images/favicons/favicon.svg" ) }}</span>  --}}{{ env('SITE_NAME') }}/</a></div>
                </div>
                <input class="form-control" type="text" name="u" id="url" placeholder="URL you want to read without giving a pageview" value="{{ $articlePermalinkURL }}" />
              </div>
            </form>
          </div>
        </div>
      </div>
    </header>
