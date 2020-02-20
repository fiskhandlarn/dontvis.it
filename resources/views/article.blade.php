@extends('layouts.default', ['page' => 'article'])

@section('main')

    <article>
      <header class="article-running article-header">
        <div class="container">
          <div class="row">
            <div class="text">
              <h1>{{ $title }}</h1>
              <p><a href="{{ $permalink }}" rel="bookmark">{{ $permalinkWithoutScheme }}</a></p>
            </div>
            <div class="donate">
              @include('partials.liberapay')
            </div>
          </div>
        </div>
      </header>

      <div class="container">
        <div class="row">
          <div class="col-md-10 col-lg-8 article-body">
            {!! $body !!}
          </div>
        </div>
      </div>

      <footer class="article-running -no-margin">
        <div class="container">
          <span class="label">Source:</span> <a href="{{ env('ANONYMIZER_URL') }}{{ $url }}" class="link">{{ $url }}</a>
        </div>
      </footer>
    </article>
@stop
