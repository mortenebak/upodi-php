# upodi-php
A PHP Wrapper for the UPODI API

```
/**
 * Usage Examples
 */
$api = new UpodiAPI([
    'access_key' => '',
    'api_version' => 'v3',
]);


$api->get('/endpoint');
$api->delete('/endpoint');
$api->post('/endpoint', array());
$api->patch('/endpoint', array());

```
