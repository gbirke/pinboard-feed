<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gbirke
 * Date: 18.06.13
 * Time: 21:24
 * To change this template use File | Settings | File Templates.
 */

namespace Birke\PinboardFeed\Processor;

/**
 * Remove items the user has already bookmarked
 *
 * @package Birke\PinboardFeed\Processor
 */
class BookmarkedRemover extends Base{

    protected $pinboardClient;

    function __construct($pinboardClient)
    {
        $this->pinboardClient = $pinboardClient;
    }


    /**
     * Add/Remove items from DOMDocument
     *
     * @param \DOMDocument $dom
     * @return \DomDocument
     */
    public function process(\DOMDocument $dom)
    {
        $client = $this->pinboardClient;
        $links = $this->collectLinks($dom, function($processor, &$links, $link) use($client) {
            $links[$link] = $client->has($link) ? 2 : 1;
        });

        $this->removeLinks($dom, $links, 0);
        return $dom;
    }


}