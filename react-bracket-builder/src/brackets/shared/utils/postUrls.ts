import { PostBase } from '../api/types/bracket'
import { BracketRes, PlayRes } from '../index'

export const getPlayBracketUrl = (bracket: BracketRes) => {
  return getPostUrlForPath(bracket, 'play')
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
