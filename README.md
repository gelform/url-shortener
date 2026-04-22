# A single-file url shortener

## Featues:
* No stats
* One file

## Requirements:
* PHP (with the `mysqlnd` driver — default in PHP 5.4+)
* mysqli
* A webserver rewrite: Apache `.htaccess` (included) or an nginx location block (see below).

## Installation
1. Create a database.
2. Run the `setup.sql` and confirm that a table called `link` was created.
3. Edit `index.php` and fill in `DOMAIN` and the four `DB_*` constants at the top of the file.
4. Add `index.php` to the webroot, plus the rewrite config for your webserver:
   * **Apache**: drop in the included `.htaccess`.
   * **Nginx**: add the location block below to your server config and reload nginx (`nginx -t && systemctl reload nginx`). Nginx does not read `.htaccess`.
     ```nginx
     location ~ ^/([a-zA-Z0-9]+)$ {
         try_files $uri /index.php?slug=$1;
     }
     ```
5. Visiting your web root with no query args or path should return `404`. It's working!

## Instructions

### Shorten a url
* Visit `yourdomain.com?url=url-to-encode` where `url-to-encode` is the url you want to encode e.g. `yourdomain.com?url=https://twitter.com/gelform`.
* You should see a shortened url e.g. `yourdomain.com/aw3se4dr5t`.

### Shorten a url with a custom slug
* Append `&slug=your-slug` to choose the short URL's path, e.g. `yourdomain.com?url=https://twitter.com/gelform&slug=twitter`.
* Slugs must be 1–64 alphanumeric characters (`a-z`, `A-Z`, `0-9`).
* If the slug is already taken you'll get a `409 error slug taken`.

### Using a shortened url
* Visiting a shortened url e.g. `yourdomain.com/aw3se4dr5t` will redirect you to the original url e.g. `https://twitter.com/gelform` with a 301 (permanent redirect).
