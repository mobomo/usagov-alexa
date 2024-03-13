<?php

namespace Drupal\alexa_wizard\Drush\Commands;

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
final class AlexaWizardCommands extends DrushCommands {

  /**
   * Constructs an AlexaWizardCommands object.
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
  #[CLI\Command(name: 'alexa_wizard:test', aliases: ['a2dt'])]
  #[CLI\Usage(name: 'alexa_wizard:test', description: 'run some testing code')]
  public function test() {

    $rs = \Drupal::service('alexa_wizard.request_subscriber');

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


    $this->logger()->success('Alexa Wizard Test command ran successfully.');
  }

}
