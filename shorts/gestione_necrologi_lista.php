<?php

$impostazioni = GestioneNecrologi::GetImpostazioni();

$tipo_vis     = (isset($impostazioni['tipo_visualizzazione'])) ? $impostazioni['tipo_visualizzazione'] :'grid';
$profilo_box  = (isset($impostazioni['profilo_box']))   ? $impostazioni['profilo_box'] : 'verticale';
$slug_singolo = (isset($impostazioni['slug_singolo']))  ? $impostazioni['slug_singolo'] : 'necrologio';

$list_classes = [];
array_push($list_classes, $tipo_vis);
array_push($list_classes, ($profilo_box == 'verticale') ? 'necro-box-verticale' : 'necro-box-orrizzontale');

?>

<div class="necrologi-loader"><div class="dg_spinner"></div></div>

<?php ?>
<div class="lista-necrologi <?php echo implode(' ',$list_classes) ?>"></div>
<script>

  jQuery(document).ready(function ($) {

    $loader = $('.necrologi-loader');
    $looper = $('.lista-necrologi');

    DgNecrologi.slugs = {
      slug_singolo: "<?php echo $slug_singolo; ?>"
    };
    
    DgPlugin.ajax('get_lista_necrologi',function (cerimonie) {
      console.log(cerimonie);
      for (let i in cerimonie) {
        if (cerimonie[i].nome_defunto) {
          let n_div = DgNecrologi.crea_necrologio_loop(cerimonie[i]);
          $looper.append(n_div);
        }
      }
      $loader.css({display: 'none'});
    });
    
    <?php if ($tipo_vis == 'grid') { ?>
      let $slider = $('.lista-necrologi.slider');
      if (typeof $slider.slick == 'function') {
        $slider.slick({
          dots: false,
          infinite: true,
          speed: 300,
          slidesToShow: 8,
          slidesToScroll: 8,
          responsive: [
            {
              breakpoint: 1024,
              settings: {
                slidesToShow: 6,
                slidesToScroll: 6,
                infinite: true,
                dots: true
              }
            },
            {
              breakpoint: 800,
              settings: {
                slidesToShow: 4,
                slidesToScroll: 4,
                infinite: true,
                dots: true
              }
            },
            {
              breakpoint: 600,
              settings: {
                slidesToShow: 3,
                slidesToScroll: 3,
                infinite: true,
                dots: true
              }
            },
            {
              breakpoint: 480,
              settings: {
                slidesToShow: 2,
                slidesToScroll: 2
              }
            },
            {
              breakpoint: 380,
              settings: {
                slidesToShow: 1,
                slidesToScroll: 1
              }
            }
        ]});
      }
    <?php } ?>

  });

</script>