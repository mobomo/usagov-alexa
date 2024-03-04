import { combineReducers, configureStore } from '@reduxjs/toolkit';
import reducer from './wizardStepsSlice';

const rootReducer = combineReducers({
  wizardSteps: reducer,
});

export const store = configureStore({
  reducer: rootReducer,
});

export type RootState = ReturnType<typeof store.getState>;
