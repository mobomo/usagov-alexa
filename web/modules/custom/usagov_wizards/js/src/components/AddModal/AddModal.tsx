import React, { useCallback } from 'react';
import { filter } from 'ramda';
import { useSelector, useDispatch } from 'react-redux';
import { useForm, SubmitHandler } from 'react-hook-form';

import {
  WizardStep,
  selectWizardSteps,
  selectIds,
} from '@/state/wizardStepsSlice';
import useUpdateWizardTree from '@/hooks/updateWizardTree';

interface PropTypes {
  wizardStep: WizardStep;
  showAddModal: boolean;
  setShowAddModal: (value: React.SetStateAction<boolean>) => void;
}

type FormData = {
  name: string;
  title: string;
  body: string;
  footerHTML: string;
  headerHTML: string;
  metaDescription: string;
  optionName: string;
};

const AddModal: React.FC<PropTypes> = ({
  wizardStep,
  showAddModal,
  setShowAddModal,
}) => {
  const dispatch = useDispatch();
  const [updateWizardTree] = useUpdateWizardTree();

  const wizardSteps = useSelector(selectWizardSteps);
  const wizardStepIds = useSelector(selectIds);

  const rootWizardSteps = filter(
    (wizardStep) => !wizardStep.parentStepId,
    wizardSteps,
  );

  const { register, handleSubmit } = useForm<FormData>();

  const handleSubmitClick: SubmitHandler<FormData> = useCallback(
    async (data) => {
      await updateWizardTree({
        entities: [
          {
            id: '-1',
            parentStepId: wizardStep.id,
            ...data,
          },
          ...wizardSteps,
        ],
        ids: ['-1', ...wizardStepIds],
        rootStepId: rootWizardSteps[0].id,
      });

      setShowAddModal(!showAddModal);
    },
    [dispatch, wizardSteps],
  );

  return (
    <div
      onClick={() => setShowAddModal(!showAddModal)}
      className="fixed left-0 top-0 flex h-screen w-full items-center  justify-center bg-black bg-opacity-30"
    >
      <div
        onClick={(e) => e.stopPropagation()}
        className="rounded-xl border bg-white p-8"
      >
        <h1 className="mb-4 text-xl">New Wizard Step</h1>

        <form
          onSubmit={handleSubmit(handleSubmitClick)}
          className="flex flex-col"
        >
          <label>Title</label>
          <input
            {...register('title')}
            className="mb-4 h-12 w-[640px] rounded-md border p-2 text-lg"
          />

          <label>Body</label>
          <input
            {...register('body')}
            className="mb-4 h-12 w-[640px] rounded-md border p-2 text-lg"
          />

          <label>Footer HTML</label>
          <input
            {...register('footerHTML')}
            className="mb-4 h-12 w-[640px] rounded-md border p-2 text-lg"
          />

          <label>Header HTML</label>
          <input
            {...register('headerHTML')}
            className="mb-4 h-12 w-[640px] rounded-md border p-2 text-lg"
          />

          <label>Meta Description</label>
          <input
            {...register('metaDescription')}
            className="mb-4 h-12 w-[640px] rounded-md border p-2 text-lg"
          />

          <label>Option Name</label>
          <input
            {...register('optionName')}
            className="mb-4 h-12 w-[640px] rounded-md border p-2 text-lg"
          />

          <div className="flex justify-evenly">
            <button
              onClick={() => setShowAddModal(!showAddModal)}
              className="rounded-md border p-4 hover:bg-gray-light"
            >
              Cancel
            </button>
            <input
              type="submit"
              className="rounded-md border p-4 hover:bg-gray-light"
            />
          </div>
        </form>
      </div>
    </div>
  );
};

export default AddModal;
