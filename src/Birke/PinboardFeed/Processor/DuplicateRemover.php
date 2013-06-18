<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gbirke
 * Date: 18.06.13
 * Time: 19:16
 * To change this template use File | Settings | File Templates.
 */

namespace Birke\PinboardFeed\Processor;



class DuplicateRemover extends Base{

    /**
     * @param \DOMDocument $dom
     * @return \DOMDocument
     */
    public function process(\DOMDocument $dom){
        $links = $this->collectLinks($dom, function($processor, &$links, $link){
            if(empty($links[$link])) {
                $links[$link] = 1;
            }
            else {
                $links[$link]++;
            }
        });

        $this->removeLinks($dom, $links);
        return $dom;
    }
}