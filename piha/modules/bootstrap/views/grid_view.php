<?
/*
	@var CListData $listData
	@var array $columns
	@var CModel $model
	@var array $htmlOptions
*/
use piha\modules\core\classes\CHtml;
use piha\modules\core\classes\CView;

$html = CHtml::create();
$trOptions = CHtml::popOption($htmlOptions, 'tr');
?>


<?= $html->table(CHtml::popOption($htmlOptions, 'table'), false); ?>
	<thead>
		<?foreach($columns as $column): ?>
			<th>
				<?=$column['label'];?>
			</th>
		<? endforeach; ?>
	</thead>
	<tbody>
		<?foreach($listData->getData() as $row): ?>
			<?= $html->tr(CView::Value($trOptions, array( $model ? new $model($row) : $row)), false);?>
				<?foreach($columns as $column): ?>
					<td>
						<?
						if (!isset($column['value'])) {
							if (isset($row[$column['id']])) {
								echo $row[$column['id']];
							}
						} else if ($model || isset($column['model'])) {
							$modelClass = isset($column['model']) ? $column['model'] : $model;
							echo $this->value($column['value'], new $modelClass($row));
						} else {
							echo $this->value($column['value'], $row);
						}
						?>
					</td>
				<? endforeach; ?>
			<?= $html->end('tr');?>
		<? endforeach; ?>
	</tbody>
<? $html->end('table')->render(); ?>