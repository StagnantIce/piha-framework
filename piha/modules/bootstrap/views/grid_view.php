<?
/*
	@var CListData $listData
	@var array $columns
*/
?>
<table class = "table table-bordered table-striped">
	<thead>
		<?foreach($columns as $column): ?>
			<th>
				<?=$column['label'];?>
			</th>
		<? endforeach; ?>
	</thead>
	<tbody>
		<?foreach($listData->getData() as $row): ?>
			<tr>
				<?foreach($columns as $column): ?>
					<td>
						<?
						if (isset($row[$column['id']])) {
							echo $row[$column['id']];
						} else if ($model && $column['model']) {
							echo $this->value($column['value'], new $model($row));
						} else {
							echo $this->value($column['value'], array($row, $column));
						}
						?>
					</td>
				<? endforeach; ?>
			</tr>
		<? endforeach; ?>
	</tbody>
</table>