import { PostBase } from '../api/types/bracket'
import { BracketRes, PlayRes } from '../index'

export const getPlayBracketUrl = (bracket: BracketRes) => {
  const baseUrl = getPostUrlForPath(bracket, 'play')
  if (bracket?.fee && bracket.fee > 0) {
    return `${baseUrl}?has_fee=true`
  }
  return baseUrl
}

export const getBracketResultsUrl = (bracket: BracketRes) => {
  return getPostUrlForPath(bracket, 'results')
}

export const getReplayPlayUrl = (play: PlayRes) => {
  return getPostUrlForPath(play, 'replay')
}

const getPostUrlForPath = (post: PostBase, path: string) => {
  if (!post?.url) {
    return ''
  }
  return `${post.url}${path}`
}
