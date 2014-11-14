<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Data\Map;

interface IKeyMap {

//    const KEY_TITLE = 'title';
//    const KEY_DESCRIPTION = 'description';

	/**
	 * Map data to the key map
	 * @param IKeyMapper $Map the map instance to add data to
	 * @internal param \CPath\Request\IRequest $Request
	 * @internal param \CPath\Request\IRequest $Request
	 * @return void
	 */
    function mapKeys(IKeyMapper $Map);
}
