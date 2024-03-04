import React, { useState } from 'react';
import { useSelector } from 'react-redux';
import { filter } from 'ramda';
import EditIcon from '@mui/icons-material/Edit';

import { WizardStep, selectWizardSteps } from '@/state/wizardStepsSlice';
import Tree from '@/components/Tree';
import EditModal from '@/components/EditModal';

interface PropTypes {
  wizardStep: WizardStep;
}

const TreeNode: React.FC<PropTypes> = ({ wizardStep }) => {
  const { childStepIds, title, body } = wizardStep;

  const wizardSteps = useSelector(selectWizardSteps);
  const [showChildren, setShowChildren] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);

  const childWizardSteps = childStepIds
    ? filter((wizardStep) => childStepIds.includes(wizardStep.id), wizardSteps)
    : [];

  return (
    <>
      <div className="mb-3 flex justify-between">
        <div onClick={() => setShowChildren(!showChildren)}>
          <div className="text-xl">{title}</div>
          <div className="text-sm text-gray">{body}</div>
        </div>
        <div onClick={() => setShowEditModal(!showEditModal)} className="flex items-center">
          <EditIcon className="text-gray ml-4" />
        </div>
      </div>
      <ul className="border-l pl-3">
        {showChildren && <Tree wizardSteps={childWizardSteps} />}
      </ul>
      {showEditModal && <EditModal wizardStep={wizardStep} showEditModal={showEditModal} setShowEditModal={setShowEditModal} />}
    </>
  );
};

export default TreeNode;
