import { useEffect } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { map, filter } from 'ramda';
import { addWizardSteps, selectWizardSteps } from './state/wizardStepsSlice';
import Tree from './components/Tree';

declare global {
  interface Window { drupalSettings: any }
}

function App() {
  const dispatch = useDispatch();

  useEffect(() => {
    const wizardTree = window.drupalSettings.wizardTree.entities;

    const formattedWizardTree = map((wizardStep: any) => {
      return {
        childStepIds: wizardStep.children,
        ...wizardStep,
      };
    }, wizardTree);

    dispatch(addWizardSteps(formattedWizardTree));
  }, [dispatch]);


  const wizardSteps = useSelector(selectWizardSteps);

  const rootWizardSteps = filter(
    (wizardStep) => !wizardStep.parentStepId,
    wizardSteps,
  );

  return (
    <div className="container mx-auto px-4 pt-8 font-sans">
      <div className="rounded-xl border p-8">
        <Tree wizardSteps={rootWizardSteps} />
      </div>
    </div>
  );
}

export default App;
