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

      case 'AnswerIntent':
        $choice = $this->getSlotValue( $request, 'Choice' );
        $nextStep = $this->findNextStep( $choice, $currentStep );
        $shouldEndSession = empty($nextStep['options']);
        $response->sessionAttributes['path'] = $nextStep['path'];
        $output = $nextStep['question'];
        if ( $shouldEndSession ) {
          $output .= '. Goodbye.';
        } else {
          $repromptSpeech = OutputSpeech::createByText($nextStep['question']);
          $reprompt = new Reprompt($repromptSpeech);
          $response->response->reprompt = $reprompt;
        }
        $response->response->outputSpeech = OutputSpeech::createByText( $output );
        // $response->response->card = Card::createSimple($title,$content);
        break;

      case 'LaunchRequest':
      default:
        $response->sessionAttributes['path'] = 'launch';
        $response->response->outputSpeech = OutputSpeech::createByText($currentStep['question']);
        $repromptSpeech = OutputSpeech::createByText($currentStep['question']);
        $reprompt = new Reprompt($repromptSpeech);
        $response->response->reprompt = $reprompt;
        break;
    }
    $response->response->shouldEndSession = $shouldEndSession;

  }

  public function getDB() {
    $fileOpen = file_get_contents("modules/custom/alexa_hello/src/EventSubscriber/wizardTree.json");
    $db = json_decode($fileOpen);

    //$this->calculatePhenomes( $db );
    $this->calculateFullPaths( $db );
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

  public function calculateFullPaths( &$step ) {

    if ( empty($step['parent']) ) {
      $step['parent'] = null;
      $step['path'] = $step['id'];
    } else if ( !empty($step['parent']['path']) ){
      $step['path'] = $step['parent']['path'].'/'.$step['id'];
    } else {
      $step['path'] = $step['id'];
    }

    foreach ( $step['options'] as &$option ) {
      $option['parent'] =& $step;
      $this->calculateFullPaths( $option );
    }

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
    foreach ( $request->request->intent->slots as $slot ) {
      if ( $slot->name == $slotName ) {
        return $slot->value;
      }
    }
    return null;
  }

  public function findNextStep( $choice, $currentStep ) {
    // prefer more specific to more fuzzy matches
    // check for exact match
    foreach ( $currentStep['options'] as $option ) {
      // echo "if ( this->choiceMatchesStep( $choice, ${option['id']}, 'exact' ) )\n";
      if ( $this->choiceMatchesStep( $choice, $option, 'exact' ) ) {
        return $option;
      }
    }
    // check for alias match
    foreach ( $currentStep['options'] as $option ) {
      // echo "if ( this->choiceMatchesStep( $choice, ".implode(',',$option['aliases']).", 'alias' ) )\n";
      if ( $this->choiceMatchesStep( $choice, $option, 'alias' ) ) {
        return $option;
      }
    }
    // check for primary phenome match
    foreach ( $currentStep['options'] as $option ) {
      // echo "if ( this->choiceMatchesStep( $choice, ".implode(',',$option['phenomes']).", 'phenomes' ) )\n";
      if ( $this->choiceMatchesStep( $choice, $option, 'phenomes' ) ) {
        return $option;
      }
    }
    return $currentStep;
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

  public function choiceMatchesStep( $choice, $step, $matchType = 'exact|alias|phenomes' ) {
    // echo "choiceMatchesStep( $choice, ${step['id']}, $matchType )\n";
    $choice = $this->normalizePhrase( $choice, true );
    if ( preg_match('/\bexact\b/',$matchType) ) {
      // echo "exact\n";
      if ( $this->phrasesMatch( $choice, $step['id'] ) ) {
        return true;
      }
    }
    if ( preg_match('/\balias\b/',$matchType) ) {
      // echo "alias\n";
      if ( !empty($step['aliases']) ) {
        if ( $this->phrasesMatch( $choice, $step['aliases'] ) ) {
          return true;
        }
      }
    }
    //if ( preg_match('/\bphenomes\b/',$matchType) ) {
    //  // echo "phenomea\n";
    //  if ( !empty($step['phenome']) || !empty($step['phenome2']) ) {
    //    if ( $this->phrasesMatch( $choice, [$step['phenome'],$step['phenome2']] ) ) {
    //      return true;
    //    }
    //  }
    //}
    // send this off to openAI?
    // use local llm to check matches?
    return false;
  }
}
