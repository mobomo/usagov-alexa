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
