<?php
/* Tour List Page Template */
if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

// Do not delete these lines
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && 'comments.php' == basename( $_SERVER['SCRIPT_FILENAME'] ) )
    die( 'Please do not load this page directly. Thanks!' );

if ( post_password_required() ) { 
    ?>

    <p class="no-comments"><?php echo esc_html__('This post is password protected. Enter the password to view comments.', 'citytours'); ?></p>

    <?php

    return;
}
?>

<?php if ( have_comments() ) : ?>

    <h4><?php comments_number(); ?></h4>

    <div id="comments">
        <ol><?php wp_list_comments('callback=ct_comment'); ?></ol>
    </div>

    <?php paginate_comments_links( array( 'type' => 'list' ) ); ?>

<?php else : // this is displayed if there are no comments so far ?>

    <?php if ( comments_open() ) : ?>
        <!-- If comments are open, but there are no comments. -->

     <?php else : // comments are closed ?>
        <!-- If comments are closed. -->
        <p class="no-comments"><?php echo esc_html__('Comments are closed.', 'citytours'); ?></p>

    <?php endif; ?>

<?php endif; ?>

<?php if ( comments_open() ) : ?>

    <div class="post-comment block">
        <div class="citytours-box">
            <?php
            $args = array(  
                'comment_field' => '<div class="form-group"> <textarea name="comment" class="form-control style_2" style="height:150px;" placeholder="Message"></textarea> </div>',
                'title_reply' => esc_html__( 'Leave a Comment', 'citytours' ),
                'comment_notes_before' => '<p class="comment-notes">' . esc_html__( 'Your email address will not be published. All fields are required.', 'citytours' ) . '</p>',
                'id_submit' => 'comment-submit',
                'class_submit' => 'btn_1',
                'fields' => array(
                    'author' => '<div class="form-group"><input class="form-control style_2" type="text" name="author" placeholder="' . esc_html__( 'Enter name', 'citytours' ) . '"></div>',
                    'email' => '<div class="form-group"><input class="form-control style_2" type="text" name="email" placeholder="' . esc_html__( 'Enter email', 'citytours' ) . '"></div>',
                ),
            );
            
            comment_form($args); 
            ?>
        </div>
    </div>
    
<?php endif;