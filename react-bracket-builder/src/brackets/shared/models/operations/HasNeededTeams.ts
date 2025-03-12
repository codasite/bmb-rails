import { MatchNode } from './MatchNode'

export const hasNeededTeams = (match: MatchNode): boolean => {
  let hasNeededTeams = true
  if (!match) {
    return true
  }
  if (!match.left && !match.getTeam1()?.name) {
    hasNeededTeams = false
  }
  if (hasNeededTeams && !match.right && !match.getTeam2()?.name) {
    hasNeededTeams = false
  }
  return hasNeededTeams
}
