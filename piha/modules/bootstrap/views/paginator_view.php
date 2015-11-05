<?
/*
@var CListData $listData
*/
?>
<div class="pagination">
	<ul>
		<? if ($listData->prevUrl()): ?>
			<li><a href="<?=$prevUrl;?>">Prev</a></li>
		<? endif; ?>
		<? foreach($listData->nearUrl() as $number => $url): ?>
			<? if ($listData->getCurrentPage() === $number):?>
				<li class="active"><a href="<?=$url;?>"><?=$number;?></a></li>
			<? else: ?>
				<li><a href="<?=$url;?>"><?=$number;?></a></li>
			<? endif; ?>
		<? endforeach; ?>
		<? if ($listData->nextUrl()): ?>
			<li><a href="<?=$nextUrl;?>">Next</a></li>
		<? endif; ?>
	</ul>
</div>
