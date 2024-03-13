<?php

namespace Drupal\Tests\alexa_wizard\Unit;

use Drupal\Tests\UnitTestCase;

/**
 * Tests Alexa Wizard module. TODO
 *
 * @coversDefaultClass \Drupal\alexa_wizard\Services\WizardService
 * @group alexa_wizard
 */
class AlexaWizardTest extends UnitTestCase {

  /**
   * @covers ::functionToCover
   * @dataProvider providerFunctionToCover
   */
  public function testFunctionToCover($var1, $var2) {
    // $this->assertEquals($val1, $val2);
  }

  public function providerFunctionToCover(): array {
    $data = [
        ['val1', 'val2']
    ];
    return $data;
  }

}
