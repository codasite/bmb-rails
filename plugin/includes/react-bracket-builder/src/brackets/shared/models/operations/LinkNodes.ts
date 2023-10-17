import { Round } from '../Round'
import { getParent } from './GetParent'
import { assignMatchToParent } from './AssignMatchToParent'

export const linkNodes = (rounds: Round[]) => {
  rounds.forEach((round, roundIndex) => {
    round.matches.forEach((match, matchIndex) => {
      if (!match) {
        return
      }
      const parent = getParent(matchIndex, roundIndex, rounds)
      match.parent = parent
      assignMatchToParent(matchIndex, match, parent)
    })
  })
}
