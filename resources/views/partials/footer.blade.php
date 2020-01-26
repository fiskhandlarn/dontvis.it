    <footer id="footer" class="site-footer" role="contentinfo">
      <div class="container">
        <div class="row">
          <div class="col-md-8 offset-md-2">
            @yield('footer')
          </div> <!-- .col-md-8 -->
        </div> <!-- .row -->
      </div> <!-- .container -->
    </footer>

    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
    <script type="text/javascript" >
        $(document).ready(function() {
            function stripScheme() {
                theURL = $("#uv").val();
                theURL = theURL.replace(/.*?:\/\//g, "");
                theURL = decodeURIComponent(theURL);
                $("#uv").val(theURL);
            }

            $("#uv").change(function() {
                stripScheme();
            });

            $("#uv-form").on('submit', function(event) {
                stripScheme();

                // redirect directly to permalink instead of submitting form (thus circumvent going through ?u=)
                location.replace(location.protocol + '//' + location.host + '/' + $("#uv").val());
                event.preventDefault();
                return false;
            });

            // Google Analytics (TODO is this needed?)
            $('.toplistLink a').on('click', function() {
                var a_href = $(this).attr('href');
                ga('send', 'event', 'toplist', 'click', a_href);
            });
            $('a#squirt').on('click', function(){
                ga('send', 'event', 'article', 'click', "squirt");
            });
        });
    </script>
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                                 m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA', '{{ env('SITE_NAME') }}');
        ga('require', 'linkid', 'linkid.js');
        ga('send', 'pageview');
    </script>
    <noscript><img src="http://nojsstats.appspot.com/UA/<?php echo $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];?><?php if($_SERVER['HTTP_REFERER']){echo '?r='.$_SERVER['HTTP_REFERER'];}; ?>&dummy=<?php echo rand(); ?>" /></noscript>
    <!-- Begin Creeper tracker code -->
    <a href="https://gnuheter.com/creeper/senaste" title="Creeper"><img src="https://gnuheter.com/creeper/image" alt="Creeper" width="80" height="15" border="0"/></a>
    <!-- End Creeper tracker code -->
  </body>
</html>
