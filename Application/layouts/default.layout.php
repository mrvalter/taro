<!DOCTYPE html>
<html>
    <head>
		<? Vi::beginBlock('head'); ?>
        <meta charset="utf-8" />
        <title><? Vi::title ?></title>
        <script src="/media/bower/jquery/dist/jquery.min.js"></script>
        <script src="/media/bower/bootstrap/dist/js/bootstrap.min.js"></script>
        <link href="/media/bower/components-font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
        <link href="/media/bower/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="/media/css/screen.css" rel="stylesheet" type="text/css"/>
		<? Vi::endBlock() ?>
    </head> 
   
	<body>
	   <div id="main_menu" style="width: 200px; position: absolute;">		
		<? Vi::widget('/widgets/menu/leftmenu') ?>
	   </div>
        <div class="page-wrapper">
            <header></header>
            <div class="row-block page-title"><?Vi::beginBlock('pageTitle') ?><?Vi::endBlock()?></div>
            <div class="wrapper-content">
                <?Vi::beginBlock('content') ?>
				<?Vi::endBlock()?>
            </div>
            
            <div class="page-buffer"></div>
        </div>
       
        <div class="page-footer">
            <div style="padding-left: 220px">
                Sworion Corporation 2016
            </div>
        </div>
</body>
</html>	




