<?php
namespace WebSharks\CommentMail\Pro;

/*
 * @var Plugin           $plugin      Plugin class.
 * @var Template         $template    Template class.
 *
 * Other variables made available in this template file:
 *
 * @var string           $site_header Parsed site header template.
 * @var string           $site_footer Parsed site footer template.
 *
 * @var \stdClass|null   $sub         Subscription object data.
 *
 * @var \WP_Post|null    $sub_post    Post they're subscribed to.
 *    This will be `NULL` if there were any `$error_codes` during processing.
 *
 * @var \WP_Comment|null $sub_comment Comment they're subcribed to; if applicable.
 *
 * @var array            $error_codes An array of any/all error codes.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php // Sets document <title> tag via `%%title%%` replacement code in header.
echo str_replace('%%title%%', __('Confirmation', SLUG_TD), $site_header); ?>

    <div class="confirm">

        <?php if ($error_codes) : // Any processing errors? ?>

            <div class="alert alert-danger" style="margin:0;">
                <h4>
                    <?php echo __('Please review the following error(s):', SLUG_TD); ?>
                </h4>
                <ul class="list-unstyled">
                    <?php foreach ($error_codes as $_error_code) : ?>
                        <li>
                            <i class="fa fa-warning fa-fw"></i>
                            <?php
                            switch ($_error_code) {
                                case 'missing_sub_key':
                                    echo __('Subscription key is missing; unable to confirm.', SLUG_TD);
                                    break; // Break switch handler.

                                case 'invalid_sub_key':
                                    echo __('Invalid subscription key; unable to confirm.', SLUG_TD);
                                    break; // Break switch handler.

                                case 'sub_post_id_missing':
                                    echo __('Unable to confirm; the post you\'re subscribing to has since been deleted. Sorry!', SLUG_TD);
                                    break; // Break switch handler.

                                case 'sub_comment_id_missing':
                                    echo __('Unable to confirm; the comment you\'re subscribing to has since been deleted. Sorry!', SLUG_TD);
                                    break; // Break switch handler.

                                case 'sub_already_confirmed':
                                    echo __('Already confirmed! Thank you.', SLUG_TD);
                                    break; // Break switch handler.

                                default: // Anything else that is unexpected/unknown at this time.
                                    echo __('Unknown error; unable to confirm. Sorry!', SLUG_TD);
                            } ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        <?php else : // Confirmed successfully. ?>

            <?php
            /*
             * Here we define a few more variables of our own.
             * All based on what the template makes available to us;
             * ~ as documented at the top of this file.
             */
            // URL to comments on the post they're subscribed to.
            $sub_post_comments_url = get_comments_link($sub_post->ID);

            // Are comments still open on this post?
            $sub_post_comments_open = comments_open($sub_post->ID);

            // A shorter clip of the full post title.
            $sub_post_title_clip = $plugin->utils_string->clip($sub_post->post_title, 70);

            // URL to comment they're subscribed to; if applicable.
            $sub_comment_url = $sub_comment ? get_comment_link($sub_comment->comment_ID) : '';

            // Subscribed to their own comment?
            $subscribed_to_own_comment = $sub_comment && strcasecmp($sub_comment->comment_author_email, $sub->email) === 0;

            // Subscription delivery option label; i.e. a translated display of the option value.
            $sub_deliver_label = $plugin->utils_i18n->deliverLabel($sub->deliver);

            // Subscriber's `"name" <email>` w/ HTML markup enhancements.
            $sub_name_email_markup = $plugin->utils_markup->nameEmail($sub->fname.' '.$sub->lname, $sub->email);

            // Subscriber's last known IP address.
            $sub_last_ip = $sub->last_ip ? $sub->last_ip : __('unknown', SLUG_TD);

            // Subscription last update time "ago"; e.g. `X [seconds/minutes/days/weeks/years] ago`.
            $sub_last_update_time_ago = $plugin->utils_date->i18nUtc('M jS, Y @ g:i a T', $sub->last_update_time);

            // Subscription edit URL; i.e. so they can make any last-minute changes.
            $sub_edit_url = $plugin->utils_url->subManageSubEditUrl($sub->key);
            ?>

            <div class="alert alert-success">
                <h4 style="margin:0;">
                    <i class="fa fa-check fa-fw"></i> <?php echo sprintf(__('Confirmed successfully. Thank you%1$s!', SLUG_TD), $sub->fname ? ' '.esc_html($sub->fname) : ''); ?>
                </h4>
            </div>

            <h4>
                <?php if ($sub_comment) : // Subscribing to a specific comment? ?>

                    <?php if ($subscribed_to_own_comment) : ?>
                        <?php echo sprintf(__('You\'ll be notified about replies to <a href="%1$s">your comment</a>; on:', SLUG_TD), esc_attr($sub_comment_url)); ?>
                    <?php else : // The comment was not authored by this subscriber; i.e. it's not their own. ?>
                        <?php echo sprintf(__('You\'ll be notified about replies to <a href="%1$s">comment ID #%2$s</a>; on:', SLUG_TD), esc_attr($sub_comment_url), esc_html($sub_comment->comment_ID)); ?>
                    <?php endif; ?>

                <?php else : // All comments/replies on this post. ?>
                    <?php echo __('You\'ll be notified about all comments/replies to:', SLUG_TD); ?>
                <?php endif; ?>
            </h4>

            <h4>
                <i class="fa fa-thumb-tack"></i>
                <?php if ($sub_comment) : // A specific comment? ?>
                    <em><a href="<?php echo esc_attr($sub_comment_url); ?>"><?php echo esc_html($sub_post_title_clip); ?></a></em>
                <?php else : // Subscribing to all comments/replies to this post. ?>
                    <em><a href="<?php echo esc_attr($sub_post_comments_url); ?>"><?php echo esc_html($sub_post_title_clip); ?></a></em>
                <?php endif; ?>
            </h4>

            <hr style="margin:0 0 1em 0;" />

            <p>
                <?php echo sprintf(__('Your email address is: &lt;<code>%1$s</code>&gt;;', SLUG_TD), esc_html($sub->email)); ?><br />
                <?php echo sprintf(__('You chose delivery option: <code>%1$s</code>;', SLUG_TD), esc_html($sub_deliver_label)); ?>
                <?php
                switch ($sub->deliver) {
                    case 'asap': // Instant notifications?
                        echo __('each email notification will be delivered to you instantly.', SLUG_TD);
                        break; // Break switch handler.

                    case 'hourly': // As a digest?
                    case 'daily':
                    case 'weekly':
                        echo __('notifications will be delivered as a digest.', SLUG_TD);
                        break; // Break switch handler.
                } ?>
            </p>

            <hr style="margin:0 0 1em 0;" />

            <h5 style="font-style:italic; margin:0;">
                <i class="fa fa-lightbulb-o"></i> <?php echo sprintf(__('If any of this is incorrect, please <a href="%1$s">click here to edit</a> your subscription.', SLUG_TD), esc_attr($sub_edit_url)); ?>
            </h5>

        <?php endif; // END: confirmed successfully w/ no major errors. ?>

    </div>

<?php echo $site_footer; ?>
