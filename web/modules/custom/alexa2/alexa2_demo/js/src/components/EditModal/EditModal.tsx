import React, { useCallback, useState } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { useForm, SubmitHandler } from 'react-hook-form';

import {
  WizardStep,
  selectWizardSteps,
  updateWizardStep,
} from '@/state/wizardStepsSlice';
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
  primaryUtterance: string;
  aliases: string;
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
  const { register, handleSubmit } = useForm<FormData>();

  const handleSubmitClick: SubmitHandler<FormData> = useCallback(
    async (data) => {
      await updateWizardTree({ wizardSteps: wizardSteps });

      dispatch(
        updateWizardStep({
          ...wizardStep,
          name: data.name ? data.name : wizardStep.name,
          title: data.title ? data.title : wizardStep.title,
          body: data.body ? data.body : wizardStep.body,
          primaryUtterance: data.primaryUtterance
            ? data.primaryUtterance
            : wizardStep.primaryUtterance,
          aliases: data.aliases ? data.aliases : wizardStep.aliases,
        }),
      );

      setShowEditModal(!showEditModal);
    },
    [dispatch],
  );

  const handleDeleteClick = useCallback(() => {
    setShowDeleteModal(!showDeleteModal);
  }, []);

  const handleDeleteSubmitClick = useCallback(() => {
    dispatch(
      updateWizardStep({
        ...wizardStep,
        delete: true,
      }),
    );
  }, [dispatch]);

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
            placeholder={wizardStep.title}
            className="mb-4 h-12 w-[640px] rounded-md border p-2 text-lg"
          />

          <label>Body</label>
          <input
            {...register('body')}
            placeholder={wizardStep.body}
            className="mb-4 h-12 w-[640px] rounded-md border p-2 text-lg"
          />

          <label>Primary Utterance</label>
          <input
            {...register('primaryUtterance')}
            placeholder={wizardStep.primaryUtterance}
            className="mb-4 h-12 w-[640px] rounded-md border p-2 text-lg"
          />

          <label>Aliases</label>
          <input
            {...register('aliases')}
            placeholder={wizardStep.aliases}
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
