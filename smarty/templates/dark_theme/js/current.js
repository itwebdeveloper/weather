$( document ).ready(function() {
    if ( $('#icon1').length ) {
        var $icon = $('#icon1');
        var icon_type = $icon.data('icon').replace(/\-/g, '_').toUpperCase();

        var skycons = new Skycons({"color": "#20b0da"});
        skycons.add("icon1", Skycons[icon_type]);

        // start animation!
        skycons.play();
    }
});