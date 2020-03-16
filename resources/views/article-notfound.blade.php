@extends('layouts.default', ['page' => 'not-found'])

@section('main')

    <div class="container not-found">
      <p>Looks like we couldn't find any content at <a href="{{ env('ANONYMIZER_URL') }}{{ $url }}">{{ $url }}</a>.</p>
@if ($randomURL)
      <p>Why not try this random URL someone else didn't visit?<br/>
        <a href="{{ ROOT_URL }}/{{ $randomURL['url'] }}">{{ $randomURL['title'] }}</a>
      </p>
@endif
    </div>
@stop
