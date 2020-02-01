@extends('layouts.default', ['page' => 'home'])

@section('main')

    <header class="article-running home-header">
      <div class="container">
        <div class="row">
          <div class="logo">
            {{ require_image( "assets/images/favicons/favicon.svg" ) }}
          </div>
          <div class="text">
            <h1>What is {{ env('SITE_NAME') }}?</h1>
            <p>{{ env('SITE_NAME') }} is a tool to escape linkbaits, trolls, idiots and asshats.</p>
          </div>
        </div>
      </div>
    </header>

    <div class="container">
      <div class="row">
        <div class="col-md-10 col-lg-8">
          <p>This tool tries to capture the content of an article or blog post without passing on your visit as a page view. {{ env('SITE_NAME') }} also reads and displays (some) news articles otherwise only visible to "subscribers". Effectively this means that you're not paying with your attention or money, so you can <strong>read and share</strong> the idiocy that it contains.</p>

          <h2>FAQ</h2>
          <dl>
            <dt>Is this legal?</dt><dd>¯\_(ツ)_/¯</dd>
            <dt>Does it work with any website?</dt><dd>Certainly not.</dd>
            <dt>Do we track you?</dt><dd>Only through Google <del>Echelon</del> Analytics.</dd>
            <dt>Is it open source?</dt><dd><a href="{{ env('GITHUB_URL') }}">Yes.</a></dd>
           </dl>
          <p>Enjoy literally not feeding the trolls!</p>

          <h2>Usage</h2>
          <p><a href="javascript:var orig%3Dlocation.href%3Blocation.replace(%27{{ env('SITE_URL') }}%27%2Borig)%3B" class="btn btn-primary">Drag <strong>this</strong> to your bookmarks bar to {{ env('SITE_NAME') }} any page</a></p>

          <p class="manual-usage">Or just put <span class="thisurl">{{ env('SITE_URL') }}</span> in front of <span class="thaturl">http://</span>, like this:<br />
            <span class="thisurl">{{ env('SITE_URL') }}</span><span class="thaturl">http://idiot.blog.tro/ll</span></p>
        </div> <!-- .col-md-8 -->
      </div> <!-- .row -->
    </div> <!-- .container -->
@stop
