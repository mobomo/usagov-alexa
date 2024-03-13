<?php

namespace Drupal\alexa_wizard\EventSubscriber;

use MaxBeckers\AmazonAlexa\Request\Request;
use MaxBeckers\AmazonAlexa\Request\Request\Standard\IntentRequest;
use Drupal\alexa2\Alexa2Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use MaxBeckers\AmazonAlexa\Helper\ResponseHelper;
use MaxBeckers\AmazonAlexa\Response\Card;
use MaxBeckers\AmazonAlexa\Response\Directives\Directive;
use MaxBeckers\AmazonAlexa\Response\Directives\Dialog;
use MaxBeckers\AmazonAlexa\Response\OutputSpeech;
use MaxBeckers\AmazonAlexa\Response\Reprompt;
use MaxBeckers\AmazonAlexa\Response\Response;
use MaxBeckers\AmazonAlexa\Response\ResponseBody;
use MaxBeckers\AmazonAlexa\Helper;
use MaxBeckers\AmazonAlexa\Intent;

/**
 * An event subscriber for Alexa request events.
 */
class RequestSubscriber implements EventSubscriberInterface {

  /**
   * Slot success code.
   * 
   * @var string
   */
  const SLOT_SUCCESS_MATCH = 'ER_SUCCESS_MATCH';

  /**
   * Holds the Alexa request data.
   * 
   * @var Request $request
   */
  protected $request;

  /**
   * Holds the Alexa response data.
   * 
   * @var Response $response
   */
  protected $response;

  /**
   * Gets the event.
   * 
   * @return array
   */
  public static function getSubscribedEvents() : array {
    $events['alexa2event.request'][] = ['onRequest', 0];
    return $events;
  }

  /**
   * An event subscriber for Alexa request events.
   * 
   * @param \Drupal\alexa2\Alexa2Event $event
   *   The event object.
   */
  public function onRequest( Alexa2Event $event ) : void {
    // Load the wizard service
    $alexaWizardService = \Drupal::service('alexa_wizard.wizard');
    // Load the wizard config
    $alexaWizardConfig = \Drupal::config(\Drupal\alexa_wizard\Form\AlexaWizardSettingsForm::SETTINGS);

    $this->request = $event->getRequest();
    $this->response =& $event->getResponse();

    // Determine the intent's name, e.g. LaunchRequest vs IntentRequest
    $intentName = $this->request?->request instanceof IntentRequest ? $this->request->request->intent->name : $this->request?->request?->type;
    switch ($intentName) {
      case 'AMAZON.CancelIntent':
      case 'CancelIntent':      
      case 'AMAZON.StopIntent':
      case 'StopIntent':
        // Stop or Cancel Intents provide an exit message and tell Alexa to end the session.
        $this->response->response->outputSpeech = OutputSpeech::createByText('Thank you for using the scam wizard. Goodbye.');
        $this->response->response->shouldEndSession = true;
        break;

      case 'NavigateHomeIntent':
      case 'AMAZON.NavigateHomeIntent':
        // TODO
        // Navigate Home intent
        $this->response->response->outputSpeech = OutputSpeech::createByText('Navigate Home Intent heard');
        $this->response->response->shouldEndSession = false;
        break;

      case 'AMAZON.HelpIntent':
      case 'HelpIntent':
        // Help intent provides a help message and does not end the session
        // TODO provide better help message depending on the current step?
        // TODO maybe a field in the CMS for each Wizard/Step, and/or a standard message in
        // TODO the wizard config?
        $this->response->response->outputSpeech = OutputSpeech::createByText('You asked for help. Here it is.');
        $this->response->response->shouldEndSession = false;
        break;
      
      case 'AMAZON.FallbackIntent':
      case 'FallbackIntent':
        // Fallback intent handles unknown utterances.
        $this->fallbackIntent();
        break;
      
      case 'WizardStepIntent':
        // Wizard Step intent is the main intent of this skill/module. It checks to see if the
        // user said a valid phrase (e.g. if the phrase corresponds to a child of the current step)
        // and "navigates" to that step, telling Alexa what to say in the process.
        if ($choice = $this->getSlotValue( 'wizardstepname', true )) {
          $this->wizardStep( $choice );
        } else {
          // If a valid child step isn't found, for now handle it the same as the fallback intent.
          // TODO - do we want to do something else when we land on this Intent but the
          // slot isn't a match?
          $this->fallbackIntent();
        }
        break;

      case 'RestartIntent':
      case 'LaunchRequest':
        // LaunchRequest and RestartIntent are handled the same. They both start the module/skill
        // from the beginning. Neither of these intents end the session.
        // TODO do we want to handle these slightly differently, maybe a different "welcome"
        // message if restarting vs launching?
        $this->response->response->shouldEndSession = false;
        // Unset current step on launch. Because the skill just launched, there is no selected wizard or step.
        $this->response->sessionAttributes['currentStep'] = null;
        // Load values from the config as needed.
        $launchText = $alexaWizardConfig->get('alexa_wizard_launch_message');
        $launchRepromptText = $alexaWizardConfig->get('alexa_wizard_launch_reprompt_message');
        $launchRepromptText ??= $launchText;
        // Generate suggestions and possible utterances
        $suggestions = $this->getSuggestions();
        $utterances = $this->getAllPossibleUtterances();
        // If there are suggestions, add them to the launch text and reprompt text.
        if ( $suggestions && !empty($suggestions) ) {
          // TODO add suggestions checkbox and text to config page?
          // E.g. auto generate suggestions checkbox
          // E.g. suggestions text: "You can say things like: It's {suggestions}."
          $launchText .= " You can say things like: It's " . $suggestions . ".";
          $launchRepromptText .= " You can say things like: It's " . $suggestions . ".";
        }
        // Sanitize both the launch and reprompt text, removing any tags that aren't SSML.
        $launchText = $alexaWizardService->sanitizeSSMLText( $launchText, true );
        $launchRepromptText = $alexaWizardService->sanitizeSSMLText( $launchRepromptText, true );
        // Set the output and reprompt speech.
        $this->response->response->outputSpeech = OutputSpeech::createBySSML( $launchText );
        $this->response->response->reprompt = new Reprompt(OutputSpeech::createBySSML( $launchRepromptText ));
        // TODO launch card text on config page
        // Create the response card.
        $this->response->response->card = Card::createSimple( 'Wizard Skill', strip_tags($launchText) );
        // Set WizardType to "wizard" e.g. My "wizard" is WizardStepName.
        $this->updateSlotSuggestions( [
          [
            "id" => "wizard_0",
            "name" => [
              "value" => "wizard",
              "synonyms" => []
            ]
          ]
        ], 'WizardType' );
        $this->updateSlotSuggestions( $utterances, 'WizardStepName' );
        break;
      default:
        // TODO some message here for the unhandled intent.
        // E.g. if the skill has a new intent added that we don't add to this module.
        break;
    }

  }

  /**
   * Uses the Update Dynamic Entities directive to update a slot's possible values.
   * 
   * @param array $utterances
   *   An array containing the new utterances to use for the next step.
   * @param string $slotName
   *   The name of the slot to which the new values should be applied.
   */
  protected function updateSlotSuggestions( array $utterances, string $slotName ) : void {
    $types = [];
    $updateDynamicEntitiesDirective = Dialog\UpdateDynamicEntities\Replace::create();
    foreach ($utterances as $utterance) {
      $types[] = $utterance;
    }
    $updateDynamicEntitiesDirective->addType(
      Dialog\Entity\Type::create($slotName, $types)
    );
    $this->response->response->addDirective($updateDynamicEntitiesDirective);
  }

  /**
   * Runs the selection provided by Alexa against the possible next wizard steps
   * and determines which step, if any, should be selected.
   * 
   * @param string $choice
   *   The user's selection.
   */
  protected function wizardStep( string $choice ) : void {
    // Performing a Wizard Step does not end the session even if
    // it is the last step in its tree path.
    $this->response->response->shouldEndSession = false;
    // $currentStep can be a Node or null. null means we're selecting a Wizard.
    // A Node means we're selecting a Wizard Step.
    $currentStep = $this->getCurrentStep();
    $nextStep = $this->findNextStep( $choice, $currentStep );
    $suggestions = $this->getSuggestions( $nextStep );
    $utterances = $this->getAllPossibleUtterances( $nextStep );
    $title = $nextStep->getTitle();

    // If the next step is null, we didn't find a matching step
    // for the uttered phrase. We should notify the user.
    // TODO fallback message in config for this?
    if ( $nextStep === null ) {
      $output = "I'm sorry, I didn't catch that. You can say things like: It's " . $suggestions . '.';
      $output .= " Or you can say 'start over' or 'stop.'";
    } else {
      // Set and sanitize output text.
      $output = $nextStep->body->value;
      $output = $alexaWizardService->sanitizeSSMLText($output, false);
      // TODO check suggestions settings in wizard config.
      if ( $suggestions && !empty($suggestions) ) {
        $output .= " You can say things like: It's " . $suggestions . '.';
      } else {
        $output .= " Would you like to ask about another scam? You can say 'start over' or 'stop.'";
      }
    }
    $nextStepId = $nextStep->id();
    $output = '<speak>' . $output . '</speak>';
    // Store the selected step in the response session attributes.
    // This is what allows us to "track" where the user is in the dialog.
    $this->response->sessionAttributes['currentStep'] = $nextStepId;
    // TODO separate reprompt field?
    // Set reprompt text, output speech, and response card, and update dynamic slot values.
    $reprompt = new Reprompt(OutputSpeech::createBySSML($output));
    $this->response->response->reprompt = $reprompt;
    $this->response->response->outputSpeech = OutputSpeech::createBySSML($output);
    $this->response->response->card = Card::createSimple($title, strip_tags($output));
    $this->updateSlotSuggestions( $this->response, $utterances, 'WizardStepIntent' );
  }

  /**
   * Handles the fallback intent.
   */
  protected function fallbackIntent() : void {
    // On fallback, it's important to maintain the current step. Without this, the user's progress through
    // the dialog would be lost.
    $this->response->sessionAttributes['currentStep'] = $this->request->session->attributes['currentStep'];
    // TODO what to say here? Settable in config? Offer suggestions?
    $this->response->response->outputSpeech = OutputSpeech::createByText('Sorry, I\'m not sure what that is. Please say your scam. Or you can say start over or stop.');
    $this->response->response->shouldEndSession = false;
  }

  /**
   * Generate suggestions text for the provided step.
   * 
   * @param Node|null $step
   *   The step for which to generate suggestions.
   * 
   * @return string
   *   A string containing the suggestions text, formatted as a comma
   *   separated list of values.
   */
  public function getSuggestions( Node|null $step = null ) : string {
    $suggestions = [];
    $separator = ' ';
    $possibleSteps = [];

    // Determine possible next steps
    if ( $step !== null ) {
      // If there is a current step, then possible next steps are always contained in the
      // field_wizard_step field. Even if it's empty, which means this is the last step in
      // this tree path.
      $possibleSteps = $step->get('field_wizard_step')->referencedEntities();
    } else {
      // If there is no current step, then we're at the top of the wizard tree. So grab
      // all wizards and generate suggestions from those.
      $possibleSteps = \Drupal::service('alexa_wizard.wizard')->getAllWizards();
    }
    $possibleSteps ??= [];

    // Suggestions should only contain the primary utterance, and no duplicates.
    // TODO limit the amount of suggestions? Maybe allow that to be set in config?
    foreach ( $possibleSteps as $possibleStep ) {
      $suggestion = $possibleStep->get('field_wizard_primary_utterance')->getString();
      if ( !in_array($suggestion, $suggestions) ) {
        $suggestions[] = $suggestion;
      }
    }
    // Determine the correct separator and where to place the "or" text.
    if ( count($suggestions) > 0 ) {
      if ( count($suggestions) > 1 ) {
        // If there's more than one item, add 'or ' to the last item.
        $suggestions[count($suggestions) - 1] = 'or ' . $suggestions[count($suggestions) - 1];
      }
      // 2 items have no comma between them, but 3 or more do.
      if ( count($suggestions) > 2) {
        $separator = ', ';
      }
    }

    return implode($separator, $suggestions);
  }

  /**
   * Gets all possible utterances for the current step's children, formatted
   * for the Alexa response in the format required by Dynamic Entities directive.
   * 
   * @param Node|null $step
   *   The step for which to get possible utterances.
   * 
   * @return array
   *   An array containing possible utterances for all child steps,
   *   formatted for use as a Dynamic Entities directive.
   */
  protected function getAllPossibleUtterances( Node|null $step = null ) : array {
    $utterances = [];
    $possibleSteps = [];

    // Determine possible next steps
    if ( $step !== null ) {
      // If there is a current step, then possible next steps are always contained in the
      // field_wizard_step field. Even if it's empty, which means this is the last step in
      // this tree path.
      $possibleSteps = $step->get('field_wizard_step')->referencedEntities();
    } else {
      // If there is no current step, then we're at the top of the wizard tree. So grab
      // all wizards and generate suggestions from those.
      $possibleSteps = \Drupal::service('alexa_wizard.wizard')->getAllWizards();
    }
    $possibleSteps ??= [];

    // Keeps track of NIDs already added to suggestions. Makes preventing duplicates much easier.
    // There shouldn't be duplicates, but since Drupal doesn't prevent it we should still check.
    $addedIds = [];
    // For all possible steps, if any, generate an array ready to be send as a
    // Dynamic Entities directive (i.e. includes id, name (value, synonyms)).
    foreach ( $possibleSteps as $possibleStep ) {
      $nid = $possibleStep->id();
      if ( !in_array($nid, $addedIds) ) {
        $addedIds[] = $nid;
        $primaryUtterance = $possibleStep->get('field_wizard_primary_utterance')->getString();
        $synonyms = $possibleStep->get('field_wizard_aliases')->getString() ?? '';
        // $synonyms = array_merge($utterances, preg_split('/\s*,\s*/', $synonyms));
        if ( $synonyms !== null && !empty($synonyms) ) {
          $synonyms = preg_split('/\s*,\s*/', $synonyms);
        } else {
          $synonyms = [];
        }

        // Note ID is generated using primary utterance and nid to avoid collisions.
        // Could just generate as nid, uuid, or something else.
        $utterance = [
          'id' => preg_replace('/[ -]/', '_', $primaryUtterance) . '_' . $nid,
          'name' => [
            'value' => $primaryUtterance,
            'synonyms' => $synonyms
          ]
        ];

        $utterances[] = $utterance;
      }
    }

    return $utterances;
  }

  /**
   * Gets the current step Node from the current Alexa Request.
   * 
   * @return Node|null
   *   The Node associated with the current Wizard or Wizard Step,
   *   or null if no wizard has been selected yet.
   */
  public function getCurrentStep() : Node|null {
    if ( !empty($this->request->session->attributes['currentStep']) ) {
      $currentStepId = $this->request->session->attributes['currentStep'];
      // TODO Validate that the ID exists and references a valid step.
      return $this->getStepById( $currentStepId );
    }

    return null;
  }

  /**
   * Gets the next Wizard Step node from a provided choice, taking
   * the current Wizard or Wizard Step into account.
   * 
   * @param string $choice
   *   The utterance text for selecting the next Wizard or Wizard Step
   * @param Node|null $currentStep
   *   The currently selected Wizard or Wizard Step, null if at the
   *   top of the wizard tree.
   * 
   * @return Node|null
   *   The Wizard or Wizard Step that is an immediate child of the currently selected
   *   step and corresponds to the provided choice. null if no valid next step.
   */
  public function findNextStep ( $choice, $currentStep = null ) : Node|null {
    // Options for the next step are only the immediate children of the current step.
    $options = [];
    if ( $currentStep != null ) {
      // If there is a valid current step, grab all Wizard Steps it can move to.
      $options = $currentStep->get('field_wizard_step')->referencedEntities();
    } else {
      // Otherwise, we're currently at the top of the tree. All Wizards are valid options.
      $options = \Drupal::service('alexa_wizard.wizard')->getAllWizards();
    }
    // First, try to match exactly against the primary utterance
    foreach ( $options as $option ) {
      if ( $this->choiceMatchesStep( $choice, $option, 'exact' ) ) {
        return $option;
      }
    }

    // If that fails, try to match against aliases
    foreach ( $options as $option ) {
      if ( $this->choiceMatchesStep( $choice, $option, 'alias' ) ) {
        return $option;
      }
    }

    // If that fails, try to match against phonemes of the primary utterance and aliases.
    foreach ( $options as $option ) {
      if ( $this->choiceMatchesStep( $choice, $option, 'phonemes' ) ) {
        return $option;
      }
    }

    // If all of those fail, there is no valid corresponding next step.
    return null;
  }

  /**
   * Loads a Node for the provided UUID.
   * 
   * @param string|null $uuid
   *   The UUID of the Node to load.
   * 
   * @return Node|null
   */
  public function getStepByUUID( string|null $uuid ) : Node|null {
    // Invalid or empty UUID, returns null
    if ( $uuid == null || empty($uuid) ) {
      return null;
    }
    // TODO what happens here if UUID doesn't exist?
    // Attempt to load a node with the UUID and return it.
    $currentStep = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['uuid' => $uuid]);
    $currentStep = reset($currentStep);
    return $currentStep;
  }

  /**
   * Loads a Node for the provided Node ID.
   * 
   * @param int|null $id
   *   The ID of the Node to load.
   * 
   * @return Node|null
   */
  public function getStepbyId( int|null $id ) : Node|null {
    // Null ID returns null
    if ($id == null) {
      return null;
    }
    // TODO what happens here if ID doesn't exist?
    // Attempt to load a node with the ID and return it.
    return Node::load($id);
  }

  /**
   * Gets a value for a slot from the Alexa Request.
   * 
   * @param string $slotName
   *   The name of the slot to pull a value from.
   * @param bool $ifMatched
   *   Only return the slot value if matched. Otherwise return null.
   * 
   * @return mixed
   *   The value loaded from the slot, or null if the slot isn't found.
   */
  public function getSlotValue( string $slotName, bool $ifMatched = false ) : mixed {
   foreach ( $this->request->request->intent->slots as $slot ) {
      if ( $slot->name == $slotName ) {
        // If we don't care whether it matched or not, return the value right away.
        if ( !$ifMatched ) {
          return $slot->value;
        } else {
          // Otherwise we need to determine whether the slot was a match for a possible
          // slot value before returning it.
          if ( $resolutions = $slot->resolutions ) {
            foreach ( $resolutions as $resolution ) {
              if ( $resolution->status->code === static::SLOT_SUCCESS_MATCH ) {
                return $slot->value;
              }
            }
          }
          // Since we already matched the slot, we can return null instead of finishing the loop.
          return null;
        }
      }
   }
   return null;
  }

  /**
   * Gets the value of the matched slot if there is a slot that
   * Alexa marked as a match.
   * 
   * @return mixed
   *   The value loaded from the matched slot, or null of no slots
   *   matched in the Request.
   */
  public function getSlotValueFromMatch() : mixed {
    foreach ( $this->request->request->intent->slots as $slot ) {
      $resolutions = $slot?->resolutions;
      if ( $resolutions ) {
        foreach ( $resolutions as $resolution ) {
          if ($resolution->status->code === static::SLOT_SUCCESS_MATCH) {
            return $resolution->values[0]->name;
          }
        }
      }
    }
    return null;
  }

  /**
   * Determines whether a phrase matches any provided phrases.
   * 
   * @param string|array $phrase
   *   The phrase or phrases to search for.
   * @param string|array $possibleMatches
   *   The possible matching phrases.
   * @param string $part
   *   The part of the phrase or phrases to try to match.
   * 
   * @return bool
   *   True if $phrase matches any of the $possible matches for the provided part,
   *   otherwise false if no match.
   */
  public function phrasesMatch( string|array $phrase, string|array $possibleMatches, string $part = 'normal' ) : bool {
    // If needed, get the string we're trying to match.
    if ( !empty($part) && is_array($phrase) && !empty($phrase[$part] ) ) {
      $phrase = $phrase[$part];
    }
    // Normalize $phrase to an array
    if ( !is_array($phrase) ) {
      $phrase = [$phrase];
    }
    // Normalize $possibleMatches to an array.
    if ( !is_array($possibleMatches) ) {
      $possibleMatches = [$possibleMatches];
    }
    /// in theory this would be better returning a confidence factor
    /// based on how many of the phrases match how many of the matches
    // check every phrase against every possible match
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

  /**
   * Determine whether a choice matches against one of its possible steps for a provided
   * match type (e.g. exact match, alias match, or phoneme match).
   * 
   * @param string $choice
   *   The choice for the current step
   * @param Node $step
   *   The step to be matched against
   * @param string $matchType
   *   The type of match to be performed
   * 
   * @return bool
   *   True if the choice matches the steps possible phrases for the provided match type,
   *   otherwise false.
   */
  public function choiceMatchesStep( $choice, $step, $matchType = 'exact|alias|phonemes' ) {
    //normalize inputs
    $choice = $this->normalizePhrase( $choice, true );
    $step_utterances = [$this->normalizePhrase( $step->get('field_wizard_primary_utterance')->getString() )];
    
    $step_aliases = preg_split('/\s*,\s*/', $step->get('field_wizard_aliases')->getString());
    $step_aliases = array_map( function($a) {
      return $this->normalizePhrase( $a, true );
    }, $step_aliases);

    // For an exact match, only check step utterances. Currently this is only the primary utterance field.
    if ( preg_match('/\bexact\b/', $matchType) ) {
      if ( $this->phrasesMatch( $choice, $step_utterances ) ) {
        return true;
      }
    }

    // For an alias match, check against the aliases field values.
    if ( preg_match('/\balias\b/', $matchType ) ) {
      if ( !empty($step_aliases) ) {
        if ( $this->phrasesMatch( $choice, $step_aliases ) ) {
          return true;
        }
      }
    }

    // For a phoneme match, check against phonemes of both the utterances and aliases.
    if ( preg_match('/\bphonemes\b/', $matchType ) ) {
      if ( !empty($step_aliases) ) {
        if ( $this->phrasesMatch( $choice, array_merge($step_utterances, $step_aliases), 'phonemes' ) ) {
          return true;
        }
      }
    }

    // TODO
    // next check closest match - calculate levenshtein distance between incoming phrase
    // and each of the aliases/phonemes of the step, and return the closest match
    // need to come up with a good threshold for closeness

    // next send this off to openAI or local llm to check matches?

    return false;
  }

  /**
   * Normalizes a string into three parts. The original string,
   * the "normalized" string - i.e. lowercase, remove non-word characters
   * - and the phonomes.
   * 
   * @param string $phrase
   *   The phrase to normalize.
   * @param bool $withPhonemes
   *   Whether to generate phonemes for the phrase or not.
   * 
   * @return array
   *   An array with three parts: original phrase 'original',
   *   normalized phrase 'normal', and phonemes 'phonemes'.
   */
  public function normalizePhrase( $phrase, $withphonemes = false ) {
    // TODO
    /// should do stemming here too in order to capture broader matches
    /// but we would need to have a separate stemmer per language, and so
    /// far both english and spanish have been supported with the same code
    // Normalize words in the phrase. Converts to lower case, splits on whitespace,
    // and removes all non-word characters.
    $words = preg_replace( '/\W/', '', preg_split('/\s+/', strtolower($phrase)) );
    $result = [
      'original' => $phrase,
      'normal' => implode( ' ', $words ),
      'phonemes' => []
    ];

    if ( $withphonemes ) {
      $primary = [];
      $secondary = [];
      foreach ( $words as $word ) {
        $dm = new \DoubleMetaphone($word);
        $primary[] = $dm->primary;
        $secondary[] = $dm->secondary;
      }
      $result['phonemes'][] = implode( ' ', $primary );
      $result['phonemes'][] = implode( ' ', $secondary );
    }
    return $result;
  }

}
