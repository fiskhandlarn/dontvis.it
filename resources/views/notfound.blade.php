@extends('layouts.default', ['page' => 'not-found'])

@section('main')

    <div class="container not-found">
      <p>Looks like we couldn't find the content ¯\_(ツ)_/¯</p>
    </div> <!-- .container -->
@stop

@section('footer')
        <p class="home"><a href="{{ ROOT_URL }}/" class="btn btn-primary" >What is {{ env('SITE_NAME') }}?</a></p>
@stop
