<?php

$impostazioni = GestioneNecrologi::GetImpostazioni();

$tipo_vis     = (isset($impostazioni['tipo_visualizzazione'])) ? $impostazioni['tipo_visualizzazione'] :'grid';
$slug_singolo = (isset($impostazioni['slug_singolo']))  ? $impostazioni['slug_singolo'] : 'necrologio';

$slide_res_desktop = (isset($impostazioni['slide_res_desktop'])) ? $impostazioni['slide_res_desktop'] : 4;
$slide_res_tablet  = (isset($impostazioni['slide_res_tablet']))  ? $impostazioni['slide_res_tablet']  : 3;
$slide_res_mobile  = (isset($impostazioni['slide_res_mobile']))  ? $impostazioni['slide_res_mobile']  : 2;

?>

<div class="necrologi-loader"><div class="dg_spinner"></div></div>

<?php ?>
<div class="lista-necrologi slider"></div>
<script>

    jQuery(document).ready(function ($) {

        $loader = $('.necrologi-loader');
        $looper = $('.lista-necrologi');

        DgNecrologi.slugs = {
            slug_singolo: "<?php echo $slug_singolo; ?>"
        };
    
        DgPlugin.ajax('get_anteprima_necrologi',function (cerimonie) {

            console.log(cerimonie);

            for (let i in cerimonie) {
                if (cerimonie[i].nome_defunto) {
                    let n_div = DgNecrologi.crea_necrologio_slide(cerimonie[i]);
                    $looper.append(n_div);
                }
            }

            $loader.css({display: 'none'});
            let $slider = $('.lista-necrologi.slider');

            if (typeof $slider.slick == 'function') {

                $slider.slick({
                    dots: false,
                    infinite: true,
                    speed: 300,
                    slidesToShow: <?php echo intval(ceil($slide_res_desktop * 1.14)); ?>,
                    slidesToScroll: 1,
                    responsive: [
                        {
                            breakpoint: 1980,
                            settings: {
                                slidesToShow: <?php echo $slide_res_desktop; ?>,
                                infinite: true,
                                dots: true
                            }
                        },
                        {
                            breakpoint: 1280,
                            settings: {
                                slidesToShow: <?php echo $slide_res_tablet; ?>,
                                infinite: true,
                                dots: true
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: <?php echo $slide_res_mobile; ?>,
                            }
                        },
                        {
                            breakpoint: 480,
                            settings: {
                                slidesToShow: 1,
                            }
                        }
                    ]
                });

            }
        
        });
        

    });

</script>