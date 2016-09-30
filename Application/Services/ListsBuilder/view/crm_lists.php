<link href="<?=$this->mediaGlob("/bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.css")?>" rel="stylesheet">
<link href="<?=$this->mediaGlob("/bower_components/datatables-responsive/css/dataTables.responsive.css")?>" rel="stylesheet">

<?if($canUpdate || $canAdd):?>
	<div class="modal fade" id="update-dialog" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog" style="z-index:2000;">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="editModalLabel">Редактирование</h4>
				</div>
				<div class="modal-body">

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
					<button type="button" id="save-form-button" class="btn btn-primary">Сохранить</button>
					<button type="button" id="update-form-button" class="btn btn-primary">Обновить</button>
				</div>
			</div>
		</div>
	</div>	
<? endif; ?>
	



<?if($canAdd):?>
	<div>			
		<button id="add-button" class="btn btn-primary">Добавить</button>		
	</div>
	<div style="clear: both; height: 20px;"></div>
<?endif;?>


<div class="panel panel-default">
        <div class="panel-heading">
            Продажи препаратов
        </div>
        <div class="panel-body">
            <div class="dataTable_wrapper">    
                <table id="table-list" class="table table-striped table-bordered table-hover" style="max-width: 800px;">
                    <thead>
                        <tr>				
                            <?if($showcheckAll):?>
                                <!--<th> <input type="checkbox" id="checkAll" name="chackAll" /> </th>-->
                            <?endif;?>

                            <?foreach($names as $name=>$title):?>
                                <th><?=  htmlspecialchars($title)?></th>
                            <?endforeach;?>	
                            <?if($operationExists):?>
                                <th>Действия</th>
                            <?endif;?>
                        </tr>				
                    </thead>
                    <tbody>				
                        <?foreach($data as $i=>$item):?>
                            <?include dirname(__FILE__).'/crm_lists_one_row.php'?>
                        <?endforeach;?>			
                    </tbody>
                </table>
            </div>
        </div>
</div>
    
    
<!-- DataTables JavaScript -->
<script src="<?=$this->mediaGlob("/bower_components/datatables/media/js/jquery.dataTables.min.js")?>"></script>
<script src="<?=$this->mediaGlob("/bower_components/datatables-plugins/integration/bootstrap/3/dataTables.bootstrap.min.js")?>"></script>

<script>
$(document).ready(function() {
	$('#table-list').DataTable({
		responsive: true,
		"order": [[ 3, "desc" ]]			
	});
});
</script>