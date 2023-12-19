<?php

namespace Drupal\alexa_hello\EventSubscriber;

use MaxBeckers\AmazonAlexa\Request\Request\Request;
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
class RequestSubscriber implements EventSubscriberInterface {

  /**
   * Gets the event.
   */
  public static function getSubscribedEvents() {
    $events['alexaevent.request'][] = ['onRequest', 0];
    return $events;
  }

  /**
   * Called upon a request event.
   *
   * @param \Drupal\alexa2\Alexa2Event $event
   *   The event object.
   */
  public function onRequest(Alexa2Event $event) {

    // \Drupal::logger('alexa2')->error('Handling Alexa Request');

    $request = $event->getRequest();
    $response =& $event->getResponse();

    $intentName = $request?->request instanceof IntentRequest ? $request->request->intent->name : $request?->request?->type;

    // $response->response->outputSpeech = OutputSpeech::createByText('Hello Drupal');
    // $response->response->shouldEndSession = true;

    $db = $this->getDB();

    $currentPath = $this->getCurrentPath( $request );
    $currentPathString = implode('/', $currentPath);
    $currentStep = $this->getCurrentStep($currentPath, $db);

    $shouldEndSession = empty($currentStep['options']);

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

      case 'HelpIntent':
        $response->response->outputSpeech = OutputSpeech::createByText('You can ask anything and I will respond with "Hello Drupal"');
        $shouldEndSession = false;
        break;

      case 'SearchQueryIntent':
        $choice = $request->request->intent->slots->query->value; // $this->getSlotValue( $request, 'Choice' ); // Choice?
        $pattern = "/ /i"; // remove white-space
        $choice = preg_replace($pattern, "", $choice);
        $pattern = "/\./i"; // remove periods
        $choice = strtolower(preg_replace($pattern, "", $choice));

        $shouldEndSession = in_array($choice, $db);
        $response->sessionAttributes['path'] .= "/" . $choice['path'];
        $output = getMessage();
        if ( $shouldEndSession ) {
          $output .= '. Goodbye.';
        } else {
          $repromptSpeech = OutputSpeech::createByText($nextStep['h2']);
          $reprompt = new Reprompt($repromptSpeech);
          $response->response->reprompt = $reprompt;
        }
        $response->response->outputSpeech = OutputSpeech::createByText( $output );
        break;

      case 'LaunchRequest':
      default:
        $response->sessionAttributes['path'] = 'launch';
        $response->response->outputSpeech = OutputSpeech::createByText($currentStep['h2']);
        $repromptSpeech = OutputSpeech::createByText($currentStep['h2']);
        $reprompt = new Reprompt($repromptSpeech);
        $response->response->reprompt = $reprompt;
        break;
    }
    $response->response->shouldEndSession = $shouldEndSession;

  }

  public function getDB() {
    $fileOpen = file_get_contents("modules/custom/alexa_hello/src/EventSubscriber/wizardTree.json");
    $db = json_decode($fileOpen);

    return $db;
  }

  public function normalizePhrase( $phrase, $withPhenomes=false ) {
    /// should do stemming here too in order to capture broader matches
    /// but we would need to have a separate stemmer per language, and so
    /// far both english and spanish have been supported with the same code
    $normal = preg_replace( '/\W/', '', preg_split('/\s+/', strtolower($phrase)) );
    $result = [
      'original' => $phrase,
      'normal' => implode( ' ', $normal )
    ];
    if ( $withPhenomes ) {
      $phenome1 = [];
      $phenome2 = [];
      foreach ( $normal as $word ) {
        $dm = new \DoubleMetaphone($word);
        $phenome1[] = $dm->primary;
        $phenome2[] = $dm->secondary;
      }
      $result['phenome'] = implode( ' ', $phenome1 );
      $result['phenome2'] = implode( ' ', $phenome2 );
    }
    return $result;
  }

  public function getCurrentPath( $request ) {
    $path = [];
    if ( !empty($request->session->attributes['path']) ) {
      $path = explode( '/', $request->session->attributes['path'] );
    }
    return $path;
  }

  public function getCurrentStep($path, $db ) {
    if ( is_string($path) ) {
      $path = explode('/', strtolower($path));
    }
    $currentStep = $db;
    foreach ( $path as $key ) {
      if ( $currentStep['id'] == $key ) {
        continue;
      } else if ( !empty( $currentStep['options'][$key] ) ) {
          $currentStep = $currentStep['options'][$key];
      } else {
        break;
      }
    }
    return $currentStep;
  }

  public function getSlotValue( $request, $slotName ) {
    foreach ( $request->request->intent->slots->query->value as $slot ) {
      if ( $slot->name == $slotName ) {
        return $slot->value;
      }
    }
    return null;
  }

  function getMessage($choice) {
    $msg = $choice->h2;


    if( !is_null($choice->children[0]) ) {
      $count = 1;

      foreach ( $choice->children as $option ) {
        if($count === count($choice->children)) {
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

  public function phrasesMatch( $phrases, $matches ) {
    if ( !is_array($phrases) ) {
      $phrases = [$phrase];
    }
    if ( !is_array($matches) ) {
      $matches = [$matches];
    }
    foreach ( $phrases as $p ) {
      foreach ( $matches as $m ) {
        if ( $p === $m ) {
          return true;
        }
      }
    }
    return false;
  }

  public function choiceMatchesStep( $choice, $step ) {
    $choice = $this->normalizePhrase( $choice, true );

    if ( $this->phrasesMatch( $choice, $step['name'] ) ) {
      return true;
    }

    return false;
  }
}
