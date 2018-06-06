<?php

// ****************************** TableSql class *******************************************************
class TableSql
{
	var $strName;
	
    // constructor 
    function TableSql($strTableName) 
    {
    	$this->strName = $strTableName;
    }
    
    function Create($str)
    {
    	return SqlCreateTable($this->strName, $str);
    }
    
    function Insert($str)
    {
    	$strTableName = $this->strName;
	    $strQuery = 'INSERT INTO '.$strTableName.$str;
	    return SqlDieByQuery($strQuery, $strTableName.' insert data failed');
    }
    
    function Update($str)
    {
    	$strTableName = $this->strName;
    	$strQuery = "UPDATE $strTableName SET $str LIMIT 1";
    	return SqlDieByQuery($strQuery, $strTableName.' update data failed');
    }
    
    function Count($strWhere = false)
    {
    	return SqlCountTableData($this->strName, $strWhere);
    }

    function GetData($strWhere = false, $strOrderBy = false, $strLimit = false)
    {
    	return SqlGetTableData($this->strName, $strWhere, $strOrderBy, $strLimit);
    }

    function GetSingleData($strWhere = false, $strOrderBy = false)
    {
    	return SqlGetSingleTableData($this->strName, $strWhere, $strOrderBy);
    }
    
    function Delete($strWhere, $strLimit = false)
    {
    	return SqlDeleteTableData($this->strName, $strWhere, $strLimit);
    }

    function DeleteById($strId)
    {
    	return $this->Delete(_SqlBuildWhere_id($strId), '1');
    }

    function DeleteInvalidDate()
    {
    	$ar = array();
    	if ($result = $this->GetAll()) 
    	{
    		while ($history = mysql_fetch_assoc($result)) 
    		{
    			$ymd = new OldestYMD();
    			if ($ymd->IsInvalid($history['date']))
    			{
    				$ar[] = $history['id'];
    			}
    		}
    		@mysql_free_result($result);
    	}

    	$iCount = count($ar);
    	if ($iCount > 0)
    	{
    		DebugVal($iCount, $this->strName.' - invalid date'); 
    		foreach ($ar as $strId)
    		{
    			$this->DeleteById($strId);
    		}
    	}
    }
}

// ****************************** IdTableSql class *******************************************************
class IdTableSql extends TableSql
{
	var $strKey;
	var $strId;
	
    function BuildWhere_id_extra($strKey, $strVal)
    {
		return _SqlBuildWhereAndArray(array($this->strKey => $this->strId, $strKey => $strVal));
    }
    
    function BuildWhere_id_stock($strStockId)
    {
		return $this->BuildWhere_id_extra('stock_id', $strStockId);
    }
    
    function BuildWhere_id()
    {
    	return _SqlBuildWhere($this->strKey, $this->strId);
    }
    
    function GetId()
    {
    	return $this->strId;
    }
    
    function IdTableSql($strId, $strPrefix, $strTableName) 
    {
    	$this->strId = $strId;
    	$this->strKey = $strPrefix.'_id';
        parent::TableSql($strTableName);
    }
    
    function Count()
    {
    	return TableSql::Count($this->BuildWhere_id());
    }
    
    function GetAll()
    {
    	return $this->GetData($this->BuildWhere_id());
    }
    
    function DeleteAll()
    {
    	return $this->Delete($this->BuildWhere_id());
    }
}

// ****************************** StockTableSql class *******************************************************
class StockTableSql extends IdTableSql
{
    function BuildWhere_stock_date($strDate)
    {
		return $this->BuildWhere_id_extra('date', $strDate);
    }
    
    function StockTableSql($strStockId, $strTableName) 
    {
        parent::IdTableSql($strStockId, 'stock', $strTableName);
    }
}

// ****************************** MemberTableSql class *******************************************************
class MemberTableSql extends IdTableSql
{
    function MemberTableSql($strMemberId, $strTableName) 
    {
        parent::IdTableSql($strMemberId, 'member', $strTableName);
    }
}

// ****************************** StockGroupTableSql class *******************************************************
class StockGroupTableSql extends IdTableSql
{
    function StockGroupTableSql($strStockGroupId, $strTableName) 
    {
        parent::IdTableSql($strStockGroupId, 'group', $strTableName);
    }
}

?>
