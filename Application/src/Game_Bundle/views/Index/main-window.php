{extends layouts@default.layout}

{block head add}
	<script>
		alert('head script');
	</script>
{/block}

{block title}Тайтл страницы{/block}


{block content}
Контент страницы 


{/block}

{block empty /}