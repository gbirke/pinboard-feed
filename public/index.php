<?php

require_once __DIR__."/../vendor/autoload.php";

use Birke\PinboardFeed\Processor\DuplicateRemover;

if(!($credFile = getenv("CRED_FILE"))) {
    die("CRED_FILE environment variable not set!");
}
$credentials = new \Birke\PinboardFeed\Credentials($credFile);

$url = "http://feeds.pinboard.in/rss/t:javascript";
$cache = new \Doctrine\Common\Cache\ApcCache();
$logger = new \Analog\Logger();
$logger->handler(function($info){
    error_log(sprintf("%s [%d] (%s) - %s", $info['date'], $info['level'], $info['machine'], $info['message']));
});

$browser = new \Buzz\Browser();

if(!($doc = $cache->fetch("feed_$url"))) {
    /** @var \Buzz\Message\Response $response */
    $response = $browser->get($url);
    if(!$response->isSuccessful()) {
        throw new RuntimeException("Error when fetching $url");
    }
    $doc = $response->getContent();
    $cache->save("feed_$url", $doc);
}
$dom = new \DOMDocument();
if(!$dom->loadXML($doc)) {
    throw new \RuntimeException("Error parsing the following XML:\n$doc");
}

$client = new \Birke\PinboardFeed\PinboardClient($cache, $browser);
$client->setLogger($logger);

if(empty($credentials['CONFIG']['CONFIG_VARS']['PINBOARD_AUTH_TOKEN'])) {
    die("PINBOARD_AUTH_TOKEN not configured.");
}
$client->setAuthToken($authToken);

$duplicates = new DuplicateRemover();
$duplicates->setLogger($logger);
$bookmarked = new \Birke\PinboardFeed\Processor\BookmarkedRemover($client);
$bookmarked->setLogger($logger);

$dom = $duplicates->process($dom);
$dom = $bookmarked->process($dom);
header("Content-type: application/rss+xml");
echo $dom->saveXML();
