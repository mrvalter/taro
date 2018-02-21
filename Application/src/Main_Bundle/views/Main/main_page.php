<?namespace Kernel\Services\Viewer;?>
<?class Template extends Viewer {?>

<?$name = '<script>alert("ALERT SCRIPT");</script>'?>
<?Vi::extend('@layouts/default.layout') ?>

<?Vi::beginBlock('head', true)?>
	
<?Vi::endBlock();?>


<?Vi::beginBlock('title');?> Тайтл страницы <?Vi::endBlock();?>

Написано вне блока

<?Vi::beginBlock('content');?>

<?= e($name) ?>

<?Vi::endBlock();?>

<?}?>


