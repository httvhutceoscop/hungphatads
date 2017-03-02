<?php global $theme; ?>

    <div <?php post_class('post clearfix wrap-post'); ?> id="post-<?php the_ID(); ?>">

        <div class="post-inner">
            <div class="entry clearfix post-entry">

            <?php
                if(has_post_thumbnail())  { ?>
                <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                <div class="post-thumb">
                    <?php the_post_thumbnail(
                        array($theme->get_option('featured_image_width'),
                            $theme->get_option('featured_image_height')),
                        array("class" => $theme->get_option('featured_image_position') . " featured_image")
                    ); ?>
                </div>

                </a>
            <?php } ?>

            <h3 class="title"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( '%s', 'themater' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark"><?php the_title(); ?></a></h3>

        </div>

        <!--
        <div class="postmeta-primary">
            <span class="meta_categories"><?php the_category(', '); ?></span>
        </div>
        -->

        </div>

    </div><!-- Post ID <?php the_ID(); ?> -->