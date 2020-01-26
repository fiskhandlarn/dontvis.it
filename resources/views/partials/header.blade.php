<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>
      @if ($title)
        {{ env('SITE_SHORT_NAME') }} : {{ $title}}
      @else
        {{ env('SITE_NAME') }} &ndash; avoid endorsing idiots
      @endif
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <link rel="stylesheet" type="text/css" media="screen" href="{{ ROOT_URL }}/assets/styles/app.css" />
    <script type="text/javascript">
        window.google_analytics_uacct = "UA";
    </script>
  </head>
  <body>
    <header id="head">
      <div class="container">
        <div class="row">
          <br>
          <div class="col-md-2"></div>
          <div class="col-md-8" id="theInputForm">
            <form class="form-inline" id="uv-form">
              <div class="form-group">
                <div class="input-group">
                  <div class="input-group-addon"><a href="{{ ROOT_URL }}/" id="logo" ><strong>{{ env('SITE_NAME') }}/</strong></a> </div>
                  <input class="form-control" type="text" name="u" id="uv" placeholder="URL you want to read without giving a pageview" value="{{ $articlePermalinkURL }}" />
                </div>
              </div>
            </form>
            <hr>
          </div>
          <div class="col-md-2"></div>
        </div>
      </div> <!-- .container -->
    </header>
