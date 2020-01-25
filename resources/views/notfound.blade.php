@extends('layouts.default')

@section('main')

      <div class="container">
        <div class="row">
          <div class="col-md-2"></div>
          <article id="theContent" class="article col-md-8">
            <p>Looks like we couldn't find the content ¯\_(ツ)_/¯</p>
          </article>
          <div class="col-md-2"></div>
        </div> <!-- .row -->
      </div> <!-- .container -->
@stop

@section('footer')

        <hr>

        <p style="text-align:center"><a href="{{ $ROOT_URL }}/" class="btn btn-default" >What is {{ env('SITE_NAME') }}?</a></p>
        <br><br>
@stop
