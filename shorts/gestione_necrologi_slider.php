<?php

if (!defined('ABSPATH')) { exit; }

if (!GestioneNecrologi::IsConfigurato()) {  echo 'slider'; return; }

$impostazioni = GestioneNecrologi::GetImpostazioni();

$tipo_vis     = (isset($impostazioni['tipo_visualizzazione'])) ? $impostazioni['tipo_visualizzazione'] :'grid';
$slug_singolo = (isset($impostazioni['slug_singolo']))  ? $impostazioni['slug_singolo'] : 'necrologio';

$slide_res_desktop = (isset($impostazioni['slide_res_desktop'])) ? $impostazioni['slide_res_desktop'] : 4;
$slide_res_tablet  = (isset($impostazioni['slide_res_tablet']))  ? $impostazioni['slide_res_tablet']  : 3;
$slide_res_mobile  = (isset($impostazioni['slide_res_mobile']))  ? $impostazioni['slide_res_mobile']  : 2;

$arrowLeft = '<svg xmlns="http://www.w3.org/2000/svg" width="25px" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns" viewBox="-5 0 24 24" version="1.1">
    <g id="Page-1" stroke="none" stroke-width="1" fill-rule="evenodd" sketch:type="MSPage">
        <g id="Icon-Set" sketch:type="MSLayerGroup" transform="translate(-421.000000, -1195.000000)">
            <path d="M423.429,1206.98 L434.686,1196.7 C435.079,1196.31 435.079,1195.67 434.686,1195.28 C434.293,1194.89 433.655,1194.89 433.263,1195.28 L421.282,1206.22 C421.073,1206.43 420.983,1206.71 420.998,1206.98 C420.983,1207.26 421.073,1207.54 421.282,1207.75 L433.263,1218.69 C433.655,1219.08 434.293,1219.08 434.686,1218.69 C435.079,1218.29 435.079,1217.66 434.686,1217.27 L423.429,1206.98" id="chevron-left" sketch:type="MSShapeGroup">
            </path>
        </g>
    </g>
</svg>';
$arrowRight = '<svg xmlns="http://www.w3.org/2000/svg" width="25px" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns" viewBox="-5 0 24 24" version="1.1">
    <g id="Page-1" stroke="none" stroke-width="1" fill-rule="evenodd" sketch:type="MSPage">
        <g id="Icon-Set" sketch:type="MSLayerGroup" transform="translate(-473.000000, -1195.000000)">
            <path d="M486.717,1206.22 L474.71,1195.28 C474.316,1194.89 473.678,1194.89 473.283,1195.28 C472.89,1195.67 472.89,1196.31 473.283,1196.7 L484.566,1206.98 L473.283,1217.27 C472.89,1217.66 472.89,1218.29 473.283,1218.69 C473.678,1219.08 474.316,1219.08 474.71,1218.69 L486.717,1207.75 C486.927,1207.54 487.017,1207.26 487.003,1206.98 C487.017,1206.71 486.927,1206.43 486.717,1206.22" id="chevron-right" sketch:type="MSShapeGroup">
            </path>
        </g>
    </g>
</svg>';

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

                let $theSlide = $slider.slick({
                    dots: false,
                    infinite: true,
                    speed: 300,
                    slidesToShow: <?php echo intval(ceil($slide_res_desktop * 1.14)); ?>,
                    prevArrow: `<button class="slick-prev" aria-label="Previous"><?php echo $arrowLeft; ?></button>`,
                    nextArrow: `<button class="slick-next" aria-label="Next"><?php echo $arrowRight; ?></button>`,
                    slidesToScroll: 1,
                    responsive: [
                        {
                            breakpoint: 1980,
                            settings: {
                                slidesToShow: <?php echo $slide_res_desktop; ?>,
                            }
                        },
                        {
                            breakpoint: 1280,
                            settings: {
                                slidesToShow: <?php echo $slide_res_tablet; ?>,
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