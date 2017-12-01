<?php
/**
 * API Framework.
 *
 * @package API_By_Marcus
 */

// Load the includes.
include 'includes/class-api-database.php';
include 'includes/class-api-router.php';
include 'includes/class-api-query.php';
include 'includes/class-api.php';

// Define a new API.
$api = new API();

// Query the database for the requested data.
$response = $api->get_response();

// Send the response to the browser.
$api->return_json( $response );
