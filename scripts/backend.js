

jQuery(document).ready(function ($) {

    $('#upload_hero_bg').on('click', function (e) {

        e.preventDefault();

        const frame = wp.media({
            title: 'Seleziona sfondo hero',
            button: { text: 'Usa questa immagine' },
            multiple: false
        });

        frame.on('select', function () {
            const attachment = frame.state().get('selection').first().toJSON();
            $('#sfondo_hero').val(attachment.id);
            const thumb = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
            $('#preview_hero_bg').html('<img src="' + thumb + '" />');
        });

        frame.open();

    });

    let initial_url = window.location;

    $('.impostazioni-header a[to]').click(function () {
        $('.impostazioni-header a[to]').removeClass('active').removeAttr('aria-current');
        let to = this.getAttribute('to');
        history.pushState('', '', initial_url + '&tab=' + to);
        this.classList.add('active');
        this.setAttribute('aria-current', 'true');
        $('.impostazioni-content content[tab]').removeClass('active');
        $('.impostazioni-content content[tab="' + to + '"]').addClass('active');
    });

    let opened_tab = new URLSearchParams(window.location.search).get('tab');

    if (opened_tab) {
        $('.impostazioni-header a[to="' + opened_tab.replace(/[^a-z0-9_-]/gi, '') + '"]').trigger('click');
    }

});
