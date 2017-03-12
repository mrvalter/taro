<!DOCTYPE html>
<html>
    <head>			
        <meta charset="utf-8" />
        <title>{block title /}</title>
		
		{block head}
        <script src="/media/bower/jquery/dist/jquery.min.js"></script>
        <script src="/media/bower/bootstrap/dist/js/bootstrap.min.js"></script>
        <link href="/media/bower/components-font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
        <link href="/media/bower/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="/media/css/screen.css" rel="stylesheet" type="text/css"/>
		{/block}
    </head> 
   
	<body>
		<div id="main_menu" style="width: 200px; position: absolute;">		
			{widget /widgets/menu/leftmenu /}
		</div>
        <div class="page-wrapper">
            <header></header>
            <div class="row-block page-title">{block title /}</div>
            <div class="wrapper-content">
                {block content /}
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




