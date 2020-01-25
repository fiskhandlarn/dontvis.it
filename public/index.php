<?php
if(!ob_start("ob_gzhandler")) ob_start(); //gzip-e-di-doo-da

// remove beginning slash added by nginx(?)
$url = ltrim($_GET['u'], '/');

$hasURL = !empty($url);

if ($hasURL) {
    // don't crawl yourself
    if (strpos($url, $_SERVER['HTTP_HOST']) !== false) {
        header("Location: " . $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . '/', true, 301);
        die();
    }

    // prepend with scheme if not present
    if (!preg_match('!^https?://!i', $url)) {
        //  assume non ssl
        $url = 'http://'.$url;
    }

    // Remove scheme from bookmarklet and direct links.
    $articlePermalinkURL = preg_replace('#^https?://#', '', $url);

    $permalinkWithoutScheme = $_SERVER['HTTP_HOST'] . '/' . $articlePermalinkURL;
    $permalink = $_SERVER['REQUEST_SCHEME'] . "://" . $permalinkWithoutScheme;

    // redirect to permalink if current address isn't the same as the wanted permalink
    if (ltrim($_SERVER['REQUEST_URI'], '/') !== $articlePermalinkURL) {
        header("Location: " . $permalink, true, 303);
        die();
    }
} else {
    // default to homepage
    $articlePermalinkURL = false;
    $permalinkWithoutScheme = $_SERVER['HTTP_HOST'] . '/';
    $permalink = $_SERVER['REQUEST_SCHEME'] . "://" . $permalinkWithoutScheme;
}

use Readability\Readability;
?>
<!DOCTYPE HTML>
<html>
<head>
	<meta charset="UTF-8">
	<title><?php if ($url) { echo 'UV : '.$url;} else { echo "unvis.it – avoid endorsing idiots";} ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<link rel="stylesheet" type="text/css" media="screen" href="/assets/css/bootstrap.min.css" />
	<!--[if IE]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/assets/img/apple-touch-icon-144-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="/assets/img/apple-touch-icon-114-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="/assets/img/apple-touch-icon-72-precomposed.png">
	<link rel="apple-touch-icon-precomposed" href="/assets/img/apple-touch-icon-57-precomposed.png">
	<link rel="shortcut icon" href="/assets/img/favicon.png">
	<script type="text/javascript">
	window.google_analytics_uacct = "UA";
	</script>
</head>
<body>
    <header id="head">
	    <div class="container">
			<div class="row">
				<br>
				<div class="col-md-2"></div>
				<div class="col-md-8" id="theInputForm">
					<form class="form-inline" id="uv-form">
					    <div class="form-group">
					        <label class="sr-only" for="exampleInputAmount">Amount (in dollars)</label>
					        <div class="input-group">
					            <div class="input-group-addon"><a href="http://unvis.it" id="logo" ><strong>unvis.it/</strong></a> </div>
					            <input class="form-control" type="text" name="u" id="uv" placeholder="URL you want to read without giving a pageview" value="<?php if ($articlePermalinkURL) { echo $articlePermalinkURL;} ?>" >
					        </div>
					    </div>

					</form>

					<hr>
				</div>
				<div class="col-md-2"></div>
			</div>
		</div> <!-- .container -->
    </header>

    <main id="main" role="main">
	    <div class="container">
		    <div class="row">
<?php
if ($hasURL) {
?>
			    <div class="col-md-2"><a href="javascript:(function(){sq=window.sq=window.sq||{};if(sq.script){sq.again();}else{sq.bookmarkletVersion='0.3.0';sq.iframeQueryParams={host:'//squirt.io',userId:'8a94e519-7e9a-4939-a023-593b24c64a2f',};sq.script=document.createElement('script');sq.script.src=sq.iframeQueryParams.host+'/bookmarklet/frame.outer.js';document.body.appendChild(sq.script);}})();" class="btn btn-default btn-mini hidden-phone" style="position: relative;top: 20px;" id="squirt">Speed read this</a></div>
                <article id="theContent" class="article col-md-8">
<?php

    require_once "includes/dbhandler.php";
    $db = new DBHandler();
    list($title, $body) = $db->read($articlePermalinkURL);

    if (!$title){
        // no cache, let's fetch the article

        //var_dump("Fetching article ...");

        // User agent switcheroo
        $UAnum = Rand (0,3) ;

        switch ($UAnum) {
            case 0:
                // TODO DN seems to restrict content if crawled from Google
                $UAstring = "User-Agent: Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)\r\n";
                break;

            case 1:
                $UAstring = "Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)\r\n";
                break;

            case 2:
                $UAstring = "Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)\r\n";
                break;

            case 3:
                $UAstring = "Baiduspider+(+http://www.baidu.com/search/spider.htm)  \r\n";
                break;

                // If this works, many lolz acquired.
        }

        //$UAstring = "User-Agent: Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)\r\n";
        //$UAstring = "Mozilla/5.0 (compatible; Yahoo! Slurp; http://help.yahoo.com/help/us/ysearch/slurp)\r\n";
        //$UAstring = "Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)\r\n";
        //$UAstring = "Baiduspider+(+http://www.baidu.com/search/spider.htm)  \r\n";

        // Create a stream
        $opts = array(
            'http'=>array(
                'method'=>"GET",
                'header'=>$UAstring
            )
        );

        $context = stream_context_create($opts);
        $html = @file_get_contents($url, false, $context);

        if ($html) {
            require_once 'includes/Readability.php';
            require_once 'includes/JSLikeHTMLElement.php';

            // PHP Readability works with UTF-8 encoded content.
            // If $html is not UTF-8 encoded, use iconv() or
            // mb_convert_encoding() to convert to UTF-8.

            // If we've got Tidy, let's clean up input.
            // This step is highly recommended - PHP's default HTML parser
            // often does a terrible job and results in strange output.
            if (function_exists('tidy_parse_string')) {
                $tidy = tidy_parse_string($html, array(), 'UTF8');
                $tidy->cleanRepair();
                $html = $tidy->value;
            }

            // give it to Readability
            $readability = new Readability($html, $url);

            // print debug output?
            // useful to compare against Arc90's original JS version -
            // simply click the bookmarklet with FireBug's
            // console window open
            $readability->debug = false;

            // convert links to footnotes?
            $readability->convertLinksToFootnotes = true;

            // process it
            $result = $readability->init();

            // does it look like we found what we wanted?
            if ($result) {
                $title = $readability->getTitle()->textContent;

                $content = $readability->getContent()->innerHTML;

                // if we've got Tidy, let's clean it up for output
                if (function_exists('tidy_parse_string')) {
                    $tidy = tidy_parse_string($content,
                                              array('indent'=>true, 'show-body-only'=>true),
                                              'UTF8');
                    $tidy->cleanRepair();
                    $content = $tidy->value;
                    $content = trim(preg_replace('/\s\s+/', ' ', $content));
                }

                $body = $content;

                /* $toCache = "<div id=\"theContent\" class=\"col-md-8\">";
                 * $toCache .= $header.$content;
                 * $toCache .= "</div>";*/

                // save to db
                $db->cache($articlePermalinkURL, $title, $body);
            }
        } else {
        }
    }
    // else {
    //     var_dump("From cache:");
	// }

    if ($title && $body) {
        $header = "<h1>";
        $header .= $title;
        $header .= '</h1><a href="' . $permalink . '" class="perma" rel="bookmark">' . $permalinkWithoutScheme . '</a>';
        $header .=  "<hr>";
        echo $header;
        echo $body;
    } else {
        echo "<p>Looks like we couldn't find the content ¯\_(ツ)_/¯</p>";
    }
?>

			    </article>
			    <div class="col-md-2"></div>
<?php } ?>
	        </div> <!-- .row -->
	    </div> <!-- .container -->
	</main>

    <footer id="footer" class="site-footer" role="contentinfo">
		<div class="container">
			<div class="row">
				<div class="col-md-2"></div>
				<div class="col-md-8">
<?php if ($url) {?><hr><?php }?>
<?php if ( $url) {?>
	                <small><em><b>Source:</b> <a href="http://nullrefer.com/?<?php echo $url; ?>"><?php echo $url; ?></a></em></small>
					<hr>

					<p style="text-align:center"><a href="/" class="btn btn-default" >What is unvis.it?</a></p>
					<br><br>
<?php } else {?>
	<?php //require_once('uv/ga/toplist.php');?>

					<h1 id="about">What is unvis.it?</h1>
					<p>Unvis.it is a tool to escape linkbaits, trolls, idiots and asshats. </p>
					<p>What the tool does is to try to capture the content of an article or blog post without passing on your visit as a pageview. Effectively this means that you're not paying with your attention, so you can <strong>read and share</strong> the idiocy that it contains.</p>
					<p><small>Now with a speed reading options from <a href="http://www.squirt.io/">Squirt</a>, so you can get dumbfounded quicker!</small></p>
					<br>
					<p><b>FAQ:</b>
						<ul>
							<li><b>Is this legal?</b> Probably not. </li>
							<li><b>Does it work with any website?</b> Certainly not. </li>
							<li><b>Do we track you?</b> Only through Google <del>Echelon</del> Analytics.</li>
							<li><b>Is it open source?</b> <a href="https://github.com/phixofor/unvis.it">Sure, why not?</a></li>
							<li><b>I heard someone made a Firefox add-on?</b> <a href="https://addons.mozilla.org/en-US/firefox/addon/unvisit/">Indeed!</a></li>
							<li><b>I need anonymous file hosting?</b> Check out <a href="http://minfil.org">Minfil.org</a></li>
						</ul>
					    <p>Enjoy literally not feeding the trolls!</p>
					    <br>
					    <p style="text-align:center"> <a href="javascript:var orig%3Dlocation.href%3Blocation.replace(%27http://unvis.it/%27%2Borig)%3B" class="btn btn-sm btn-info">Drag <b>this</b> to your bookmarks bar to unvis.it any page</a></p>
					    <hr>
					    <h2>Now: the same info in infographics</h2>
					    <p style="text-align:center;"><img src="/assets/img/unvisit-xplaind.png" alt="What's this, I don't even…" title="What's this, I don't even…" ></p>
					    <hr>
					    <p style="text-align:center">
						    <img src="/assets/img/icon_large.png" alt="OMG LOGOTYPE" title="OMG LOGOTYPE" style="width:150px;height:150px">
						    <br><br><br>
						    <?php //<a href="http://www.lolontai.re"><img src="/assets/img/lulz.png" id="lulz" alt="Sir Lulz-a-Lot approves" title="Sir Lulz-a-Lot approves"></a>?>
						    <br><br><br><br><br><br><br><br>
					    </p>
<?php } ?>
				</div> <!-- .col-md-8 -->
				<div class="col-md-2"></div>
			</div> <!-- .row -->
		</div> <!-- .container -->
	</footer>

	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
	<script type="text/javascript" src="/assets/js/bootstrap.min.js"></script>
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

	  ga('create', 'UA', 'unvis.it');
	  ga('require', 'linkid', 'linkid.js');
	  ga('send', 'pageview');



	</script>
	<noscript><img src="http://nojsstats.appspot.com/UA/<?php echo $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];?><?php if($_SERVER['HTTP_REFERER']){echo '?r='.$_SERVER['HTTP_REFERER'];}; ?>&dummy=<?php echo rand(); ?>" /></noscript>
    <!-- Begin Creeper tracker code -->
    <a href="https://gnuheter.com/creeper/senaste" title="Creeper"><img src="https://gnuheter.com/creeper/image" alt="Creeper" width="80" height="15" border="0"/></a>
    <!-- End Creeper tracker code -->

</body>
</html>
