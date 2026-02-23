DgNecrologi = new function () {

    let settings = DgPlugin.get_var('necro_settings');

    let get_img_url = function (img_name) { return DgPlugin.get_var('necro_img_url')+img_name; };
    let get_necro_url = function (necro_slug) {
        let single = DgNecrologi.slugs.slug_singolo;
        return DgPlugin.get_var('necrologio_url')+'/'+necro_slug;
    };

    let singolo = {};

    let raccogli_dati_cordoglio = function ($form) {
        let out = {};
        $form.find('input, select, textarea').each(function () {
            let kname = this.getAttribute('name');
            let value = this.value;
            out[kname] = value;
        });
        return out;
    };

    let scheda_donazioni = function (dati) {

        if (!dati.ente_donazione && !dati.iban_donazione && !dati.codfisc_donazione) { return ''; }
        
        return `<div class="scheda-invito">
            <div class="scheda-invito-head">Donazioni</div>
            <div class="scheda-invito-body"><ul>
                <li><span>Ente benefico:</span>`+dati.ente_donazione+`</li>
                <li><span>Iban:</span>`+dati.iban_donazione+`</li>
                <li><span>Codice fiscale:</span>`+dati.codfisc_donazione+`</li>
            </ul></div>
        </div>`;
    }

    let mostra_dati_cerimonia = function (what, dati_cerimonia, impostazioni) {
        if (!impostazioni) { return ''; }
        let data_cerimonia = '';
        if (dati_cerimonia.data || dati_cerimonia.ora_da) {
            if (settings.mostra_dettagli == '1') { data_cerimonia += ' <span class="titoletto">Data:</span> '; }
            data_cerimonia += '<span class="data-cerimonia">'+(dati_cerimonia.data ? (new Date(dati_cerimonia.data)).toLocaleDateString("it-IT") : '')+'</span> ';
            if (settings.mostra_orari == '1') {
                if (settings.mostra_dettagli == '1') { data_cerimonia += ' - '; }
                data_cerimonia += ' <span class="ora_cerionia">'+(dati_cerimonia.ora_da ? dati_cerimonia.ora_da : '')+'</span>';
            }
            
        }
        let tit = '<h4>'+what+':</h4>';
        let luogo = '';
        if (dati_cerimonia.luogo)  { 
            if (settings.mostra_dettagli == '1') { luogo = ' <span class="titoletto">Luogo:</span> '+luogo; }
            luogo += '<span class="luogo-cerimonia">'+dati_cerimonia.luogo+'</span>';
        }
        switch(impostazioni) {
            case "luogo_data":
                if (!luogo && !data_cerimonia) { return ''; }
                return tit+'<div class="data-e-luogo"><div class="luogo-wrap">'+luogo+'</div> <div class="data-wrap">'+data_cerimonia+'</div></div>';
            case "data_luogo":
                if (!luogo && !data_cerimonia) { return ''; }
                return tit+'<div class="data-e-luogo"><div class="data-wrap">'+data_cerimonia+'</div> <div class="luogo-wrap">'+luogo+'</div></div>';
            case "data":
                if (!data_cerimonia) { return ''; }
                return tit+'<div class="data-e-luogo">'+data_cerimonia+'</div>';
            case "luogo":
                if (!luogo) { return ''; }
                return tit+'<div class="data-e-luogo">'+luogo+'</div>';

        }
    }

    this.crea_necrologio_loop = function (necro) {
        
        let link_div =  document.createElement('a');

        let necroURL = get_necro_url(necro.slug);
        link_div.setAttribute('href',necroURL);

        let mostra_un_bottone = (settings.con_bottone == '1') ? true : false;

        let out = (mostra_un_bottone) ? document.createElement('div') : link_div;
        
        out.classList.add('necrologio-loop');
        
        let thumbnail = get_img_url(necro.thumbnail);

        let defunto   = necro.dati_defunto;
        
        
        let loop_code = '<div class="necro-content"><picture>';
        if (mostra_un_bottone) { loop_code += '<a href="'+necroURL+'">'; }
        loop_code    += ((necro.thumbnail) ? '<img src="'+thumbnail+'"/>' : '');
        if (mostra_un_bottone) { loop_code += '</a>'; }
        loop_code    += '</picture><div class="necro-description">';
        if (mostra_un_bottone) { loop_code += '<a href="'+necroURL+'">'; }
        loop_code    += '<h2>'+necro.nome_defunto+'</h2>';
        if (mostra_un_bottone) { loop_code += '</a>'; }
        
        if (defunto) {
            
            let data_morte   = (defunto.data_morte)  ? (new Date(defunto.data_morte)) : null;
            let data_nascita = (defunto.data_nascita)  ? (new Date(defunto.data_nascita)) : null;

            if (settings.mostra_eta == '1' && data_morte && data_nascita) {

                let anni = data_morte.getFullYear() - data_nascita.getFullYear();

                if (settings.mostra_dettagli) {
                    loop_code += '<div class="eta-defunto"><span class="titolo-eta-defunto">Età:</span> '+anni+' Anni</div>';
                } else {
                    loop_code += '<div class="eta-defunto">'+anni+' Anni</div>';
                }

                if (settings.mostra_decesso == '1' && data_morte) {
                    if (settings.mostra_dettagli) {
                        loop_code += '<div class="data-decesso">Data del decesso: '+data_morte.toLocaleDateString("it-IT")+'</div>';
                    } else {
                        loop_code += '<div class="data-decesso">'+data_morte.toLocaleDateString("it-IT")+'</div>';
                    }
                    
                }
                
            }
        }
        
        loop_code    += mostra_dati_cerimonia('Funerale', necro.funerale, settings.cerimonia); 
        loop_code    += mostra_dati_cerimonia('Rosario', necro.rosario, settings.rosario);
        loop_code    += '</div>';
        if (mostra_un_bottone) {
            loop_code += '<div class="button-wrapper"><a class="button bottone-vai-al-necrologio" href="'+get_necro_url(necro.slug)+'"> Vai al necrologio</a></div>';
        }
        loop_code    += '</div>';
        out.innerHTML = loop_code;

        return out;

    };

    this.crea_necrologio_slide = function (necro) {
        
        let link_div =  document.createElement('a');
        link_div.setAttribute('href',get_necro_url(necro.slug));
        
        let out = link_div;
        
        out.classList.add('necrologio-slide');
        
        let thumbnail = get_img_url(necro.thumbnail);

        let defunto   = necro.dati_defunto;
        
        let loop_code = '<div class="necro-content">';
        loop_code    += '<picture>'+((necro.thumbnail) ? '<img src="'+thumbnail+'"/>' : '')+'</picture>';
        loop_code    += '<div class="necro-description">';
        loop_code    += '<h2>'+necro.nome_defunto+'</h2>';
        
        if (defunto) {
            
            let data_morte   = (defunto.data_morte)  ? (new Date(defunto.data_morte)) : null;
            let data_nascita = (defunto.data_nascita)  ? (new Date(defunto.data_nascita)) : null;

            if (settings.slide_mostra_eta == '1' && data_morte && data_nascita) {

                let anni = data_morte.getFullYear() - data_nascita.getFullYear();

                if (settings.mostra_eta) {
                    loop_code += '<div class="eta-defunto">'+anni+' Anni</div>';
                }
                
            }
            
            if (settings.slide_mostra_decesso == '1' && data_morte) {
                    
                loop_code += '<div class="data-decesso">'+data_morte.toLocaleDateString("it-IT")+'</div>';
                
            }

            if (settings.slide_cerimonia) {
                loop_code    += mostra_dati_cerimonia('Funerale', necro.funerale, settings.cerimonia); 
            }
            if (settings.slide_rosario) {
                loop_code    += mostra_dati_cerimonia('Rosario', necro.rosario, settings.rosario);
            }
            
        }
        
        loop_code    += '</div> </div>';
        out.innerHTML = loop_code;

        return out;

    };

    this.crea_necrologio_singolo = function (necro) {
        
        let necroContent = document.querySelector('.post-necrologio');
        let nomeDefunto  = necroContent.querySelector('.necro-nome-defunto');
        let fotoDefunto  = necroContent.querySelector('picture');
        let testoDefunto = necroContent.querySelector('.necro-testo');
        let mappaWrapper = necroContent.querySelector('.necro-mappa');
        let sezioneDona  = necroContent.querySelector('.sezione-donazioni');

        
        let defunto   = necro.dati_defunto;

        if (settings.defunto_in_hero == "1") {
            nomeDefunto  = necroContent.querySelector('.necro-hero-section h1');
        }

        let titolo_cerimonia = (necro && necro.nome_defunto) ? necro.nome_defunto : '';

        if (!titolo_cerimonia) {
            necroContent.innerHTML = '<h1>Non Trovato</h1><p>Questo necrologio non è stato trovato</p>';
            mappaWrapper.innerHTML = '';
        }

        let TESTO_NECROLOGIO = '';
        
        let data_morte   = (defunto.data_morte)   ? (new Date(defunto.data_morte)) : null;
        let data_nascita = (defunto.data_nascita) ? (new Date(defunto.data_nascita)) : null;

        if (settings.eta_su_singolo == '1' && data_morte && data_nascita) { 

            let anni = data_morte.getFullYear() - data_nascita.getFullYear();

            if (settings.mostra_dettagli) {
                TESTO_NECROLOGIO += '<div class="eta-defunto"><span class="titolo-eta-defunto">Età:</span> '+anni+' Anni</div>';
            } else {
                TESTO_NECROLOGIO += '<div class="eta-defunto">'+anni+' Anni</div>';
            }

        }

        let mono      = (necro.cordogli.whatsapp ^ necro.cordogli.email ^ necro.cordogli.pdf) ? true : false;
        let nessuno   = !necro.cordogli.whatsapp && !necro.cordogli.email && !necro.cordogli.pdf;
        let tipo_mono = '';

        if (mono) {
            if (necro.cordogli.email) { tipo_mono = 'email'; }
            if (necro.cordogli.pdf) { tipo_mono = 'pdf'; }
            if (necro.cordogli.whatsapp) { tipo_mono = 'whatsapp'; }
        } else {
            tipo_mono = 'pdf';
            mono      = true;
            nessuno   = false;
        }

        singolo.is_mono              = mono;
        singolo.mono_tipo            = tipo_mono;
        singolo.has_nessun_cordoglio = nessuno;

        if (settings.mostra_donazioni) {
            let donacode = scheda_donazioni(necro.donazioni);
            if (donacode) {
                sezioneDona.innerHTML = donacode;
            } else {
                sezioneDona.remove();
            }
        }

        nomeDefunto.innerText = titolo_cerimonia;

        if (necro.thumbnail) {
            let immagine = get_img_url(necro.thumbnail);
            fotoDefunto.innerHTML = '<img src="'+immagine+'"/>';
        }
        if (necro.testo) {
            TESTO_NECROLOGIO += necro.testo;
        }

        testoDefunto.innerHTML = TESTO_NECROLOGIO;

        let sluggerInput = document.querySelector('.cordo-form-wrapper form input[name="slug"]');
        sluggerInput.value = necro.slug;

        return singolo;
        
    };


    let validaMail = function (email) {
        return email.match(
            /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
        );
    };

    let convalida_form = function ($form) {

        let is_valid = true;

        $form.find('.convalidazione').html('<div class="dg_spinner"></div>');

        $form.find('input[required], textarea[required], select[required]').each(function () {
            if (!is_valid) { return; }
            let mex = '';
            
            if (!this.id) {
                if (this.checked) { return; }
                is_valid = false;
                mex ='Devi prima accettare la privacy policy!';
            } else {
                if (this.value) { 
                    if (this.getAttribute('type') == 'email') {
                        if (validaMail(this.value)) { return; }
                        else {
                            mex ='l\'email inserita è in un formato non valido';
                            is_valid = false;
                        }
                    } else {
                        return;

                    }
                }
                is_valid = false;
                if (!mex) {
                    let campo = $form.find('label[for="'+this.id+'"]').text();
                    mex = campo+' è un campo obbligatorio';
                }
            }
            $form.find('.convalidazione').html('<h4 class="messaggio-error">'+mex+'</h4>');
        });

        if (is_valid) {
            $form.find('button.invia-a-api').removeClass('disabled');
        } else {
            $form.find('button.invia-a-api').addClass('disabled');
        }

        return is_valid;

    };


    this.invia_cordoglio = function ($form_wrapper) {

        $form = $form_wrapper.find('form');

        if (convalida_form($form)) {

            let dati = raccogli_dati_cordoglio($form);
            
            DgPlugin.ajax('invia_cordoglio_api',function (res) {
                if (res.status == 'success') {
                    $form.find('.input-content').html('<div class="necrologi-loader"><h2 class="messaggio-success">Invio riuscito</h2><p>Il tuo cordoglio è stato inviato. Grazie</p></div>');
                } else {
                    $form.find('.input-content').html('<div class="necrologi-loader"><h1 class="messaggio-error">Errore</h1><p>Abbiamo riscontrato un errore. Si prega di riprovare piu tardi</p></div>');
                }
                
            },dati);

        }
        
    };
    
};