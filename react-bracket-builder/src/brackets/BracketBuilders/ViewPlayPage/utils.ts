import { wpbbAjax } from '../../../utils/WpbbAjax'
import { bracketApi } from '../../shared/api/bracketApi'

export const addToApparelHandler = async (playId: number) => {
  const redirectUrl = wpbbAjax.getAppObj().bracketProductArchiveUrl
  await bracketApi.generatePlayImages(playId)
  window.location.href = redirectUrl
}
