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
   * 
   * @param \Drupal\alexa2\Alexa2Event $event
   *   The event object.
   */
  public function onRequest(Alexa2Event $event) {
    // TODO don't hardcode this
    $defaultUUID = 'fa83c8db-eaef-4c93-b309-dac114ff777a';
    // Note we want to strip the <speak> tag since we manually add it later.
    $ssmlTags = '<amazon:domain><amazon:effect><amazon:emotion><audio><break><emphasis><lang><p><phoneme><prosody><s><say-as><sub><voice><w>';

    $request = $event->getRequest();
    $response =& $event->getResponse();

    // E.g. LaunchRequest vs IntentRequest
    $intentName = $request?->request instanceof IntentRequest ? $request->request->intent->name : $request?->request?->type;
    if ( $intentName ) {
      $intentName = ltrim($intentName, 'AMAZON.');
    }
    switch ($intentName) {
      case 'CancelIntent':      
      case 'StopIntent':
        $response->response->outputSpeech = OutputSpeech::createByText('Thank you for using the scam wizard. Goodbye.');
        $response->response->shouldEndSession = true;
        break;

      case 'NavigateHomeIntent':
        $response->response->outputSpeech = OutputSpeech::createByText('Navigate Home Intent heard');
        $response->response->shouldEndSession = false;
        break;

      case 'HelpIntent':
        $response->response->outputSpeech = OutputSpeech::createByText('You asked for help. Here it is.');
        $response->response->shouldEndSession = false;
        break;
      
      case 'FallbackIntent':
        $this->fallbackIntent( $request, $response );
        break;
      
      case 'DynamicEntitiesIntent':
        //ER_SUCCESS_NO_MATCH
        //ER_SUCCESS_MATCH
        // Check to see if this is a direct match for the slot. Alexa will also match non-slot values.
        // E.g. if your utterance is "My scam is {slotname}" and slotname has values ["test"] as options,
        // saying "My scam is test" will trigger this Intent and result in ER_SUCCESS_MATCH for the slot, while
        // saying "My scam is nothing" will trigger this Intent and result in ER_SUCCESS_NO_MATCH.
        // Note that indirect matches will still show ER_SUCCESS_MATCH, e.g. in the above example,
        // "My scam is tests" would still be ER_SUCCESS_MATCH
        if ($choice = $this->getSlotValueFromMatch( $request )) {
          $this->wizardStep( $request, $response, $choice );
        } else {
          // TODO - do we want to do something else when we land on this Intent but the
          // slot isn't a match.
          $this->fallbackIntent( $request, $response );
        }


        break;

      case 'SearchQueryIntent':
        if ( $choice = $this->getSlotValue( $request, 'query' ) ) {
          $this->wizardStep( $request, $response, $choice );
        } else {
          // TODO
          $this->fallbackIntent( $request, $response );
        }
        break;

      case 'RestartIntent':
      case 'LaunchRequest':
        $response->response->shouldEndSession = false;
        // TODO - hardcoded for now
        $currentStepUUID = $defaultUUID;
        $currentStep = $this->getStepByUUID( $currentStepUUID );
        $response->sessionAttributes['currentStep'] = $currentStep->uuid();
        $title = $currentStep->getTitle();
        $question = $currentStep->body->value;
        $question = strip_tags(html_entity_decode($question), $ssmlTags);
        $suggestions = $this->getSuggestions($currentStep);
        $utterances = $this->getAllPossibleUtterances( $currentStep );
        if ( $suggestions && !empty($suggestions) ) {
          $question .= " You can say things like: It's " . $suggestions . '.';
        }
        $question = '<speak>' . $question . '</speak>';
        $response->response->outputSpeech = OutputSpeech::createBySSML( $question );
        $reprompt = new Reprompt(OutputSpeech::createBySSML( $question ));
        $response->response->reprompt = $reprompt;
        $response->response->card = Card::createSimple( $title, $question );

        $this->updateSlotSuggestions( $request, $response, $utterances );
        break;
    }

  }

  protected function updateSlotSuggestions( &$request, &$response, $utterances ) {
    $types = [];
    $updateDynamicEntitiesDirective = \MaxBeckers\AmazonAlexa\Response\Directives\Dialog\UpdateDynamicEntities\Replace::create();
    foreach ($utterances as $utterance) {
      $types[] = $utterance;
    }
    $updateDynamicEntitiesDirective->addType(
      \MaxBeckers\AmazonAlexa\Response\Directives\Dialog\Entity\Type::create('DynamicSlot', $types)
    );
    $response->response->addDirective($updateDynamicEntitiesDirective);
  }

  protected function wizardStep( &$request, &$response, $choice ) {
    $response->response->shouldEndSession = false;
    $currentStep = $this->getCurrentStep( $request );
    $nextStep = $this->findNextStep( $choice, $currentStep );
    $suggestions = $this->getSuggestions( $nextStep );
    $utterances = $this->getAllPossibleUtterances( $nextStep );
    $title = $nextStep->getTitle();

    // If the current step and next step are equal, we didn't find a matching step
    // for the uttered phrase. We should say something else.
    if ($currentStep && $nextStep && $currentStep->id() === $nextStep->id()) {
      $output = "I'm sorry, I didn't catch that. You can say things like: It's " . $suggestions . '.';
      $output .= " Or you can say 'start over' or 'stop.'";
    } else {
      $output = $nextStep->body->value;
      $output = strip_tags(html_entity_decode($output), $ssmlTags);
      if ( $suggestions && !empty($suggestions) ) {
        $output .= " You can say things like: It's " . $suggestions . '.';
      } else {
        $output .= " Would you like to ask about another scam? You can say 'start over' or 'stop.'";
      }
    }
    $nextStepUUID = $nextStep->uuid();
    $output = '<speak>' . $output . '</speak>';
    $response->sessionAttributes['currentStep'] = $nextStepUUID;
    $reprompt = new Reprompt(OutputSpeech::createBySSML($output));
    $response->response->reprompt = $reprompt;
    $response->response->outputSpeech = OutputSpeech::createBySSML($output);
    $response->response->card = Card::createSimple($title, $output);
    $this->updateSlotSuggestions( $request, $response, $utterances );
  }

  protected function fallbackIntent( &$request, &$response ) {
    $response->sessionAttributes['currentStep'] = $request->session->attributes['currentStep'];
    $response->response->outputSpeech = OutputSpeech::createByText('Sorry, I\'m not sure what that is. Please say your scam. Or you can say start over or stop.');
    $response->response->shouldEndSession = false;
  }

  public function getSuggestions( $currentStep ) {
    $suggestions = [];

    if ( $currentStep && ($children = $currentStep->get('field_wizard_step')->referencedEntities()) ) {
      foreach ($children as $child) {
        $suggestions[] = $child->get('field_wizard_primary_utterance')->getString();
      }
      if ( count($suggestions) > 0 ) {
        $separator = ' ';
        if ( count($suggestions) > 1 ) {
          $suggestions[count($suggestions) - 1] = 'or ' . $suggestions[count($suggestions) - 1];
        }
        if ( count($suggestions) > 2 ) {
          $separator = ', ';
        }
      }
    }

    return implode($separator, $suggestions);
  }

  protected function getAllPossibleUtterances( $currentStep ) {
    $utterances = [];

    if ( $currentStep && ($children = $currentStep->get('field_wizard_step')->referencedEntities()) ) {
      foreach ($children as $child) {
        // Add primary utterance and aliases to possible phrases
        $primaryUtterance = $child->get('field_wizard_primary_utterance')->getString();
        $utterance = [
          'id' => preg_replace('/[ -]/', '_', $primaryUtterance),
          'name' => [
            'value' => $primaryUtterance,
            'synonyms' => []
          ]
        ];
        $aliases = $child->get('field_wizard_aliases')->getString();
        if ( !empty($aliases) ) {
          $utterance['synonyms'] = array_merge($utterances, preg_split('/\s*,\s*/', $aliases));
        }
        $utterances[] = $utterance;
      }
    }

    return $utterances;
  }

  public function getCurrentStep( $request ) {
    $currentStepUUID = '';
    if ( !empty($request->session->attributes['currentStep']) ) {
      $currentStepUUID = $request->session->attributes['currentStep'];
      // Validate that the UUID exists and references a valid step.
      return $this->getStepByUUID( $currentStepUUID );
    }

    return null;
  }

  public function findNextStep ($choice, $currentStep ) {
    $options = $currentStep->get('field_wizard_step')->referencedEntities();
    foreach ( $options as $option ) {
      if ( $this->choiceMatchesStep( $choice, $option, 'exact' ) ) {
        return $option;
      }
    }

    foreach ( $options as $option ) {
      if ( $this->choiceMatchesStep( $choice, $option, 'alias' ) ) {
        return $option;
      }
    }

    foreach ( $options as $option ) {
      if ( $this->choiceMatchesStep( $choice, $option, 'phenomes' ) ) {
        return $option;
      }
    }

    return $currentStep;
  }

  public function getStepByUUID( $uuid ) {
    if ( empty($uuid) ) {
      return null;
    }
    $currentStep = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['uuid' => $uuid]);
    $currentStep = reset($currentStep);
    return $currentStep;
  }

  public function getCurrentPath( $request ) {
   $path = [];
   if ( !empty($request->session->attributes['path']) ) {
     $path = explode( '/', $request->session->attributes['path'] );
   }
   return $path;
  }

  public function getSlotValue( $request, $slotName ) {
   foreach ( $request->request->intent->slots as $slot ) {
     if ( $slot->name == $slotName ) {
       return $slot->value;
     }
   }
   return null;
  }

  public function getSlotValueFromMatch( $request ) {
    foreach ( $request->request->intent->slots as $slot ) {
      $resolutions = $slot?->resolutions;
      if ( $resolutions ) {
        foreach ( $resolutions as $resolution ) {
          if ($resolution->status->code === "ER_SUCCESS_MATCH") {
            return $resolution->values[0]->name;
          }
        }
      }
    }
    return null;
  }

  public function phrasesMatch( $phrase, $possibleMatches, $part = 'normal' ) {
    if ( !empty($part) && is_array($phrase) && !empty($phrase[$part] ) ) {
      $phrase = $phrase[$part];
    }
    if ( !is_array($phrase) ) {
      $phrase = [$phrase];
    }
    if ( !is_array($possibleMatches) ) {
      $possibleMatches = [$possibleMatches];
    }
    /// in theory this would be better returning a confidence factor
    /// based on how many of the phrases match how many of the matches
    foreach ( $phrase as $phraseItem ) {
      foreach ($possibleMatches as $possibleMatch ) {
        /// this might be one of our nomalized arrays, if so just get whatever part we are interested in
        if ( !empty($part) && is_array($possibleMatch) && !empty($possibleMatch[$part]) ) {
          $possibleMatch = $possibleMatch[$part];
        }
        /// the possible match may be a list of possible matches
        if ( is_array($possibleMatch) ) {
          foreach ( $possibleMatch as $possibleMatch2 ) {
            if ( $phraseItem === $possibleMatch2 ) {
              return true;
            }
          }
        } else {
          if ( $phraseItem === $possibleMatch ) {
            return true;
          }
        }
      }
    }
    return false;
  }

  public function choiceMatchesStep( $choice, $step, $matchType = 'exact|alias|phenomes' ) {
    //normalize inputs
    $choice = $this->normalizePhrase( $choice, true );
    // TODO don't match against title
    // $step_utterances = [$this->normalizePhrase( $step->getTitle(), true )];
    $step_utterances = [$this->normalizePhrase( $step->get('field_wizard_primary_utterance')->getString() )];
    
    $step_aliases = preg_split('/\s*,\s*/', $step->get('field_wizard_aliases')->getString());
    $step_aliases = array_map( function($a) {
      return $this->normalizePhrase( $a, true );
    }, $step_aliases);

    if ( preg_match('/\bexact\b/', $matchType) ) {
      if ( $this->phrasesMatch( $choice, $step_utterances ) ) {
        return true;
      }
    }

    if ( preg_match('/\balias\b/', $matchType ) ) {
      if ( !empty($step_aliases) ) {
        if ( $this->phrasesMatch( $choice, $step_aliases ) ) {
          return true;
        }
      }
    }

    if ( preg_match('/\bphenomes\b/', $matchType ) ) {
      if ( !empty($step_aliases) ) {
        if ( $this->phrasesMatch( $choice, $step_aliases, 'phenomes' ) ) {
          return true;
        }
      }
    }

    // next check closest match - calculate levenshtein distance between incoming phrase
    // and each of the aliases/phenomes of the step, and return the closest match
    // need to come up with a good threshold for closeness

    // next send this off to openAI or local llm to check matches?

    return false;
  }

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

  public function normalizePhrase( $phrase, $withPhenomes = false ) {
    /// should do stemming here too in order to capture broader matches
    /// but we would need to have a separate stemmer per language, and so
    /// far both english and spanish have been supported with the same code
    $words = preg_replace( '/\W/', '', preg_split('/\s+/', strtolower($phrase)) );
    $result = [
      'original' => $phrase,
      'normal' => implode( ' ', $words ),
      'phenomes' => []
    ];

    if ( $withPhenomes ) {
      $primary = [];
      $secondary = [];
      foreach ( $words as $word ) {
        $dm = new \DoubleMetaphone($word);
        $primary[] = $dm->primary;
        $secondary[] = $dm->secondary;
      }
      $result['phenomes'][] = implode( ' ', $primary );
      $result['phenomes'][] = implode( ' ', $secondary );
    }
    return $result;
  }

}
