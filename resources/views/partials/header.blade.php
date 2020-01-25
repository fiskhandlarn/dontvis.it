<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title><?php if ($title) { echo env('SITE_SHORT_NAME') . ' : '.$title;} else { echo env('SITE_NAME') . " &ndash; avoid endorsing idiots";} ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <link rel="stylesheet" type="text/css" media="screen" href="{{ ROOT_URL }}/assets/css/bootstrap.min.css" />
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="{{ ROOT_URL }}/assets/images/favicons/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="{{ ROOT_URL }}/assets/images/favicons/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="{{ ROOT_URL }}/assets/images/favicons/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="{{ ROOT_URL }}/assets/images/favicons/apple-touch-icon-57-precomposed.png">
    <link rel="shortcut icon" href="{{ ROOT_URL }}/assets/images/favicons/favicon.png">
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
                  <input class="form-control" type="text" name="u" id="uv" placeholder="URL you want to read without giving a pageview" value="<?php if ($articlePermalinkURL) { echo $articlePermalinkURL;} ?>" >
                </div>
              </div>
            </form>
            <hr>
          </div>
          <div class="col-md-2"></div>
        </div>
      </div> <!-- .container -->
    </header>
