import { BracketData } from './dashboard/brackets/BracketData'

export const parseBracketDataFromEl = (el: HTMLElement): BracketData => {
  return {
    id: el.dataset.bracketId ? parseInt(el.dataset.bracketId) : undefined,
    title: el.dataset.bracketTitle,
    month: el.dataset.bracketMonth,
    year: el.dataset.bracketYear,
    fee: el.dataset.fee ? parseInt(el.dataset.fee) : undefined,
    playBracketUrl: el.dataset.playBracketUrl,
    copyBracketUrl: el.dataset.copyBracketUrl,
    mostPopularPicksUrl: el.dataset.mostPopularPicksUrl,
    goLiveUrl: el.dataset.goLiveUrl,
    liveRoundIndex: el.dataset.liveRoundIndex
      ? parseInt(el.dataset.liveRoundIndex)
      : undefined,
    isFinalRound: el.dataset.isFinalRound === 'true',
    notificationId: el.dataset.notificationId
      ? parseInt(el.dataset.notificationId)
      : undefined,
  }
}
