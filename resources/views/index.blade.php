@extends('layouts.default')

@section('main')

    <header class="article-running">
      <div class="container">
        <div class="row">
          <div class="col-md-10">
            <h1>What is {{ env('SITE_NAME') }}?</h1>
            <p>{{ env('SITE_NAME') }} is a tool to escape linkbaits, trolls, idiots and asshats.</p>
          </div>
        </div>
      </div>
    </header>

    <div class="container">
      <div class="row">
        <div class="col-md-8">
          <p>This tool tries to capture the content of an article or blog post without passing on your visit as a page view. {{ env('SITE_NAME') }} also reads and displays some news articles otherwise only visible to "subscribers". Effectively this means that you're not paying with your attention or money, so you can <strong>read and share</strong> the idiocy that it contains.</p>
          <p><b>FAQ:</b></p>
          <ul>
            <li><b>Is this legal?</b> Probably not.</li>
            <li><b>Does it work with any website?</b> Certainly not.</li>
            <li><b>Do we track you?</b> Only through Google <del>Echelon</del> Analytics.</li>
            <li><b>Is it open source?</b> <a href="{{ env('GITHUB_URL') }}">Yes.</a></li>
          </ul>
          <p>Enjoy literally not feeding the trolls!</p>
          <p class="bookmarklet"> <a href="javascript:var orig%3Dlocation.href%3Blocation.replace(%27{{ env('SITE_URL') }}%27%2Borig)%3B" class="btn btn-primary">Drag <b>this</b> to your bookmarks bar to {{ env('SITE_NAME') }} any page</a></p>

          <h2>Now: the same info in infographics</h2>
          <p style="text-align:center;"><img src="{{ ROOT_URL }}/assets/images/site-xplaind.png" alt="What's this, I don't even ..." title="What's this, I don't even ..." ></p>
        </div> <!-- .col-md-8 -->
      </div> <!-- .row -->
    </div> <!-- .container -->
@stop

@section('footer')
        <p style="text-align:center">
          <img src="{{ ROOT_URL }}/assets/images/icon_large.png" alt="OMG LOGOTYPE" title="OMG LOGOTYPE" style="width:150px;height:150px">
        </p>
@stop
