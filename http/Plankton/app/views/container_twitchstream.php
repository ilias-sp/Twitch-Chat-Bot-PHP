<section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2>Twitch Chat<?php if (isset($twitch_channel_name) && mb_strlen($twitch_channel_name) > 0) {echo ' - <span class="font-bold col-orange">' . $twitch_channel_name . '</span>'; }?></h2>
            </div>

            <div class="row clearfix">
            <?php 

            echo '
            <iframe
                src="http://player.twitch.tv/?channel=' . $twitch_channel_name . '"
                height="800"
                width="100%"
                scrolling="no"
                allowfullscreen="true">
            </iframe>
';

            ?>
            </div>

            

        </div>
    </section>
