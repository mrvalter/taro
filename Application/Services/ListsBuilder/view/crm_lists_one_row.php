
<tr data-id="<?=$item->id?>">
	<?if($showcheckAll):?>
		<!--<td data-id="<?=$item->id?>"><input type="checkbox" name="id[]" value="<?=$item->id?>"></td>-->
	<?endif;?>
	<?foreach($names as $name=>$label):?>
		<?if(!isset($types[$name])) {$types[$name] = 'text';}?>
		<td>
		<?switch ($types[$name]):
			case 'text':?>
				<?= htmlspecialchars($item->$name)?>
			<?break;?>
			<?case 'checkbox':?>
				<?if($canUpdate):?>
					<input data-id="<?=$item->id?>" class="__checkbox" name="<?=$name?>" type="checkbox" <?=(bool)$item->$name == 1? 'checked="checked"': ''?> value="1" />
				<?else:?>
					<?=(bool)$item->$name == 1? 'Да': 'Нет'?>
				<?endif;?>
			<?break;?>
		<? endswitch;?>		
		
		</td>
	<?endforeach;?>
	<?if($operationExists):?>
		<td style="width: 100px;">
		<?if($canDelete):?>
			<i data-id="<?=$item->id?>" class="fa fa-trash pointer zoom-hover __del-row"></i>
		<?endif;?>
			
		<?if($canUpdate):?>
			<i data-id="<?=$item->id?>" class="fa fa-pencil pointer zoom-hover __upd-row"></i>
		<?endif;?>
		</td>
	<?endif?>
</tr>
