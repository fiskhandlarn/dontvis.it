@extends('layouts.default')

@section('main')

    <div class="container">
      <div class="row">
        <div class="col-md-2"></div>
        <div class="col-md-8">
          <h1 id="about">What is <?php echo env('SITE_NAME'); ?>?</h1>
          <p><?php echo env('SITE_NAME'); ?> is a tool to escape linkbaits, trolls, idiots and asshats.</p>
          <p>What the tool does is to try to capture the content of an article or blog post without passing on your visit as a pageview. Effectively this means that you're not paying with your attention, so you can <strong>read and share</strong> the idiocy that it contains.</p>
          <p><small>Now with a speed reading options from <a href="http://www.squirt.io/">Squirt</a>, so you can get dumbfounded quicker!</small></p>
          <br>
          <p><b>FAQ:</b></p>
          <ul>
            <li><b>Is this legal?</b> Probably not. </li>
            <li><b>Does it work with any website?</b> Certainly not. </li>
            <li><b>Do we track you?</b> Only through Google <del>Echelon</del> Analytics.</li>
            <li><b>Is it open source?</b> <a href="<?php echo env('GITHUB_URL'); ?>">Sure, why not?</a></li>
          </ul>
          <p>Enjoy literally not feeding the trolls!</p>
          <br>
          <p style="text-align:center"> <a href="javascript:var orig%3Dlocation.href%3Blocation.replace(%27<?php echo env('SITE_URL'); ?>%27%2Borig)%3B" class="btn btn-sm btn-info">Drag <b>this</b> to your bookmarks bar to <?php echo env('SITE_NAME'); ?> any page</a></p>
          <hr>
          <h2>Now: the same info in infographics</h2>
          <p style="text-align:center;"><img src="<?php echo ROOT_URL; ?>/assets/images/site-xplaind.png" alt="What's this, I don't even ..." title="What's this, I don't even ..." ></p>
        </div> <!-- .col-md-8 -->
      </div> <!-- .row -->
    </div> <!-- .container -->
@stop

@section('footer')

        <hr>
        <p style="text-align:center">
          <img src="<?php echo ROOT_URL; ?>/assets/images/icon_large.png" alt="OMG LOGOTYPE" title="OMG LOGOTYPE" style="width:150px;height:150px">
          <br><br><br><br><br><br><br><br>
        </p>
@stop
