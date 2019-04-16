<?php
require_once('/php/debug.php');
require_once('/php/sql/sqltable.php');

class PrimeNumberSql extends TableSql
{
    function PrimeNumberSql() 
    {
        parent::TableSql('primenumber');
        $this->Create();
    }
    
    function Create()
    {
    	$str = ' `val` BIGINT UNSIGNED NOT NULL ,'
         	. ' UNIQUE ( `val` )';
    	return $this->CreateTable($str);
    }
    
    function Insert($strVal)
    {
    	return $this->InsertData("(id, val) VALUES('0', '$strVal')");
    }
    
    function Get($iMax)
    {
    	$strMax = strval($iMax);
    	return $this->GetData("val <= '$strMax'", '`val` ASC');
    }
}

function _lookUpPrimeNumber($iNum)
{
	$sql = new PrimeNumberSql();
	if ($sql->CountData() == 0)
	{
		$aiPrime = array(2);
		$sql->Insert('2');
		for ($i = 3; ($i * $i) <= PHP_INT_MAX; $i += 2)
		{
			$bPrime = true;
			foreach ($aiPrime as $iPrime)
			{
				if ($iPrime * $iPrime > $i)		break;
				else if (($i % $iPrime) == 0)
				{
					$bPrime = false;
					break;
				}
			}
			if ($bPrime)
			{
				$aiPrime[] = $i;
				$sql->Insert(strval($i));
			}
		}
	}

	$aiNum = array();
    if ($result = $sql->Get(sqrt($iNum))) 
    {
        while ($record = mysql_fetch_assoc($result)) 
        {
        	$iPrime = intval($record['val']);
        	if ($iPrime * $iPrime > $iNum)	break;
        	while (($iNum % $iPrime) == 0)
        	{
        		$iNum /= $iPrime;
        		$aiNum[] = $iPrime;
        	}
        }
        if ($iNum > 1) 		$aiNum[] = $iNum;
        @mysql_free_result($result);
    }
	return $aiNum;
}

function _onePassPrimeNumber($iNum)
{
	$aiNum = array();
	for ($i = 2; ($i * $i) <= $iNum; $i ++)
	{
		while (($iNum % $i) == 0)
		{
			$iNum /= $i;
			$aiNum[] = $i;
		}
	}
	if ($iNum > 1) 		$aiNum[] = $iNum;
	return $aiNum;
}

function _getPrimeNumberString($callback, $strNumber)
{
    $fStart = microtime(true);
    $aiNum = call_user_func($callback, intval($strNumber));
	$str = $strNumber.'=';
	foreach ($aiNum as $iPrime)
	{
		$str .= strval($iPrime).'*';
	}
	return rtrim($str, '*').DebugGetStopWatchDisplay($fStart);
}

function GetPrimeNumberString($strNumber)
{
	$str = _getPrimeNumberString(_onePassPrimeNumber, $strNumber);
	$str .= '<br />'._getPrimeNumberString(_lookUpPrimeNumber, $strNumber);
	return $str;
}

?>