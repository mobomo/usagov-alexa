import { createEntityAdapter, createSlice } from '@reduxjs/toolkit';
import { RootState } from '@/state/store';

export interface WizardStep {
  id: string;
  parentStepId?: string;
  childStepIds?: {
    id: string;
    weight: string;
  }[];
  children?: {
    id: string;
    weight: string;
  }[];
  name?: string;
  title?: string;
  body?: string;
  footerHTML?: string;
  headerHTML?: string;
  metaDescription?: string;
  optionName?: string;
  delete?: boolean;
}

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
    mergeWizardSteps: wizardStepsAdapter.upsertMany,
  },
});

export const { selectAll: selectWizardSteps, selectIds } =
  wizardStepsAdapter.getSelectors<RootState>((state) => state.wizardSteps);

export const {
  addWizardStep,
  addWizardSteps,
  updateWizardStep,
  removeWizardStep,
  mergeWizardSteps,
} = wizardStepsSlice.actions;
export default wizardStepsSlice.reducer;
