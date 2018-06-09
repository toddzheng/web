<?php
require_once('stocktable.php');

function _getSmaRow($strKey, $bChinese)
{
    if ($bChinese)
    {
        $arRow = array('D' => '日', 'W' => '周', 'M' => '月', 'BOLLUP' => '布林上轨', 'BOLLDN' => '布林下轨', 'EMA' => '牛熊分界');
    }
    else
    {
        $arRow = array('D' => ' Days', 'W' => ' Weeks', 'M' => ' Months', 'BOLLUP' => 'Boll Up', 'BOLLDN' => 'Boll Down', 'EMA' => 'EMA200/50');
    }
    $strFirst = substr($strKey, 0, 1);
    if ($strFirst != 'B' && $strFirst != 'E')
    {
        return substr($strKey, 1, strlen($strKey) - 1).$arRow[$strFirst];
    }
    return $arRow[$strKey];
}

function _getTradingRangeRow($stock_his, $strKey)
{
    $iVal = $stock_his->aiTradingRange[$strKey];
    if ($iVal < 0)
    {
        return '';
    }
    return strval($iVal); 
}

function _getSmaCallbackPriceDisplay($callback, $ref, $fVal, $strColor)
{
	return GetTableColumnDisplay($ref->GetPriceDisplay(call_user_func($callback, $ref, $fVal)), $strColor);
}

function _echoSmaTableItem($stock_his, $strKey, $fVal, $fNext, $ref, $callback, $callback2, $strColor, $bChinese)
{
    $stock_ref = $stock_his->stock_ref;
    
    $strSma = _getSmaRow($strKey, $bChinese);
    $strPrice = $stock_ref->GetPriceDisplay($fVal);
    $strNext = $stock_ref->GetPriceDisplay($fNext);
    $strPercentage = $stock_ref->GetPercentageDisplay($fVal);
    $strTradingRange = _getTradingRangeRow($stock_his, $strKey);
    
    $strDisplayEx = '';
    if ($callback)
    {
        $strDisplayEx = _getSmaCallbackPriceDisplay($callback, $ref, $fVal, $strColor);
        $strDisplayEx .= _getSmaCallbackPriceDisplay($callback, $ref, $fNext, $strColor);
    }

    $strUserDefined = '';  
    if ($callback2)    $strUserDefined = GetTableColumnDisplay(call_user_func($callback2, $bChinese, $fVal, $fNext), $strColor);

    $strBackGround = GetTableColumnColor($strColor);
    echo <<<END
    <tr>
        <td $strBackGround class=c1>$strSma</td>
        <td $strBackGround class=c1>$strPrice</td>
        <td $strBackGround class=c1>$strPercentage</td>
        <td $strBackGround class=c1>$strTradingRange</td>
        <td $strBackGround class=c1>$strNext</td>
        $strDisplayEx
        $strUserDefined
    </tr>
END;
}

class MaxMin
{
    var $fMax;
    var $fMin;
    
    // constructor 
    function MaxMin() 
    {
        $this->fMax = false;
        $this->fMin = false;
    }

    function Init($fMax, $fMin)
    {
        if ($this->fMin == false && $this->fMax == false)
        {
            $this->fMin = $fMin;
            $this->fMax = $fMax;
        }
    }
    
    function Set($fVal) 
    {
        if ($fVal > $this->fMax)  $this->fMax = $fVal;
        if ($fVal < $this->fMin)  $this->fMin = $fVal;
    }
    
    function Fit($fVal)
    {
        if ($fVal > $this->fMin && $fVal < $this->fMax) return true;
        return false;
    }
}

function _echoSmaTableData($stock_his, $ref, $callback, $callback2, $bChinese)
{
    $mm = new MaxMin();
    $mmB = new MaxMin();
    $mmW = new MaxMin();
    foreach ($stock_his->afSMA as $strKey => $fVal)
    {
    	$fNext = $stock_his->afNext[$strKey];
        $strColor = false;
        $strFirst = substr($strKey, 0, 1); 
        if ($strFirst == 'D')
        {
            $mm->Init(0.0, 10000000.0);
            $mm->Set($fVal);
        }
        else if ($strFirst == 'B')
        {
            $mmB->Init($mm->fMax, $mm->fMin);
            $mmB->Set($fVal);
        }
        else if ($strFirst == 'W')
        {
            $mmW->Init($mm->fMax, $mm->fMin);
            $mmW->Set($fVal);
            if ($mm->Fit($fVal))         $strColor = 'gray';
            else if ($mmB->Fit($fVal))  $strColor = 'silver';
        }
        else if ($strFirst == 'M')
        {
            if ($mmW->Fit($fVal))        $strColor = 'gray';
            else if ($mmB->Fit($fVal))  $strColor = 'silver';
        }
        else if ($strFirst == 'E')
        {
            if ($mmB->Fit($fVal) || $mmB->Fit($fNext))     $strColor = 'yellow';
        }
        _echoSmaTableItem($stock_his, $strKey, $fVal, $fNext, $ref, $callback, $callback2, $strColor, $bChinese);
    }
}

function _selectSmaExternalLink($strSymbol)
{
    $sym = new StockSymbol($strSymbol);
    if ($sym->IsSymbolA())
    {
        if ($sym->IsFundA() == false && $sym->IsIndexA() == false)
        {
            return GetSinaN8n8Link($sym);
        }
    }
    return GetXueQiuLink($sym);
}

function EchoSmaParagraphBegin($stock_his, $bChinese)
{
	$strDate = $stock_his->strDate;
	$strSymbol = $stock_his->GetStockSymbol();
	$strSymbolLink = _selectSmaExternalLink($strSymbol);
	$arColumn = GetSmaTableColumn($bChinese);
	$strSMA = $arColumn[0];
	$strDays = $arColumn[3];
    if ($bChinese)     
    {
        $str = "{$strSymbolLink}从{$strDate}开始的过去100个交易日中{$strSMA}落在当天成交范围内的{$strDays}";
    }
    else
    {
        $str = "$strDays of $strSymbolLink trading range covered the $strSMA in past 100 trading days starting from $strDate";
    }
    $str .= ' '.GetStockSymbolLink('stockhistory', $strSymbol, $bChinese, '历史记录', 'History');
    EchoParagraphBegin($str);
    return $arColumn;
}

function EchoSmaTable($arColumn, $stock_his, $bChinese, $ref = false, $callback = false, $callback2 = false)
{
	if ($bChinese)	$strEst = $arColumn[1];
	else				$strEst = ' '.$arColumn[1];
	$strNextEst = 'T+1'.$strEst;
	$arColumn[] = $strNextEst;
	
	$iWidth = 360;
    $strColumnEx = '';
	if ($callback)
    {
    	$est_ref = call_user_func($callback, $ref);
    	$strColumnEx = GetTableColumn(110, GetXueQiuLink($est_ref->sym).$strEst);
    	$strColumnEx .= GetTableColumn(70, $strNextEst);
    	$iWidth += 180;
    }
    
    $strUserDefined = '';  
    if ($callback2)
    {
    	$strUserDefined = GetTableColumn(100, call_user_func($callback2, $bChinese));
    	$iWidth += 100;
    }
    
    $strWidth = strval($iWidth);
    echo <<<END
    <TABLE borderColor=#cccccc cellSpacing=0 width=$strWidth border=1 class="text" id="smatable">
    <tr>
        <td class=c1 width=90 align=center>{$arColumn[0]}</td>
        <td class=c1 width=70 align=center>{$arColumn[1]}</td>
        <td class=c1 width=70 align=center>{$arColumn[2]}</td>
        <td class=c1 width=60 align=center>{$arColumn[3]}</td>
        <td class=c1 width=70 align=center>{$arColumn[4]}</td>
        $strColumnEx
        $strUserDefined
    </tr>
END;

    _echoSmaTableData($stock_his, $ref, $callback, $callback2, $bChinese);
    EchoTableEnd();
}

function EchoSmaParagraph($stock_ref, $bChinese, $ref = false, $callback = false, $callback2 = false)
{
	$stock_his = new StockHistory($stock_ref);
	$arColumn = EchoSmaParagraphBegin($stock_his, $bChinese);
	EchoSmaTable($arColumn, $stock_his, $bChinese, $ref, $callback, $callback2);
    EchoParagraphEnd();
}

function EchoSmaLeverageParagraph($stock_his, $arRef, $callback, $bChinese, $callback2 = false)
{
    if ($stock_his == false)              return;
    
	$arColumn = EchoSmaParagraphBegin($stock_his, $bChinese);
	foreach ($arRef as $ref)
	{
		EchoSmaTable($arColumn, $stock_his, $bChinese, $ref, $callback, $callback2);
		EchoNewLine();
	}
    EchoParagraphEnd();
}

?>
