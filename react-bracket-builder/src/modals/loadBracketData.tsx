import { BracketData } from './dashboard/brackets/BracketData'

export const loadBracketData = (
  el: HTMLElement,
  setBracketData: (data: BracketData) => void
) => {
  setBracketData({
    id: el.dataset.bracketId ? parseInt(el.dataset.bracketId) : undefined,
    title: el.dataset.bracketTitle,
    month: el.dataset.bracketMonth,
    year: el.dataset.bracketYear,
    fee: el.dataset.fee ? parseInt(el.dataset.fee) : undefined,
    playBracketUrl: el.dataset.playBracketUrl,
    copyBracketUrl: el.dataset.copyBracketUrl,
    mostPopularPicksUrl: el.dataset.mostPopularPicksUrl,
    goLiveUrl: el.dataset.goLiveUrl,
  })
}
