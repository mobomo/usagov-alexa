import { useEffect, useCallback } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { map, filter } from 'ramda';

import { addWizardSteps, selectWizardSteps } from '@/state/wizardStepsSlice';
import Tree from '@/components/Tree';
import useUpdateWizardTree from '@/hooks/updateWizardTree';

declare global {
  interface Window {
    drupalSettings: any;
  }
}

function App() {
  const dispatch = useDispatch();
  const [updateWizardTree] = useUpdateWizardTree();

  const wizardTree = window.drupalSettings.wizardTree.entities;

  useEffect(() => {
    const formattedWizardTree = map((wizardStep: any) => {
      return {
        childStepIds: wizardStep.children,
        ...wizardStep,
      };
    }, wizardTree);

    dispatch(addWizardSteps(formattedWizardTree));
  }, [dispatch, wizardTree]);

  const wizardSteps = useSelector(selectWizardSteps);

  const rootWizardSteps = filter(
    (wizardStep) => !wizardStep.parentStepId,
    wizardSteps,
  );

  const handleSubmitClick = useCallback(async () => {
    await updateWizardTree({ wizardSteps: wizardSteps });
  }, [updateWizardTree, wizardSteps]);

  return (
    <div className="container mx-auto px-4 pt-8 font-sans">
      <div className="rounded-xl border p-8">
        <Tree wizardSteps={rootWizardSteps} />
      </div>
      <div className="mt-3 flex justify-center">
        <button
          onClick={() => console.log('h')}
          className="mr-3 rounded-md border p-4 hover:bg-gray-light"
        >
          Cancel
        </button>
        <button
          onClick={handleSubmitClick}
          className="rounded-md border p-4 hover:bg-gray-light"
        >
          Submit
        </button>
      </div>
    </div>
  );
}

export default App;
