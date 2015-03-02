<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 2/1/2015
 * Time: 12:20 PM
 */
namespace CPath\Data\Date;


class DateUtil
{
	static function elapsedTime($d) {
		if(is_numeric($d))
			$ts = time() - intval($d);
        else
    		$ts = time() - strtotime(str_replace("-","/",$d));

		if($ts>94608000) $val = round($ts/31536000,0).' years ago';
		else if($ts>63072000) $val = 'two years ago';
		else if($ts>31536000) $val = 'last year';

		else if($ts>7257600) $val = round($ts/2419200,0).' months ago';
		else if($ts>4838400) $val = 'two months ago';
		else if($ts>2419200) $val = 'last month';

		else if($ts>1814400) $val = round($ts/604800,0).' weeks ago';
		else if($ts>1209600) $val = 'two weeks ago';
		else if($ts>604800) $val = 'last week';

		else if($ts>259200) $val = round($ts/86400,0).' days ago';
		else if($ts>172800) $val = 'two days ago';
		else if($ts>86400) $val = 'yesterday';

		else if($ts>10800) $val = round($ts/3600,0).' hours ago';
		else if($ts>7200) $val = 'two hours ago';
		else if($ts>3600) $val = 'an hour ago';

		else if($ts>180) $val = round($ts/60,0).' minutes ago';
		else if($ts>120) $val = 'two minutes ago';
		else if($ts>60) $val = 'a minute ago';

		else if($ts>2) $val = round($ts,0).' seconds ago';
        else if($ts>1) $val = 'two seconds ago';
        else if($ts>0) $val = 'a second ago';
        else if($ts === 0) $val = 'now';
        else if($ts==-1) $val = 'one second into the future';
		else $val = (-$ts) . ' seconds into the future';


		return $val;
	}
}