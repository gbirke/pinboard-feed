<?php

require_once __DIR__."/../vendor/autoload.php";

use Birke\PinboardFeed\Processor\DuplicateRemover;

header("Content-type: application/rss+xml");

$url = "http://feeds.pinboard.in/rss/t:javascript";
$cache = new \Doctrine\Common\Cache\ApcCache();

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

if(!($authToken = getEnv("PINBOARD_AUTH_TOKEN"))) {
    die("Environment variable PINBOARD_AUTH_TOKEN not found.");
}
$client->setAuthToken($authToken);

$duplicates = new DuplicateRemover();
$bookmarked = new \Birke\PinboardFeed\Processor\BookmarkedRemover($client);
$dom = $duplicates->process($dom);
$dom = $bookmarked->process($dom);
echo $dom->saveXML();