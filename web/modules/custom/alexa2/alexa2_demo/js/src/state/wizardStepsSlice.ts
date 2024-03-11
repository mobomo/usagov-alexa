import { createEntityAdapter, createSlice } from '@reduxjs/toolkit';
import { RootState } from '@/state/store';

export interface WizardStep {
  id: string;
  parentStepId?: string;
  childStepIds?: string[];
  name?: string;
  title?: string;
  body?: string;
  primaryUtterance?: string;
  aliases?: string;
  delete?: boolean;
}

export const initialState = {
  entities: {
    '2': {
      id: '2',
      childStepIds: ['3', '6', '7', '8'],
      name: 'scams_wizard',
      title: 'Scams Wizard',
      body: "<p><prosody rate='medium'>Welcome to the <prosody rate='x-slow'>USA gov</prosody> scams wizard!<amazon:emotion name='disappointed' intensity='high'> I'm sorry that you are experiencing a scam. I can help you learn where to report it!</amazon:emotion> Please tell me the type of scam you want to report.</prosody></p>",
      primaryUtterance: 'scam',
      aliases: 'scams',
    },
    '3': {
      id: '3',
      parentStepId: '2',
      childStepIds: ['4'],
      name: 'financial',
      title: 'Financial',
      body: "<p><prosody rate='medium'>A scam that affects your financial accounts, such as savings, checking, or loans, is a financial scam. Also, any scam that involves investment accounts, credit card, or mortgage accounts is a financial scam.</prosody></p>",
      primaryUtterance: 'financial',
      aliases: 'bank, banking',
    },
    '4': {
      id: '4',
      parentStepId: '3',
      childStepIds: [],
      name: 'fake_check',
      title: 'Fake Check',
      body: "<p>A Fake Check Scam. Here's what to do about a fake check scam...</p>",
      primaryUtterance: 'fake check',
      aliases: '',
    },
    '6': {
      id: '6',
      parentStepId: '2',
      childStepIds: [],
      name: 'imposter',
      title: 'Imposter',
      body: '<p>An imposter scam involves some sort of trickery where a person is trying to make you think they are from a trusted entity, such as the government, or a known company. An imposter scam can also involve someone pretending to be a love interest, or a family member.</p>',
      primaryUtterance: 'imposter',
      aliases: '',
    },
    '7': {
      id: '7',
      parentStepId: '2',
      childStepIds: [],
      name: 'moving',
      title: 'Moving',
      body: "<p><prosody rate='medium'>Some moving companies use fraudulent practices such as demanding cash up front, or requiring you to pay additional money before they will return your belongings.</prosody></p>",
      primaryUtterance: 'moving',
      aliases: '',
    },
    '8': {
      id: '8',
      parentStepId: '2',
      childStepIds: [],
      name: 'fraud',
      title: 'Fraud',
      body: "<p><prosody rate='medium'>Fraud scams occur when someone steals personal information from you, then uses that information to pretend they are you<break time='1ms'/> in order to gain access to services such as unemployment, or social security.</prosody></p>",
      primaryUtterance: 'fraud',
      aliases: '',
    },
  },
  ids: ['2', '3', '4', '6', '7', '8'],
};

const wizardStepsAdapter = createEntityAdapter({
  selectId: (wizardStep: WizardStep) => wizardStep.id,
});

const wizardStepsSlice = createSlice({
  name: 'wizardSteps',
  initialState: wizardStepsAdapter.getInitialState(),
  reducers: {
    addWizardStep: wizardStepsAdapter.addOne,
    addWizardSteps: wizardStepsAdapter.addMany,
    updateWizardStep: wizardStepsAdapter.upsertOne,
    removeWizardStep: wizardStepsAdapter.removeOne,
  },
});

export const { selectAll: selectWizardSteps } =
  wizardStepsAdapter.getSelectors<RootState>((state) => state.wizardSteps);

export const {
  addWizardStep,
  addWizardSteps,
  updateWizardStep,
  removeWizardStep,
} = wizardStepsSlice.actions;
export default wizardStepsSlice.reducer;
