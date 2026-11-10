<form method="get" class="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<div class="input-group">
		<input type="text" class="form-control" placeholder="<?php echo esc_html__( 'Search...', 'citytours' ) ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
		<span class="input-group-btn">
			<button class="btn btn-default" type="submit" style="margin-left:0;"><i class="icon-search"></i></button>
		</span>
	</div>
	<input type="hidden" name="post_type" value="post">
</form>