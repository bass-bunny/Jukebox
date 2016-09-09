var edit_album_tracks = $.parseJSON(atob($('#album-tracks').val()));

var edit_album_deleted_tracks = [];

var album_id = $('#album-id').val();
var album_cover_img = $('#album_cover_img');
var edit_tracks = $('#edit-tracks');

// Moves element of an array to a new position
Array.prototype.move = function (old_index, new_index) {
    if (new_index >= this.length) {
        var k = new_index - this.length;
        while ((k--) + 1) {
            this.push(undefined);
        }
    }
    this.splice(new_index, 0, this.splice(old_index, 1)[0]);
    return this; // for testing purposes
};

function submit(callback) {
    var album_id = $('#album-id').val();
    var album_artist = $('#album-artist').val();
    var album_title = $('#album-title').val();

    var data = {
        album_id: album_id,
        album_title: album_title,
        album_artist: album_artist,
        album_tracks: edit_album_tracks
    };

    if (edit_album_deleted_tracks.length > 0) {
        data.album_removed_tracks = edit_album_deleted_tracks;
    }

    if (imageSelector.imageUrl != null) {
        data.album_cover_url = imageSelector.imageUrl;
    }


    data = JSON.stringify(data);

    $.post('assets/php/edit_album.php', data, function (resp) {
        var json = $.parseJSON(resp);

        if (json.success == true) {
            alert("Album updated successfully.");

            if (typeof callback !== 'undefined') {
                callback();
            }
        } else {
            alert(json.message);
        }
    });
}

$('#edit-album-form').submit(function (e) {
    e.preventDefault();

    submit();
});

$('#edit-album-save').click(function () {
    submit();
});



edit_tracks.find('.edit').click(function (e) {
    e.stopPropagation();

    var li = $(this).closest('li');

    var track_no = parseInt(li.attr('data-id'));
    var title_cont = li.find('.title');

    var input = $('<input type="text" />');

    var title_original = title_cont.text();

    input.val(title_original);

    title_cont.hide();

    li.prepend(input);

    input.focus();

    input.blur(function () {
        var title_new = input.val();

        if (title_new != title_original) {
            edit_album_tracks[track_no].title = title_new;

            title_cont.html(title_new);
        }

        input.remove();
        title_cont.show();
    });

    input.keypress(function (e) { // Press enter
        if (e.which == 13) {
            input.blur();
        }

        else if (e.which == 27) { // Press escape - Resets input
            e.preventDefault();
            input.val(title_original);
            input.blur();
        }

        console.log(e.which);
    });
});

edit_tracks.find('.delete').click(function (e) {
    e.stopPropagation();

    var li = $(this).closest('li');

    var track_no = parseInt(li.attr('data-id'));

    li.remove();

    edit_album_deleted_tracks.push(edit_album_tracks[track_no]);

    edit_album_tracks.splice(track_no, 1);

});

function openPickCoverModal() {
    imageSelector.albumArtist = true;
    imageSelector.to = 'assets/modals/edit_album/?id=' + album_id;
    imageSelector.from = 'assets/modals/edit_album/?id=' + album_id;
    imageSelector.open();
}


if (imageSelector.imageUrl != null) {
    submit(function () {

        var src = album_cover_img.attr('src') + "?date=" + +new Date().getTime();

        album_cover_img.attr('src', src);

        reload();
    });
}

function appendCd() {
    var last_cd = parseInt(edit_tracks.find('.cd').last().attr('data-cd')) + 1;

    console.log(last_cd);

    var html = $("<li class='cd header'></li>");

    html.html("CD "+last_cd);

    html.attr('data-cd', last_cd);

    edit_tracks.append(html);
}

$(function () { // Allows sorting of the tracks
    var container = document.getElementById("edit-tracks");
    var sort = Sortable.create(container, {
        animation: animation_medium, // ms, animation speed moving items when sorting, `0` — without animation
        handle: ".handle", // Restricts sort start click/touch to the specified element
        //draggable: ".track", // Specifies which items inside the element should be sortable
        onUpdate: function (evt) {
            //var item = evt.item; // the current dragged HTMLElement
            edit_album_tracks.move(evt.oldIndex, evt.newIndex);

            // Used to update the track_no value inside the track
            // edit_album_tracks.forEach(function (track, key) {
            //     track.track_no = key + 1;
            // });
        }
    });
});