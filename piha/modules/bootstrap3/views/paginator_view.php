<?
/*
@var CListData $listData
*/
?>
<div>
	<ul class="pagination">
		<? if ($prevUrl = $listData->prevUrl()): ?>
			<li><a href="<?=$prevUrl;?>">Prev</a></li>
		<? endif; ?>
		<? foreach($listData->nearUrl() as $number => $url): ?>
			<? if ($listData->getCurrentPage() === $number):?>
				<li class="active"><a href="<?=$url;?>"><?=$number;?></a></li>
			<? else: ?>
				<li><a href="<?=$url;?>"><?=$number;?></a></li>
			<? endif; ?>
		<? endforeach; ?>
		<? if ($nextUrl = $listData->nextUrl()): ?>
			<li><a href="<?=$nextUrl;?>">Next</a></li>
		<? endif; ?>
	</ul>
</div>
