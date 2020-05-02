<?php
require_once('/php/stockgroup.php');

class GroupIdAccount extends StockAccount
{
    function GroupIdAccount() 
    {
        parent::StockAccount('groupid');
    }
    
    function GetStockGroupId()
    {
    	$strGroupId = $this->GetQuery();
    	if (is_numeric($strGroupId) == false)		return false;
    	
    	return $strGroupId; 
    }
    
    function EchoStockGroup()
    {
    	if ($strGroupId = $this->GetStockGroupId())
    	{
    		$this->EchoStockGroupParagraph($strGroupId);
    	}
    	return $strGroupId;
    }
    
    function GetWhoseGroupDisplay()
    {
    	if ($strGroupId = $this->GetStockGroupId())
    	{
    		if ($strMemberId = SqlGetStockGroupMemberId($strGroupId))
    		{
    			return $this->GetWhoseDisplay($strMemberId).SqlGetStockGroupName($strGroupId); 
    		}
    		return '';
    	}
    	return $this->GetWhoseAllDisplay();
    }
}

?>
