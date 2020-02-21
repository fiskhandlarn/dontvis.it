@extends('layouts.default', ['page' => 'not-found'])

@section('main')

    <div class="container not-found">
      <p>Looks like we couldn't find any content at <a href="{{ env('ANONYMIZER_URL') }}{{ $url }}">{{ $url }}</a>.</p>
    </div>
@stop