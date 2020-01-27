@extends('layouts.default')

@section('main')

      <div class="container">
        <div class="row">
          <article class="col-md-8 offset-md-2">
            <p>Looks like we couldn't find the content ¯\_(ツ)_/¯</p>
          </article>
        </div> <!-- .row -->
      </div> <!-- .container -->
@stop

@section('footer')
        <p class="home"><a href="{{ ROOT_URL }}/" class="btn btn-primary" >What is {{ env('SITE_NAME') }}?</a></p>
@stop
