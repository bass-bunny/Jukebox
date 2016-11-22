<?php
session_start();

function getConsecutiveCombinations($array) {
    $length = count($array);
    $results = [];
    foreach ($array as $key => $item) {
        $this_train = '';
        for ($i = $key; $i < $length; $i ++) {
            $this_train .= $array[$i] . ' ';
            $results[] = trim($this_train);
        }
    }
    return $results;
}

if (!$album_count = count($_SESSION['possible_albums']) OR ! $artist_count = count($_SESSION['possible_artist'])) { // if there are no album or artist get from id3 it will try to get some from the filenames
    require '../../php-lib/isThisPatternEverywhere.php';
    $tracks = $_SESSION['tracks'];
    $urls = array_column($tracks, 'url');
    $name_slices = explode('-', preg_replace('/(\.[^.]*$)/', '', $urls[0]));
    $found_slices;

    foreach ($name_slices as $name_slice) {
        if (isThisPatternEverywhere('/' . $name_slice . '/', $urls)) {
            $found_slices[] = $name_slice;
        } else {
            //Just nuffin' for now
        }
    }

    $found_slices = getConsecutiveCombinations($found_slices);


    foreach ($found_slices as $slice) {
        if (!$album_count) {
            $_SESSION['possible_albums'][] = $slice;
        }
        if (!$artist_count) {
            $_SESSION['possible_artist'][] = $slice;
        }
    }


}


?>
<div class="modalHeader">Album Details</div>
<div class="modalBody mCustomScrollbar" data-mcs-theme="dark" style="max-height: 350px;">

    <?php
        if (isset($_SESSION['albumTitle'])){
            $title_value = $_SESSION['albumTitle'];
        } else if(count($_SESSION['possible_albums']) == 1) {
            $title_value = $_SESSION['possible_albums'][0];
        } else {
            
        }

        if (isset($_SESSION['albumArtist'])){
            $artist_value = $_SESSION['albumArtist'];
        } else if(count($_SESSION['possible_artist']) > 1) {
            $artist_value = "Various Artists";
        } else if(count($_SESSION['possible_artist']) == 1) {
            $artist_value = $_SESSION['possible_artist'][0];
        }

        if(count($_SESSION['possible_artist']) > 1 ){
            if (!in_array("Various Artists", $_SESSION['possible_artist'])) {
                $_SESSION['possible_artist'][] = "Various Artists"; 
            }
        }

    ?>
    <form id="addAlbumForm" action="assets/php/add_album_details.php" class="text-center">
        <h3>Artist</h3>
            <label>
                <input type="text" id="albumArtistField" name="albumArtist" placeholder="Artist" class="half-wide" value="<?php echo $artist_value ?>" required/>
            </label>
        <br/>
        <br/>
        <div id="possible_artists">
            <?php
            foreach ($_SESSION['possible_artist'] as $possibile_artist) {
                echo '<div class="box-btn">', $possibile_artist, '</div>';
            }
            ?>
        </div>
        <hr/>
        <h3>Title</h3>
            <label>
                <input type="text" id="albumTitleField" name="albumTitle" class="half-wide" placeholder="Album Title" value="<?php echo $title_value ?>"  required/>
            </label>
        <br/>
        <br/>
        <div id="titleWarning"></div>
        <div id="possible_albums">
            <?php
            foreach ($_SESSION['possible_albums'] as $possibile_album) {
                echo '<div class="box-btn">', $possibile_album, '</div>';
            }
            ?>
        </div>
    </form>

</div>
<div class="modalFooter">
    <div class="box-btn pull-right" id="submit">Next</div>
    <div class="box-btn" onclick="openModalPage('assets/modals/add_album/2.fix_titles.php');">Back</div>
</div>


<script>
    var addAlbumForm = $('#addAlbumForm');

    var submit_btn = $('#submit');

    var possible_albums = $('#possible_albums');
    var possible_artists = $('#possible_artists');

    var albumTitleField = $('#albumTitleField');
    var albumArtistField = $('#albumArtistField');

    $('#possible_albums *').click(function() {
        albumTitleField.val($(this).html());
        albumTitleField.change();
    });

    $('#possible_artists *').click(function() {
        albumArtistField.val($(this).html());
    });

    function checkIfAblumExists(){
         var title = albumTitleField.val();

        $.getJSON('assets/php/check_album_exists.php?title=' + title).done(function(response) {
            if (response[0] != 0) {
                $('#titleWarning').html('Warning, there is another album with a similar name: <br> "' + response.title + '" <br> <img height= "80" src="' + response.cover_url + '"/>');
            } else {
                $('#titleWarning').text('');
            }
        });
    }

    albumTitleField.change(function() {
       checkIfAblumExists();
    });

    albumTitleField.keyup(function(){
        checkIfAblumExists();
    });

    submit_btn.click(function() {
        $.post(addAlbumForm.attr('action'), addAlbumForm.serialize()).done(function(data) {
            if (data === '0') {
                openModalPage('assets/modals/add_album/4.add_album_cover.php');
            } else {
                alert('error code: ' + data);
            }
        });
    });

    addAlbumForm.submit(function(event) {
        event.preventDefault();
        submit_btn.click();
    });

    checkIfAblumExists();

</script>