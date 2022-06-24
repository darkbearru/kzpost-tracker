<?php
const REMOTE_URL = 'http://track.kazpost.kz/api/v2/';
const REMOTE_DICTIONARY = 'status_mappings/index.json';
const CACHE_DIR = __DIR__ . '/cache/';
const CACHE_FILE_NAME = CACHE_DIR . 'dictionary.json';
const TEMPLATE_DIR = __DIR__ . '/src/resources/';

require_once(__DIR__ . '/src/functions.php');
require_once(__DIR__ . '/src/Tracker.php');

$tracker = new Tracker();
echo $tracker->run();