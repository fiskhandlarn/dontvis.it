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
    @include('partials.og')
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
              <input class="form-control" type="text" name="u" id="url" placeholder="URL you don't want to visit" value="{{ $articlePermalinkURL }}" />
            </div>
          </form>
        </div>
      </div>
    </div>
  </header>
