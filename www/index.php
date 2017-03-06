<html>
    <head>
		<script src="/media/bower/jquery/dist/jquery.min.js"></script>
        <script src="/media/js/game_b/game.js"></script>
    </head> 
   
<style>		
	.fill-line {
		transition-property: width;
		transition-duration: 1.5s;
	}
	.container, .fill-line {
		border-radius: 50px;
	}
	
</style>

	<body>
		<div style="height: 30px;" data-sw="value, Character.name"></div>
		
		<div data-sw="event, health-line, Character.health">
			<div class="numeric-line" style="width:20px;"></div>
			<div class="container" style="height:20px; width:300px; border: 1px solid green">
				<div class="fill-line" style="background-color: green; height:100%; width:0px;"></div>
			</div>
		</div>
		<div style="height: 20px;"></div>
		<div data-sw="event, mana-line, Character.mana">
			<div class="numeric-line" style="width:20px;"></div>
			<div class="container" style="height:20px; width:300px; border: 1px solid blue">
				<div class="fill-line" style="background-color: blue; height:100%; width:0px;"></div>
			</div>
		</div>	
		
		<script>
			var gManager = {};															
			var character = {};
			
			$(window).on('load', function(){				
				gManager = new AutoSWManager();								
				character = new Character(12, 'Alex', 'male', 700, 800, 1200, 1200);
				gManager.addEntity(character);
				
				
				gManager.addEvent('health-line',function(el, value, swManager){															
					var character = swManager.getEntityByName('Character');			
					let maxHealth = character.maxHealth;
					let maxWidth = el.find('.container').width();					
					let nowWidth = Math.floor(maxWidth*value/maxHealth);					
					el.find('.fill-line').css('width', nowWidth);
					el.find('.numeric-line').html(value+'\\'+maxHealth);
					
				});
				
				gManager.addEvent('mana-line',function(el, value, swManager){					
					var character = swManager.getEntityByName('Character');			
					let maxMana = character.maxMana;
					let maxWidth = el.find('.container').width();						
					let nowWidth = Math.floor(maxWidth*value/maxMana);					
					el.find('.fill-line').css('width', nowWidth);
					el.find('.numeric-line').html(value+'\\'+maxMana);
										
				});				
				
				
				gManager.draw();
				
								
				
			});
			
			$(document).ready(function(){
										
				/*character.name = 'new name!!';
				character.sex = 'new male';
				character.maxHealth = 800;
				character.health = 400;
				character.mana = 400;
				character.maxMana = 400;*/
				
				
			});
		</script>
	</body>
</html>	


<?php
die();
setlocale(LC_ALL, 'ru_RU.utf8');

	
function e($v) {
	echo htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}


require_once __DIR__.'/../Application/App.php';
$loader = require_once __DIR__.'/../Application/autoload.php';

App::run(__DIR__, $loader);

