<?php

namespace Drupal\jugaad_product\Plugin\Block;

require ('../vendor/autoload.php');

use Com\Tecnick\Barcode\Barcode as BarcodeGenerator;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\Cache;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a 'Barcode' block.
 *
 * @Block(
 *  id = "barcode",
 *  admin_label = @Translation("Barcode"),
 * )
 */
class Barcode extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $theme_path = base_path() . \Drupal::theme()->getActiveTheme()->getPath();
    $host = \Drupal::request()->getSchemeAndHttpHost();
    $node = \Drupal::request()->attributes->get('node');
    $nid = $node->id();
    $field_app_purchase_link = $node->get('field_app_purchase_link')->getValue();
    $field_app_purchase_link = $field_app_purchase_link[0]['value'];
    
    $generator = new BarcodeGenerator();

    // generate a barcode
    $barcode = $generator->getBarcodeObj(
      'QRCODE,H',                     // barcode type and additional comma-separated parameters
      $field_app_purchase_link,          // data string to encode
      -4,                             // bar width (use absolute or negative value as multiplication factor)
      -4,                             // bar height (use absolute or negative value as multiplication factor)
      'black',                        // foreground color
      array(-2, -2, -2, -2)           // padding (use absolute or negative values as multiplication factors)
    )->setBackgroundColor('white'); // background color

    $barcode_val = "<p style=\"font-family:monospace;\">".$barcode->getHtmlDiv()."</p>";    

    return [
      '#theme' => 'barcode',
      '#barcode' => $barcode_val,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    if ($node = \Drupal::routeMatch()->getParameter('node')) {
      return Cache::mergeTags(parent::getCacheTags(), array('node:' . $node->id()));
    } else {
      return parent::getCacheTags();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }
}