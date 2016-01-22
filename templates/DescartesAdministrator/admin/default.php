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
				<div id="map"></div>
			</div>
			<hr/>
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
<script>
	var map;
	function initMap()
	{
		map = new google.maps.Map(document.getElementById('map'), {
			center: {lat: 46.768196, lng: 2.4326639},
			zoom: 7
		});
	}

</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB-SExd1Yer7Cn4IZnrvBjw4Mo7LGbCZao&callback=initMap"></script>
<script>
	jQuery('document').ready(function($)
	{
		instanciateAjaxBlocks();
		jQuery('body').on('click', '.ajaxLink', function (e){
			e.preventDefault();
			jQuery.get(jQuery(this).attr('href'));
			reloadAjaxBlocks();
		});
	});
</script>
<?php
	$incs->footer();
