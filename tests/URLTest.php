<?php

namespace Dontvisit;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use DOMDocument;

class URLTest extends TestCase
{
    private static $client;
    private static $host;

    public static function setUpBeforeClass(): void
    {
        if (file_exists('/.dockerenv')) {
            self::$host = 'https://172.17.0.1:3000/';
        } else {
            self::$host = 'https://127.0.0.1:3000/';
        }

        echo "Using " . self::$host . "\n";

        self::$client = new Client([
            'base_uri' => self::$host,
            'verify' => false,
            'http_errors' => false,
        ]);
    }

    public function test200()
    {
        foreach ([
            '',
            '.gitignore',
            '.php',
            'assets/images/creeper.png',
            'favicon.ico',
            'index.php',
            'index.php/',
            'index.php?u=',
            'index/',
            'robots.txt',
        ] as $url) {
            $response = self::$client->request('GET', $url);
            $this->assertEquals(200, $response->getStatusCode(), $url);
        }

        // test for no redirection
        $response = self::$client->request('GET', urlencode('github.com/fiskhandlarn/dontvis.it'));
        $this->assertEquals(200, $response->getStatusCode(), urlencode('github.com/fiskhandlarn/dontvis.it'));
    }

    public function testScrape()
    {
        $response = self::$client->request('GET', urlencode('github.com/fiskhandlarn/dontvis.it'));
        $this->assertEquals('fiskhandlarn/dontvis.it: dontvis.it, the idiot circumventor tool – dontvis.it', $this->getTitle($response->getBody()->getContents()));
    }

    public function test301()
    {
        $response = self::$client->request('GET', urlencode(self::$host), ['allow_redirects' => false]);
        $this->assertEquals(301, $response->getStatusCode(), urlencode(self::$host));
    }

    public function test303()
    {
        $response = self::$client->request('GET', urlencode('https://github.com/fiskhandlarn/dontvis.it'), ['allow_redirects' => false]);
        $this->assertEquals(303, $response->getStatusCode(), urlencode('https://github.com/fiskhandlarn/dontvis.it'));
    }

    public function test303InternationalizedTLD()
    {
        // test that this doesn't result in 404
        // (use url with scheme to prevent logging unsuccessful scrape)
        $response = self::$client->request('GET', urlencode('http://dontvis.xn--ygbi2ammx'), ['allow_redirects' => false]);
        $this->assertEquals(303, $response->getStatusCode(), urlencode('dontvis.xn--ygbi2ammx'));
    }

    public function test404Length()
    {
        $response = self::$client->request('GET', 'abc');
        $this->assertEquals(404, $response->getStatusCode(), 'abc');
    }

    public function test404Punctuation()
    {
        foreach ([
            '!.gitignore',
            '!.htaccess',
            '!.htpasswd',
            '$RECYCLE.BIN/',
            '%23.configuration.php%23',
            '%23config.inc.php%23',
            '%23config.php%23',
            '%23index.php%23',
            '%23localsettings.php%23',
            '%23settings.php%23',
            '%23web.config%23',
            '.git/HEAD',
            '.htaccess',
            '?a=fetch&content=%3Cphp%3Edie(@md5(HelloThinkCMF))%3C/php%3E',
            '?homescreen=1',
            '?s=/Index/\think\app/invokefunction&function=call_user_func_array&vars[0]=md5&vars[1][]=HelloThinkPHP',
            '?XDEBUG_SESSION_START=phpstorm',
        ] as $url) {
            $response = self::$client->request('GET', $url);
            $this->assertEquals(404, $response->getStatusCode(), $url);
        }
    }

    public function test404Hosts()
    {
        foreach ([
            'index.php?u=%0DSplitting:Detectify',
            'index.php?u=%0D%0ASplitting:Detectify',
            urlencode('Splitting:Detectify'),
        ] as $url) {
            $response = self::$client->request('GET', $url);
            $this->assertEquals(404, $response->getStatusCode(), $url);
        }
    }

    public function test404Domains()
    {
        foreach ([
            '/a2billing/customer/templates/default/footer.tpl',
            'a2billing/customer/templates/default/footer.tpl',
            'abc.d',
            'abcd',
            'abcd.',
            'TP/public/index.php',
            urlencode('localhost:3000/'),
        ] as $url) {
            $response = self::$client->request('GET', $url);
            $this->assertEquals(404, $response->getStatusCode(), $url);
        }
    }

    public function test404NumberTLDs()
    {
        foreach ([
            'phpMyAdmin-2.5.5-pl1/',
            'phpMyAdmin-2.5.5-rc1/',
            'phpMyAdmin-2.5.5-rc2/',
            'phpMyAdmin-2.5.6-rc1/',
            'phpMyAdmin-2.5.6-rc2/',
            'phpMyAdmin-2.5.7-pl1/',
            'phpMyAdmin-2.6.0-alpha/',
            'phpMyAdmin-2.6.0-alpha2/',
            'phpMyAdmin-2.6.0-beta1/',
            'phpMyAdmin-2.6.0-beta2/',
            'phpMyAdmin-2.6.0-pl1/',
            'phpMyAdmin-2.6.0-pl2/',
            'phpMyAdmin-2.6.0-pl3/',
            'phpMyAdmin-2.6.0-rc1/',
            'phpMyAdmin-2.6.0-rc2/',
            'phpMyAdmin-2.6.0-rc3/',
            'phpMyAdmin-2.6.1-pl1/',
            'phpMyAdmin-2.6.1-pl2/',
            'phpMyAdmin-2.6.1-pl3/',
            'phpMyAdmin-2.6.1-rc1/',
            'phpMyAdmin-2.6.1-rc2/',
            'phpMyAdmin-2.6.2-beta1/',
            'phpMyAdmin-2.6.2-pl1/',
            'phpMyAdmin-2.6.2-rc1/',
            'phpMyAdmin-2.6.3-pl1/',
            'phpMyAdmin-2.6.3-rc1/',
            'phpMyAdmin-2.6.4-pl1/',
            'phpMyAdmin-2.6.4-pl2/',
            'phpMyAdmin-2.6.4-pl3/',
            'phpMyAdmin-2.6.4-pl4/',
            'phpMyAdmin-2.6.4-rc1/',
            'phpMyAdmin-2.7.0-beta1/',
            'phpMyAdmin-2.7.0-pl1/',
            'phpMyAdmin-2.7.0-pl2/',
            'phpMyAdmin-2.7.0-rc1/',
            'phpMyAdmin-2.8.0-beta1/',
            'phpMyAdmin-2.8.0-rc1/',
            'phpMyAdmin-2.8.0-rc2/',
            'phpMyAdmin-2.8.1-rc1/',
        ] as $url) {
            $response = self::$client->request('GET', $url);
            $this->assertEquals(404, $response->getStatusCode(), $url);
        }
    }

    public function test404Tilde()
    {
        foreach ([
            'web.config~',
            'configuration.php~',
        ] as $url) {
            $response = self::$client->request('GET', $url);
            $this->assertEquals(404, $response->getStatusCode(), $url);
        }
    }

    public function test404FileExtensions()
    {
        foreach ([
            '0000000000000.cfg',
            '2012.tar.gz',
            '2013.sql',
            '2013.tar.gz',
            '2014.tar.gz',
            '2014.tgz',
            '2014.zip',
            '2015.tar.gz',
            '2016.tar.gz',
            '2016.zip',
            '2017.tar.gz',
            '2018.tar',
            '2018.tar.gz',
            '403.jsp',
            '582ee3472c68446f9d0f2bc1922609fc.asp',
            '582ee3472c68446f9d0f2bc1922609fc.aspx',
            '582ee3472c68446f9d0f2bc1922609fc.cfm',
            '582ee3472c68446f9d0f2bc1922609fc.cgi',
            '582ee3472c68446f9d0f2bc1922609fc.css',
            '582ee3472c68446f9d0f2bc1922609fc.js',
            '582ee3472c68446f9d0f2bc1922609fc.jsp',
            '582ee3472c68446f9d0f2bc1922609fc.swf',
            '582ee3472c68446f9d0f2bc1922609fc.txt',
            '582ee3472c68446f9d0f2bc1922609fc.xml',
            'About.aspx',
            'abstract.jsp',
            'AddEditCourierPage.aspx',
            'adm_auth.aspx',
            'admin.cfm',
            'admin.jsp',
            'admin.pac',
            'admin.py',
            'admin2.asp',
            'admin_area.asp',
            'admincontrol.asp',
            'administrator.cfm',
            'administrator.jsp',
            'administrator.php4',
            'administrators.cfm',
            'administrators.jsp',
            'adminitem.aspx',
            'adminitem.cfm',
            'adminitem.jsp',
            'adminitems.cfm',
            'adminlogin.cfm',
            'adminlogin.jsp',
            'admins.asp',
            'affiliate.cfm',
            'api.php5',
            'article_search.jsp',
            'auth.asp',
            'authadmin.aspx',
            'authuser.cfm',
            'authuser.jsp',
            'base.rb',
            'bs-config.json',
            'build.gradle',
            'check.aspx',
            'checkadmin.aspx',
            'checkoutanon.aspx',
            'checkoutpayment.aspx',
            'class_upload.asp',
            'click.cgi',
            'CMakeLists.txt',
            'column.swf',
            'comments.jsp',
            'component.json',
            'composer.lock',
            'conf.js',
            'conf.py',
            'config.inc.php~',
            'config.js',
            'config.json',
            'config.php-eb',
            'config.php.bak',
            'config.php.dist',
            'config.php.inc',
            'config.php.inc~',
            'config.php.old',
            'config.php~',
            'config.yml',
            'configuration.jsp',
            'configuration.php.bak',
            'configuration.php.dist',
            'configuration.php.old',
            'configuration.php.swp',
            'configuration.php~',
            'connector.asp',
            'ContentPage.aspx',
            'contents.xcworkspacedata',
            'Convert.aspx',
            'cp.asp',
            'createaccount.aspx',
            'cron.aspx',
            'cron.cgi',
            'crossdomain.xml',
            'css_init.jsp',
            'ct.aspx',
            'custom_fields.jsp',
            'default.jsp',
            'Default3.aspx',
            'demo.asp',
            'Details.aspx',
            'details.cgi',
            'dev.js',
            'dispatch.fcgi',
            'django.po',
            'DLSampleTestPage.aspx',
            'docker-compose.yml',
            'dontvis-htdocs.rar',
            'dump.sql',
            'EchoHeaders.jws',
            'edit_article.jsp',
            'edit_entry.jsp',
            'edit_site.jsp',
            'editaddress.aspx',
            'employees.pac',
            'en.lproj',
            'entry_action.jsp',
            'Env.aspx',
            'env.rb',
            'error.aspx',
            'error.log',
            'es.yml',
            'ExampleRunner.java',
            'extconf.rb',
            'f60cgi.exe',
            'facebook.jsp',
            'fckeditor.asp',
            'file.xsql?name=foobar',
            'fileadmin.cfm',
            'fileadmin.jsp',
            'folder_action.jsp',
            'forum.php',
            'ga.aspx',
            'Gemfile.lock',
            'geocam.ru.swf',
            'globals.jsa',
            'guest.pac',
            'hellouser.jsp',
            'help.jsp',
            'helper.rb',
            'helpers.rb',
            'httpd.pid',
            'humans.txt',
            'img_auth.php5',
            'info.cfm',
            'info.jsp',
            'Info.plist',
            'init.jsp',
            'initialize_spec.rb',
            'install.platform/',
            'INSTALL.sqlite.txt',
            'io.swf',
            'jmsws1.ear',
            'Kconfig.debug',
            'Kesion.FsoVarCls.asp',
            'load.php5',
            'localHeader.jsp',
            'localsettings.php.bak',
            'localsettings.php.dist',
            'localsettings.php.old',
            'localsettings.php.save',
            'localsettings.php~',
            'login.act',
            'login.aspx',
            'login_out.asp',
            'login_user.cfm',
            'login_user.jsp',
            'loginsuper.asp',
            'Makefile.boot',
            'manage.py',
            'management.aspx',
            'management.cfm',
            'management.jsp',
            'manager.asp',
            'managers.pac',
            'manifest.json',
            'MANIFEST.MF',
            'members.asp',
            'members.aspx',
            'mime_types.rb',
            'mixml.plx',
            'moderator.aspx',
            'MonitorList.aspx',
            'Mp3MediaStreamSourceDemoTestPage.aspx',
            'myaccount.asp',
            'names.nsf?OpenDatabase',
            'ncbook.cgi',
            'new_spec.rb',
            'notification.aspx',
            'npm-shrinkwrap.json',
            'null.jsp',
            'opensearch_desc.php5',
            'orderconfirmation.aspx',
            'owa_util.cellsprint',
            'owa_util.cellsprint?p_theQuery=select+1+from+dual',
            'owa_util.listprint?p_theQuery=select+1+from+dual',
            'owa_util.show_query_columns?ctable=sys.dba_users',
            'owa_util.showsource?cname=books',
            'page.jsp',
            'panel.cfm',
            'paypalnotification.aspx',
            'phpinfo.php5',
            'phpMyAdmin-2.2.3/',
            'phpMyAdmin-2.2.6/',
            'phpMyAdmin-2.5.1/',
            'phpMyAdmin-2.5.4/',
            'phpMyAdmin-2.5.5/',
            'phpMyAdmin-2.5.6/',
            'phpMyAdmin-2.5.7/',
            'phpMyAdmin-2.6.0/',
            'phpMyAdmin-2.6.1/',
            'phpMyAdmin-2.6.2/',
            'phpMyAdmin-2.6.3/',
            'phpMyAdmin-2.6.4/',
            'phpMyAdmin-2.7.0/',
            'phpMyAdmin-2.8.0.1/',
            'phpMyAdmin-2.8.0.2/',
            'phpMyAdmin-2.8.0.3/',
            'phpMyAdmin-2.8.0.4/',
            'phpMyAdmin-2.8.0/',
            'phpMyAdmin-2.8.1/',
            'phpMyAdmin-2.8.2/',
            'phpunit.xml',
            'pi.php5',
            'placeholder.txt',
            'polycom.cfg',
            'popup.aspx',
            'portlet-model-hints.xml',
            'posts.json',
            'Print.aspx',
            'PrintDeliveryPage.aspx',
            'process_bug.cgi',
            'processlogin.aspx',
            'production.rb',
            'profileinfo.php5',
            'project.pbxproj',
            'project.xcworkspace',
            'proxy.pac',
            'public.api/',
            'quikstore.cgi',
            'railtie.rb',
            'randomfile4322.jpg',
            'README.markdown',
            'README.rdoc',
            'receipt.aspx',
            'recomp_exit.dyn',
            'redirect.asp',
            'redirect.php5',
            'remote_user.jsp',
            'remove.cgi',
            'reorder.aspx',
            'ror.xml',
            'rorindex.aspx',
            'sample.aspx',
            'sampleposteddata.cgi',
            'schema.rb',
            'searchpar.cgi',
            'searchresults.aspx',
            'seeds.rb',
            'select_article.jsp',
            'select_attachments.jsp',
            'selectaddress.aspx',
            'Session.asp',
            'setcurrency.aspx',
            'SetSecurity.shm',
            'setup.py',
            'shard-data-source-spring.xml',
            'shopin.asp',
            'showcase.action',
            'sidebar.jsp',
            'sign_in.asp',
            'sign_in.aspx',
            'signin.aspx',
            'signin.cfm',
            'signin.jsp',
            'SilverlightExampleTestPage.aspx',
            'simple.jsp',
            'siteadmin.aspx',
            'StockQuoteService.jws',
            'submit.cgi',
            'subscribe.jsp',
            'summary.txt',
            'super1.asp',
            'super_index.aspx',
            'super_login.cfm',
            'super_login.jsp',
            'superman.asp',
            'supermanager.asp',
            't.plx',
            'tags.txt',
            'Telerik.Web.UI.WebResource.axd',
            'template_action.jsp',
            'template_search.jsp',
            'TemplateIcon.icns',
            'TemplateInfo.plist',
            'terms.cgi',
            'test.aspx',
            'test.html',
            'testcgi.exe',
            'tests.py',
            'thumb.php5',
            'thumb_handler.php5',
            'tr.plx',
            'treetable.jsp',
            'UnitTests.xcconfig',
            'user.aspx',
            'user.cfm',
            'user.jsp',
            'user_display.jsp',
            'User_Files.asp',
            'User_LogEdays.asp',
            'User_MyArticle.asp',
            'UserRegResult.asp',
            'users.pac',
            'users.yml',
            'utils.py',
            'vacations_index.asp',
            'vacations_list.asp',
            'video.swf',
            'view_entries.jsp',
            'vorud.cfm',
            'wallet.dat',
            'wallet.dat.1',
            'wallet_backup.dat',
            'web.config',
            'web.config.back',
            'web.config.backup',
            'web.config.bak',
            'web.config.local',
            'web.config.Net3_5.MVC',
            'web.config.Net4.MVC',
            'web.config.old',
            'web.config.orig',
            'web.config~',
            'web.sitemap',
            'webadmin.asp',
            'webmaster.cfm',
            'webmaster.jsp',
            'WebResource.axd',
            'wp-login.jsp',
            'wp-login.php',
            'yaml.plx',
            'yonetim.asp',
            //'admin.pl',
            //'administrator.pl',
            // 'basket.pl',
            //'build.properties',
            //'config.ru',
            // 'create_release.sh',
            // 'cron.sh',
            // 'debug.pl',
            // 'DEPLOY.md',
            // 'gruntfile.coffee',
            //'History.md',
            // 'install.sh',
            //'login.pl',
            //'logout.pl',
            //'logs.pl',
            //'loot.pl',
            //'main.pl',
            //'Makefile.am',
            //'metamod.pl',
            //'mt.pl',
            // 'newsletter.sh',
            //'onlogo.pl',
            //'partners.pl',
            //'payment.pl',
            //'paytypes.pl',
            //'pne.pl',
            //'print.pl',
            //'profile.pl',
            //'project.properties',
            //'proxy.pl',
            //'pubkey.pl',
            //'README.md',
            //'regterms.pl',
            //'requestor.pl',
            //'rivals.pl',
            //'rss.pl',
            //'rt.pl',
            //'rtable.pl',
            //'search.pl',
            //'send.pl',
            //'showAd.pl',
            //'technology.pl',
            //'termcond.pl',
            //'test.pl',
            //'ttable.pl',
            //'upload.pl',
            //'users2.pl',
            //'webmaster.pl',
            //'zayavka.pl',
        ] as $url) {
            $response = self::$client->request('GET', $url);
            $this->assertEquals(404, $response->getStatusCode(), $url);
        }
    }

    // https://stackoverflow.com/a/30523600/1109380
    private function getTitle($html): string
    {
        $title = '';
        $dom = new DOMDocument();

        libxml_use_internal_errors(true); // https://stackoverflow.com/a/6090728/1109380
        if ($dom->loadHTML($html)) {
            $list = $dom->getElementsByTagName("title");
            if ($list->length > 0) {
                $title = $list->item(0)->textContent;
            }
        }
        libxml_clear_errors();

        return $title;
    }
}
