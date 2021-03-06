<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gbirke
 * Date: 18.06.13
 * Time: 21:56
 * To change this template use File | Settings | File Templates.
 */

namespace Birke\PinboardFeed;

use Buzz\Browser;
use Buzz\Message\Response;
use Doctrine\Common\Cache\CacheProvider;
use Psr\Log\LoggerInterface;


class PinboardClient {

    const WAIT=4;

    /**
     * @var CacheProvider
     */
    protected $cache;

    /**
     * @var Browser
     */
    protected $browser;

    protected $authToken = "";

    protected $apiUrl = "api.pinboard.in/v1/posts/get?";

    protected $lastRequest = 0;

    protected $cachePrefix = "";

    protected $httpErrorCount = 0;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    function __construct($cache, $browser)
    {
        $this->cache = $cache;
        $this->browser = $browser;
    }

    public function has($link) {
        $logger = $this->getLogger();
        $cacheId = $this->cachePrefix.'.'.$link;
        if($this->cache->contains($cacheId)) {
            $logger->debug("$link found in cache.");
            return $this->cache->fetch($link);
        }
        else {
            $logger->debug("$link not found in cache, fetching ...");
            $now = time();
            if($now - $this->lastRequest < self::WAIT) {
                sleep(self::WAIT);
            }
            $this->lastRequest = $now;
            $url = "https://".$this->apiUrl."auth_token={$this->authToken}&url=".urlencode($link);
            /** @var Response $response */
            $response = $this->browser->get($url);
            if(!$response->isSuccessful()) {
                $logger->error("Getting Link status at $url was not successful. (".$response->getStatusCode()
                    ." ".$response->getReasonPhrase()).")";
                $this->httpErrorCount++;
                return false;
            }
            $dom = new \DOMDocument();
            if(!$dom->loadXML($response->getContent())) {
                $logger->error("Parsing pinboard XML response was not succesful:\n".$response->getContent());
                return false;
            }
            $posts = $dom->getElementsByTagName("post");
            $hasBookmarkedLink = $posts->length > 0;
            $this->cache->save($cacheId, $hasBookmarkedLink, 3600);
            return $hasBookmarkedLink;
        }
    }

    /**
     * @param array $links All the links
     * @return array
     */
    public function checkLinks($links){
        $linkststats = array();
        $this->httpErrorCount = 0;
        $maxErrors = count($links) / 10;
        foreach($links as $link) {
            $linkststats[$link] = $this->has($link);
            if($this->httpErrorCount > $maxErrors) {
                throw new \RuntimeException("Too many HTTP errors when fetching from pinboard.in");
            }
        }
        return $linkststats;
    }

    /**
     * @param string $apiUrl
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * @param string $authToken
     */
    public function setAuthToken($authToken)
    {
        $this->authToken = $authToken;
        $this->cachePrefix = substr($authToken, 0, strpos($authToken, ":"));
    }

    /**
     * @return string
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

}