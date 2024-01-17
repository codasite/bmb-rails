import { bracketApi } from '../../shared/api/bracketApi'

export const addToApparelHandler = async (
  playId: number,
  redirectUrl: string
) => {
  await bracketApi.generatePlayImages(playId)
  window.location.href = redirectUrl
}
