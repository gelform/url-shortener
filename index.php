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

	if (strlen($url) > 2048) {
		done('error url too long', 400);
	}

	$slug = substr(md5(uniqid('', true)), -10);

	$stmt = $conn->prepare('INSERT INTO link (slug, url) VALUES (?, ?)');
	$stmt->bind_param('ss', $slug, $url);

	if ($stmt->execute()) {
		$id = $stmt->insert_id;
		$stmt->close();
		done(sprintf('%s/%d%s', DOMAIN, $id, $slug));
	}

	$stmt->close();
	done('error insert', 500);
}

/**
 * No slug, 404.
 */
if (empty($_GET['slug'])) {
	done('404', 404);
}

/**
 * Find url from slug and id.
 */
$id = (int) substr($_GET['slug'], 0, -10);
$slug = substr($_GET['slug'], -10);

$stmt = $conn->prepare('SELECT slug, url FROM link WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Timing-safe compare so the slug guard can't be brute-forced by timing.
if (!$row || !hash_equals($row['slug'], $slug)) {
	done('404', 404);
}

header('Location: ' . $row['url'], true, 301);
exit;
