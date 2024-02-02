<?php

/**
 * @file
 * ScamWizard
 */

namespace Drupal\scamwizard\Controller;

use Drupal\Core\Controller\ControllerBase;

//$fo = fopen("wizardTree.json", "r");
//$data = json_decode($fo);
//$data->scams->path
//$sampleString = "SUCCESS";

class ScamsController extends ControllerBase {
  public function syncFromAPI() {
    $fileOpen = file_get_contents("modules/custom/scamwizard/src/Controller/wizardTree.json");
    $data = json_decode($fileOpen);

    // dpm($data);

    /**
     * Undocumented function
     *
     * foreach(data, wizardNode) {
     *  if(wizardNode) {
     *    wizardNode.getUUID()
     *    wizardNode.getLastChangedTime()
     *    $node = Node::load(UUID)
     *    if ($node) {
     *      if ($node.lastChangedTime() < wizardNode.getLastChangedTime())
     *        $node.title = wizard.title;
     *        $node.save()
     *      }
     *    } else {
     *     $newNode = $node.create();
     *     $node.title = wizard.title;
     *     $node.UUID = wizard.UUID;
     *     $node.save();
     *   }
     *  }
     * }
     *
     * $oldNodes = getDrupalNodesNotInJSON()
     *
     * foreach(data, oldNodes) {
     *   $node = $node.UUID;
     *   $node.delete() // unpublish()?
     * }
     */

    foreach ($data as $key => $wizJSON) {
      //dpm($wizJSON);
      $isWizard = $wizJSON->parent === NULL;
      $isWizardStep = !$isWizard;
      $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['uuid' => $wizJSON->uuid]);
      $node = reset($nodes);

      //dpm($node);

      if ($node) {
        if ($node->hasField("title") && property_exists($wizJSON, "title")) {
          $node->set("title", $wizJSON->title);
        }

        if ($node->hasField("body") && property_exists($wizJSON, "p1")) {
          $node->set("body", $wizJSON->p1);
        }

        if ($node->hasField("header") && property_exists($wizJSON, "h2")) {
          $node->set("h2", $wizJSON->h2);
        }

        if ($node->hasField("wizard_step") && property_exists($wizJSON, "li")) {
          $node->set("li", "module step");
        }

        $node->save();
      }
    }
  }

  public function scamWizard() {
    // self::syncFromAPI();

    $fileOpen = file_get_contents("modules/custom/scamwizard/src/Controller/wizardTree.json");
    $data = json_decode($fileOpen);

    $parent = $data->scams->parent;
    $path = $data->scams->path;
    $title = $data->scams->title;
    $h2 = $data->scams->h2;

    $option_banking = $data->banking->name;
    $option_trickery = $data->trickery->name;
    $option_moving = $data->moving->name;
    $option_fraud = $data->fraud->name;
    $option_other = $data->other->name;

    $path_banking = $data->banking->path;
    $path_trickery = $data->trickery->path;
    $path_moving = $data->moving->path;
    $path_fraud = $data->fraud->path;
    $path_other = $data->other->path;

    $domain = 'http://127.0.0.1:8888';

    return [
      "#type" => "markup",
      "#markup" => t(""),
    ];
  }

  public function scamWizardBanking() {
    $fileOpen = file_get_contents("modules/custom/scamwizard/src/Controller/wizardTree.json");
    $data = json_decode($fileOpen);

    $parent = $data->banking->parent;
    $path = $data->banking->path;
    $title = $data->banking->title;
    $h2 = $data->banking->h2;

    $option_fakecheck = $data->fakecheck->name;
    $option_unsolicitedcheck = $data->unsolicitedcheck->name;
    $option_unauthorizedwithdrawal = $data->unauthorizedwithdrawal->name;
    $option_other = $data->other->name;

    $path_fakecheck = $data->fakecheck->path;
    $path_unsolicitedcheck = $data->unsolicitedcheck->path;
    $path_unauthorizedwithdrawal = $data->unauthorizedwithdrawal->path;
    $path_other = $data->other->path;

    $domain = 'http://127.0.0.1:8888';

    return [
      "#type" => "markup",
      "#markup" => t("
          <h2>$h2</h2>
          <ul style='list-style-type: none;'>
            <li>
              <label class='container' style='cursor: pointer;'>
                <input type='radio' id='optionone' onclick='myFunction()' name='radio'
                  style='cursor: pointer;'>
                $option_fakecheck
              </label>
            </li>
            <li>
              <label class='container' style='cursor: pointer;'>
                <input type='radio' id='optiontwo' onclick='myFunction()' name='radio'
                  style='cursor: pointer;'>
                $option_unsolicitedcheck
              </label>
            </li>
            <li>
              <label class='container' style='cursor: pointer;'>
                <input type='radio' id='optionthree' onclick='myFunction()' name='radio'
                  style='cursor: pointer;'>
                $option_unauthorizedwithdrawal
              </label>
            </li>
            <li>
              <label class='container' style='cursor: pointer;'>
                <input type='radio' id='optionfour' onclick='myFunction()' name='radio'
                  style='cursor: pointer;'>
                $option_other
              </label>
            </li>
            <li style='cursor: pointer; padding-left: 65px;'>
              <button onclick='redirector()'>
                <b>Submit</b>
              </button>
              <span id='noRadioSelected' style='color:red;'></span>
            </li>
          </ul>

          <script>
            let url = '';

            function myFunction() {

              if (document.getElementById('optionone').checked) {
                url = '$domain$path_fakecheck';
              }

              if (document.getElementById('optiontwo').checked) {
                url = '$domain$path_unsolicitedcheck';
              }

              if (document.getElementById('optionthree').checked) {
                url = '$domain$path_unauthorizedwithdrawal';
              }

              if (document.getElementById('optionfour').checked) {
                url = '$domain$path_other';
              }
            }

            function redirector() {
              if (url === '') {

                document.getElementById('noRadioSelected').innerHTML = 'Please select an option'
              } else {
                window.location.href = url;
              }
            }
          </script>
        "
      ),
    ];
  }

  public function scamWizardBankingFakecheck() {
    $fileOpen = file_get_contents("modules/custom/scamwizard/src/Controller/wizardTree.json");
    $data = json_decode($fileOpen);

    $parent = $data->fakecheck->parent;
    $path = $data->fakecheck->path;
    $title = $data->fakecheck->title;
    $h2 = $data->fakecheck->h2;
    $p1 = $data->fakecheck->p1;

    return [
      "#type" => "markup",
      "#markup" => t("
        <h2>$h2</h2>
        <p>$p1</p>
      ")
    ];
  }

  public function scamWizardBankingUnsolicitedcheck() {
    $fileOpen = file_get_contents("modules/custom/scamwizard/src/Controller/wizardTree.json");
    $data = json_decode($fileOpen);

    $parent = $data->unsolicitedcheck->parent;
    $path = $data->unsolicitedcheck->path;
    $title = $data->unsolicitedcheck->title;
    $h2 = $data->unsolicitedcheck->h2;
    $p1 = $data->unsolicitedcheck->p1;

    return [
      "#type" => "markup",
      "#markup" => t("
        <h2>$h2</h2>
        <p>$p1</p>
      ")
    ];
  }

  public function scamWizardBankingUnauthorizedwithdrawal() {
    $fileOpen = file_get_contents("modules/custom/scamwizard/src/Controller/wizardTree.json");
    $data = json_decode($fileOpen);

    $parent = $data->unauthorizedwithdrawal->parent;
    $path = $data->unauthorizedwithdrawal->path;
    $title = $data->unauthorizedwithdrawal->title;
    $h2 = $data->unauthorizedwithdrawal->h2;
    $p1 = $data->unauthorizedwithdrawal->p1;

    return [
      "#type" => "markup",
      "#markup" => t("
        <h2>$h2</h2>
        <p>$p1</p>
      ")
    ];
  }

  public function scamWizardTrickery() {
    $fileOpen = file_get_contents("modules/custom/scamwizard/src/Controller/wizardTree.json");
    $data = json_decode($fileOpen);

    $parent = $data->trickery->parent;
    $path = $data->trickery->path;
    $title = $data->trickery->title;
    $h2 = $data->trickery->h2;

    $option_irs = $data->irs->name;
    $option_socialsecurityoffice = $data->socialsecurityoffice->name;
    $option_company = $data->company->name;
    $option_other = $data->other->name;

    $path_irs = $data->irs->path;
    $path_socialsecurityoffice = $data->socialsecurityoffice->path;
    $path_company = $data->company->path;
    $path_other = $data->other->path;

    $domain = 'http://127.0.0.1:8888';

    return [
      "#type" => "markup",
      "#markup" => t("
          <h2>$h2</h2>
          <ul style='list-style-type: none;'>
            <li>
              <label class='container' style='cursor: pointer;'>
                <input type='radio' id='optionone' onclick='myFunction()' name='radio'
                  style='cursor: pointer;'>
                $option_irs
              </label>
            </li>
            <li>
              <label class='container' style='cursor: pointer;'>
                <input type='radio' id='optiontwo' onclick='myFunction()' name='radio'
                  style='cursor: pointer;'>
                $option_socialsecurityoffice
              </label>
            </li>
            <li>
              <label class='container' style='cursor: pointer;'>
                <input type='radio' id='optionthree' onclick='myFunction()' name='radio'
                  style='cursor: pointer;'>
                $option_company
              </label>
            </li>
            <li>
              <label class='container' style='cursor: pointer;'>
                <input type='radio' id='optionfour' onclick='myFunction()' name='radio'
                  style='cursor: pointer;'>
                $option_other
              </label>
            </li>
            <li style='cursor: pointer; padding-left: 65px;'>
              <button onclick='redirector()'>
                <b>Submit</b>
              </button>
              <span id='noRadioSelected' style='color:red;'></span>
            </li>
          </ul>

          <script>
            let url = '';

            function myFunction() {

              if (document.getElementById('optionone').checked) {
                url = '$domain$path_irs';
              }

              if (document.getElementById('optiontwo').checked) {
                url = '$domain$path_socialsecurityoffice';
              }

              if (document.getElementById('optionthree').checked) {
                url = '$domain$path_company';
              }

              if (document.getElementById('optionfour').checked) {
                url = '$domain$path_other';
              }
            }

            function redirector() {
              if (url === '') {

                document.getElementById('noRadioSelected').innerHTML = 'Please select an option'
              } else {
                window.location.href = url;
              }
            }
          </script>
        "
      ),
    ];
  }

  public function scamWizardTrickeryIRS() {
    $fileOpen = file_get_contents("modules/custom/scamwizard/src/Controller/wizardTree.json");
    $data = json_decode($fileOpen);

    $parent = $data->irs->parent;
    $path = $data->irs->path;
    $title = $data->irs->title;
    $h2 = $data->irs->h2;
    $p1 = $data->irs->p1;

    return [
      "#type" => "markup",
      "#markup" => t("
        <h2>$h2</h2>
        <p>$p1</p>
      ")
    ];
  }

  public function scamWizardTrickerySocialsecurityoffice() {
    $fileOpen = file_get_contents("modules/custom/scamwizard/src/Controller/wizardTree.json");
    $data = json_decode($fileOpen);

    $parent = $data->socialsecurityoffice->parent;
    $path = $data->socialsecurityoffice->path;
    $title = $data->socialsecurityoffice->title;
    $h2 = $data->socialsecurityoffice->h2;
    $p1 = $data->socialsecurityoffice->p1;

    return [
      "#type" => "markup",
      "#markup" => t("
        <h2>$h2</h2>
        <p>$p1</p>
      ")
    ];
  }

  public function scamWizardTrickeryCompany() {
    $fileOpen = file_get_contents("modules/custom/scamwizard/src/Controller/wizardTree.json");
    $data = json_decode($fileOpen);

    $parent = $data->company->parent;
    $path = $data->company->path;
    $title = $data->company->title;
    $h2 = $data->company->h2;
    $p1 = $data->company->p1;

    return [
      "#type" => "markup",
      "#markup" => t("
        <h2>$h2</h2>
        <p>$p1</p>
      ")
    ];
  }

  public function scamWizardMoving() {
    $fileOpen = file_get_contents("modules/custom/scamwizard/src/Controller/wizardTree.json");
    $data = json_decode($fileOpen);

    $parent = $data->moving->parent;
    $path = $data->moving->path;
    $title = $data->moving->title;
    $h2 = $data->moving->h2;

    $option_instate = $data->instate->name;
    $option_outofstate = $data->outofstate->name;
    $option_other = $data->other->name;

    $path_instate = $data->instate->path;
    $path_outofstate = $data->outofstate->path;
    $path_other = $data->other->path;

    $domain = 'http://127.0.0.1:8888';

    return [
      "#type" => "markup",
      "#markup" => t("
          <h2>$h2</h2>
          <ul style='list-style-type: none;'>
            <li>
              <label class='container' style='cursor: pointer;'>
                <input type='radio' id='optionone' onclick='myFunction()' name='radio'
                  style='cursor: pointer;'>
                $option_instate
              </label>
            </li>
            <li>
              <label class='container' style='cursor: pointer;'>
                <input type='radio' id='optiontwo' onclick='myFunction()' name='radio'
                  style='cursor: pointer;'>
                $option_outofstate
              </label>
            </li>
            <li>
              <label class='container' style='cursor: pointer;'>
                <input type='radio' id='optionthree' onclick='myFunction()' name='radio'
                  style='cursor: pointer;'>
                $option_other
              </label>
            </li>
            <li style='cursor: pointer; padding-left: 65px;'>
              <button onclick='redirector()'>
                <b>Submit</b>
              </button>
              <span id='noRadioSelected' style='color:red;'></span>
            </li>
          </ul>

          <script>
            let url = '';

            function myFunction() {

              if (document.getElementById('optionone').checked) {
                url = '$domain$path_instate';
              }

              if (document.getElementById('optiontwo').checked) {
                url = '$domain$path_outofstate';
              }

              if (document.getElementById('optionthree').checked) {
                url = '$domain$path_other';
              }
            }

            function redirector() {
              if (url === '') {

                document.getElementById('noRadioSelected').innerHTML = 'Please select an option'
              } else {
                window.location.href = url;
              }
            }
          </script>
        "
      ),
    ];
  }

  public function scamWizardMovingInstate() {
    $fileOpen = file_get_contents("modules/custom/scamwizard/src/Controller/wizardTree.json");
    $data = json_decode($fileOpen);

    $parent = $data->instate->parent;
    $path = $data->instate->path;
    $title = $data->instate->title;
    $h2 = $data->instate->h2;
    $p1 = $data->instate->p1;

    return [
      "#type" => "markup",
      "#markup" => t("
        <h2>$h2</h2>
        <p>$p1</p>
      ")
    ];
  }

  public function scamWizardMovingOutofstate() {
    $fileOpen = file_get_contents("modules/custom/scamwizard/src/Controller/wizardTree.json");
    $data = json_decode($fileOpen);

    $parent = $data->outofstate->parent;
    $path = $data->outofstate->path;
    $title = $data->outofstate->title;
    $h2 = $data->outofstate->h2;
    $p1 = $data->outofstate->p1;

    return [
      "#type" => "markup",
      "#markup" => t("
        <h2>$h2</h2>
        <p>$p1</p>
      ")
    ];
  }

  public function scamWizardFraud() {
    $fileOpen = file_get_contents("modules/custom/scamwizard/src/Controller/wizardTree.json");
    $data = json_decode($fileOpen);

    $parent = $data->fraud->parent;
    $path = $data->fraud->path;
    $title = $data->fraud->title;
    $h2 = $data->fraud->h2;

    $option_socialsecurityfraud = $data->socialsecurityfraud->name;
    $option_taxfraud = $data->taxfraud->name;
    $option_medicalfraud = $data->medicalfraud->name;
    $option_medicaidfraud = $data->medicaidfraud->name;
    $option_other = $data->other->name;

    $path_socialsecurityfraud = $data->socialsecurityfraud->path;
    $path_taxfraud = $data->taxfraud->path;
    $path_medicalfraud = $data->medicalfraud->path;
    $path_medicaidfraud = $data->medicaidfraud->path;
    $path_other = $data->other->path;

    $domain = 'http://127.0.0.1:8888';

    return [
      "#type" => "markup",
      "#markup" => t("
          <h2>$h2</h2>
          <ul style='list-style-type: none;'>
            <li>
              <label class='container' style='cursor: pointer;'>
                <input type='radio' id='optionone' onclick='myFunction()' name='radio'
                  style='cursor: pointer;'>
                $option_socialsecurityfraud
              </label>
            </li>
            <li>
              <label class='container' style='cursor: pointer;'>
                <input type='radio' id='optiontwo' onclick='myFunction()' name='radio'
                  style='cursor: pointer;'>
                $option_taxfraud
              </label>
            </li>
            <li>
              <label class='container' style='cursor: pointer;'>
                <input type='radio' id='optionthree' onclick='myFunction()' name='radio'
                  style='cursor: pointer;'>
                $option_medicalfraud
              </label>
            </li>
            <li>
              <label class='container' style='cursor: pointer;'>
                <input type='radio' id='optionfour' onclick='myFunction()' name='radio'
                  style='cursor: pointer;'>
                $option_medicaidfraud
              </label>
            </li>
            <li>
              <label class='container' style='cursor: pointer;'>
                <input type='radio' id='optionfive' onclick='myFunction()' name='radio'
                  style='cursor: pointer;'>
                $option_other
              </label>
            </li>
            <li style='cursor: pointer; padding-left: 65px;'>
              <button onclick='redirector()'>
                <b>Submit</b>
              </button>
              <span id='noRadioSelected' style='color:red;'></span>
            </li>
          </ul>

          <script>
            let url = '';

            function myFunction() {

              if (document.getElementById('optionone').checked) {
                url = '$domain$path_socialsecurityfraud';
              }

              if (document.getElementById('optiontwo').checked) {
                url = '$domain$path_taxfraud';
              }

              if (document.getElementById('optionthree').checked) {
                url = '$domain$path_medicalfraud';
              }

              if (document.getElementById('optionfour').checked) {
                url = '$domain$path_medicaidfraud';
              }

              if (document.getElementById('optionfive').checked) {
                url = '$domain$path_other';
              }
            }

            function redirector() {
              if (url === '') {

                document.getElementById('noRadioSelected').innerHTML = 'Please select an option'
              } else {
                window.location.href = url;
              }
            }
          </script>
        "
      ),
    ];
  }

  public function scamWizardFraudSocialsecurityfraud() {
    return [
      "#type" => "markup",
      "#markup" => t("
        <h2>A Social Security Fraud Scam</h2>
        <p>Here's what to do...</p>
      ")
    ];
  }

  public function scamWizardFraudTaxfraud() {
    return [
      "#type" => "markup",
      "#markup" => t("
        <h2>A Tax Fraud Scam</h2>
        <p>Here's what to do...</p>
      ")
    ];
  }

  public function scamWizardFraudMedicalfraud() {
    return [
      "#type" => "markup",
      "#markup" => t("
        <h2>A Medical Fraud Scam</h2>
        <p>Here's what to do...</p>
      ")
    ];
  }

  public function scamWizardFraudMedicaidormedicarefraud() {
    return [
      "#type" => "markup",
      "#markup" => t("
        <h2>A Medicaid or Medicare Fraud Scam</h2>
        <p>Here's what to do...</p>
      ")
    ];
  }

  public function scamWizardOther() {
    return [
      "#type" => "markup",
      "#markup" => t("
        <h2>Some Other Scam</h2>
        <p>Here's what to do...</p>
      ")
    ];
  }
}
