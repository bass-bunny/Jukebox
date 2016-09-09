<?php

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: text/html; charset=utf8");

?>
<div class="modalHeader">Set Tracks Titles</div>

<div class="modalBody mCustomScrollbar" data-mcs-theme="dark" style="max-height: 350px;">
    <form action="assets/php/fix_title.php" id="fixTitleForm">
        <table class="cooltable">
            <tr>
                <th>#</th>
                <th>New Title</th>
                <th>Original Title</th>
                <th>File name</th>
                
            </tr>
            <?php

            require '../../php-lib/matrix-sorter.php';

            $tracks = $_SESSION['tracks'];
            
            
            if (areAllTrackNoPresent($tracks)) {
                $sortBy = 'cd,track_no,title';
                //echo 'ci su tutti pari, figgh\'i bottana ';
            } else {

                require '../../php-lib/isThisPatternEverywhere.php';

                /* MESSY STUFF STARTS HERE */
                /* this should get useful data from the filenames*/
                $urls = array_column($tracks, 'url');

                if (isThisPatternEverywhere('/[0-9]+-/', $urls)) {
                    //echo '<pre>Every item has the pattern</pre>';


                    foreach ($tracks as $key => $track) {

                        $cose_attruvate;
                        preg_match('/([0-9]+)-/', $tracks[$key]['url'], $cose_attruvate); // don't ask me why I can't use the pointer, damn bug.
                        if (!isset($tracks[$key]['track_no'])) {
                            $tracks[$key]['track_no'] = $cose_attruvate[1];
                        }
                    }
                } else {
                    // foreach ($tracks as $key => $track) {
                    //     $track['track_no'] = $key;
                    // }
                }
                /* MESSY STUFF ENDS HERE. THANK YOU GOD OF CODING */

                $sortBy = 'cd,track_no,title';
                //$sortBy = 'title';
            }

            @sort_matrix($tracks, $sortBy);

            $_SESSION['tracks'] = $tracks;

            session_write_close();

            ///* === DEBUG === */ file_put_contents('sorted.txt', var_export($tracks, true));

            foreach ($tracks as $key => $track) {
                $CD = &$track['cd'];
                if ($CD != @$previousCD) {
                    echo '<th colspan="4">CD ', $CD, '</th>';
                }

                if(isset($track['track_no'])){
                    $track_no = utf8_encode($track['track_no']);
                } else {
                    $track_no = $key + 1;
                }

                $previousCD = $track ['cd'];
                echo '<tr><td>', $track_no, '</td><td><input type="text" name="track', $key, '" value="', utf8_encode($track['title']), '"/></td><td>', utf8_encode($track['title']), '</td><td>', utf8_encode($track['url']), '</td></tr>';
            }
            ?>
        </table>
    </form>
</div>
<div class="modalFooter">
    <div class="box-btn pull-right" id="submit">Next</div>
    <div class="box-btn" onclick="openModalPage('assets/modals/add_album/1.upload_album.php');">Previous</div>
    <div class="box-btn center" id="nextCD">Add CD2</div>
</div>
<script>
    var addAlbumForm = $('#fixTitleForm');

    var submit_btn = $('#submit');

    $('#nextCD').click(function() {
        $.ajax('assets/php/set_next_cd_upload.php');
        openModalPage('assets/modals/add_album/1.upload_album.php');
    });

    submit_btn.click(function(event) {
        $.post(addAlbumForm.attr('action'), addAlbumForm.serialize()).done(function(data) {
            if (data === '0') {
                openModalPage('assets/modals/add_album/3.add_album_details.php');
            } else {
                alert('error code: ' + data);
            }
        });
    });

    $('#fixTitleForm').submit(function(e) {
        e.preventDefault();
        submit_btn.click();
    });

</script>
