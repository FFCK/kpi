export const useStatus = () => {
  const checkOnline = () => {
    return navigator.onLine;
  };

  return { checkOnline };
};