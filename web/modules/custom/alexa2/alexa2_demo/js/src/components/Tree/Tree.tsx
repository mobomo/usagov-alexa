import React from 'react';
import { map } from 'ramda';
import { WizardStep } from '@/state/wizardStepsSlice';
import TreeNode from '@/components/TreeNode';

interface PropTypes {
  wizardSteps: WizardStep[];
}

const Tree: React.FC<PropTypes> = ({ wizardSteps }) => {
  return (
    <ul>
      {map(
        (wizardStep) => (
          <TreeNode wizardStep={wizardStep} key={wizardStep.id} />
        ),
        wizardSteps,
      )}
    </ul>
  );
};

export default Tree;
