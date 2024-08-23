import { wpbbAjax } from '../../../../utils/WpbbAjax'
import { logger } from '../../../../utils/Logger'
import { bracketApi } from '../../../shared/api/bracketApi'

// export const addExistingPlayToApparelHandler = async (
//   playId: number,
//   setProcessing: (processing: boolean) => void,
//   onError: (err: any) => void
// ) => {
export const addExistingPlayToApparelHandler = async ({
  playId,
  setProcessing,
  onError,
}: {
  playId: number
  setProcessing?: (processing: boolean) => void
  onError?: (err: any) => void
}) => {
  const redirectUrl = wpbbAjax.getAppObj().bracketProductArchiveUrl
  setProcessing?.(true)
  await bracketApi
    .generatePlayImages(playId)
    .then((res) => {
      window.location.href = redirectUrl
    })
    .catch((err) => {
      console.error('error: ', err)
      logger.error(err)
      setProcessing?.(false)
      onError?.(err)
    })
}
