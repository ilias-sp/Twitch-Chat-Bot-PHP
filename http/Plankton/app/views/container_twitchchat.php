    <section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2>Twitch Chat<?php if (isset($twitch_channel_name) && mb_strlen($twitch_channel_name) > 0) {echo ' - <span class="font-bold col-orange">' . $twitch_channel_name . '</span>'; }?></h2>
            </div>

            <div class="row clearfix">
            <?php 

            echo '
            <iframe class="embed-responsive-item" style="width:100%; height:800px;" frameborder="0"
            scrolling="yes"
            id="chat_embed"
            src="http://www.twitch.tv/embed/' . $twitch_channel_name . '/chat?darkpopout">
            </iframe>
';

            ?>
            </div>

            

        </div>
    </section>
