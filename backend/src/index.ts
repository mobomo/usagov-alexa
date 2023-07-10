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
import { util } from "./util";


import FLIGHT_DATA from "./flight-data.json";

interface FLIGHT_DATAType {
  [key: string]: {
    [key: string]: {
      "cost": string,
      "time": string,
      "airline": string
    }
  }
}

const parsedFLIGHT_DATA: FLIGHT_DATAType = FLIGHT_DATA;
const getFlightData = (cityFrom: string, cityTo: string) => {
  if (cityFrom && parsedFLIGHT_DATA[cityFrom] && cityTo && parsedFLIGHT_DATA[cityFrom][cityTo]) {
    return parsedFLIGHT_DATA[cityFrom][cityTo];
  }
  return {
    cost: "",
    time: "",
    airline: "",
  };
};

/* *
 * FlightFinderHandler searches for the flight for given departure and arrival city and returns the response to the skill.
 * This handler will be triggered when three slots are collected: arrivalCity, departureCity and date
 * Response contains the json which maps FlightDetails type in ACDL and display for APL template
 * */
const FlightFinderHandler = {
  canHandle(handlerInput: HandlerInput) {
    return util.isApiRequest(handlerInput, "com.flightsearch.FlightFinder"); //this needs to be your namespace and api name
  },
  handle(handlerInput: HandlerInput) {
    console.log(`flight finder handler: ~~~ ${JSON.stringify(handlerInput)}`);

    const departure = util.getApiSlotBestValue(handlerInput, "departureCity"); //name of the U.S. city given in the api for departureCity slot (API definition)
    const arrival = util.getApiSlotBestValue(handlerInput, "arrivalCity"); //name of the U.S. city given in the api for arrivalCity slot (API definition)
    const date = util.getApiSlotBestValue(handlerInput, "date"); //date in the api for date slot (API definition)
    const flightData = getFlightData(departure, arrival);

    // response maps to FlightDetails type in ACDL
    // arrivalCity, departureCity, date, time, cost and airline
    // display is used in APL template
    const response = {
      arrivalCity: arrival,
      departureCity: departure,
      date: date,
      time: flightData.time,
      cost: flightData.cost,
      airline: flightData.airline,
    };
    console.log("response: ", response);

    return handlerInput.responseBuilder
      .withApiResponse(response)
      .withShouldEndSession(false) // Setting this to false keeps the mic on after Alexa responds
      .getResponse();
  },
};

// const parsedData: DataType = data;


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

/* *
 * SessionEndedRequest notifies that a session was ended. This handler will be triggered when a currently open
 * session is closed for one of the following reasons: 1) The user says "exit" or "quit". 2) The user does not
 * respond or says something that does not match an intent defined in your voice model. 3) An error occurs
 * */
const SessionEndedRequestHandler = {
  canHandle(handlerInput: any) {
    return handlerInput.getRequestType(handlerInput.requestEnvelope) === "SessionEndedRequest";
  },
  handle(handlerInput: any) {
    console.log(`~~~~ Session ended: ${JSON.stringify(handlerInput.requestEnvelope)}`);

    // Any cleanup logic goes here.
    return handlerInput.responseBuilder.getResponse(); // notice we send an empty response
  },
};

/**
 * Generic error handling to capture any syntax or routing errors. If you receive an error
 * stating the request handler chain is not found, you have not implemented a handler for
 * the intent being invoked or included it in the skill builder below
 * */
const ErrorHandler: ErrorHandler = {
  canHandle() {
    return true;
  },
  handle(handlerInput: HandlerInput, error) {
    const speakOutput = "Sorry, I had trouble doing what you asked. Please try again.";
    console.log(`~~~~ Error handled: ${JSON.stringify(error)}`);

    return handlerInput.responseBuilder.speak(speakOutput).reprompt(speakOutput).getResponse();
  },
};


let skill: Skill;


exports.handler = async (event: RequestEnvelope, context: unknown) => {
  console.log(`REQUEST++++${JSON.stringify(event)}`);
  if (!skill) {
    skill = SkillBuilders.custom()
      .addRequestHandlers(FlightFinderHandler, SessionEndedRequestHandler)
      .addErrorHandlers(ErrorHandler)
      .withCustomUserAgent("sample/hello-world/v1.2")
      .create();
  }


  const response = await skill.invoke(event, context);
  console.log(`RESPONSE++++${JSON.stringify(response)}`);


  return response;
};


const app = express();
skill = SkillBuilders.custom()
  .addRequestHandlers(FlightFinderHandler, SessionEndedRequestHandler)
  .addErrorHandlers(ErrorHandler)
  .withCustomUserAgent("sample/hello-world/v1.2")
  .create();
const adapter = new ExpressAdapter(skill, true, true);


app.post('/', adapter.getRequestHandlers());
app.listen(process.env.PORT || 3000);

