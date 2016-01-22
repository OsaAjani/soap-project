<div class="col-xs-12 col-md-6" id="runningTrucksBlock">
	<div class="panel panel-default">
		<div class="panel-heading">Camions qui circulent</div>
		<div class="panel-body">
			<table class="table">
				<tr>
					<th>Camion</th>
					<th>Chauffeur</th>
					<th>Position</th>
					<th>Status</th>
				</tr>
				<?php foreach ($paths as $path) { ?>
					<tr>
						<td><?php secho($path['truck']['matriculation']); ?></td>
						<td><?php secho($path['driver']['name']); ?></td>
						<td><?php secho(($path['position'] ? $path['position']['latitude'] . '/' . $path['position']['longitude'] : 'Inconnue')); ?></td>
						<td><?php echo internalTools::getIconForPathStatus($path['status']); ?></td>
					</tr>
				<?php } ?>
			</table>
		</div>
	</div>
</div>
