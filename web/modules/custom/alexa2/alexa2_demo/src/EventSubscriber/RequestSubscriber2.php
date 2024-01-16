<?php

namespace Drupal\alexa2_demo\EventSubscriber;

use MaxBeckers\AmazonAlexa\Request\Request;
use MaxBeckers\AmazonAlexa\Request\Request\Standard\IntentRequest;
use Drupal\alexa2\Alexa2Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

// use MaxBeckers\AmazonAlexa\Helper\ResponseHelper;
use MaxBeckers\AmazonAlexa\Response\Card;
use MaxBeckers\AmazonAlexa\Response\Directives\Directive;
use MaxBeckers\AmazonAlexa\Response\OutputSpeech;
use MaxBeckers\AmazonAlexa\Response\Reprompt;
use MaxBeckers\AmazonAlexa\Response\Response;
use MaxBeckers\AmazonAlexa\Response\ResponseBody;
use MaxBeckers\AmazonAlexa\Helper;

/**
 * An event subscriber for Alexa request events.
 */
class RequestSubscriber2 implements EventSubscriberInterface {

  /**
   * Gets the event.
   */
  public static function getSubscribedEvents() {
    $events['alexa2event.request'][] = ['onRequest', 0];
    return $events;
  }

  /**
   * An event subscriber for Alexa request events.
   */
  public function onRequest(Alexa2Event $event) {

    // \Drupal::logger('alexa2')->error('Handling Alexa Request');

    $request = $event->getRequest();
    error_log(json_encode($request));
    error_log(json_encode($request->request));
    $response =& $event->getResponse();

    //$intentName = $request?->request instanceof IntentRequest ? $request->request->intent->name : $request?->request?->type;
    $intentName = "";

    if($request->request->type == "LaunchRequest") {
      $intentName = $request->request->type;
    }
    elseif ($request->request->type == "IntentRequest") {
      $intentName = $request->request->intent->name;
    }

    // $response->response->outputSpeech = OutputSpeech::createByText('Hello Drupal');
    // $response->response->shouldEndSession = true;

    //$db = (object) [
    //  "colorado" => (object) [
    //    "options" => "Pick Denver or Alamosa"
    //    "denver" => "Population 2,000,000",
    //    "alamosa" => "Population 2,000"
    //  ],
    //  "texas" => (object) [
    //    "options" => "Pick Houston or Austin",
    //    "houston" => "Population 5,000,000",
    //    "austin" => "Population 1,500,000"
    //  ]
    //];

    //$currentPath = $this->getCurrentPath( $request );
    //$currentPathString = implode('/', $currentPath);
    //$currentStep = $this->getCurrentStep($currentPath, $db);

    //$shouldEndSession = empty($currentStep['options']);
    $shouldEndSession = false;


    //switch($intentName) {
    //  case 'CancelIntent':
    //    $response->response->outputSpeech = OutputSpeech::createByText('Cancel Intent heard');
    //    $shouldEndSession = true;
    //    break;

    //  case 'StopIntent':
    //    $response->response->outputSpeech = OutputSpeech::createByText('Stop Intent heard');
    //    $shouldEndSession = true;
    //    break;

    //  case 'NavigateHomeIntent':
    //    $response->response->outputSpeech = OutputSpeech::createByText('Navigate Home Intent heard');
    //    $shouldEndSession = false;
    //    break;

    //  case 'AMAZON.HelpIntent':
    //    $response->response->outputSpeech = OutputSpeech::createByText('You can ask anything and I will respond with "Hello Drupal"');
    //    $shouldEndSession = false;
    //    break;

    //  case 'LaunchRequest':
    //    $response->response->outputSpeech = OutputSpeech::createByText( "Hello light switch and " . $request->request->type);
    //    break;

    //  default:
    //    $response->response->outputSpeech = OutputSpeech::createByText( "Default message " . $intentName);
    //    break;
    //}

    switch ($intentName) {
      case 'CancelIntent':
        $response->response->outputSpeech = OutputSpeech::createByText('Cancel Intent heard');
        $shouldEndSession = true;
        break;

      case 'StopIntent':
        $response->response->outputSpeech = OutputSpeech::createByText('Stop Intent heard');
        $shouldEndSession = true;
        break;

      case 'NavigateHomeIntent':
        $response->response->outputSpeech = OutputSpeech::createByText('Navigate Home Intent heard');
        $shouldEndSession = false;
        break;

      case 'AMAZON.HelpIntent':
        $response->response->outputSpeech = OutputSpeech::createByText('You asked for help. Here it is.');
        $shouldEndSession = false;
        break;

      case 'SearchQueryIntent':
        $output;
        $choice = $request->request->intent->slots->query->value; // $this->getSlotValue( $request, 'Choice' ); // Choice?
        $pattern = "/ /i"; // remove white-space
        $choice = preg_replace($pattern, "", $choice);
        $pattern = "/\./i"; // remove periods
        $choice = strtolower(preg_replace($pattern, "", $choice));

        //$shouldEndSession = in_array($choice, $db);
        //if ( $shouldEndSession ) {
        //  $output .= '. Goodbye.';
        //} else {
        //  $response->sessionAttributes['path'] .= "/" . $choice['path'];
        //  $output = getMessage($db[$choice]);
        //  $repromptSpeech = OutputSpeech::createByText($db[$choice]['h2']);
        //  $reprompt = new Reprompt($repromptSpeech);
        //  $response->response->reprompt = $reprompt;
        //}
        $response->response->outputSpeech = OutputSpeech::createByText( "Anything can happen. " . $request->request->intent->slots->query->name /* $request->request->intent->slots->query->value */ );
        $shouldEndSession = false;
        break;

      case 'LaunchRequest':
        $response->response->outputSpeech = OutputSpeech::createByText( "This is a launch request for the scam wizard. Please say your scam." );
        break;

      default:
        $response->response->outputSpeech = OutputSpeech::createByText( "Default message " . $intentName);
        break;
    }
    $response->response->shouldEndSession = $shouldEndSession;

  }

  public function getDB() {
    $fileOpen = file_get_contents("modules/custom/alexa2/alexa2_demo/src/EventSubscriber/wizardTree.json");
    $db = json_decode($fileOpen);

    return $db;
  }

  //public function normalizePhrase( $phrase, $withPhenomes=false ) {
  //  /// should do stemming here too in order to capture broader matches
  //  /// but we would need to have a separate stemmer per language, and so
  //  /// far both english and spanish have been supported with the same code
  //  $normal = preg_replace( '/\W/', '', preg_split('/\s+/', strtolower($phrase)) );
  //  $result = [
  //    'original' => $phrase,
  //    'normal' => implode( ' ', $normal )
  //  ];
  //  if ( $withPhenomes ) {
  //    $phenome1 = [];
  //    $phenome2 = [];
  //    foreach ( $normal as $word ) {
  //      $dm = new \DoubleMetaphone($word);
  //      $phenome1[] = $dm->primary;
  //      $phenome2[] = $dm->secondary;
  //    }
  //    $result['phenome'] = implode( ' ', $phenome1 );
  //    $result['phenome2'] = implode( ' ', $phenome2 );
  //  }
  //  return $result;
  //}

  //public function getCurrentPath( $request ) {
  //  $path = [];
  //  if ( !empty($request->session->attributes['path']) ) {
  //    $path = explode( '/', $request->session->attributes['path'] );
  //  }
  //  return $path;
  //}

  //public function getSlotValue( $request, $slotName ) {
  //  foreach ( $request->request->intent->slots->query->value as $slot ) {
  //    if ( $slot->name == $slotName ) {
  //      return $slot->value;
  //    }
  //  }
  //  return null;
  //}

  function getMessage($data) {
    $msg = $data->h2;


    if( !is_null($data->children[0]) ) {
      $count = 1;

      foreach ( $data->children as $option ) {
        if($count === count($data->children)) {
          $msg .= ", or " . $option->name;
        } else if($count === 1){
          $msg .= " " . $option->name;
        } else {
          $msg .= ", " . $option->name;
        }
        $count++;
      }
    }

    return $msg;
  }

  //public function phrasesMatch( $phrases, $matches ) {
  //  if ( !is_array($phrases) ) {
  //    $phrases = [$phrase];
  //  }
  //  if ( !is_array($matches) ) {
  //    $matches = [$matches];
  //  }
  //  foreach ( $phrases as $p ) {
  //    foreach ( $matches as $m ) {
  //      if ( $p === $m ) {
  //        return true;
  //      }
  //    }
  //  }
  //  return false;
  //}

  //public function choiceMatchesStep( $choice, $step ) {
  //  $choice = $this->normalizePhrase( $choice, true );

  //  if ( $this->phrasesMatch( $choice, $step['name'] ) ) {
  //    return true;
  //  }

  //  return false;
  //}
}
