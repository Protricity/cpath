<?php
/**
 * Project: CleverPath Framework
 * IDE: JetBrains PhpStorm
 * Author: Ari Asulin
 * Email: ari.asulin@gmail.com
 * Date: 4/06/11 */
namespace CPath\Response;

use CPath\Config;

interface IResponse
{
	const HTTP_SUCCESS = 200;

	const HTTP_SEE_OTHER = 303;
	const HTTP_TEMPORARY_REDIRECT = 307;

	const HTTP_ERROR = 400;
	const HTTP_NOT_FOUND = 404;
	const HTTP_CONFLICT = 409;

	const STR_CODE = 'code';
    const STR_MESSAGE = 'message';

    //const JSON_RESPONSE = 'response';

	/**
	 * Get the request status code
	 * @return int
	 */
	function getCode();

    /**
     * Get the IResponse Message
     * @return String
     */
    function getMessage();

//    /**
//     * @param mixed|NULL $_path optional varargs specifying a path to data
//     * Example: ->getData(0, 'key') gets $data[0]['key'];
//     * @return mixed the data array or targeted data specified by path
//     * @throws \InvalidArgumentException
//     */
//    function &getDataPath($_path = NULL);

//    /**
//     * Add a log entry to the response
//     * @param ILogEntry $Log
//     */
//    function addLogEntry(ILogEntry $Log);
//
//    /**
//     * Get all log entries
//     * @return ILogEntry[]
//     */
//    function getLogs();
//
//    /**
//     * Send response headers for this request
//     * @param null $mimeType
//     * @return mixed
//     */
//    function sendHeaders($mimeType = NULL);
}
