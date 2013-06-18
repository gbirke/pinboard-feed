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
use Doctrine\Common\Cache\CacheProvider;



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

    function __construct($cache, $browser)
    {
        $this->cache = $cache;
        $this->browser = $browser;
    }

    public function has($link) {
        if($this->cache->contains($link)) {
            return $this->cache->fetch($link);
        }
        else {
            if(time() - $this->lastRequest < self::WAIT) {
                sleep(self::WAIT);
            }
            $url = "https://".$this->apiUrl."auth_token={$this->authToken}&url=".urlencode($link);
            $response = $this->browser->get($url);
            $dom = new \DOMDocument();
            $dom->loadXML($response->getContent());
            $posts = $dom->getElementsByTagName("post");
            $hasBookmarkedLink = $posts->length > 0;
            $this->cache->save($link, $hasBookmarkedLink);
            return $hasBookmarkedLink;
        }
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
    }

    /**
     * @return string
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }

}