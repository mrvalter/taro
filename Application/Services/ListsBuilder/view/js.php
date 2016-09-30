<script>
$(document).ready(function(){
	
	<?if($this->canAdd):?>
			
	$('#add-button').click(function(){
		$.ajax({
			url: '<?=isset($this->addParams['getFormAction'])? $this->addParams['getFormAction'] : ''?>',
			data: '<?=isset($this->addParams['getFormParams'])? $this->addParams['getFormParams'] : ''?>',
			type: "POST",			
		}).done(function(responce){
			$('#update-dialog .modal-body').html(responce);
			$("#update-dialog").modal({
				backdrop: 'static',
				keyboard: true
			});
			$('#update-dialog').modal('show');
			$('#save-form-button').show();
			$('#update-form-button').hide();
			$('#editModalLabel').html('Добавление записи');
		});
		return false;				
	});		
	
	/* Добавление записи */
	$('#save-form-button').click(function(){
		
		var formData = $('#update-dialog').find('form').serialize();
		
		$.ajax({
			url: '<?=isset($this->addParams['action'])? $this->addParams['action'] : ''?>',
			data: '<?=isset($this->addParams['params'])? $this->addParams['params'] : ''?>' + '&' + formData,
			type: "POST",
			dataType: 'json',
		}).done(function(responce){
			if(responce.errors[0] == undefined){				
				$('#table-list tbody').prepend(responce.result);
				$('#update-dialog').modal('hide');
			}else{
				alert(responce.errors);
			}
		});
		return false;
	});
	<?endif;?>
	
	
	<?if($this->canDelete):?>
	/* Удаление записи */
	$('#table-list').on('click', '.__del-row',function(){	
		
		var tr = $(this).parents('tr');
		var id = tr.data('id');
		
		$.ajax({
			url: '<?=isset($this->deleteParams['action'])? $this->deleteParams['action'] : ''?>',
			data: '<?=isset($this->deleteParams['params'])? $this->deleteParams['params'] : ''?>' + '&' + 'id[]='+id,
			type: "POST",
			dataType: 'json',
		}).done(function(responce){
			if(responce.errors[0] == undefined){
				$(tr).remove();
			}else{
				alert(responce.errors);
			}
		});
		return false;
	});
	<?endif;?>
	
	<?if($this->canUpdate):?>
	
	var UPD_ROW = {};
	/* Обновление записи диологовое окно */
	$('#table-list').on('click', '.__upd-row',function(){
		var id = $(this).data('id');
		UPD_ROW = $(this).parents('tr');
		
		$.ajax({
			url: '<?=isset($this->updateParams['getFormAction'])? $this->updateParams['getFormAction'] : ''?>',
			data: '<?=isset($this->updateParams['getFormParams'])? $this->updateParams['getFormParams'] : ''?>' + '&' + 'id='+id,
			type: "POST",			
		}).done(function(responce){
			$('#update-dialog .modal-body').html(responce);
			$("#update-dialog").modal({
				backdrop: 'static',
				keyboard: true
			});
			$('#update-dialog').modal('show');
			$('#save-form-button').hide();
			$('#update-form-button').show();
			$('#editModalLabel').html('Обновление записи');
			
		});
		return false;				
	});
	
	/* Обновление записи */
	$('#update-form-button').click(function(){
		
		var formData = $('#update-dialog').find('form').serialize();
		
		$.ajax({
			url: '<?=isset($this->addParams['action'])? $this->addParams['action'] : ''?>',
			data: '<?=isset($this->addParams['params'])? $this->addParams['params'] : ''?>' + '&' + formData,
			type: "POST",
			dataType: 'json',
		}).done(function(responce){
			if(responce.errors[0] == undefined){
					
				UPD_ROW.after(responce.result);
				UPD_ROW.remove();
				UPD_ROW = {};
				//$('#table-list tbody').prepend(responce.result);
				$('#update-dialog').modal('hide');
			}else{
				alert(responce.errors);
			}
		});
		return false;
	});
	
	/* Обновляем чекбоксы */
	$('#table-list').on('change', '.__checkbox',function(){
		var id = $(this).data('id');
		var name = $(this).prop('name');
		var value = $(this).prop('checked')? $(this).val() : 0;
		
		console.log('<?=isset($this->updateParams['params'])? $this->updateParams['params'] : ''?>&id='+id+'&'+name+'='+value);
		$.ajax({
			url: '<?=isset($this->updateParams['action'])? $this->updateParams['action'] : ''?>',
			data: '<?=isset($this->updateParams['params'])? $this->updateParams['params'] : ''?>&id='+id+'&'+name+'='+value,
			type: "POST",
			dataType: 'json',
		}).done(function(responce){
			$('body').prepend(responce);
		});
		return false;
		
		
	});
	
	<?endif;?>
			
	
	<?if($this->showCheckAll):?>
	/* Показыать общие чекбоксы */
	$('#checkAll').change(function(){
		if($(this).prop('checked')){
			$('#table-list input[type=checkbox]').prop('checked', true);
		}else{
			$('#table-list input[type=checkbox]').prop('checked', false);
		}
	});
	<?endif;?>
});
</script>
