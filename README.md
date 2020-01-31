# dontvis.it

[dontvis.it](https://dontvis.it) is a tool to escape linkbaits, trolls, idiots and asshats.

This tool tries to capture the content of an article or blog post without passing on your visit as a page view. dontvis.it also reads and displays (some) news articles otherwise only visible to "subscribers". Effectively this means that you're not paying with your attention or money, so you can read and share< the idiocy that it contains. (This is basically a repackaged Instapaper/Pocket/ReadLater/whatever.)

## How it works

A user will paste a link, or type `TODO?dontvis.it/` before the URL in the address bar and then the script goes to work:

* If the URL is found in the database a cached version is served
* If not:
 * cURL fetches the URL with a randomized webcrawler user agent, in order to not stand out in server logs
 * The web page is parsed through PHP Readability (by [fivefilters.org](https://fivefilters.org)) to remove ads and other stuff
* The user can now share the URL to the contents without providing traffic to stuff the user doesn't want to support and/or pay for

## Build

TODO

## TODO
* Use [memcache](https://www.php.net/manual/en/book.memcached.php) to speed things up
* Consolidate URL-caching so that www. and trailing slashes don't create separate caching
* Rehost images on [Imgur](https://imgur.com/)
* Log debug output when Readability cannot find the main text

## Built with:
- [PHP-Readability](https://bitbucket.org/Dither/php-readability/src/master/) 1.7.2
- [Bootstrap](https://getbootstrap.com/) 4.4.1

## Thanks to
* [gonedjur](https://github.com/gonedjur/unvis.it) for providing the base of this tool
