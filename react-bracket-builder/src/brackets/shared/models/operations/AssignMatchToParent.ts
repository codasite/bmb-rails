import { MatchNode } from './MatchNode'

export const assignMatchToParent = (
  matchIndex: number,
  match: MatchNode,
  parent: MatchNode | null
) => {
  if (parent === null) {
    return
  }
  if (matchIndex % 2 === 0) {
    parent.left = match
  } else {
    parent.right = match
  }
}
