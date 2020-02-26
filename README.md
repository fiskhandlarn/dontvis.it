# dontvis.it

[dontvis.it](https://dontvis.it) is a tool to escape linkbaits, trolls, idiots and asshats.

[![Build Status](https://badgen.net/travis/fiskhandlarn/dontvis.it/master)](https://travis-ci.com/fiskhandlarn/dontvis.it)
[![StyleCI](https://github.styleci.io/repos/235092767/shield?branch=master)](https://github.styleci.io/repos/235092767)

This tool tries to capture the content of an article or blog post without passing on your visit as a page view. dontvis.it also reads and displays (some) news articles otherwise only visible to "subscribers". Effectively this means that you're not paying with your attention or money, so you can read and share< the idiocy that it contains. (This is basically a repackaged Instapaper/Pocket/ReadLater/whatever.)

## How it works

A user will paste a link, or type `dontvis.it/` before the URL in the address bar and then the script goes to work:

* If the URL is found in the database a cached version is served
* If not:
 * cURL fetches the URL with a randomized webcrawler user agent, in order to not stand out in server logs
 * The web page is parsed through PHP Readability (by [fivefilters.org](https://fivefilters.org)) to remove ads and other stuff
* The user can now share the URL to the contents without providing traffic to stuff the user doesn't want to support and/or pay for

## Install

Install the composer dependencies:

```bash
$ composer install
```

Install the node dependencies and build the resources:

```bash
$ npm install && npm run dev
```

(Or use `make install && make build:dev` instead of the above.)

Configure your web server setting the web root to the `public/` folder or use [Docker](#docker).

Edit the `.env` file with your database credentials.

## Develop

Build CSS & JS files one time:

```bash
$ npm run dev
```

or

```bash
$ make build:dev
```

Build CSS & JS files and watch for file changes:

```bash
$ npm run watch
```

or

```bash
$ make watch
```

Build minified CSS & JS files one time:

```bash
$ npm run prod
```

or

```bash
$ make build
```

## Docker

Use this database host:
```env
DB_HOST=mysql
```

Create SSL certificate:
```bash
$ mkdir -p .docker/.ssl
$ openssl req -x509 -nodes -days 3650 -newkey rsa:2048 -keyout .docker/.ssl/server.key -out .docker/.ssl/server.pem
```

or

```bash
make ssl:create
```

Start Docker:
```bash
$ docker-compose up -d
```

or

```bash
$ make up
```

Access the site via https://localhost:3000/ and phpMyAdmin via http://localhost:8082/.

Stop Docker:
```bash
$ docker-compose down
```

or

```bash
$ make down
```

## TODO
* Use [memcache](https://www.php.net/manual/en/book.memcached.php) to speed things up
* Consolidate URL-caching so that www. and trailing slashes don't create separate caching
* Rehost images on [Imgur](https://imgur.com/)

## Built with:
- [PHP-Readability](https://bitbucket.org/Dither/php-readability/src/master/) 1.7.2
- [Bootstrap](https://getbootstrap.com/) 4.4.1

## Thanks to
* [gonedjur](https://github.com/gonedjur/unvis.it) for providing the base of this tool
