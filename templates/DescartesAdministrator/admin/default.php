<?php
	$gestion = new gestion();
	$incs = new internalIncs();
	$incs->head('Accueil');
?>
<?php 
	$this->headerAdmin();
?>
	<div class="section admin-section">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<h2 class="section-title">Administration</h2>
				</div>
				<div class="row">
					<?php $gestion->runningTrucks(); ?>
					<?php $gestion->brokenTrucks(); ?>
				</div>
				<div class="row">
					<?php $gestion->currentInterventions(); ?>
				</div>
			</div>
		</div> <!-- /.row -->
	</div> <!-- /.container -->
</div> <!-- /.section -->
<?php
	$incs->footer();
