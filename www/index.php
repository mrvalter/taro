<html>
    <head>
		<script src="/media/bower/jquery/dist/jquery.min.js"></script>
        <script src="/media/js/game_b/game.js"></script>
    </head> 
   
<style>		
	.health-line {
		transition-property: width;
		transition-duration: 2s;
	}
	
</style>

	<body>
		<span data-sw="value, Character.name"></span>
		<div>
			<span data-sw="value, Character.health"></span>\<span  data-sw="value, Character.maxHealth"></span>
		</div>
		<div data-sw="event, health-line, Character.health" style="height:20px; width:300px; border: 1px solid green">
			<div class="fill-line" style="background-color: green; height:100%; width:100px;"></div>
		</div>
		
		<div data-sw="event, mana-line, Character.mana" style="margin-top: 20px;height:20px; width:300px; border: 1px solid blue">
			<div class="fill-line" style="background-color: blue; height:100%; width:100px;"></div>
		</div>
		
		<div data-sw="event, expierence-line, Character.expierence" style="margin-top: 20px;height:20px; width:300px; border: 1px solid blue">
			<div class="fill-line" style="background-color: blue; height:100%; width:100px;"></div>
		</div>
		
		<script>
			var gManager = {};															
			var character = {};
			
			$(window).on('load', function(){				
				gManager = new AutoSWManager();								
				character = new Character(12, 'Alex', 'male', 450, 800);
				gManager.addEntity(character);
				
				
				gManager.addEvent('health-line',function(el, value, swManager){
					console.log('health line');
					var character = swManager.getEntityByName('Character');
					console.log(character);
					console.log(character.health);
					console.log(character.maxHealth);
					let maxHealth = character.maxHealth;
					let maxWidth = el.width();										
					let nowWidth = Math.floor(maxWidth*value/maxHealth);					
					el.find('.health-line').css('width', nowWidth);
					
				});
				
				gManager.addEvent('mana-line',function(el, value, swManager){					
					el.find('.mana-line').css('width', value);
				});
				
				gManager.addEvent('expierence-line',function(el, value, swManager){					
					el.find('.expierence-line').css('width', value);
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

