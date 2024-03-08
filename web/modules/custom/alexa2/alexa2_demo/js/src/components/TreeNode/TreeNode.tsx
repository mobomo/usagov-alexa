import React, { useState } from 'react';
import { useSelector } from 'react-redux';
import { filter } from 'ramda';

import { WizardStep, selectWizardSteps } from '@/state/wizardStepsSlice';
import Tree from '@/components/Tree';
import EditModal from '@/components/EditModal';
import AddModal from '@/components/AddModal';

import KeyboardArrowUpIcon from '@mui/icons-material/KeyboardArrowUp';
import KeyboardArrowDownIcon from '@mui/icons-material/KeyboardArrowDown';

interface PropTypes {
  wizardStep: WizardStep;
}

const TreeNode: React.FC<PropTypes> = ({ wizardStep }) => {
  const { childStepIds, title } = wizardStep;

  const wizardSteps = useSelector(selectWizardSteps);
  const [showChildren, setShowChildren] = useState(false);
  const [showEditModal, setShowEditModal] = useState(false);
  const [showAddModal, setShowAddModal] = useState(false);

  const childWizardSteps = childStepIds
    ? filter((wizardStep) => childStepIds.includes(wizardStep.id), wizardSteps)
    : [];

  return (
    <>
      <div className="mb-3 flex justify-between">
        <div
          onClick={() => setShowChildren(!showChildren)}
          className="flex-grow"
        >
          <div className="text-xl">{title}</div>

          <div className="mt-2 flex">
            <div
              onClick={(e) => {
                e.stopPropagation();
                setShowEditModal(!showEditModal);
              }}
              className="cursor-pointer rounded text-gray hover:text-black"
            >
              Edit
            </div>
            <div
              onClick={(e) => {
                e.stopPropagation();
                setShowAddModal(!showAddModal);
              }}
              className="ml-2 cursor-pointer rounded text-gray hover:text-black"
            >
              Add Child
            </div>
          </div>
        </div>

        <div
          onClick={() => setShowChildren(!showChildren)}
          className="flex items-center"
        >
          {childWizardSteps.length > 0 ? (
            showChildren ? (
              <KeyboardArrowUpIcon className="ml-4 text-gray" />
            ) : (
              <KeyboardArrowDownIcon className="ml-4 text-xl text-gray" />
            )
          ) : undefined}
        </div>
      </div>

      {showChildren && childWizardSteps.length > 0 && (
        <ul className="ml-0 rounded-md border p-4">
          <Tree wizardSteps={childWizardSteps} />
        </ul>
      )}

      {showEditModal && (
        <EditModal
          wizardStep={wizardStep}
          showEditModal={showEditModal}
          setShowEditModal={setShowEditModal}
        />
      )}
      {showAddModal && (
        <AddModal
          wizardStep={wizardStep}
          showAddModal={showAddModal}
          setShowAddModal={setShowAddModal}
        />
      )}
    </>
  );
};

export default TreeNode;
