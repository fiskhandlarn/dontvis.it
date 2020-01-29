@extends('layouts.default', ['page' => 'home'])

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

          <h2>Usage</h2>
          <p><a href="javascript:var orig%3Dlocation.href%3Blocation.replace(%27{{ env('SITE_URL') }}%27%2Borig)%3B" class="btn btn-primary">Drag <strong>this</strong> to your bookmarks bar to {{ env('SITE_NAME') }} any page</a></p>

          <p class="manual-usage">Or just put <span class="thisurl">{{ env('SITE_URL') }}</span> in front of <span class="thaturl">http://</span>, like this:<br />
            <span class="thisurl">{{ env('SITE_URL') }}</span><span class="thaturl">http://idiot.blog.tro/ll</span></p>
        </div> <!-- .col-md-8 -->
      </div> <!-- .row -->
    </div> <!-- .container -->
@stop

@section('footer')
        <p class="logo"><img src="{{ ROOT_URL }}/assets/images/icon_large.png" alt="OMG LOGOTYPE" title="OMG LOGOTYPE"></p>
@stop
