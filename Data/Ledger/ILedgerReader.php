<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 10/6/14
 * Time: 2:45 PM
 */
namespace CPath\Data\Ledger;

interface ILedgerReader
{
	function readRow(&$row);
}

