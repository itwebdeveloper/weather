$( document ).ready(function() {
    var $icon = $('#icon1');
    var icon_type = $icon.data('icon').replace(/\-/g, '_').toUpperCase();

    var skycons = new Skycons({"color": "black"});
    skycons.add("icon1", Skycons[icon_type]);

    // start animation!
    skycons.play();
});