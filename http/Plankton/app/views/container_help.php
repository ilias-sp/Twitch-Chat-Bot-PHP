<section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2>Help</h2>
            </div>

            <div class="row clearfix">
                <div class="col-xs-12">
                    <div class="info-box bg-pink info-box hover-zoom-effect">
                        <div class="icon">
                            <i class="material-icons">info</i>
                        </div>
                        <div class="content">
                            <div class="number">Read more</div>
                            <div class="text">The documentation below can be found at <a href="https://github.com/ilias-sp/Twitch-Chat-Bot-PHP" target="_blank">GitHub's page of the bot</a></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row clearfix">
                <div class="col-xs-12">
                    <!-- all text from readme.md is pasted in here: START -->

<div id="README_MD">
<?php
$help = file_get_contents('../../README.md');

echo nl2br($help);
?>
</div>

                    
                    <!-- all text from readme.md is pasted in here: END -->
                </div>            
            </div>

        </div>
    </section>

