@extends('layouts.default', ['page' => 'article'])

@section('main')

  <article>
    <header class="article-running">
      <div class="container">
        <div class="row">
          <div class="col-md-10">
            <h1>{{ $title }}</h1>
            <p><a href="{{ $permalink }}" rel="bookmark">{{ $permalinkWithoutScheme }}</a></p>
          </div>
        </div>
      </div>
    </header>

    <div class="container">
      <div class="row">
        <div class="col-md-10 col-lg-8 article-body">
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
