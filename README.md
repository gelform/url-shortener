# A single-file url shortener

## Featues:
* No stats
* One file

## Requirements:
* PHP
* mysqli
* htaccess

## Installation
1. Create a database.
2. Run the `setup.sql` and confirm that a table called `link` was created.
3. Add the `.htaccess` and `index.php` files to the webroot. Visiting your web root with no query args or path should return `404`. It's working!

## Instructions

### Shorten a url
* Visit `yourdomain.com?url=url-to-encode` where `url-to-encode` is the url you want to encode e.g. `yourdomain.com?url=https://twitter.com/gelform`.
* You should see a shortened url e.g. `yourdomain.com/1aw3se4dr5t`.

### Using a shortened url
* Visiting a shortened url e.g. `yourdomain.com/1aw3se4dr5t` will redirect you to the original url e.g. `https://twitter.com/gelform` with a 301 (permanent redirect).
