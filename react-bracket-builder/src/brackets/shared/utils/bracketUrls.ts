import { BracketRes } from '../index'

export const getPlayBracketUrl = (bracket: BracketRes) => {
  return getBracketUrlForPath(bracket, 'play')
}

export const getBracketResultsUrl = (bracket: BracketRes) => {
  return getBracketUrlForPath(bracket, 'results')
}

const getBracketUrlForPath = (bracket: BracketRes, path: string) => {
  if (!bracket?.url) {
    return ''
  }
  return `${bracket.url}${path}`
}
