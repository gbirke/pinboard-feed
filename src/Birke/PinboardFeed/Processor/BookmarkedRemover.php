<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gbirke
 * Date: 18.06.13
 * Time: 21:24
 * To change this template use File | Settings | File Templates.
 */

namespace Birke\PinboardFeed\Processor;

use Birke\PinboardFeed\PinboardClient;

/**
 * Remove items the user has already bookmarked
 *
 * @package Birke\PinboardFeed\Processor
 */
class BookmarkedRemover extends Base{

    /**
     * @var PinboardClient
     */
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
        $links = $this->collectLinks($dom, function($processor, &$links, $link) {
            $links[] = $link;
        });
        $linkList = array();
        foreach($this->pinboardClient->checkLinks($links) as $link => $hasLink) {
            $linkList[$link] = $hasLink ? 2 : 1;
        }

        $this->removeLinks($dom, $linkList, 0);
        return $dom;
    }


}