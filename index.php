<?php

// Canonical domain — do not trust the Host header.
define('DOMAIN', 'https://yourdomain.com');

// Set the database vars.
define('DB_HOST', 'DATABASE HOST');
define('DB_USER', 'YOUR DATABASE USER');
define('DB_NAME', 'YOUR DATABASE NAME');
define('DB_PASSWORD', 'YOUR PASSWORD');

/**
 * Utility function to end the request.
 */
function done($message = '', $http_response_code = 200) {
	global $conn;
	if ($conn instanceof mysqli) {
		$conn->close();
	}
	http_response_code($http_response_code);
	exit($message);
}

/**
 * Connect to database.
 */
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
	done("Connection failed: " . $conn->connect_error, 500);
}
$conn->set_charset('utf8mb4');

/**
 * Encode url and return the shortened url.
 */
if (!empty($_GET['url'])) {
	$url = filter_var($_GET['url'], FILTER_SANITIZE_URL);

	// Require a valid http(s) URL — blocks javascript:, data:, etc.
	if (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $url)) {
		done('error url', 400);
	}

	if (mb_strlen($url, 'UTF-8') > 2048) {
		done('error url too long', 400);
	}

	// Custom slug if provided; otherwise random 10-char slug.
	if (!empty($_GET['slug'])) {
		$slug = $_GET['slug'];

		// Must match the .htaccess rewrite charset and fit the column.
		if (!preg_match('/^[a-zA-Z0-9]{1,64}$/', $slug)) {
			done('error slug', 400);
		}

		$stmt = $conn->prepare('SELECT 1 FROM link WHERE slug = ? LIMIT 1');
		$stmt->bind_param('s', $slug);
		$stmt->execute();
		$taken = (bool) $stmt->get_result()->fetch_row();
		$stmt->close();

		if ($taken) {
			done('error slug taken', 409);
		}
	} else {
		$slug = substr(md5(uniqid('', true)), -10);
	}

	$stmt = $conn->prepare('INSERT INTO link (slug, url) VALUES (?, ?)');
	$stmt->bind_param('ss', $slug, $url);

	if ($stmt->execute()) {
		$stmt->close();
		done(sprintf('%s/%s', DOMAIN, $slug));
	}

	// Unique-key violation (race between the taken-check and INSERT).
	$errno = $stmt->errno;
	$stmt->close();
	if ($errno === 1062) {
		done('error slug taken', 409);
	}
	done('error insert', 500);
}

/**
 * No slug, 404.
 */
if (empty($_GET['slug'])) {
	done('404', 404);
}

/**
 * Find url from slug.
 */
$slug = $_GET['slug'];

$stmt = $conn->prepare('SELECT url FROM link WHERE slug = ? LIMIT 1');
$stmt->bind_param('s', $slug);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
	done('404', 404);
}

header('Location: ' . $row['url'], true, 301);
exit;
