<?php
/*
 Template Name: Login Page Template
 */
if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

get_header();

global $ct_options;

$signup_url = add_query_arg( 'action', 'register', ct_get_permalink_clang( $ct_options['login_page'] ) );
$login_url = strtok($_SERVER["REQUEST_URI"],'?');

if ( have_posts() ) :
	while ( have_posts() ) : the_post(); ?>

	<section id="hero" class="login">
		<div class="container">
			<div class="row">
				<div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">
					<div id="login">
						<div class="text-center"><img src="<?php echo esc_url( ct_logo_sticky_url() ) ?>" alt="" data-retina="true" ></div>

						<hr>

						<?php if ( isset( $_GET['action'] ) && ( $_GET['action'] == 'register' ) ) { ?>

							<form name="registerform" action="<?php echo esc_url( wp_registration_url() )?>" method="post">
								<?php
								// Display CAPTCHA error message if redirected back due to error
								if (isset($_SESSION['captcha_error_message'])) {
									echo '<div class="alert alert-danger">' . $_SESSION['captcha_error_message'] . '</div>';
									unset($_SESSION['captcha_error_message']); // Clear after displaying
								}
								?>
								<div class="form-group">
									<?php esc_html_e(  'Register For This Site', 'citytours' );?>
								</div>

								<div class="form-group">
									<label><?php esc_html_e(  'Username', 'citytours' ); ?></label>
									<input type="text" name="user_login" class=" form-control" placeholder="<?php esc_html_e(  'Username', 'citytours' ); ?>" value="<?php echo isset($_SESSION['registration_form_data']['user_login']) ? esc_attr($_SESSION['registration_form_data']['user_login']) : ''; ?>">
								</div>
								<div class="form-group">
									<label><?php esc_html_e(  'Email', 'citytours' ); ?></label>
 									<input type="email" name="user_email" class=" form-control" placeholder="<?php esc_html_e(  'Email', 'citytours' ); ?>" value="<?php echo isset($_SESSION['registration_form_data']['user_email']) ? esc_attr($_SESSION['registration_form_data']['user_email']) : ''; ?>">

								</div>
								
								<div class="form-group">
									<label><?php esc_html_e(  'Password', 'citytours' ); ?></label>
									<input type="password" name="pwd" class=" form-control" placeholder="<?php esc_html_e(  'Password', 'citytours' ); ?>">
								</div>
								
								<div class="form-group">
									<?php do_action('custom_captcha_register_form'); ?>
								</div>
								
								<input type="hidden" name="redirect_to" value="<?php echo esc_url( add_query_arg( 'checkemail', 'registered', ct_get_current_page_url() ) ); ?>">

								<div id="pass-info" class="clearfix"></div>

								<button class="btn_full"><?php esc_html_e(  'Create an account', 'citytours' ); ?></button>
								<br /><?php esc_html_e(  'Already a member?', 'citytours' ); ?> <a href="<?php echo esc_url( $login_url );?>"><?php esc_html_e(  'Login', 'citytours' ); ?></a>
								
								<?php
								// Clear form data from session after displaying
								if (isset($_SESSION['registration_form_data'])) {
									unset($_SESSION['registration_form_data']);
								}
								?>
							</form>

						<?php } else if ( isset( $_GET['action'] ) && ( $_GET['action'] == 'lostpassword' ) ) { ?>

							<form name="lostpasswordform" action="<?php echo esc_url( wp_lostpassword_url() ) ?>" method="post">
								<div class="form-group">
									<?php esc_html_e( 'Please enter your username or email address. You will receive a link to create a new password via email.', 'citytours')?>
								</div>

								<div class="form-group">
									<label><?php esc_html_e(  'Username or E-mail', 'citytours' ); ?></label>
									<input type="text" name="user_login"  class="form-control" placeholder="<?php esc_html_e(  'Username or E-mail', 'citytours' ); ?>" value="">
								</div>

								<button type="submit" class="btn_full"><?php esc_html_e( 'Get New Password', 'citytours')?></button>

								<input type="hidden" name="redirect_to" value="<?php echo esc_url( add_query_arg( 'checkemail', 'confirm', $login_url ) ); ?>">

								<br />

								<div style="text-align:center">
									<a href="<?php echo esc_url( $login_url );?>" class="underline"><?php esc_html_e(  'Login', 'citytours' ); ?></a>
									<?php if ( get_option('users_can_register') ) { ?>
									 | <a href="<?php echo esc_url( $signup_url ); ?>" class="underline"><?php esc_html_e(  "Sign Up", 'citytours' ) ?></a>
									<?php } ?>
								</div>
							</form>

						<?php } else { ?>

							<form name="loginform" class="login-form" action="<?php echo esc_url( wp_login_url() )?>" method="post">
								<?php if ( ! empty( $_GET['login'] ) && ( $_GET['login'] == 'failed' ) ) { ?>
									<div class="alert alert-info"><span class="message"><?php esc_html_e(  'Invalid username or password','citytours' ); ?></span></div>
								<?php } ?>

								<div class="form-group">
									<?php if ( isset( $_GET['checkemail'] ) ) {
										esc_html_e( 'Check your e-mail for the confirmation link.', 'citytours' );
									} else {
										esc_html_e( 'Please login to your account.', 'citytours' );
									} ?>
								</div>

								<div class="form-group">
									<label><?php esc_html_e(  'Username', 'citytours' ); ?></label>
									<input type="text" name="log" class="form-control" placeholder="<?php esc_html_e(  'Username', 'citytours' ); ?>" value="<?php echo empty($_GET['user']) ? '' : esc_attr( $_GET['user'] ) ?>">
								</div>

								<div class="form-group">
									<label><?php esc_html_e(  'Password', 'citytours' ); ?></label>
									<input type="password" name="pwd" class="form-control" placeholder="<?php esc_html_e(  'Password', 'citytours' ); ?>">
								</div>

								<div class="form-group">
									<input type="checkbox" name="rememberme" tabindex="3" value="forever" id="rememberme" class="pull-left"> <label for="rememberme" class="pl-8"><?php esc_html_e(  'Remember my details', 'citytours' ); ?></label>
									<div class="small pull-right"><a href="<?php echo esc_url( add_query_arg( 'action', 'lostpassword', $login_url ) ); ?>"><?php esc_html_e(  'Forgot password?', 'citytours' ); ?></a></div>
								</div>

								<button type="submit" class="btn_full"><?php esc_html_e( 'Sign in', 'citytours')?></button>

								<input type="hidden" name="redirect_to" value="<?php echo esc_url( ct_start_page_url() ) ?>">

								<?php if ( get_option('users_can_register') ) { ?>
									<br><?php esc_html_e(  "Don't have an account?", 'citytours' ) ?> 
									<a href="<?php echo esc_url( $signup_url ); ?>" class=""><?php esc_html_e(  "Register", 'citytours' ) ?></a>
								<?php } ?>
							</form>

						<?php }?>

					</div>

					<?php if ( !empty( $ct_options['login_page_bg_image'] ) && !empty( $ct_options['login_page_bg_image']['url'] ) ) { ?>
						<style type="text/css">
							#hero.login{background-image:url(<?php echo $ct_options['login_page_bg_image']['url'] ?>);}
						</style>
					<?php } ?>
				</div>
			</div>
		</div>
	</section>

	<?php 
	endwhile;
endif;

get_footer();