<?php require_once('php/_groups.php'); ?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>标普500基金净值计算工具</title>
<meta name="description" content="计算标普500基金的净值, 目前包括沪市标普500(SH513500)和深市标普500(SZ161125).使用标普500指数(^GSPC)估值, SPY仅用于参考.">
<link href="../../common/style.css" rel="stylesheet" type="text/css" />
</head>

<body bgproperties=fixed leftmargin=0 topmargin=0>
<?php _LayoutTopLeft(true); ?>

<div>
<h1>标普500基金净值计算工具</h1>
<p>使用标普500指数(^GSPC)估值, SPY仅用于参考.
<?php EchoSpyFundToolTable(true); ?>
</p>
<?php EchoPromotionHead('', true); ?>
<p>相关软件:
<?php 
    EchoStockCategoryLinks(true);
    EchoStockGroupLinks(true);
?>
</p>
</div>

<?php LayoutTailLogin(true); ?>

</body>
</html>