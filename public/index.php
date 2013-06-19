<?php

require_once __DIR__."/../vendor/autoload.php";

use Birke\PinboardFeed\Processor\DuplicateRemover;

$url = "http://feeds.pinboard.in/rss/t:javascript";
$cache = new \Doctrine\Common\Cache\ApcCache();
$logger = new \Analog\Logger();
$logger->handler(function($info){
    error_log(sprintf("%s [%d] (%s) - %s", $info['date'], $info['level'], $info['machine'], $info['message']));
});

$browser = new \Buzz\Browser();

if(!($doc = $cache->fetch("feed_$url"))) {

    $response = $browser->get($url);
    // TODO check for errors
    $doc = $response->getContent();
    $cache->save("feed_$url", $doc);
}
$dom = new \DOMDocument();
$dom->load($doc);

$client = new \Birke\PinboardFeed\PinboardClient($cache, $browser);
$client->setLogger($logger);

if(!($authToken = getEnv("PINBOARD_AUTH_TOKEN"))) {
    die("Environment variable PINBOARD_AUTH_TOKEN not found.");
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
