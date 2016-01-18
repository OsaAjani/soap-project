<?php
	$incs = new internalIncs();
	$incs->head('Accueil');
?>
<?php 
	$incs->header("Index");
?>
	<div class="container-fluid main-page-body">
		<div class="row">
			<?php $this->runningTrucks(); ?>
			<?php $this->brokenTrucks(); ?>
		</div>
		<div class="row">
			<?php $this->currentInterventions(); ?>
		</div>
	</div>
<?php
	$incs->footer();
