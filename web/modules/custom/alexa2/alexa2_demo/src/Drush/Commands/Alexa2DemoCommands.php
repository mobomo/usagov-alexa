<?php

namespace Drupal\alexa2_demo\Drush\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Core\Utility\Token;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 */
final class Alexa2DemoCommands extends DrushCommands {

  /**
   * Constructs an Alexa2DemoCommands object.
   */
  public function __construct() {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

  /**
   * Command description here.
   */
  #[CLI\Command(name: 'alexa2_demo:test', aliases: ['a2dt'])]
  #[CLI\Usage(name: 'alexa2_demo:test', description: 'run some testing code')]
  public function test() {

    $rs = \Drupal::service('alexa2_demo.request_subscriber');

    $choice = 'skam';
    $currentPath = 'launch';

    $db = $rs->getDB();
    $currentStep = $rs->getCurrentStep($currentPath, $db);

    $this->logger()->success('herd: '.print_r($choice,1));
    $this->logger()->success('path: '.print_r($currentPath,1));
    $this->logger()->success('curr: '.print_r($currentStep['id'],1));
    $this->logger()->success('opts: '.print_r(implode(', ',array_keys($currentStep['options'])),1));

    $nextStep = $rs->findNextStep( $choice, $currentStep );
    if ( empty($nextStep) ) { $nextStep = ['id'=>'']; }
    $this->logger()->success('nStp: '.print_r($nextStep['id'],1));

    if ( $nextStep == $currentStep ) {
      $this->logger()->success('nPth: '.print_r($currentPath,1));
    } else {
      $this->logger()->success('nPth: '.print_r($nextStep['path'],1));
   }


    $this->logger()->success('Alexa2 Demo Test command ran successfully.');
  }

}
