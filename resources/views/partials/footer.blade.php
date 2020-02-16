    <footer class="site-footer" role="contentinfo">
      <div class="container">
        <div class="row">
          <div class="home">
            <a href="{{ ROOT_URL }}/" class="home-button"><span class="logo">{{ require_image( "assets/images/favicons/favicon.svg" ) }}</span> <span class="label">{{ env('SITE_NAME') }}</span></a>
          </div>
          <div class="creeper">
            <!-- Begin Creeper tracker code -->
            <a href="https://gnuheter.com/creeper/senaste" title="Creeper"><img src="{{ ROOT_URL }}/assets/images/creeper.png" /></a>
            <!-- End Creeper tracker code -->
          </div>
        </div> <!-- .row -->
      </div> <!-- .container -->
    </footer>

    <script async src="https://www.googletagmanager.com/gtag/js?id={{ env('GOOGLE_ANALYTICS_ID') }}"></script>
    <script src="//unpkg.com/@beyonk/gdpr-cookie-consent-banner/dist/browser/bundle.min.js"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.slim.min.js"></script>
    <script type="text/javascript" >
        $(document).ready(function() {
          function stripScheme() {
            theURL = $("#url").val();
            theURL = theURL.replace(/.*?:\/\//g, "");
            theURL = decodeURIComponent(theURL);
            $("#url").val(theURL);
          }

          $("#url").change(function() {
            stripScheme();
          });

          $("#url-form").on('submit', function(event) {
            stripScheme();

            // redirect directly to permalink instead of submitting form (thus circumvent going through ?u=)
            window.location.href = location.protocol + '//' + location.host + '/' + $("#url").val();
            event.preventDefault();
            return false;
          });
        });
    </script>
    <script>
        var options = {
          cookieName: '{{ env('SITE_NAME')}}_gdpr',
          description: 'We use cookies to offer a better browsing experience and analyze site traffic. By clicking <strong>accept</strong> you consent to the use of cookies.',
          acceptLabel: 'Accept',
          settingsLabel: 'Settings',
          choices: {
            necessary: {
              label: 'Necessary cookies',
              description: "Used for cookie control. Can't be turned off.",
              value: true
            },
            tracking: false,
            marketing: false
          },
          showEditIcon: false,
          categories: {
            analytics: function() {
              window.dataLayer = window.dataLayer || [];
              function gtag(){dataLayer.push(arguments);}
              gtag('js', new Date());
              gtag('config', '{{ env('GOOGLE_ANALYTICS_ID') }}', {'cookie_expires': 31536000});
            },
            necessary: function() {}
          }
        }
        GdprConsent.attachBanner(document.body, options)
    </script>
  </body>
</html>
