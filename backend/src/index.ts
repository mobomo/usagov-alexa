import {
  ErrorHandler,
  HandlerInput,
  RequestHandler,
  Skill,
  SkillBuilders,
} from 'ask-sdk-core';
import {
  RequestEnvelope,
  Response,
  SessionEndedRequest,
} from 'ask-sdk-model';
import express from 'express';
import { ExpressAdapter } from 'ask-sdk-express-adapter';




import data from "./data.json";




interface DataType {
  [key: string]: BankingScamTypesL2;
}




interface BankingScamTypesL2 {
  level1: string,
  result: string
}




interface CustomHandlerInput {
  requestEnvelope: {
    request?: any
  };
}




const parsedData: DataType = data;




const LaunchRequestHandler: RequestHandler = {
  canHandle(handlerInput: HandlerInput): boolean {
    const request = handlerInput.requestEnvelope.request;
    return request.type === 'LaunchRequest';
  },
  handle(handlerInput: HandlerInput): Response {
    const speechText = 'Welcome to your SDK weather skill. Ask me the weather!';




    return handlerInput.responseBuilder
      .speak(speechText)
      .reprompt(speechText)
      .withSimpleCard('Welcome to your SDK weather skill. Ask me the weather!', speechText)
      .getResponse();
  },
};




const GetRecommendationAPIHandler: RequestHandler = {
  canHandle(handlerInput: HandlerInput): boolean {
    const request: any = handlerInput.requestEnvelope.request;
    return request.type === 'Dialog.API.Invoked'
      && request.apiRequest.name === 'getRecommendation';
  },
  handle(handlerInput: CustomHandlerInput): Response {
    const apiRequest: {
      [x: string]: any; apiRequest?: any
    } = handlerInput.requestEnvelope.request.apiRequest;




    let Fruit: string = resolveEntity(apiRequest.slots, "Fruit");


    const recommendationEntity: {
      name: string,
      Fruit: string
    } = {
      name: "",
      Fruit: ""
    };




    if (Fruit !== null) {
      const key = `banking-${Fruit}`;
      const databaseResponse = parsedData[key];




      console.log("Response from mock database ", databaseResponse);




      recommendationEntity.name = databaseResponse.result;
      recommendationEntity.Fruit = Fruit;
    }




    const response = buildSuccessApiResponse(recommendationEntity);
    return response;
  },
};




const buildSuccessApiResponse = (returnEntity: { name: string; Fruit: string; }) => {
  return { apiResponse: returnEntity };
};




const resolveEntity = function (resolvedEntity: { [x: string]: { resolutions: { resolutionsPerAuthority: any[]; }; }; }, slot: string) {
  //This is built in functionality with SDK Using Alexa's ER
  let erAuthorityResolution = resolvedEntity[slot].resolutions.resolutionsPerAuthority[0];
  let value = null;




  if (erAuthorityResolution.status.code === "ER_SUCCESS_MATCH") {
    value = erAuthorityResolution.values[0].value.name;
  }




  return value;
};




const HelpIntentHandler: RequestHandler = {
  canHandle(handlerInput: HandlerInput): boolean {
    const request = handlerInput.requestEnvelope.request;
    return request.type === 'IntentRequest'
      && request.intent.name === 'AMAZON.HelpIntent';
  },
  handle(handlerInput: HandlerInput): Response {
    const speechText = 'You can ask me the weather!';




    return handlerInput.responseBuilder
      .speak(speechText)
      .reprompt(speechText)
      .withSimpleCard('You can ask me the weather!', speechText)
      .getResponse();
  },
};




const CancelAndStopIntentHandler: RequestHandler = {
  canHandle(handlerInput: HandlerInput): boolean {
    const request = handlerInput.requestEnvelope.request;
    return request.type === 'IntentRequest'
      && (request.intent.name === 'AMAZON.CancelIntent'
        || request.intent.name === 'AMAZON.StopIntent');
  },
  handle(handlerInput: HandlerInput): Response {
    const speechText = 'Goodbye!';




    return handlerInput.responseBuilder
      .speak(speechText)
      .withSimpleCard('Goodbye!', speechText)
      .withShouldEndSession(true)
      .getResponse();
  },
};




const SessionEndedRequestHandler: RequestHandler = {
  canHandle(handlerInput: HandlerInput): boolean {
    const request = handlerInput.requestEnvelope.request;
    return request.type === 'SessionEndedRequest';
  },
  handle(handlerInput: HandlerInput): Response {
    console.log(`Session ended with reason: ${(handlerInput.requestEnvelope.request as SessionEndedRequest).reason}`);




    return handlerInput.responseBuilder.getResponse();
  },
};




const ErrorHandler: ErrorHandler = {
  canHandle(handlerInput: HandlerInput, error: Error): boolean {
    return true;
  },
  handle(handlerInput: HandlerInput, error: Error): Response {
    console.log(`Error handled: ${error.message}`);




    return handlerInput.responseBuilder
      .speak('Sorry, I don\'t understand your command. Please say it again.')
      .reprompt('Sorry, I don\'t understand your command. Please say it again.')
      .getResponse();
  }
};




let skill: Skill;




exports.handler = async (event: RequestEnvelope, context: unknown) => {
  console.log(`REQUEST++++${JSON.stringify(event)}`);
  if (!skill) {
    skill = SkillBuilders.custom()
      .addRequestHandlers(
        LaunchRequestHandler,
        GetRecommendationAPIHandler,
        HelpIntentHandler,
        CancelAndStopIntentHandler,
        SessionEndedRequestHandler,
      )
      .addErrorHandlers(ErrorHandler)
      .create();
  }




  const response = await skill.invoke(event, context);
  console.log(`RESPONSE++++${JSON.stringify(response)}`);




  return response;
};




const app = express();
skill = SkillBuilders.custom()
  .addRequestHandlers(
    LaunchRequestHandler,
    GetRecommendationAPIHandler,
    HelpIntentHandler,
    CancelAndStopIntentHandler,
    SessionEndedRequestHandler,
  )
  .addErrorHandlers(ErrorHandler)
  .create();
const adapter = new ExpressAdapter(skill, true, true);




app.post('/', adapter.getRequestHandlers());
app.listen(process.env.PORT || 3000);






