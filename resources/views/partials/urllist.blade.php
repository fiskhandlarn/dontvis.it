<ul>
@foreach ($list as $li)
  <li><a href="{{ ROOT_URL }}/{{ $li['url'] }}">{{ $li['title'] }}</a></li>
@endforeach
</ul>
