import { useState, useCallback } from 'react';
import { values, map } from 'ramda';
import { useDispatch } from 'react-redux';
import { WizardStep, mergeWizardSteps } from '@/state/wizardStepsSlice';

const PATH = window.drupalSettings.wizardUpdateUrl;

interface UpdateWizardTreeParams {
  entities: WizardStep[];
  ids: string[];
  rootStepId: string;
}

interface UpdateWizardTreeResult {
  entities: WizardStep[];
}

export class UpdateWizardTreeError extends Error {}

type UpdateWizardTreeHook = () => [
  (params: UpdateWizardTreeParams) => Promise<UpdateWizardTreeResult>,
  boolean,
];

const useUpdateWizardTree: UpdateWizardTreeHook = () => {
  const [error, setError] = useState<boolean>(false);
  const dispatch = useDispatch();

  const updateWizardTree = useCallback(
    async (params: UpdateWizardTreeParams) => {
      setError(false);

      const response = await fetch(PATH, {
        method: 'POST',
        body: JSON.stringify({
          entities: params.entities,
          ids: params.ids,
          rootStepId: params.rootStepId,
        }),
      });

      if (!response.ok) {
        setError(true);
        return Promise.reject(new UpdateWizardTreeError());
      }

      const result = (await response.json()) as UpdateWizardTreeResult;

      const formattedWizardTree = map((wizardStep: any) => {
        return {
          childStepIds: wizardStep.children,
          ...wizardStep,
        };
      }, result.entities);
      const formattedArray = values(formattedWizardTree);

      dispatch(mergeWizardSteps(formattedArray));

      return result;
    },
    [dispatch],
  );

  return [updateWizardTree, error];
};

export default useUpdateWizardTree;
