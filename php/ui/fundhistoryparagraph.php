<?php
require_once('stocktable.php');

function GetDailyCloseByDate($ref, $sql, $strDate)
{
	if ($ref)
	{
		if ($history = $sql->Get($strDate))
		{
			if ($history_prev = $sql->GetPrev($strDate))
			{
				$ref->SetPrice($history['close'], $history_prev['close']);
				return $ref;
			}
		}
	}
    return false;
}

function EchoFundHistoryTableItem($ref, $csv, $history, $fund, $clone_ref)
{
	$strDate = $history['date'];
    $strFundClose = $history['close'];
    $strNetValue = $fund['close'];
    $strEstValue = $fund['estimated'];
    $strEstTime = $fund['time'];
    
    $fNetValue = floatval($strNetValue);
    $fFundClose = floatval($strFundClose);
    $strNetValueDisplay = StockGetPriceDisplay($fNetValue, $fFundClose);
    $strPercentage = StockGetPercentageDisplay($fFundClose, $fNetValue);
    if ($csv && (empty($fNetValue) == false))
    {
    	$csv->WriteArray(array($strDate, $strNetValue, strval(StockGetPercentage($fFundClose, $fNetValue))));
    }
    
    $strEstClose = ''; 
    $strEstChange = '';
    if ($clone_ref)
    {
        $strEstClose = $clone_ref->GetCurrentPriceDisplay();
        $strEstChange = $clone_ref->GetCurrentPercentageDisplay();
    }

    $strEstValueDisplay = '';
    $strError = ''; 
    if ($strEstValue != '0')
    {
        $fEstValue = floatval($strEstValue);
        $strEstValueDisplay = StockGetPriceDisplay($fEstValue, $fFundClose);
        $fPercentage = GetEstErrorPercentage($fEstValue, $fNetValue);
        if (empty($fPercentage))
        {
            $strError = '<font color=grey>0</font>'; 
        }
        else
        {
            $strError = StockGetPercentageDisplay($fEstValue, $fNetValue);
        }
    }
    else
    {
        $strEstTime = '';
    }
    
    echo <<<END
    <tr>
        <td class=c1>$strDate</td>
        <td class=c1>$strFundClose</td>
        <td class=c1>$strNetValueDisplay</td>
        <td class=c1>$strPercentage</td>
        <td class=c1>$strEstClose</td>
        <td class=c1>$strEstChange</td>
        <td class=c1>$strEstValueDisplay</td>
        <td class=c1>$strEstTime</td>
        <td class=c1>$strError</td>
    </tr>
END;
}

function GetNextTradingDayYMD($strYMD)
{
    $ymd = new StringYMD($strYMD);
    $iTick = $ymd->GetNextTradingDayTick();
    
    $next_ymd = new TickYMD($iTick);
    return $next_ymd->GetYMD();
}

function _echoHistoryTableData($sql, $fund, $csv, $est_ref, $iStart, $iNum)
{
	$bSameDayNetValue	 = true;
	$clone_ref = false;
	if ($est_ref)
	{
		if ($est_ref->sym->IsSymbolUS())		$bSameDayNetValue	 = false;
		$est_sql = new StockHistorySql($est_ref->GetStockId());
		$clone_ref = clone $est_ref;
	}
	
    if ($result = $sql->GetAll($iStart, $iNum)) 
    {
        while ($record = mysql_fetch_assoc($result)) 
        {
            if ($bSameDayNetValue)
            {
                $strDate = $record['date'];
            }
            else
            {
                $strDate = GetNextTradingDayYMD($record['date']);
            }
            
            if ($history = $sql->stock_sql->Get($strDate))
            {
                EchoFundHistoryTableItem($ref, $csv, $history, $record, GetDailyCloseByDate($clone_ref, $est_sql, $record['date']));
            }
        }
        @mysql_free_result($result);
    }
}

function EchoFundHistoryTableBegin($arColumn)
{
    echo <<<END
    <TABLE borderColor=#cccccc cellSpacing=0 width=640 border=1 class="text" id="history">
    <tr>
        <td class=c1 width=100 align=center>{$arColumn[0]}</td>
        <td class=c1 width=60 align=center>{$arColumn[1]}</td>
        <td class=c1 width=60 align=center>{$arColumn[2]}</td>
        <td class=c1 width=70 align=center>{$arColumn[3]}</td>
        <td class=c1 width=80 align=center>{$arColumn[4]}</td>
        <td class=c1 width=60 align=center>{$arColumn[5]}</td>
        <td class=c1 width=70 align=center>{$arColumn[6]}</td>
        <td class=c1 width=80 align=center>{$arColumn[7]}</td>
        <td class=c1 width=60 align=center>{$arColumn[8]}</td>
    </tr>
END;
}

function FundHistoryTableGetColumn($est_ref, $bChinese)
{
    if ($est_ref)
    {
        $strSymbol = RefGetMyStockLink($est_ref, $bChinese);
        if ($bChinese)  $strChange = '涨跌';
        else              $strChange = 'Change';
    }
    else
    {
        $strSymbol = '';
        $strChange = '';
    }
    
	$arFundEst = GetFundEstTableColumn($bChinese);
	$strOfficialEst = $arFundEst[1];
	$strNetValue = $arFundEst[7];
	$arSma = GetSmaTableColumn($bChinese);
	$strPremium = $arSma[2];
    if ($bChinese)     
    {
        $arColumn = array('日期', '<font color=indigo>收盘价</font>', $strNetValue, $strPremium, $strSymbol, $strChange, $strOfficialEst, '估值时间', '误差');
    }
    else
    {
        $arColumn = array('Date', '<font color=indigo>Close</font>', $strNetValue, $strPremium, $strSymbol, $strChange, $strOfficialEst, 'Est Time', 'Error');
    }
    return $arColumn;
}

function EchoFundHistoryParagraph($fund, $bChinese, $csv = false, $iStart = 0, $iNum = TABLE_COMMON_DISPLAY)
{
	$est_ref = $fund->est_ref;
    $arColumn = FundHistoryTableGetColumn($est_ref, $bChinese);
    $strSymbol = $fund->GetStockSymbol();
    $strSymbolLink = GetMyStockLink($strSymbol, $bChinese);
    if ($bChinese)     
    {
        $str = "{$strSymbolLink}历史{$arColumn[1]}相对于{$arColumn[2]}的{$arColumn[3]}";
    }
    else
    {
        $str = "The {$arColumn[3]} history of $strSymbolLink {$arColumn[1]} price comparing with {$arColumn[2]}";
    }
    
	$sql = new FundHistorySql($fund->GetStockId());
    if (IsTableCommonDisplay($iStart, $iNum))
    {
        $str .= ' '.GetNetValueHistoryLink($strSymbol, $bChinese);
        $strNavLink = '';
    }
    else
    {
    	$iTotal = $sql->Count();
    	$strNavLink = StockGetNavLink($strSymbol, $iTotal, $iStart, $iNum, $bChinese);
    }

    if (AcctIsDebug())		$str .= ' '.$fund->DebugLink();
    
    EchoParagraphBegin($str.' '.$strNavLink);
    EchoFundHistoryTableBegin($arColumn);
    _echoHistoryTableData($sql, $fund, $csv, $est_ref, $iStart, $iNum);
    EchoTableParagraphEnd($strNavLink);
}

?>
