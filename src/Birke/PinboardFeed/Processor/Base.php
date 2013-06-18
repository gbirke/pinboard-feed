<?php
/**
 * Created by JetBrains PhpStorm.
 * User: gbirke
 * Date: 18.06.13
 * Time: 21:25
 * To change this template use File | Settings | File Templates.
 */

namespace Birke\PinboardFeed\Processor;


abstract class Base {

    const NS_RDF = "http://www.w3.org/1999/02/22-rdf-syntax-ns#";
    const NS_RSS = "http://purl.org/rss/1.0/";

    /**
     * Add/Remove items from DOMDocument
     *
     * @param \DOMDocument $dom
     * @return \DomDocument
     */
    abstract public function process(\DOMDocument $dom);

    protected function removeLinks(&$dom, $links, $position = 1) {
        $xpath = $this->getXpath($dom);
        $doc = $dom->getElementsByTagNameNS(self::NS_RDF, "RDF")->item(0);
        $items = $xpath->query("/rdf:RDF/rss:channel/rss:items/rdf:Seq")->item(0);
        foreach($links as $link => $count) {
            if($count < 2) {
                continue;
            }
            foreach($xpath->query("rdf:li[@rdf:resource='$link' and position() > $position]", $items) as $elm) {
                $items->removeChild($elm);
            }
            foreach($xpath->query("rss:item[@rdf:about='$link' and position() > $position]", $doc) as $item) {
                $doc->removeChild($item);
            }
        }
    }

    protected function getXpath(\DOMDocument $dom) {
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace("rdf", self::NS_RDF);
        $xpath->registerNamespace("rss", self::NS_RSS);
        return $xpath;
    }

    protected function collectLinks(\DOMDocument $dom, $callback) {
        $links = array();
        $xpath = $this->getXpath($dom);
        foreach($xpath->query("/rdf:RDF/rss:item") as $item) {
            $link = $item->attributes->getNamedItemNS(self::NS_RDF, "about")->value;
            $callback($this, $links, $link);
        }
        return $links;
    }


}