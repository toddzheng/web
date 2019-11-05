<?php
require_once('stocktable.php');

function _echoFundPurchaseTableItem($strStockId, $strAmount, $bChinese)
{
	$strSymbol = SqlGetStockSymbol($strStockId);
    EchoTableColumn(array(GetMyStockLink($strSymbol), $strAmount));
/*    
    $strLink = GetMyStockLink($strSymbol);

    echo <<<END
    <tr>
        <td class=c1>$strLink</td>
        <td class=c1>$strAmount</td>
    </tr>
END;*/
}

function _echoFundPurchaseTableData($strMemberId, $iStart, $iNum, $bChinese)
{
	if ($result = SqlGetFundPurchase($strMemberId, $iStart, $iNum)) 
	{
		while ($record = mysql_fetch_assoc($result)) 
		{
			_echoFundPurchaseTableItem($record['stock_id'], $record['amount'], $bChinese);
		}
		@mysql_free_result($result);
	}
}

function EchoFundPurchaseParagraph($str, $strMemberId, $bChinese, $iStart = 0, $iNum = TABLE_COMMON_DISPLAY)
{
/*	$strSymbol = GetTableColumnSymbol();
    $arColumn = array($strSymbol, '金额');
    
    echo <<<END
	    <p>$str
        <TABLE borderColor=#cccccc cellSpacing=0 width=200 border=1 class="text" id="fund">
        <tr>
            <td class=c1 width=100 align=center>{$arColumn[0]}</td>
            <td class=c1 width=100 align=center>{$arColumn[1]}</td>
        </tr>
END;
*/
	EchoTableParagraphBegin(array(new TableColumnSymbol(),
								   new TableColumnAmount()
								   ), 'fund', $str);

	_echoFundPurchaseTableData($strMemberId, $iStart, $iNum, $bChinese);
    EchoTableParagraphEnd();
}

?>
