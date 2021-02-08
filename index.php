<?php

// Get the domain.
define('DOMAIN', 'https://' . $_SERVER['HTTP_HOST']);

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
	$conn->close();
	http_response_code($http_response_code);
	exit($message);
}

/**
 * Connect to database.
 */
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Test the connection.
if ($conn->connect_error) {
	done("Connection failed: " . $conn->connect_error, 500);
}

/**
 * Encode url and return the shortened url.
 */
if (!empty($_GET['url'])) {
  
  // Make sure the url is valid.
	$url = filter_var($_GET['url'], FILTER_SANITIZE_URL);

	if (!$url) {
		done('error url', 500);
	}

  // Create a slug.
	$slug = substr(md5(uniqid()), -10);

  // Build the sql.
	$sql = sprintf('INSERT INTO link (slug, url) VALUES ("%s", "%s")', $slug, $url);

  // If INSERT was successful, return the shortened url.
	if ($conn->query($sql) === TRUE) {
		$id = $conn->insert_id;
		done(sprintf('%s/%d%s', DOMAIN, $id, $slug));
	} else {
		done('error insert', 500);
	}
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
 
// Get the id from the slug.
$id = substr($_GET['slug'], 0, -10);
$sql = sprintf('SELECT * FROM link WHERE id = %d LIMIT 1;', $id);
$result = $conn->query($sql);

// If not found by id, 404.
if (0 === $result->num_rows) {
	done('404', 404);
}

// Check the slug.
$slug = substr($_GET['slug'], -10);
while ($row = $result->fetch_assoc()) {
	if ($slug !== $row['slug']) {
		done('404', 404);
	}

  // Redirect to the 
  http_response_code(301);
	$url = $row['url'];  
	header("Location: {$url}");
	exit;
}

done('end', 500);
