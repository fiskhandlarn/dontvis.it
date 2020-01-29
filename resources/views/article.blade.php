@extends('layouts.default')

@section('main')

  <article>
    <header class="article-running">
      <div class="container">
        <div class="row">
          <div class="col-md-10">
            <h1>{{ $title }}</h1>
            <p><a href="{{ $permalink }}" rel="bookmark">{{ $permalinkWithoutScheme }}</a></p>
          </div>
          <div class="col-md-2"><button onclick="javascript:(function(){sq=window.sq=window.sq||{};if(sq.script){sq.again();}else{sq.bookmarkletVersion='0.3.0';sq.iframeQueryParams={host:'//squirt.io',userId:'8a94e519-7e9a-4939-a023-593b24c64a2f',};sq.script=document.createElement('script');sq.script.src=sq.iframeQueryParams.host+'/bookmarklet/frame.outer.js';document.body.appendChild(sq.script);}})();" class="btn btn-sm btn-primary" id="squirt">Speed read this</button></div>
        </div>
      </div>
    </header>

    <div class="container">
      <div class="row">
        <div class="col-md-8 article-body">
          {!! $body !!}
        </div>
      </div> <!-- .row -->
    </div> <!-- .container -->

    <footer class="article-running -no-margin">
      <div class="container">
        <span class="label">Source:</span> <a href="http://nullrefer.com/?{{ $url }}" class="link">{{ $url }}</a>
      </div>
    </footer>
  </article>
@stop

@section('footer')
        <p class="home"><a href="{{ ROOT_URL }}/" class="btn btn-primary" >What is {{ env('SITE_NAME') }}?</a></p>
@stop
