import React from 'react';

interface PropTypes {
  handleDeleteSubmitClick: () => void;
  showDeleteModal: boolean;
  setShowDeleteModal: (value: React.SetStateAction<boolean>) => void;
}

const ConfirmDeleteModal: React.FC<PropTypes> = ({
  handleDeleteSubmitClick,
  showDeleteModal,
  setShowDeleteModal,
}) => {
  return (
    <div
      onClick={() => setShowDeleteModal(!showDeleteModal)}
      className="fixed left-0 top-0 flex h-screen w-full items-center  justify-center bg-black bg-opacity-30"
    >
      <div
        onClick={(e) => e.stopPropagation()}
        className="rounded-xl border bg-white p-8"
      >
        <h1 className="mb-4 mt-0 text-xl">Are you sure?</h1>

        <div className="flex justify-evenly">
          <button
            onClick={() => setShowDeleteModal(!showDeleteModal)}
            className="rounded-md border p-4 hover:bg-gray-light"
          >
            Cancel
          </button>
          <button
            onClick={() => handleDeleteSubmitClick()}
            className="ml-4 rounded-md border p-4 hover:bg-gray-light"
          >
            Delete
          </button>
        </div>
      </div>
    </div>
  );
};

export default ConfirmDeleteModal;
