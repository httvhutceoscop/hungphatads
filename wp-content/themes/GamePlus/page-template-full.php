<?php
/**
 * Template Name: Full Width, no sidebar(s)
*/
global $theme;
get_header(); ?>

    <div id="main-fullwidth">
    <?php $theme->hook("main_before"); $theme->hook("content_before");  ?>
        
        <?php 
            if (have_posts()) : while (have_posts()) : the_post();
                /**
                 * Find the post formatting for the pages in the post-page.php file
                 */
                get_template_part('post', 'homepage');
                
                if(comments_open( get_the_ID() ))  {
                    comments_template('', true); 
                }
            endwhile;
            
            else :
                get_template_part('post', 'noresults');
            endif; 
        ?>
        
    </div><!-- #main-fullwidth -->
    
<?php get_footer(); ?>