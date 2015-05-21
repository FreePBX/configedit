<link rel="stylesheet" href="assets/configedit/css/themes/proton/style.min.css" />
<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<h1><?php echo _("Configuration File Editor")?></h1>
			<div class="panel panel-info">
				<div class="panel-heading">
					<div class="panel-title">
						<a href="#" data-toggle="collapse" data-target="#moreinfo"><i class="glyphicon glyphicon-info-sign"></i></a>&nbsp;&nbsp;&nbsp;<?php echo _("What is Configuration File Editor")?>
					</div>
				</div>
				<!--At some point we can probably kill this... Maybe make is a 1 time panel that may be dismissed-->
				<div class="panel-body collapse" id="moreinfo">
					<p><?php echo sprintf(_("Configuration file editor gives you the ability to edit custom %s files in the browser that you would normally have to edit through the CLI."),$brand)?></p>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-4">
			<div id="jstree-proton-1" style="margin-top:20px;" class="proton-demo">
				<ul>
					<?php foreach($listings as $path => $listing) {?>
						<li data-jstree='{"opened":true,"selected":true, "icon":"fa fa-folder-o"}'><?php echo $listing['name']?>
							<?php foreach($listing['files'] as $file) {?>
								<ul>
									<li data-jstree='{"icon":"fa <?php echo !empty($file['size']) ? 'fa-file-text-o' : 'fa-file-o'?>"}' data-path="<?php echo $path?>" data-file="<?php echo $file['file']?>" data-mime="<?php echo $listing['mime']?>"><?php echo $file['file']?></li>
								</ul>
							<?php } ?>
						</li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<div class="col-md-8">
			<h3 id="filemessage"><?php echo _('Click a file on the left to edit')?></h3>
			<div id="message" class="alert alert-danger hidden" role="alert"></div>
			<textarea id="editor" disabled></textarea>
			<br/>
			<button id="save" class="pull-right" disabled>Save</button>
		</div>
	</div>
</div>