<?php

declare(strict_types=1);

use Dotenv\Dotenv;

Dotenv::createImmutable(realpath(__DIR__ . '/..'))->safeLoad();

function require_image($imagePath)
{
    if (defined('DEBUG') && DEBUG) {
        echo '<!-- '.esc_html($imagePath).' -->'.PHP_EOL;
    }

    $image = file_get_contents($imagePath);

    $hash = md5($imagePath);
    $image = str_replace('cls-', 'cls-' . $hash . '-', $image);

    echo $image;
}

/*
 * This file is part of WordPlate.
 *
 * (c) Vincent Klaiber <hello@doubledip.se>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }

        if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
            return $matches[2];
        }

        return $value;
    }
}
