<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Interfaces;

interface IXML {
    /**
     * EXPORT Object to XML
     * @param \SimpleXMLElement $xml the XML instance to modify
     * @return void
     */
    function toXML(\SimpleXMLElement $xml);
}