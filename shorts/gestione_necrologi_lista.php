<?php 

if (!defined('ABSPATH')) { exit; }

if (!PortaleFunebreNecrologi::IsConfigurato()) {  echo 'lista'; return; }

$impostazioni = PortaleFunebreNecrologi::GetImpostazioni();

$tipo_vis     = (isset($impostazioni['tipo_visualizzazione'])) ? $impostazioni['tipo_visualizzazione'] :'grid';
$profilo_box  = (isset($impostazioni['profilo_box']))   ? $impostazioni['profilo_box'] : 'verticale';
$slug_singolo = (isset($impostazioni['slug_singolo']))  ? $impostazioni['slug_singolo'] : 'necrologio';

$list_classes = [];
array_push($list_classes, $tipo_vis);
array_push($list_classes, ($profilo_box == 'verticale') ? 'necro-box-verticale' : 'necro-box-orrizzontale');

?>

<div class="necrologi-loader"><div class="dg_spinner"></div></div>

<?php ?>
<div
  class="lista-necrologi <?php echo esc_attr(implode(' ', $list_classes)); ?>"
  data-pfn-list="1"
  data-slug-singolo="<?php echo esc_attr($slug_singolo); ?>"
  data-tipo-vis="<?php echo esc_attr($tipo_vis); ?>">
</div>
