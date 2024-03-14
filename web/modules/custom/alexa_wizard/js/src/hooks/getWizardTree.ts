import { useState, useCallback } from 'react';
import { useDispatch } from 'react-redux';

const PATH = window.drupalSettings.wizardUpdateUrl;

interface GetWizardTreeParams {
  rootStepId: string;
}

export class GetWizardTreeError extends Error {}

type GetWizardTreeHook = () => [
  (params: GetWizardTreeParams) => Promise<void>,
  boolean,
];

const useGetWizardTree: GetWizardTreeHook = () => {
  const [error, setError] = useState<boolean>(false);
  const dispatch = useDispatch();

  const getWizardTree = useCallback(
    async (params: GetWizardTreeParams) => {
      setError(false);

      const response = await fetch(PATH, {
        method: 'POST',
        body: JSON.stringify({
          rootStepId: params.rootStepId,
        }),
      });

      if (!response.ok) {
        setError(true);
        return Promise.reject(new GetWizardTreeError());
      }

      const result = await response.json();
      return result;
    },
    [dispatch],
  );

  return [getWizardTree, error];
};

export default useGetWizardTree;
