<nav class="container">
	<ul class="breadcrumb clearfix">
		<?php if (function_exists('breadcrumbs')) breadcrumbs(); ?>
		<?php get_template_part('metadata'); ?>
	</ul>
</nav>