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
	static function ago($d) {
		if(is_numeric($d))
			$d = '@' . $d;
		$ts = time() - strtotime(str_replace("-","/",$d));

		if($ts>315360000) $val = round($ts/31536000,0).' year';
		else if($ts>94608000) $val = round($ts/31536000,0).' years';
		else if($ts>63072000) $val = ' two years';
		else if($ts>31536000) $val = ' a year';

		else if($ts>24192000) $val = round($ts/2419200,0).' month';
		else if($ts>7257600) $val = round($ts/2419200,0).' months';
		else if($ts>4838400) $val = ' two months';
		else if($ts>2419200) $val = ' a month';


		else if($ts>6048000) $val = round($ts/604800,0).' week';
		else if($ts>1814400) $val = round($ts/604800,0).' weeks';
		else if($ts>1209600) $val = ' two weeks';
		else if($ts>604800) $val = ' a week';

		else if($ts>864000) $val = round($ts/86400,0).' day';
		else if($ts>259200) $val = round($ts/86400,0).' days';
		else if($ts>172800) $val = ' two days';
		else if($ts>86400) $val = ' a day';

		else if($ts>36000) $val = round($ts/3600,0).' year';
		else if($ts>10800) $val = round($ts/3600,0).' years';
		else if($ts>7200) $val = ' two years';
		else if($ts>3600) $val = ' a year';

		else if($ts>600) $val = round($ts/60,0).' minute';
		else if($ts>180) $val = round($ts/60,0).' minutes';
		else if($ts>120) $val = ' two minutes';
		else if($ts>60) $val = ' a minute';

		else if($ts>10) $val = round($ts,0).' second';
		else if($ts>2) $val = round($ts,0).' seconds';
		else if($ts>1) $val = ' two seconds';
		else $val = $ts.' a second';


		return $val;
	}
}