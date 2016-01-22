<div class="col-xs-12 col-md-6" id="brokenTrucksBlock">
	<div class="panel panel-default">
		<div class="panel-heading">Camions en panne</div>
		<div class="panel-body">
			<table class="table">
				<tr>
					<th>Camion</th>
					<th>Demander un réparateur</th>
				</tr>
				<?php foreach ($paths as $path) { ?>
					<tr>
						<td><?php secho($path['truck']['matriculation']); ?></td>
						<td>
							<div class="btn-group action-dropdown">
								<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">Appeler un réparateur <span class="caret"></span></button>
								<ul class="dropdown-menu pull-right" role="menu">
									<?php foreach ($repairers as $repairer) { ?>
										<li><a class="ajaxLink" href="<?php secho($this->generateUrl('gestion', 'askForIntervention', [$path['id'], $repairer['id']])); ?>"><?php secho($repairer['name']); ?></a></li>
									<?php } ?>
								</ul>
							</div>
						</td>
					</tr>
				<?php } ?>
			</table>
		</div>
	</div>
</div>
