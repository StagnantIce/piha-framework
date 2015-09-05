<html>
<body>


<h1>Piha test page</h1>


<? echo 'You see layout ' . $this->layoutPath . ' page';?><br/><br/>

<? $this->content();?>

<br/>
<br/>

<div>Page generated in <?= sprintf('%0.3f', CCore::app()->getTime());?> sec / <?=round(memory_get_peak_usage()/(1024*1024),2) ?> mb
</div>

</body>
</html>