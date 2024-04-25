import React, { useCallback, useState } from 'react';
import { filter } from 'ramda';
import { useSelector, useDispatch } from 'react-redux';
import { useForm, SubmitHandler } from 'react-hook-form';

import { WizardStep, selectWizardSteps } from '@/state/wizardStepsSlice';
import useUpdateWizardTree from '@/hooks/updateWizardTree';
import ConfirmDeleteModal from '@/components/ConfirmDeleteModal';

interface PropTypes {
  wizardStep: WizardStep;
  showEditModal: boolean;
  setShowEditModal: (value: React.SetStateAction<boolean>) => void;
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

const EditModal: React.FC<PropTypes> = ({
  wizardStep,
  showEditModal,
  setShowEditModal,
}) => {
  const dispatch = useDispatch();
  const [updateWizardTree] = useUpdateWizardTree();

  const [showDeleteModal, setShowDeleteModal] = useState(false);
  const wizardSteps = useSelector(selectWizardSteps);

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
            ...wizardStep,
            name: data.name ? data.name : wizardStep.name,
            title: data.title ? data.title : wizardStep.title,
            body: data.body ? data.body : wizardStep.body,
            footerHTML: data.footerHTML ? data.footerHTML : wizardStep.footerHTML,
            headerHTML: data.headerHTML ? data.headerHTML : wizardStep.headerHTML,
            metaDescription: data.metaDescription ? data.metaDescription : wizardStep.metaDescription,
            optionName: data.optionName ? data.optionName : wizardStep.optionName,
          },
        ],
        ids: [wizardStep.id],
        rootStepId: rootWizardSteps[0].id,
      });

      setShowEditModal(!showEditModal);
    },
    [dispatch],
  );

  const handleDeleteClick = useCallback(() => {
    setShowDeleteModal(!showDeleteModal);
  }, []);

  const handleDeleteSubmitClick = useCallback(async () => {
    await updateWizardTree({
      entities: [
        {
          ...wizardStep,
          delete: true,
        },
      ],
      ids: [wizardStep.id],
      rootStepId: rootWizardSteps[0].id,
    });
  }, []);

  return (
    <div
      onClick={() => setShowEditModal(!showEditModal)}
      className="fixed left-0 top-0 flex h-screen w-full items-center  justify-center bg-black bg-opacity-30"
    >
      <div
        onClick={(e) => e.stopPropagation()}
        className="rounded-xl border bg-white p-8"
      >
        <h1 className="mb-4 text-xl">{wizardStep.title}</h1>

        <form
          onSubmit={handleSubmit(handleSubmitClick)}
          className="flex flex-col"
        >
          <label>Title</label>
          <input
            {...register('title')}
            defaultValue={wizardStep.title}
            className="mb-4 h-12 w-[640px] rounded-md border p-2 text-lg"
          />

          <label>Body</label>
          <input
            {...register('body')}
            defaultValue={wizardStep.body}
            className="mb-4 h-12 w-[640px] rounded-md border p-2 text-lg"
          />

          <label>Footer HTML</label>
          <input
            {...register('footerHTML')}
            defaultValue={wizardStep.footerHTML}
            className="mb-4 h-12 w-[640px] rounded-md border p-2 text-lg"
          />

          <label>Header HTML</label>
          <input
            {...register('headerHTML')}
            defaultValue={wizardStep.headerHTML}
            className="mb-4 h-12 w-[640px] rounded-md border p-2 text-lg"
          />

          <label>Meta Description</label>
          <input
            {...register('metaDescription')}
            defaultValue={wizardStep.metaDescription}
            className="mb-4 h-12 w-[640px] rounded-md border p-2 text-lg"
          />

          <label>Option Name</label>
          <input
            {...register('optionName')}
            defaultValue={wizardStep.optionName}
            className="mb-4 h-12 w-[640px] rounded-md border p-2 text-lg"
          />

          <div className="flex justify-evenly">
            <button
              onClick={() => setShowEditModal(!showEditModal)}
              className="rounded-md border p-4 hover:bg-gray-light"
            >
              Cancel
            </button>

            <button
              onClick={(e) => {
                e.preventDefault();
                e.stopPropagation();
                handleDeleteClick();
              }}
              className="rounded-md border p-4 hover:bg-gray-light"
            >
              Delete
            </button>

            <input
              type="submit"
              className="rounded-md border p-4 hover:bg-gray-light"
            />
          </div>
        </form>
      </div>

      {showDeleteModal && (
        <ConfirmDeleteModal
          handleDeleteSubmitClick={handleDeleteSubmitClick}
          showDeleteModal={showDeleteModal}
          setShowDeleteModal={setShowDeleteModal}
        />
      )}
    </div>
  );
};

export default EditModal;
