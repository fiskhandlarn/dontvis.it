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
        $response = self::$client->request('GET', '');
        $this->assertEquals(200, $response->getStatusCode());

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

    public function test404DotFiles()
    {
        foreach ([
            '.git/HEAD',
            '.htaccess',
        ] as $url) {
            $response = self::$client->request('GET', $url);
            $this->assertEquals(404, $response->getStatusCode(), $url);
        }
    }

    public function test404Exclamation()
    {
        foreach ([
            '!.htpasswd',
        ] as $url) {
            $response = self::$client->request('GET', $url);
            $this->assertEquals(404, $response->getStatusCode(), $url);
        }
    }

    public function test404Hash()
    {
        foreach ([
            '%23localsettings.php%23',
        ] as $url) {
            $response = self::$client->request('GET', $url);
            $this->assertEquals(404, $response->getStatusCode(), $url);
        }
    }

    public function test404QueryStrings()
    {
        foreach ([
            '?a=fetch&content=%3Cphp%3Edie(@md5(HelloThinkCMF))%3C/php%3E',
            '?homescreen=1',
            '?s=/Index/\think\app/invokefunction&function=call_user_func_array&vars[0]=md5&vars[1][]=HelloThinkPHP',
            '?XDEBUG_SESSION_START=phpstorm',
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
            'phpMyAdmin-2.5.5-rc2/',
            'phpMyAdmin-2.5.6-rc2/',
            'phpMyAdmin-2.5.7-pl1/',
            'phpMyAdmin-2.6.0-pl1/',
            'phpMyAdmin-2.6.0-pl3/',
            'phpMyAdmin-2.6.1-pl3/',
            'phpMyAdmin-2.6.2-rc1/',
            'phpMyAdmin-2.7.0-pl2/',
            'phpMyAdmin-2.7.0-rc1/',
            'phpMyAdmin-2.8.0-beta1/',
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
            '2014.tgz',
            '2014.zip',
            '2015.tar.gz',
            '2016.tar.gz',
            '2016.zip',
            '2018.tar',
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
            'conf.js',
            'conf.py',
            'config.js',
            'config.json',
            'config.php.bak',
            'config.php.inc',
            'config.yml',
            'configuration.jsp',
            'configuration.php.bak',
            'configuration.php.dist',
            'configuration.php.swp',
            'connector.asp',
            'ContentPage.aspx',
            'contents.xcworkspacedata',
            'Convert.aspx',
            'cp.asp',
            'createaccount.aspx',
            'cron.aspx',
            'cron.cgi',
            'crossdomain.xml',
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
            'dontvis-htdocs.rar',
            'dump.sql',
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
            'facebook.jsp',
            'fckeditor.asp',
            'file.xsql?name=foobar',
            'fileadmin.cfm',
            'fileadmin.jsp',
            'folder_action.jsp',
            'forum.php',
            'ga.aspx',
            'geocam.ru.swf',
            'hellouser.jsp',
            'help.jsp',
            'helper.rb',
            'helpers.rb',
            'humans.txt',
            'info.cfm',
            'info.jsp',
            'init.jsp',
            'initialize_spec.rb',
            'INSTALL.sqlite.txt',
            'io.swf',
            'jmsws1.ear',
            'Kesion.FsoVarCls.asp',
            'load.php5',
            'localHeader.jsp',
            'localsettings.php.dist',
            'localsettings.php.old',
            'localsettings.php.save',
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
            'ncbook.cgi',
            'new_spec.rb',
            'notification.aspx',
            'npm-shrinkwrap.json',
            'null.jsp',
            'orderconfirmation.aspx',
            'owa_util.cellsprint',
            'page.jsp',
            'panel.cfm',
            'paypalnotification.aspx',
            'phpMyAdmin-2.2.6/',
            'phpMyAdmin-2.5.6/',
            'phpMyAdmin-2.6.0/',
            'phpMyAdmin-2.6.1/',
            'phpMyAdmin-2.6.4/',
            'phpMyAdmin-2.8.0.2/',
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
            'quikstore.cgi',
            'railtie.rb',
            'randomfile4322.jpg',
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
            'submit.cgi',
            'subscribe.jsp',
            'summary.txt',
            'super1.asp',
            'super_index.aspx',
            'super_login.cfm',
            'super_login.jsp',
            'superman.asp',
            'supermanager.asp',
            'tags.txt',
            'Telerik.Web.UI.WebResource.axd',
            'template_action.jsp',
            'template_search.jsp',
            'TemplateIcon.icns',
            'terms.cgi',
            'test.aspx',
            'test.html',
            'tests.py',
            'thumb.php5',
            'thumb_handler.php5',
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
            'web.config.back',
            'web.config.backup',
            'web.sitemap',
            'webadmin.asp',
            'webmaster.cfm',
            'webmaster.jsp',
            'WebResource.axd',
            'wp-login.jsp',
            'wp-login.php',
            'yaml.plx',
            'yonetim.asp',
            // 'project.properties',
            //'basket.pl',
            //'cron.sh',
            //'debug.pl',
            //'metamod.pl',
            //'partners.pl',
            //'requestor.pl',
            //'rivals.pl',
            //'send.pl',
            //'showAd.pl',
            //'ttable.pl',
            //'upload.pl',
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
