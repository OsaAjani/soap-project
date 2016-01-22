<div class="col-xs-12" id="currentInterventionsBlock">
	<div class="panel panel-default">
		<div class="panel-heading">Interventions en cours</div>
		<div class="panel-body">
			<table class="table">
				<tr>
					<th>Camion</th>
					<th>Chauffeur</th>
					<th>Position</th>
					<th>Réparateur</th>
					<th>Status</th>
				</tr>
				<?php foreach ($interventions as $intervention) { ?>
					<tr>
						<td><?php secho($intervention['path']['truck']['matriculation']); ?></td>
						<td><?php secho($intervention['path']['driver']['name']); ?></td>
						<td><?php secho(($intervention['path']['position'] ? $intervention['path']['position']['latitude'] . '/' . $intervention['path']['position']['longitude'] : 'Inconnue')); ?></td>
						<td><?php secho($intervention['repairer']['name']); ?></td>
						<td><?php echo internalTools::getIconForInterventionStatus($intervention['status']); ?></td>
					</tr>
				<?php } ?>
			</table>
		</div>
	</div>
</div>
