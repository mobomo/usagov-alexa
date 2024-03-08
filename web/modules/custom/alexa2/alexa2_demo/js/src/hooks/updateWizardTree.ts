import { useState, useCallback } from 'react';
import { useDispatch } from 'react-redux';
import { WizardStep } from '@/state/wizardStepsSlice';

const PATH = window.drupalSettings.wizardUpdateUrl;

interface UpdateWizardTreeParams {
  wizardSteps: WizardStep[];
}

export class UpdateWizardTreeError extends Error {}

type UpdateWizardTreeHook = () => [
  (params: UpdateWizardTreeParams) => Promise<void>,
  boolean,
];

const useUpdateWizardTree: UpdateWizardTreeHook = () => {
  const [error, setError] = useState<boolean>(false);
  const dispatch = useDispatch();

  const updateWizardTree = useCallback(
    async (params: UpdateWizardTreeParams) => {
      setError(false);

      const unflattenWizardTree = (
        wizardSteps: WizardStep[],
        parentId: string | null = null,
      ) => {
        const unflattenedWizardTree: any = [];

        wizardSteps.forEach((wizardStep) => {
          if (wizardStep.parentStepId === parentId) {
            const newNode = {
              ...wizardStep,
              children: unflattenWizardTree(wizardSteps, wizardStep.id),
            };

            unflattenedWizardTree.push(newNode);
          }
        });

        return unflattenedWizardTree.length ? unflattenedWizardTree : null;
      };

      const response = await fetch(PATH, {
        method: 'POST',
        body: JSON.stringify(
          // TODO modify function so as not to require [0] here
          unflattenWizardTree(params.wizardSteps)[0],
        ),
      });

      if (!response.ok) {
        setError(true);
        return Promise.reject(new UpdateWizardTreeError());
      }

      const result = await response.json();
      return result;
    },
    [dispatch],
  );

  return [updateWizardTree, error];
};

export default useUpdateWizardTree;
