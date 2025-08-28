
export function useStatus() {

  async function checkOnline() {
    const status = await Status.find(1)
    if (!status.online) {
      Status.update({
        where: 1,
        data: {
          messageText: t('status.Offline'),
          messageClass: 'alert-danger'
        }
      })
      return false
    }
    return true
  }

  return {
    checkOnline
  }
}
